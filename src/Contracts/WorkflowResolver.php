<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Contracts;

use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

interface WorkflowResolver
{
    /**
     * Get allowed target stages from the current stage.
     * Returns stage IDs the task can transition to.
     *
     * @return array<string>
     */
    public function allowedTransitions(Task $task, WorkflowStage $currentStage): array;

    /**
     * Check if a specific transition is allowed.
     * Use for guard logic (e.g., required fields, role checks).
     */
    public function canTransitionTo(Task $task, WorkflowStage $targetStage): bool;

    /**
     * Get the reason a transition was denied (for error messages).
     */
    public function message(): string;
}
