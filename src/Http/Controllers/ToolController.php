<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\Workflow;
use Opscale\NovaServiceDesk\Services\Actions\ChangeStatus;

class ToolController extends Controller
{
    /**
     * The slug used for the default (TaskStatus-based) workflow.
     */
    public const DEFAULT_WORKFLOW_SLUG = 'default';

    /**
     * Get all available workflows, including the default TaskStatus workflow.
     */
    public function getWorkflows(): JsonResponse
    {
        $workflows = [
            [
                'slug' => self::DEFAULT_WORKFLOW_SLUG,
                'name' => __('Default'),
                'description' => __('Tasks without a custom workflow'),
                'is_default' => true,
                'stages' => $this->defaultStages(),
            ],
        ];

        Workflow::with('stages')->get()->each(function (Workflow $workflow) use (&$workflows) {
            $workflows[] = [
                'slug' => $workflow->slug,
                'name' => $workflow->name,
                'description' => $workflow->description,
                'is_default' => false,
                'stages' => $workflow->stages->map(fn ($stage) => [
                    'id' => $stage->id,
                    'name' => $stage->name,
                    'description' => $stage->description,
                    'color' => $stage->color,
                    'maps_to_status' => $stage->maps_to_status->value,
                ])->values()->all(),
            ];
        });

        return response()->json($workflows);
    }

    /**
     * Get tasks scoped to the requested workflow.
     */
    public function index(Request $request): JsonResponse
    {
        $slug = $request->query('workflow', self::DEFAULT_WORKFLOW_SLUG);

        $query = Task::active()->orderByPriority();

        if ($slug === self::DEFAULT_WORKFLOW_SLUG) {
            $query->whereNull('workflow_id');
        } else {
            $workflow = Workflow::where('slug', $slug)->firstOrFail();
            $query->where('workflow_id', $workflow->id);
        }

        $tasks = $query->get()->map(function (Task $task) {
            return [
                'id' => $task->id,
                'key' => $task->key,
                'title' => $task->title,
                'status' => $task->status->value,
                'status_alias' => $task->status_alias,
                'workflow_id' => $task->workflow_id,
                'workflow_stage_id' => $task->workflow_stage_id,
                'priority' => $task->priority?->value,
                'priority_score' => $task->priority_score,
                'due_date' => $task->due_date?->toIso8601String(),
            ];
        });

        return response()->json($tasks);
    }

    /**
     * Transition a task. Delegates to the ChangeStatus Opscale Action,
     * which handles both workflow stage transitions and direct status changes.
     */
    public function transition(Request $request, string $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        $attributes = ['task' => $task];

        if ($task->workflow_id) {
            $request->validate(['stage_id' => 'required|string']);
            $attributes['stage_id'] = $request->input('stage_id');
        } else {
            $request->validate(['status' => 'required|string']);
            $attributes['status'] = $request->input('status');
        }

        $result = ChangeStatus::run($attributes);

        if (empty($result['success'])) {
            return response()->json([
                'message' => $result['message'] ?? __('Transition failed.'),
            ], 422);
        }

        $task = $result['task'];

        return response()->json([
            'message' => $result['message'],
            'task' => [
                'id' => $task->id,
                'key' => $task->key,
                'title' => $task->title,
                'status' => $task->status->value,
                'status_alias' => $task->status_alias,
                'workflow_id' => $task->workflow_id,
                'workflow_stage_id' => $task->workflow_stage_id,
                'priority' => $task->priority?->value,
            ],
        ]);
    }

    /**
     * Build the synthetic stages array for the default TaskStatus workflow.
     */
    protected function defaultStages(): array
    {
        $colors = [
            TaskStatus::Open->value => 'info',
            TaskStatus::InProgress->value => 'warning',
            TaskStatus::Blocked->value => 'danger',
            TaskStatus::Resolved->value => 'success',
            TaskStatus::Closed->value => 'success',
            TaskStatus::Cancelled->value => 'danger',
        ];

        return collect(TaskStatus::cases())->map(fn (TaskStatus $status) => [
            'id' => $status->value,
            'name' => __($status->value),
            'description' => null,
            'color' => $colors[$status->value] ?? 'info',
            'maps_to_status' => $status->value,
        ])->all();
    }
}
