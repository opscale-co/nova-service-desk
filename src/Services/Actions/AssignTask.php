<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Services\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Contracts\ProvidesService;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Request;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\Workflow;
use Throwable;

class AssignTask extends Action
{
    public function identifier(): string
    {
        return 'assign-task';
    }

    public function name(): string
    {
        return __('Assign Task');
    }

    public function description(): string
    {
        return __('Assigns a new task for a service desk request');
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'request',
                'description' => 'The request to assign the task for',
                'type' => Request::class,
                'rules' => ['required'],
            ],
            [
                'name' => 'title',
                'description' => 'The title of the task',
                'type' => 'string',
                'rules' => ['required', 'string', 'max:255'],
            ],
            [
                'name' => 'description',
                'description' => 'The description of the task',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'assignee_id',
                'description' => 'The ID of the user to assign the task to',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'workflow_id',
                'description' => 'The workflow to assign to the task',
                'type' => 'string',
                'rules' => ['nullable', 'string'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'task',
                'description' => 'The task that was assigned',
                'type' => Task::class,
                'rules' => ['required'],
            ],
        ];
    }

    public function handle(array $inputs = []): array
    {
        /** @var Request $request */
        $request = $inputs['request'];

        $task = new Task;
        $task->fill([
            'request_id' => $request->id,
            'title' => $inputs['title'],
            'description' => $inputs['description'],
            'status' => TaskStatus::Open,
            'assignee_id' => $inputs['assignee_id'],
            'assigner_id' => Auth::id(),
        ]);

        $keyResult = GetTaskSequence::run([
            'subcategory_id' => $request->subcategory_id,
        ]);

        $task->key = $keyResult->data()['sequence'] ?? null;

        $priorityResult = CalculatePriority::run([
            'task' => $task,
        ]);

        $task->priority = $priorityResult->data()['priority'] ?? null;
        $task->priority_score = $priorityResult->data()['score'] ?? null;

        $dueDateResult = CalculateDueDate::run([
            'task' => $task,
        ]);

        $task->due_date = $dueDateResult->data()['due_date'] ?? null;

        // Resolve workflow: use explicit selection, or fall back to template key resolution
        $workflow = null;

        if (! empty($inputs['workflow_id'])) {
            $workflow = Workflow::query()->find($inputs['workflow_id']);
        }

        if (! $workflow) {
            $templateKey = strtoupper(substr($task->key, 0, 3));
            $workflow = Workflow::resolveForTemplate($templateKey);
        }

        if ($workflow) {
            $initialStage = $workflow->initialStage();
            $task->workflow_id = $workflow->id;

            if ($initialStage) {
                $task->workflow_stage_id = $initialStage->id;
                $task->status = $initialStage->maps_to_status;
                $task->status_alias = $initialStage->name;
            }
        }

        $task->save();

        $request->update(['assigned' => true]);

        return $this->succeed([
            'task' => $task,
            'message' => __('Task assigned successfully'),
        ]);
    }

    #[\Override]
    public function asNovaAction(ActionFields $fields, Collection $models): mixed
    {
        try {
            $attributes = $fields->toArray();
            $attributes['request'] = $models->first();

            $result = static::run($attributes);

            if ($result->isFail()) {
                return $this->danger($result->message() ?? __('Something went wrong while executing the action.'));
            }

            $task = $result->data()['task'];

            return $this->visit("/resources/tasks/{$task->id}");
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = "{$field}: ".implode(', ', $messages);
            }

            return $this->danger(implode("\n", $errors));
        } catch (Throwable $e) {
            return $this->danger($e->getMessage());
        }
    }

    #[\Override]
    public function getActionFields(): array
    {
        $users = $this->serviceProviders();
        $workflows = Workflow::all()->pluck('name', 'id')->toArray();

        return [
            Text::make(__('Title'), 'title')
                ->rules('required', 'string', 'max:255'),

            Textarea::make(__('Description'), 'description')
                ->rules('required', 'string'),

            Select::make(__('Assignee'), 'assignee_id')
                ->options($users)
                ->searchable()
                ->nullable(),

            Select::make(__('Workflow'), 'workflow_id')
                ->options($workflows)
                ->searchable()
                ->nullable()
                ->help(__('Optional. If not selected, the workflow will be resolved automatically.')),
        ];
    }

    /**
     * Resolve the {@see ProvidesService} implementation from the container
     * and build the assignee list from `servingAgents()`. Returns an empty
     * list when no implementation is bound, so the action stays usable
     * without breaking the form.
     *
     * @return array<int|string, string>
     */
    protected function serviceProviders(): array
    {
        if (! app()->bound(ProvidesService::class)) {
            return [];
        }

        /** @var ProvidesService $providesService */
        $providesService = resolve(ProvidesService::class);

        return collect($providesService->servingAgents())
            ->unique(fn (Model $model) => $model->getKey())
            ->mapWithKeys(fn (Model $model): array => [$model->getKey() => (string) ($model->name ?? $model->getKey())])
            ->all();
    }
}
