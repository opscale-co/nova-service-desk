<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Account;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Request;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\Template;
use Opscale\NovaServiceDesk\Models\Workflow;
use Opscale\NovaServiceDesk\Models\WorkflowStage;
use Opscale\NovaServiceDesk\Services\Actions\ChangeStatus;
use Workbench\App\Models\User;

beforeEach(function (): void {
    $category = Category::create([
        'name' => 'IT Support',
        'key' => 'ITS',
    ]);

    $subcategory = Subcategory::create([
        'category_id' => $category->id,
        'name' => 'Hardware',
        'key' => 'ITS-01',
    ]);

    $account = Account::create([
        'customer_type' => User::class,
        'customer_id' => '01test000000000000000000001',
        'profile' => 'Test Account',
    ]);

    $template = Template::create([
        'singular_label' => 'IT Request',
        'label' => 'IT Requests',
        'uri_key' => 'it-requests',
    ]);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->request = Request::create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'template_id' => $template->id,
    ]);

    $this->workflow = Workflow::create([
        'name' => 'Default Workflow',
        'slug' => 'default-workflow',
        'key' => 'DEF',
    ]);

    $this->stageOpen = WorkflowStage::create([
        'workflow_id' => $this->workflow->id,
        'name' => 'Open',
        'maps_to_status' => TaskStatus::Open->value,
    ]);

    $this->stageInProgress = WorkflowStage::create([
        'workflow_id' => $this->workflow->id,
        'name' => 'In Progress',
        'maps_to_status' => TaskStatus::InProgress->value,
    ]);
});

// --- Workflow-based transitions ---

it('transitions a workflow task to a new stage', function (): void {
    $task = Task::create([
        'request_id' => $this->request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000001',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
        'workflow_id' => $this->workflow->id,
        'workflow_stage_id' => $this->stageOpen->id,
    ]);

    $result = ChangeStatus::run([
        'task' => $task,
        'stage_id' => $this->stageInProgress->id,
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['task']->workflow_stage_id)->toBe($this->stageInProgress->id);
    expect($result['task']->status->value)->toBe(TaskStatus::InProgress->value);
    expect($result['task']->status_alias)->toBe('In Progress');
});

it('fails when stage does not exist', function (): void {
    $task = Task::create([
        'request_id' => $this->request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000001',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
        'workflow_id' => $this->workflow->id,
        'workflow_stage_id' => $this->stageOpen->id,
    ]);

    $result = ChangeStatus::run([
        'task' => $task,
        'stage_id' => 'non-existent-id',
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('Invalid workflow stage');
});

it('fails when stage belongs to a different workflow', function (): void {
    $task = Task::create([
        'request_id' => $this->request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000001',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
        'workflow_id' => $this->workflow->id,
        'workflow_stage_id' => $this->stageOpen->id,
    ]);

    $otherWorkflow = Workflow::create([
        'name' => 'Other Workflow',
        'slug' => 'other-workflow',
        'key' => 'OTH',
    ]);

    $otherStage = WorkflowStage::create([
        'workflow_id' => $otherWorkflow->id,
        'name' => 'Other Stage',
        'maps_to_status' => TaskStatus::Open->value,
    ]);

    $result = ChangeStatus::run([
        'task' => $task,
        'stage_id' => $otherStage->id,
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('does not belong');
});

// --- Status-based transitions (no workflow) ---

it('transitions a non-workflow task by status', function (): void {
    $task = Task::create([
        'request_id' => $this->request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000002',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = ChangeStatus::run([
        'task' => $task,
        'status' => TaskStatus::InProgress->value,
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['task']->status->value)->toBe(TaskStatus::InProgress->value);
});

it('rejects invalid status transitions', function (): void {
    $task = Task::create([
        'request_id' => $this->request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000003',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = ChangeStatus::run([
        'task' => $task,
        'status' => TaskStatus::Closed->value,
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('Cannot transition');
});

it('rejects transition with invalid status value', function (): void {
    $task = Task::create([
        'request_id' => $this->request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000004',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = ChangeStatus::run([
        'task' => $task,
        'status' => 'NonExistent',
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('Invalid status');
});
