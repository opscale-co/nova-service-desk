<?php

declare(strict_types=1);

namespace Workbench\App\Resolvers;

use Opscale\NovaServiceDesk\Contracts\WorkflowResolver;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

/**
 * Example WorkflowResolver for the seeded "Technical Support" workflow (key TEC).
 *
 * Defines the allowed transitions between named stages following a typical
 * IT support lifecycle. Stages are looked up by name within the task's
 * workflow so the resolver remains portable across environments where the
 * stage IDs (ULIDs) differ.
 *
 * Stage map:
 *   New             → Triaged | Cancelled (master)
 *   Triaged         → In Progress | Cancelled
 *   In Progress     → Waiting on Customer | Escalated | Resolved
 *   Waiting on Customer → In Progress | Cancelled
 *   Escalated       → In Progress | Resolved
 *   Resolved        → Closed | In Progress
 *   Closed          → (terminal)
 */
class TechnicalSupportWorkflowResolver implements WorkflowResolver
{
    /**
     * Allowed next stages by current stage name.
     *
     * @var array<string, array<int, string>>
     */
    protected array $transitions = [
        'New' => ['Triaged'],
        'Triaged' => ['In Progress'],
        'In Progress' => ['Waiting on Customer', 'Escalated', 'Resolved'],
        'Waiting on Customer' => ['In Progress'],
        'Escalated' => ['In Progress', 'Resolved'],
        'Resolved' => ['Closed', 'In Progress'],
        'Closed' => [],
    ];

    /**
     * The error message to surface when a transition is denied.
     */
    protected string $message = '';

    /**
     * Get the IDs of the workflow stages the task can transition to.
     *
     * @return array<int, string>
     */
    public function allowedTransitions(Task $task, WorkflowStage $currentStage): array
    {
        $allowedNames = $this->transitions[$currentStage->name] ?? [];

        if ($allowedNames === []) {
            return [];
        }

        return WorkflowStage::query()
            ->where('workflow_id', $task->workflow_id)
            ->whereIn('name', $allowedNames)
            ->pluck('id')
            ->all();
    }

    /**
     * Decide whether the task can transition to the given target stage.
     */
    public function canTransitionTo(Task $task, WorkflowStage $targetStage): bool
    {
        $currentStage = $task->workflowStage;

        if (! $currentStage) {
            $this->message = __('Task has no current stage.');

            return false;
        }

        $allowedNames = $this->transitions[$currentStage->name] ?? [];

        if (! in_array($targetStage->name, $allowedNames, true)) {
            $this->message = __('Cannot transition from :from to :to.', [
                'from' => $currentStage->name,
                'to' => $targetStage->name,
            ]);

            return false;
        }

        // Example guard: prevent escalation without an assignee.
        if ($targetStage->name === 'Escalated' && empty($task->assignee_id)) {
            $this->message = __('A task must have an assignee before it can be escalated.');

            return false;
        }

        return true;
    }

    /**
     * Get the reason a transition was denied.
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * Bump the priority score for tasks that hit the SLA contact deadline,
     * so they bubble up in the orderByPriority queue. Returns null to fall
     * back to CalculatePriority's default mapping otherwise.
     */
    public function priorityScore(Task $task): ?float
    {
        if ($task->due_date && $task->due_date->isPast()) {
            return 1.0;
        }

        return null;
    }
}
