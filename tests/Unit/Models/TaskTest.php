<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Task;

it('has the correct table name', function (): void {
    $task = new Task;
    expect($task->getTable())->toBe('service_desk_tasks');
});

it('has the correct fillable attributes', function (): void {
    $task = new Task;
    expect($task->getFillable())->toContain('request_id')
        ->toContain('assignee_id')
        ->toContain('title')
        ->toContain('status')
        ->toContain('priority')
        ->toContain('due_date')
        ->toContain('workflow_id')
        ->toContain('workflow_stage_id');
});

it('casts status to TaskStatus enum', function (): void {
    $task = new Task;
    $casts = $task->getCasts();
    expect($casts['status'])->toBe(TaskStatus::class);
});

it('casts priority to SLAPriority enum', function (): void {
    $task = new Task;
    $casts = $task->getCasts();
    expect($casts['priority'])->toBe(SLAPriority::class);
});

it('casts due_date to datetime', function (): void {
    $task = new Task;
    $casts = $task->getCasts();
    expect($casts['due_date'])->toBe('datetime');
});
