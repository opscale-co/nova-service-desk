<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Opscale\NovaServiceDesk\Contracts\WorkflowResolver;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

trait TaskRepository
{
    /**
     * Boot the trait.
     */
    public static function bootTaskRepository(): void
    {
        static::updating(function ($model) {
            // Workflow-driven transition: validate via WorkflowResolver
            if ($model->isDirty('workflow_stage_id') && $model->workflow_stage_id !== null) {
                $targetStage = WorkflowStage::find($model->workflow_stage_id);

                if (! $targetStage) {
                    abort(422, __('Invalid workflow stage.'));
                }

                $templateKey = strtoupper(substr($model->key ?? '', 0, 3));
                $resolver = static::resolveWorkflowResolver($templateKey);

                if ($resolver) {
                    if (! $resolver->canTransitionTo($model, $targetStage)) {
                        abort(422, $resolver->message());
                    }
                }

                // Sync master status and alias from stage
                $model->status = $targetStage->maps_to_status;
                $model->status_alias = $targetStage->name;

                return;
            }

            // Legacy path: validate TaskStatus enum transitions
            if ($model->isDirty('status') && $model->workflow_stage_id === null) {
                $original = $model->getOriginal('status');
                $currentStatus = $original instanceof TaskStatus ? $original : TaskStatus::from($original);

                $new = $model->status;
                $newStatus = $new instanceof TaskStatus ? $new : TaskStatus::from($new);

                if (! $currentStatus->canTransitionTo($newStatus)) {
                    abort(422, __('Invalid status transition from :current to :new.', [
                        'current' => $currentStatus->value,
                        'new' => $newStatus->value,
                    ]));
                }
            }
        });
    }

    /**
     * Resolve a WorkflowResolver for the given template key.
     */
    protected static function resolveWorkflowResolver(string $templateKey): ?WorkflowResolver
    {
        $resolvers = config('nova-service-desk.workflow_resolvers', []);

        if (isset($resolvers[$templateKey])) {
            return app($resolvers[$templateKey]);
        }

        return null;
    }

    /**
     * Scope a query to order tasks by priority.
     */
    public function scopeOrderByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE status
                WHEN 'In Progress' THEN 1
                WHEN 'Blocked' THEN 2
                WHEN 'Open' THEN 3
                WHEN 'Resolved' THEN 4
                WHEN 'Closed' THEN 5
                WHEN 'Cancelled' THEN 6
                ELSE 7
            END
        ")->orderBy('priority_score', 'desc');
    }

    /**
     * Scope a query to get only open tasks.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'Open');
    }

    /**
     * Scope a query to get only in progress tasks.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'In Progress');
    }

    /**
     * Scope a query to get only blocked tasks.
     */
    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', 'Blocked');
    }

    /**
     * Scope a query to get only resolved tasks.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'Resolved');
    }

    /**
     * Scope a query to get only closed tasks.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'Closed');
    }

    /**
     * Scope a query to get only cancelled tasks.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'Cancelled');
    }

    /**
     * Scope a query to get active tasks (not closed or cancelled).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['Closed', 'Cancelled']);
    }

    /**
     * Scope a query to get tasks by assignee.
     *
     * @param  mixed  $assignee
     */
    public function scopeForAssignee(Builder $query, $assignee): Builder
    {
        return $query->where('assignee_type', get_class($assignee))
            ->where('assignee_id', $assignee->id);
    }

    /**
     * Scope a query to get tasks by request.
     */
    public function scopeForRequest(Builder $query, string $requestId): Builder
    {
        return $query->where('request_id', $requestId);
    }

    /**
     * Scope a query to get overdue tasks.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', ['Closed', 'Cancelled']);
    }
}
