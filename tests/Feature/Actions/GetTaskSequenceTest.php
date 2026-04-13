<?php

declare(strict_types=1);

use Opscale\NovaServiceDesk\Models\Account;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Request;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\Template;
use Opscale\NovaServiceDesk\Services\Actions\GetTaskSequence;
use Workbench\App\Models\User;

it('generates the first task sequence for a subcategory', function (): void {
    $category = Category::create([
        'name' => 'IT Support',
        'key' => 'ITS',
    ]);

    $subcategory = Subcategory::create([
        'category_id' => $category->id,
        'name' => 'Hardware',
        'key' => 'ITS-01',
    ]);

    $result = GetTaskSequence::run([
        'subcategory_id' => $subcategory->id,
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['sequence'])->toBe('ITS-01-000001');
});

it('increments the task sequence', function (): void {
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

    $request = Request::create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'template_id' => $template->id,
    ]);

    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Task::create([
        'request_id' => $request->id,
        'assignee_id' => $user->id,
        'assigner_id' => $user->id,
        'title' => 'Existing Task',
        'key' => 'ITS-01-000001',
        'description' => 'Test description',
        'status' => TaskStatus::Open,
        'priority' => 'Medium',
        'priority_score' => 0.5,
    ]);

    $result = GetTaskSequence::run([
        'subcategory_id' => $subcategory->id,
    ]);

    expect($result['success'])->toBeTrue();
    expect($result['sequence'])->toBe('ITS-01-000002');
});

it('returns failure when subcategory does not exist', function (): void {
    $result = GetTaskSequence::run([
        'subcategory_id' => 'non-existent-id',
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['sequence'])->toBeNull();
});
