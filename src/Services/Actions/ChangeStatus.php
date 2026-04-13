<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Services\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Contracts\WorkflowResolver;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

class ChangeStatus extends Action
{
    public function identifier(): string
    {
        return 'change-status';
    }

    public function name(): string
    {
        return __('Change Status');
    }

    public function description(): string
    {
        return __('Transitions a task to a new status or workflow stage');
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'task',
                'description' => 'The task to transition',
                'type' => Task::class,
                'rules' => ['required'],
            ],
            [
                'name' => 'stage_id',
                'description' => 'The target workflow stage ID (for workflow tasks)',
                'type' => 'string',
                'rules' => ['nullable', 'string'],
            ],
            [
                'name' => 'status',
                'description' => 'The target status (for tasks without workflow)',
                'type' => 'string',
                'rules' => ['nullable', 'string'],
            ],
        ];
    }

    public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validatedData = $this->validateAttributes();

        /** @var Task $task */
        $task = $validatedData['task'];

        if ($task->workflow_id) {
            return $this->transitionByWorkflow($task, $validatedData['stage_id'] ?? null);
        }

        return $this->transitionByStatus($task, $validatedData['status'] ?? null);
    }

    // --- Nova integration ---

    public function asNovaAction(ActionFields $fields, Collection $models): mixed
    {
        foreach ($models as $task) {
            $attributes = ['task' => $task];

            if ($task->workflow_id) {
                $attributes['stage_id'] = $fields->stage_id;
            } else {
                $attributes['status'] = $fields->status;
            }

            $result = static::run($attributes);

            if (empty($result) || ! $result['success']) {
                return $this->danger($result['message'] ?? __('Something went wrong while executing the action.'));
            }
        }

        return $this->message(__('Status updated successfully'));
    }

    public function getActionFields(): array
    {
        $request = app(NovaRequest::class);
        $taskId = $this->resolveTaskIdFromRequest($request);

        if ($taskId) {
            $task = Task::with(['workflow.stages', 'workflowStage'])->find($taskId);

            if ($task) {
                return $task->workflow_id
                    ? [$this->stageField($task)]
                    : [$this->statusField($task)];
            }
        }

        return [$this->defaultStatusField()];
    }

    /**
     * Transition a task using workflow stages.
     */
    protected function transitionByWorkflow(Task $task, ?string $stageId): array
    {
        if (! $stageId) {
            return [
                'success' => false,
                'message' => __('A stage is required for workflow tasks.'),
            ];
        }

        $targetStage = WorkflowStage::find($stageId);

        if (! $targetStage) {
            return [
                'success' => false,
                'message' => __('Invalid workflow stage.'),
            ];
        }

        if ($targetStage->workflow_id !== $task->workflow_id) {
            return [
                'success' => false,
                'message' => __('Stage does not belong to the task workflow.'),
            ];
        }

        $templateKey = strtoupper(substr($task->key ?? '', 0, 3));
        $resolver = $this->resolveWorkflowResolver($templateKey);

        if ($resolver && ! $resolver->canTransitionTo($task, $targetStage)) {
            return [
                'success' => false,
                'message' => $resolver->message(),
            ];
        }

        $task->update([
            'workflow_stage_id' => $targetStage->id,
            'status' => $targetStage->maps_to_status,
            'status_alias' => $targetStage->name,
        ]);

        return [
            'success' => true,
            'task' => $task->fresh(),
            'message' => __('Task transitioned successfully'),
        ];
    }

    /**
     * Transition a task using direct status change (no workflow).
     */
    protected function transitionByStatus(Task $task, ?string $status): array
    {
        if (! $status) {
            return [
                'success' => false,
                'message' => __('A status is required.'),
            ];
        }

        $targetStatus = TaskStatus::tryFrom($status);

        if (! $targetStatus) {
            return [
                'success' => false,
                'message' => __('Invalid status.'),
            ];
        }

        if (! $task->status->canTransitionTo($targetStatus)) {
            return [
                'success' => false,
                'message' => __('Cannot transition from :from to :to.', [
                    'from' => $task->status->value,
                    'to' => $targetStatus->value,
                ]),
            ];
        }

        $task->update([
            'status' => $targetStatus,
        ]);

        return [
            'success' => true,
            'task' => $task->fresh(),
            'message' => __('Status updated successfully'),
        ];
    }

    /**
     * Resolve the first selected task ID from the Nova request.
     *
     * Nova passes the target resource ID under different keys depending
     * on the context that triggered the action fields lookup:
     *   - Detail page fetching available actions → `resourceId` (singular)
     *   - Action submission from index/detail → `resources[]` (array) or
     *     comma-separated string
     *   - Related resource context → `viaResourceId`
     *   - "Run on all" submissions → `'all'` (no specific ID)
     */
    protected function resolveTaskIdFromRequest(NovaRequest $request): ?string
    {
        $candidates = [
            $request->input('resourceId'),
            $request->input('resources'),
            $request->input('viaResourceId'),
            $request->route('resourceId'),
        ];

        foreach ($candidates as $candidate) {
            $id = $this->normalizeResourceId($candidate);

            if ($id !== null) {
                return $id;
            }
        }

        return null;
    }

    /**
     * Normalize a resource identifier coming from a Nova request input.
     * Returns null for empty values, the "all" sentinel and arrays that
     * cannot be reduced to a single id.
     */
    protected function normalizeResourceId(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        if (is_array($value)) {
            $first = reset($value);

            return $first === false || $first === '' ? null : (string) $first;
        }

        $parts = explode(',', (string) $value);
        $first = $parts[0] ?? null;

        return $first === null || $first === '' ? null : $first;
    }

    protected function stageField(Task $task): Select
    {
        $templateKey = strtoupper(substr($task->key ?? '', 0, 3));
        $resolver = $this->resolveWorkflowResolver($templateKey);

        if ($resolver && $task->workflowStage) {
            $allowedIds = $resolver->allowedTransitions($task, $task->workflowStage);

            $stages = WorkflowStage::where('workflow_id', $task->workflow_id)
                ->whereIn('id', $allowedIds)
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $stages = WorkflowStage::where('workflow_id', $task->workflow_id)
                ->where('id', '!=', $task->workflow_stage_id)
                ->pluck('name', 'id')
                ->toArray();
        }

        return Select::make(__('Stage'), 'stage_id')
            ->options($stages)
            ->rules('required');
    }

    protected function statusField(Task $task): Select
    {
        $allowed = $task->status->allowedTransitions();
        $options = collect($allowed)->mapWithKeys(fn (TaskStatus $s) => [$s->value => __($s->value)])->toArray();

        return Select::make(__('Status'), 'status')
            ->options($options)
            ->rules('required');
    }

    protected function defaultStatusField(): Select
    {
        $statuses = collect(TaskStatus::cases())->mapWithKeys(fn ($case) => [$case->value => __($case->value)])->toArray();

        return Select::make(__('Status'), 'status')
            ->options($statuses)
            ->rules('required');
    }

    protected function resolveWorkflowResolver(string $templateKey): ?WorkflowResolver
    {
        $resolvers = config('nova-service-desk.workflow_resolvers', []);

        if (isset($resolvers[$templateKey])) {
            return app($resolvers[$templateKey]);
        }

        return null;
    }
}
