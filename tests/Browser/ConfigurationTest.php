<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Tests\Browser;

use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\SLAPolicy;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Models\Workflow;
use Opscale\NovaServiceDesk\Models\WorkflowStage;
use Opscale\NovaServiceDesk\Tests\DuskTestCase;
use Override;
use PHPUnit\Framework\Attributes\Test;

/**
 * Configuration test — covers the bootstrap records required to operate
 * the service desk: Category, Subcategory, SLA Policy and Workflow.
 *
 * The workbench seeder creates the full data set for manual development.
 * Before this test class runs we wipe ONLY the records this test will
 * recreate (Category, Subcategory, SLA Policy, Workflow + their stages
 * and pivots) so the UI creation flow can replay the same values without
 * tripping over unique constraints.
 *
 * Account, Template and Users are left intact because they are used by
 * the OperationTest and cannot be reproduced via Nova UI.
 */
final class ConfigurationTest extends DuskTestCase
{
    /**
     * Wipe-once flag — ensures the fixture cleanup happens a single time
     * for the whole class run, not per test.
     */
    private static bool $wiped = false;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        if (! self::$wiped) {
            $this->wipeFixtureTables();
            self::$wiped = true;
        }
    }

    #[Test]
    final public function creates_a_category(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/categories/new')
                ->waitForText('Create Category')
                ->type('@name', 'Technical Support')
                ->type('@key', 'TEC')
                ->type('@description', 'Technical support issues and requests')
                ->press('Create Category')
                ->waitForText('Technical Support')
                ->assertSee('Technical Support');
        });
    }

    #[Test]
    final public function creates_a_subcategory(): void
    {
        $categoryId = Category::where('key', 'TEC')->firstOrFail()->id;

        $this->browse(function (Browser $browser) use ($categoryId): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/subcategories/new')
                ->waitForText('Create Subcategory')
                ->select('@category', $categoryId)
                ->pause(500)
                ->type('@name', 'General Inquiry')
                ->select('@impact', 'Medium')
                ->select('@urgency', 'Medium')
                ->press('Create Subcategory')
                ->waitForText('General Inquiry')
                ->assertSee('General Inquiry');
        });
    }

    #[Test]
    final public function creates_an_sla_policy(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/sla-policies/new')
                ->waitForText('Create SLA Policy')
                ->type('@name', 'Medium Priority SLA')
                ->type('@description', 'SLA policy for medium priority issues')
                ->select('@priority', 'Medium')
                ->select('@max-contact-time', '4')
                ->select('@max-resolution-time', '24')
                ->select('@update-frequency', '8')
                ->select('@service-timezone', 'America/New_York')
                ->press('Create SLA Policy')
                ->waitForText('Medium Priority SLA')
                ->assertSee('Medium Priority SLA');
        });
    }

    #[Test]
    final public function creates_a_workflow(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->loginToNova($browser)
                ->visit('/nova/resources/workflows/new')
                ->waitForText('Create Workflow')
                ->type('@name', 'Technical Support')
                ->type('@slug', 'technical-support')
                ->type('@key', 'TEC')
                ->type('@description', 'Standard workflow for technical support tickets')
                ->press('Create Workflow')
                ->waitForText('Technical Support')
                ->assertSee('Technical Support');
        });
    }

    /**
     * Remove records the configuration test will recreate via the UI.
     * Order matters: pivots and dependents go first.
     */
    private function wipeFixtureTables(): void
    {
        DB::table('service_desk_account_sla_policy')->delete();
        DB::table('service_desk_account_category')->delete();
        DB::table('service_desk_category_template')->delete();

        WorkflowStage::query()->delete();
        Workflow::query()->delete();
        SLAPolicy::query()->delete();
        Subcategory::query()->delete();
        Category::query()->delete();
    }
}
