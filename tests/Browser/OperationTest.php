<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Tests\Browser;

use Laravel\Dusk\Browser;
use Opscale\NovaServiceDesk\Models\Account;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\Enums\SLAPolicyStatus;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Request as RequestModel;
use Opscale\NovaServiceDesk\Models\SLAPolicy;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Task;
use Opscale\NovaServiceDesk\Models\Template;
use Opscale\NovaServiceDesk\Models\Workflow;
use Opscale\NovaServiceDesk\Models\WorkflowStage;
use Opscale\NovaServiceDesk\Tests\DuskTestCase;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\User;

/**
 * Operation test — exercises the daily ticket lifecycle on top of the
 * configuration created by ConfigurationTest:
 *   1. Create a request from the seeded "Support Tickets" template
 *   2. Assign a task to the request via the AssignTask action
 *   3. View the resulting task
 *   4. Transition the task across workflow stages via ChangeStatus
 *
 * The setUp ensures all fixtures are present (idempotent), so the test
 * can run on its own after a `workbench:build` even if ConfigurationTest
 * has not run yet.
 */
final class OperationTest extends DuskTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureFixtures();
    }

    #[Test]
    final public function creates_a_request_from_the_template(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/support-tickets/new')
                ->waitForText('Create Support Ticket')
                ->type('@title', 'Email server is down')
                ->press('Create Support Ticket')
                ->waitForText('Email server is down');
        });
    }

    #[Test]
    final public function assigns_a_task_to_the_request(): void
    {
        $request = RequestModel::latest('created_at')->firstOrFail();
        $assigneeId = (string) User::where('email', 'admin@laravel.com')->firstOrFail()->id;

        $this->browse(function (Browser $browser) use ($request, $assigneeId): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/support-tickets/'.$request->id)
                ->waitForText('Email server is down')
                ->press('Assign Task')
                ->waitForText('Title')
                ->type('@title', 'Investigate mail relay outage')
                ->type('@description', 'Customers cannot send or receive email since 09:00 UTC.')
                ->select('@assignee-id', $assigneeId)
                ->press('Run Action')
                ->waitForText('Task assigned successfully');
        });
    }

    #[Test]
    final public function views_the_assigned_task(): void
    {
        $task = Task::latest('created_at')->firstOrFail();

        $this->browse(function (Browser $browser) use ($task): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/tasks/'.$task->id)
                ->waitForText('Investigate mail relay outage')
                ->assertSee('Investigate mail relay outage')
                ->assertSee($task->key);
        });
    }

    #[Test]
    final public function transitions_the_task_between_stages(): void
    {
        $task = Task::latest('created_at')->firstOrFail();
        $triagedStageId = (string) WorkflowStage::query()
            ->whereHas('workflow', fn ($q) => $q->where('slug', 'technical-support'))
            ->where('name', 'Triaged')
            ->firstOrFail()
            ->id;

        $this->browse(function (Browser $browser) use ($task, $triagedStageId): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/tasks/'.$task->id)
                ->waitForText('Investigate mail relay outage')
                ->press('Change Status')
                ->waitForText('Stage')
                ->select('@stage-id', $triagedStageId)
                ->press('Run Action')
                ->waitForText('Status updated successfully');
        });
    }

    /**
     * Idempotently create everything the operation flow needs:
     *   - Category, Subcategory (matching the seeder values)
     *   - SLA Policies for every priority + linking to the Account
     *   - Workflow with all 7 stages + linking on the Account's category
     *   - Template preset categorization (account_id, category_id, subcategory_id)
     */
    private function ensureFixtures(): void
    {
        $category = Category::firstOrCreate(
            ['key' => 'TEC'],
            [
                'name' => 'Technical Support',
                'description' => 'Technical support issues and requests',
            ],
        );

        $subcategory = Subcategory::firstOrCreate(
            ['key' => 'TEC-001'],
            [
                'category_id' => $category->id,
                'name' => 'General Inquiry',
                'description' => 'General technical inquiries',
                'impact' => SLAPriority::Medium->value,
                'urgency' => SLAPriority::Medium->value,
            ],
        );

        $serviceTime = $this->defaultServiceTime();
        $supportedChannels = ['Web', 'Email', 'Phone'];

        $policiesByPriority = [
            ['name' => 'Critical Priority SLA', 'priority' => SLAPriority::Critical, 'contact' => 1, 'resolution' => 4],
            ['name' => 'High Priority SLA', 'priority' => SLAPriority::High, 'contact' => 2, 'resolution' => 8],
            ['name' => 'Medium Priority SLA', 'priority' => SLAPriority::Medium, 'contact' => 4, 'resolution' => 24],
            ['name' => 'Low Priority SLA', 'priority' => SLAPriority::Low, 'contact' => 8, 'resolution' => 48],
            ['name' => 'Planning Priority SLA', 'priority' => SLAPriority::Planning, 'contact' => 24, 'resolution' => 192],
        ];

        $createdPolicies = collect($policiesByPriority)->map(function (array $data) use ($serviceTime, $supportedChannels) {
            return SLAPolicy::firstOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['name'],
                    'priority' => $data['priority'],
                    'status' => SLAPolicyStatus::Active,
                    'max_contact_time' => $data['contact'],
                    'max_resolution_time' => $data['resolution'],
                    'update_frequency' => $data['contact'],
                    'supported_channels' => $supportedChannels,
                    'service_timezone' => 'America/New_York',
                    'service_time' => $serviceTime,
                    'service_exceptions' => [],
                ],
            );
        });

        $account = Account::firstOrFail();
        $account->policies()->syncWithoutDetaching($createdPolicies->pluck('id')->all());
        $account->categories()->syncWithoutDetaching([$category->id]);

        $template = Template::firstOrFail();
        $template->categories()->syncWithoutDetaching([$category->id]);

        if ($template->getData('account_id') !== $account->id) {
            $template->account_id = $account->id;
            $template->category_id = $category->id;
            $template->subcategory_id = $subcategory->id;
            $template->save();
        }

        $workflow = Workflow::firstOrCreate(
            ['key' => 'TEC'],
            [
                'name' => 'Technical Support',
                'slug' => 'technical-support',
                'description' => 'Standard workflow for technical support tickets',
            ],
        );

        $stages = [
            ['name' => 'New', 'description' => 'Ticket received, pending triage', 'color' => 'warning', 'maps_to_status' => TaskStatus::Open->value],
            ['name' => 'Triaged', 'description' => 'Assessed and prioritized, ready for assignment', 'color' => 'info', 'maps_to_status' => TaskStatus::Open->value],
            ['name' => 'In Progress', 'description' => 'Actively being worked on by an agent', 'color' => 'info', 'maps_to_status' => TaskStatus::InProgress->value],
            ['name' => 'Waiting on Customer', 'description' => 'Pending response or information from the customer', 'color' => 'warning', 'maps_to_status' => TaskStatus::Blocked->value],
            ['name' => 'Escalated', 'description' => 'Escalated to a specialist or higher tier', 'color' => 'danger', 'maps_to_status' => TaskStatus::InProgress->value],
            ['name' => 'Resolved', 'description' => 'Solution provided, awaiting customer confirmation', 'color' => 'success', 'maps_to_status' => TaskStatus::Resolved->value],
            ['name' => 'Closed', 'description' => 'Confirmed resolved and closed', 'color' => 'success', 'maps_to_status' => TaskStatus::Closed->value],
        ];

        foreach ($stages as $stageData) {
            WorkflowStage::firstOrCreate(
                [
                    'workflow_id' => $workflow->id,
                    'name' => $stageData['name'],
                ],
                $stageData,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultServiceTime(): array
    {
        $type = 'time-slot';
        $hours = ['start_time' => '08:00', 'end_time' => '17:00'];

        return collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
            ->map(fn (string $day) => ['type' => $type, 'fields' => array_merge(['day' => $day], $hours)])
            ->all();
    }
}
