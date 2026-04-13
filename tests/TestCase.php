<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Tests;

use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Override;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    #[Override]
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \Opscale\NovaServiceDesk\ToolServiceProvider::class,
            NovaServiceProvider::class,
            NovaCoreServiceProvider::class,
        ]);
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    #[Override]
    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../vendor/opscale-co/nova-dynamic-resources/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../vendor/opscale-co/nova-catalogs/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
