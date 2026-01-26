<?php

namespace Opscale\NovaServiceDesk\Models\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;

trait TaskRepository
{
    /**
     * Boot the trait.
     */
    public static function bootTaskRepository(): void
    {
        static::updating(function ($model) {
            if ($model->isDirty('status')) {
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
