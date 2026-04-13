<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Opscale\NovaServiceDesk\Contracts\ProvidesService;
use Opscale\NovaServiceDesk\Contracts\RequiresService;
use Workbench\App\Channels\StdoutChannel;
use Workbench\App\Resolvers\ServiceResolver;
use Workbench\App\Resolvers\TechnicalSupportWorkflowResolver;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app['config']->set('nova-service-desk.workflow_resolvers', array_merge(
            $this->app['config']->get('nova-service-desk.workflow_resolvers', []),
            [
                'TEC' => TechnicalSupportWorkflowResolver::class,
            ],
        ));

        // Bind the service contracts to a single resolver implementation.
        // Both interfaces share the same singleton so package code can
        // resolve them through the container without scanning models.
        $this->app->singleton(ServiceResolver::class);
        $this->app->bind(RequiresService::class, ServiceResolver::class);
        $this->app->bind(ProvidesService::class, ServiceResolver::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Notification::resolved(function (ChannelManager $service) {
            $service->extend('stdout', function ($app) {
                return new StdoutChannel;
            });
        });
    }
}
