<?php

namespace Opscale\NovaServiceDesk\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Task;

class ToolController extends Controller
{
    /**
     * Get all available task statuses.
     */
    public function getStatuses(): JsonResponse
    {
        $statuses = collect(TaskStatus::cases())->map(function ($status) {
            return [
                'key' => $status->value,
                'value' => __($status->value),
            ];
        });

        return response()->json($statuses);
    }

    /**
     * Get all tasks for the kanban board.
     */
    public function index(): JsonResponse
    {
        $tasks = Task::active()
            ->orderByPriority()
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'key' => $task->key,
                    'title' => $task->title,
                    'status' => $task->status->value,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date?->toDateString(),
                ];
            });

        return response()->json($tasks);
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:Open,In Progress,Blocked,Resolved,Closed,Cancelled',
        ]);

        $task = Task::findOrFail($id);
        $task->status = $request->status;
        $task->save();

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => [
                'id' => $task->id,
                'key' => $task->key,
                'title' => $task->title,
                'status' => $task->status->value,
                'priority' => $task->priority,
            ],
        ]);
    }
}
