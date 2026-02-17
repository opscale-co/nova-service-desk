<?php

namespace Opscale\NovaServiceDesk\Contracts;

use Opscale\NovaServiceDesk\Models\Task;

interface StatusesResolver
{
    /**
     * Get the custom statuses mapping.
     *
     * Returns an array where the key is a TaskStatus and the value is an array
     * of custom status strings. If more than one value is defined, they are
     * assumed to move sequentially.
     *
     * Example:
     * [
     *     TaskStatus::Open->value => ['New', 'Triaged'],
     *     TaskStatus::InProgress->value => ['Investigating', 'Implementing', 'Testing'],
     *     TaskStatus::Resolved->value => ['Pending Approval'],
     * ]
     *
     * @return array<string, array<string>>
     */
    public function getStatuses(): array;

    /**
     * Check if a transition to the new status is allowed.
     *
     * This method can be used for custom logic for closing, cancelling,
     * or resolving tasks based on specific business rules.
     */
    public function canTransitionTo(Task $task, string $newStatus): bool;
}
