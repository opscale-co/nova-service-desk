<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk;

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;
use Opscale\NovaPackageTools\NovaPackage;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Opscale\NovaServiceDesk\Http\Middleware\Authorize;
use Opscale\NovaServiceDesk\Nova\Account;
use Opscale\NovaServiceDesk\Nova\Category;
use Opscale\NovaServiceDesk\Nova\Insight;
use Opscale\NovaServiceDesk\Nova\Request;
use Opscale\NovaServiceDesk\Nova\Resolution;
use Opscale\NovaServiceDesk\Nova\SLAPolicy;
use Opscale\NovaServiceDesk\Nova\Subcategory;
use Opscale\NovaServiceDesk\Nova\Task;
use Opscale\NovaServiceDesk\Nova\Template;
use Opscale\NovaServiceDesk\Nova\Workflow;
use Opscale\NovaServiceDesk\Nova\WorkflowStage;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;

class ToolServiceProvider extends NovaPackageServiceProvider
{
    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function configurePackage(Package $package): void
    {
        /** @var NovaPackage $package */
        $package
            ->name('nova-service-desk')
            ->hasConfigFile()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasResources([
                Account::class,
                Category::class,
                Insight::class,
                SLAPolicy::class,
                Subcategory::class,
                Resolution::class,
                Request::class,
                Task::class,
                Template::class,
                Workflow::class,
                WorkflowStage::class,
            ])
            ->hasTranslations()
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('opscale-co/nova-service-desk');
            });
    }

    public function packageBooted(): void
    {
        parent::packageBooted();
        $this->registerRoutes();
        $this->registerToolbarActionMacros();
    }

    protected function registerToolbarActionMacros(): void
    {
        if (! Action::hasMacro('showAsButton')) {
            Action::macro('showAsButton', fn (bool $show = true) => $this->withMeta(['showAsButton' => $show]));
        }

        if (! Action::hasMacro('showOnDetailToolbar')) {
            Action::macro('showOnDetailToolbar', fn (bool $show = true) => $this->withMeta(['showOnDetailToolbar' => $show]));
        }

        if (! Action::hasMacro('showOnIndexToolbar')) {
            Action::macro('showOnIndexToolbar', fn (bool $show = true) => $this->withMeta(['showOnIndexToolbar' => $show]));
        }
    }

    protected function registerRoutes()
    {
        if (optional($this->app)->routesAreCached()) {
            return;
        }

        Nova::router(['nova', Authenticate::class, Authorize::class], '/nova-service-desk')
            ->group(__DIR__.'/../routes/inertia.php');

        Route::middleware(['nova', Authorize::class])
            ->prefix('nova-vendor/opscale-co/nova-service-desk')
            ->group(__DIR__.'/../routes/api.php');
    }
}
