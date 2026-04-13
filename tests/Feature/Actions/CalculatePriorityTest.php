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
use Opscale\NovaServiceDesk\Services\Actions\CalculatePriority;
use Workbench\App\Models\User;

beforeEach(function (): void {
    $this->category = Category::create([
        'name' => 'IT Support',
        'key' => 'ITS',
    ]);

    $this->account = Account::create([
        'customer_type' => User::class,
        'customer_id' => '01test000000000000000000001',
        'profile' => 'Test Account',
    ]);

    $this->template = Template::create([
        'singular_label' => 'IT Request',
        'label' => 'IT Requests',
        'uri_key' => 'it-requests',
    ]);

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('calculates Critical priority for High impact and High urgency', function (): void {
    $subcategory = Subcategory::create([
        'category_id' => $this->category->id,
        'name' => 'Hardware Failure',
        'key' => 'ITS-01',
        'impact' => SLAPriority::High->value,
        'urgency' => SLAPriority::High->value,
    ]);

    $request = Request::create([
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'subcategory_id' => $subcategory->id,
        'template_id' => $this->template->id,
    ]);

    $task = Task::create([
        'request_id' => $request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-01-000001',
        'description' => 'Test',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = CalculatePriority::run(['task' => $task]);

    expect($result['success'])->toBeTrue();
    expect($result['priority'])->toBe(SLAPriority::Critical->value);
    expect($result['score'])->toBe(1.0);
});

it('calculates Medium priority for Medium impact and Medium urgency', function (): void {
    $subcategory = Subcategory::create([
        'category_id' => $this->category->id,
        'name' => 'Software Issue',
        'key' => 'ITS-02',
        'impact' => SLAPriority::Medium->value,
        'urgency' => SLAPriority::Medium->value,
    ]);

    $request = Request::create([
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'subcategory_id' => $subcategory->id,
        'template_id' => $this->template->id,
    ]);

    $task = Task::create([
        'request_id' => $request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-02-000001',
        'description' => 'Test',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = CalculatePriority::run(['task' => $task]);

    expect($result['success'])->toBeTrue();
    expect($result['priority'])->toBe(SLAPriority::Medium->value);
    expect($result['score'])->toBe(0.5);
});

it('calculates Planning priority for Low impact and Low urgency', function (): void {
    $subcategory = Subcategory::create([
        'category_id' => $this->category->id,
        'name' => 'Documentation',
        'key' => 'ITS-03',
        'impact' => SLAPriority::Low->value,
        'urgency' => SLAPriority::Low->value,
    ]);

    $request = Request::create([
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'subcategory_id' => $subcategory->id,
        'template_id' => $this->template->id,
    ]);

    $task = Task::create([
        'request_id' => $request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-03-000001',
        'description' => 'Test',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = CalculatePriority::run(['task' => $task]);

    expect($result['success'])->toBeTrue();
    expect($result['priority'])->toBe(SLAPriority::Planning->value);
    expect($result['score'])->toBe(0.0);
});

it('defaults to Medium when subcategory has no impact/urgency', function (): void {
    $subcategory = Subcategory::create([
        'category_id' => $this->category->id,
        'name' => 'General',
        'key' => 'ITS-04',
    ]);

    $request = Request::create([
        'account_id' => $this->account->id,
        'category_id' => $this->category->id,
        'subcategory_id' => $subcategory->id,
        'template_id' => $this->template->id,
    ]);

    $task = Task::create([
        'request_id' => $request->id,
        'assignee_id' => $this->user->id,
        'assigner_id' => $this->user->id,
        'title' => 'Test Task',
        'key' => 'ITS-04-000001',
        'description' => 'Test',
        'status' => TaskStatus::Open,
        'priority' => SLAPriority::Medium,
        'priority_score' => 0.5,
    ]);

    $result = CalculatePriority::run(['task' => $task]);

    expect($result['success'])->toBeTrue();
    expect($result['priority'])->toBe(SLAPriority::Medium->value);
});
