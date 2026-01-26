<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Models\Field;
use Opscale\NovaServiceDesk\Models\Account;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\Enums\ServiceChannel;
use Opscale\NovaServiceDesk\Models\Enums\SLAPolicyStatus;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\SLAPolicy;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Template;
use Opscale\NovaServiceDesk\Nova\Request;
use Workbench\App\Models\User;

class ServiceDeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Template for support tickets
        $template = Template::create([
            'type' => TemplateType::Inherited->value,
            'related_class' => Request::class,
            'singular_label' => 'Support Ticket',
            'identifier' => 'SUP',
            'description' => 'Template for support ticket requests',
        ]);

        // Add fields to template
        Field::create([
            'template_id' => $template->id,
            'type' => 'title',
            'label' => 'Title',
            'name' => 'title',
            'required' => true,
            'rules' => ['string', 'max:255'],
        ]);

        Field::create([
            'template_id' => $template->id,
            'type' => 'description',
            'label' => 'Description',
            'name' => 'description',
            'required' => false,
            'rules' => ['string'],
        ]);

        // 2. Create Category
        $category = Category::create([
            'name' => 'Technical Support',
            'key' => 'TEC',
            'description' => 'Technical support issues and requests',
        ]);

        // Link category to template
        $category->templates()->attach($template->id);

        // 3. Create Subcategories
        Subcategory::create([
            'catalog_id' => $category->id,
            'name' => 'General Inquiry',
            'key' => 'TEC-001',
            'description' => 'General technical inquiries',
            'data' => [
                'impact' => SLAPriority::Medium->value,
                'urgency' => SLAPriority::Medium->value,
            ],
        ]);

        Subcategory::create([
            'catalog_id' => $category->id,
            'name' => 'System Outage',
            'key' => 'TEC-002',
            'description' => 'System outages and critical failures',
            'data' => [
                'impact' => SLAPriority::High->value,
                'urgency' => SLAPriority::High->value,
            ],
        ]);

        Subcategory::create([
            'catalog_id' => $category->id,
            'name' => 'Feature Request',
            'key' => 'TEC-003',
            'description' => 'New feature requests and enhancements',
            'data' => [
                'impact' => SLAPriority::Low->value,
                'urgency' => SLAPriority::Low->value,
            ],
        ]);

        // 4. Create SLA Policies for each priority
        $serviceTime = $this->getDefaultServiceTime();
        $supportedChannels = [
            ServiceChannel::Web->value,
            ServiceChannel::Email->value,
            ServiceChannel::Phone->value,
        ];

        $slaPolicies = [
            [
                'name' => 'Critical Priority SLA',
                'description' => 'SLA policy for critical priority issues',
                'priority' => SLAPriority::Critical,
                'status' => SLAPolicyStatus::Active,
                'max_contact_time' => 1,
                'max_resolution_time' => 4,
                'update_frequency' => 1,
                'supported_channels' => $supportedChannels,
                'service_timezone' => 'America/New_York',
                'service_time' => $serviceTime,
                'service_exceptions' => [],
            ],
            [
                'name' => 'High Priority SLA',
                'description' => 'SLA policy for high priority issues',
                'priority' => SLAPriority::High,
                'status' => SLAPolicyStatus::Active,
                'max_contact_time' => 2,
                'max_resolution_time' => 8,
                'update_frequency' => 2,
                'supported_channels' => $supportedChannels,
                'service_timezone' => 'America/New_York',
                'service_time' => $serviceTime,
                'service_exceptions' => [],
            ],
            [
                'name' => 'Medium Priority SLA',
                'description' => 'SLA policy for medium priority issues',
                'priority' => SLAPriority::Medium,
                'status' => SLAPolicyStatus::Active,
                'max_contact_time' => 4,
                'max_resolution_time' => 24,
                'update_frequency' => 8,
                'supported_channels' => $supportedChannels,
                'service_timezone' => 'America/New_York',
                'service_time' => $serviceTime,
                'service_exceptions' => [],
            ],
            [
                'name' => 'Low Priority SLA',
                'description' => 'SLA policy for low priority issues',
                'priority' => SLAPriority::Low,
                'status' => SLAPolicyStatus::Active,
                'max_contact_time' => 8,
                'max_resolution_time' => 48,
                'update_frequency' => 24,
                'supported_channels' => $supportedChannels,
                'service_timezone' => 'America/New_York',
                'service_time' => $serviceTime,
                'service_exceptions' => [],
            ],
            [
                'name' => 'Planning Priority SLA',
                'description' => 'SLA policy for planning priority issues',
                'priority' => SLAPriority::Planning,
                'status' => SLAPolicyStatus::Active,
                'max_contact_time' => 24,
                'max_resolution_time' => 192,
                'update_frequency' => 48,
                'supported_channels' => $supportedChannels,
                'service_timezone' => 'America/New_York',
                'service_time' => $serviceTime,
                'service_exceptions' => [],
            ],
        ];

        $createdPolicies = [];
        foreach ($slaPolicies as $policyData) {
            $createdPolicies[] = SLAPolicy::create($policyData);
        }

        // 5. Create Account
        $user = User::first();

        $account = Account::create([
            'customer_type' => User::class,
            'customer_id' => $user->id,
            'profile' => 'Default Account',
            'metadata' => [],
        ]);

        // Link account to SLA policies
        $account->policies()->attach(collect($createdPolicies)->pluck('id'));

        // Link account to category
        $account->categories()->attach($category->id);
    }

    /**
     * Get default service time configuration.
     */
    protected function getDefaultServiceTime(): array
    {
        $type = 'time-slot';

        return [
            ['type' => $type, 'fields' => ['day' => 'monday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'fields' => ['day' => 'tuesday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'fields' => ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'fields' => ['day' => 'thursday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'fields' => ['day' => 'friday', 'start_time' => '08:00', 'end_time' => '17:00']],
        ];
    }
}
