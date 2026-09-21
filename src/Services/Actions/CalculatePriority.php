<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Services\Actions;

use Opscale\Actions\Action;
use Opscale\NovaServiceDesk\Contracts\WorkflowResolver;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Task;

class CalculatePriority extends Action
{
    public function identifier(): string
    {
        return 'calculate-priority';
    }

    public function name(): string
    {
        return 'Calculate Priority';
    }

    public function description(): string
    {
        return "Calculates ITIL-based priority from task's request subcategory impact and urgency";
    }

    public function parameters(): array
    {
        return [
            [
                'name' => 'task',
                'description' => 'The task to calculate priority for',
                'type' => Task::class,
                'rules' => ['required'],
            ],
        ];
    }

    public function outputs(): array
    {
        return [
            [
                'name' => 'priority',
                'description' => 'The calculated ITIL priority',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
            [
                'name' => 'score',
                'description' => 'The numeric priority score',
                'type' => 'float',
                'rules' => ['required', 'numeric'],
            ],
        ];
    }

    public function handle(array $inputs = []): array
    {
        /** @var Task $task */
        $task = $inputs['task'];
        $request = $task->request;
        $impact = SLAPriority::Medium->value;
        $urgency = SLAPriority::Medium->value;

        if ($request->subcategory_id) {
            $subcategory = Subcategory::query()->find($request->subcategory_id);
            if ($subcategory) {
                $impact = $subcategory->impact ?? SLAPriority::Medium->value;
                $urgency = $subcategory->urgency ?? SLAPriority::Medium->value;
            }
        }

        $slaPriority = $this->getPriority($impact, $urgency);
        $score = $this->resolveScore($task, $slaPriority);

        return $this->succeed([
            'priority' => $slaPriority->value,
            'score' => $score,
        ]);
    }

    /**
     * Resolve the priority score: first ask the workflow resolver (if any)
     * for a custom score, otherwise fall back to the default mapping.
     */
    protected function resolveScore(Task $task, SLAPriority $slaPriority): float
    {
        $templateKey = strtoupper(substr($task->key ?? '', 0, 3));
        $resolvers = config('nova-service-desk.workflow_resolvers', []);

        if (isset($resolvers[$templateKey])) {
            /** @var WorkflowResolver $resolver */
            $resolver = resolve($resolvers[$templateKey]);
            $custom = $resolver->priorityScore($task);

            if ($custom !== null) {
                return $custom;
            }
        }

        return $this->getScore($slaPriority);
    }

    /**
     * Get priority from impact and urgency.
     */
    protected function getPriority(string $impact, string $urgency, SLAPriority $slaPriority = SLAPriority::Medium): SLAPriority
    {
        $matrix = [
            'High' => [
                'High' => SLAPriority::Critical,
                'Medium' => SLAPriority::High,
                'Low' => SLAPriority::Medium,
            ],
            'Medium' => [
                'High' => SLAPriority::High,
                'Medium' => SLAPriority::Medium,
                'Low' => SLAPriority::Low,
            ],
            'Low' => [
                'High' => SLAPriority::Medium,
                'Medium' => SLAPriority::Low,
                'Low' => SLAPriority::Planning,
            ],
        ];

        return $matrix[$impact][$urgency] ?? $slaPriority;
    }

    /**
     * Get score from priority.
     */
    protected function getScore(SLAPriority $slaPriority, float $default = 0.5): float
    {
        return match ($slaPriority) {
            SLAPriority::Critical => 1.0,
            SLAPriority::High => 0.75,
            SLAPriority::Medium => 0.5,
            SLAPriority::Low => 0.25,
            SLAPriority::Planning => 0.0,
            default => $default,
        };
    }
}
