<?php

namespace Opscale\NovaServiceDesk\Services\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Request;
use Opscale\NovaServiceDesk\Models\Task;

class AssignTask extends Action
{
    public function identifier(): string
    {
        return 'assign-task';
    }

    public function name(): string
    {
        return 'Assign Task';
    }

    public function description(): string
    {
        return 'Assigns a new task for a service desk request';
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
                'rules' => ['nullable', 'string'],
            ],
            [
                'name' => 'assignee_id',
                'description' => 'The ID of the user to assign the task to',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        /** @var Request $request */
        $request = $validatedData['request'];

        $task = new Task;
        $task->fill([
            'request_id' => $request->id,
            'title' => $validatedData['title'],
            'description' => $validatedData['description'] ?? null,
            'status' => TaskStatus::Open,
            'assignee_id' => $validatedData['assignee_id'],
            'assigner_id' => Auth::id(),
        ]);

        $keyResult = GetTaskSequence::run([
            'subcategory_id' => $request->subcategory_id,
        ]);

        $task->key = $keyResult['sequence'];

        $priorityResult = CalculatePriority::run([
            'task' => $task,
        ]);

        $task->priority = $priorityResult['priority'];
        $task->priority_score = $priorityResult['score'];

        $dueDateResult = CalculateDueDate::run([
            'task' => $task,
        ]);

        $task->due_date = $dueDateResult['due_date'];

        $task->save();

        $request->update(['assigned' => true]);

        return [
            'success' => true,
            'task' => $task,
            'message' => __('Task assigned successfully'),
        ];
    }

    public function asNovaAction(ActionFields $fields, Collection $models): mixed
    {
        try {
            $attributes = $fields->toArray();
            $attributes['request'] = $models->first();

            $this->fill($attributes);
            $validatedData = $this->validateAttributes();

            $result = $this->handle($validatedData);

            if (empty($result) || ! $result['success']) {
                return Action::danger($result['message'] ?? 'Something went wrong while executing the action.');
            }

            $task = $result['task'];

            return Action::visit("/resources/tasks/{$task->id}");
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->errors() as $field => $messages) {
                $errors[] = "{$field}: " . implode(', ', $messages);
            }

            return Action::danger(implode("\n", $errors));
        } catch (Throwable $e) {
            return Action::danger($e->getMessage());
        }
    }

    public function getActionFields(): array
    {
        $userModel = config('auth.providers.users.model');
        $users = $userModel::all()->pluck('name', 'id')->toArray();

        return [
            Text::make(__('Title'), 'title')
                ->rules('required', 'string', 'max:255'),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string'),

            Select::make(__('Assignee'), 'assignee_id')
                ->options($users)
                ->searchable()
                ->nullable(),
        ];
    }
}
