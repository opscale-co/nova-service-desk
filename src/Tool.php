<?php

namespace Opscale\NovaServiceDesk;

use Illuminate\Http\Request as HttpRequest;
use Laravel\Nova\Menu\MenuGroup;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaServiceDesk\Nova\Account;
use Opscale\NovaServiceDesk\Nova\Category;
use Opscale\NovaServiceDesk\Nova\Request;
use Opscale\NovaServiceDesk\Nova\SLAPolicy;
use Opscale\NovaServiceDesk\Nova\Task;
use Opscale\NovaServiceDesk\Nova\Template;

class Tool extends NovaTool
{
    public function boot()
    {
        Nova::script('nova-service-desk', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-service-desk', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(HttpRequest $request)
    {
        return MenuSection::make(__('Service Desk'), [
            MenuItem::link(__('Task Board'), '/nova-service-desk'),

            MenuGroup::make(__('Operation'), [
                MenuItem::resource(Task::class),
                ...$this->requestResources(),
            ]),

            MenuGroup::make(__('Administration'), [
                MenuItem::resource(SLAPolicy::class),
                MenuItem::resource(Category::class),
                MenuItem::resource(Account::class),
            ]),

            MenuGroup::make(__('Configuration'), [
                MenuItem::resource(Template::class),
                MenuItem::resource(Request::class),
            ]),
        ])->icon('inbox-stack')->collapsable();
    }

    /**
     * Get menu items for resources that inherit from Request.
     *
     * @return array<MenuItem>
     */
    protected function requestResources(): array
    {
        return collect(Nova::$resources)
            ->filter(fn ($resource) => is_subclass_of($resource, Request::class))
            ->map(fn ($resource) => MenuItem::resource($resource))
            ->values()
            ->all();
    }
}
