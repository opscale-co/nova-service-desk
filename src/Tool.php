<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk;

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Schema;
use Laravel\Nova\Menu\MenuGroup;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaServiceDesk\Models\Workflow as WorkflowModel;
use Opscale\NovaServiceDesk\Nova\Account;
use Opscale\NovaServiceDesk\Nova\Category;
use Opscale\NovaServiceDesk\Nova\Request;
use Opscale\NovaServiceDesk\Nova\Resolution;
use Opscale\NovaServiceDesk\Nova\SLAPolicy;
use Opscale\NovaServiceDesk\Nova\Task;
use Opscale\NovaServiceDesk\Nova\Template;
use Opscale\NovaServiceDesk\Nova\Workflow;

class Tool extends NovaTool
{
    public function boot()
    {
        Nova::script('nova-service-desk', __DIR__.'/../dist/js/tool.js');
        Nova::style('nova-service-desk', __DIR__.'/../dist/css/tool.css');
    }

    public function menu(HttpRequest $request)
    {
        return MenuSection::make(__('Service Desk'), [
            MenuGroup::make(__('Task Board'), $this->taskBoardItems()),

            MenuGroup::make(__('Operation'), [
                MenuItem::resource(Task::class),
                ...$this->requestResources(),
            ]),

            MenuGroup::make(__('Administration'), [
                MenuItem::resource(SLAPolicy::class),
                MenuItem::resource(Category::class),
                MenuItem::resource(Account::class),
                MenuItem::resource(Resolution::class),
                MenuItem::resource(Workflow::class),
            ]),

            MenuGroup::make(__('Configuration'), [
                MenuItem::resource(Template::class),
                MenuItem::resource(Request::class),
            ]),
        ])->icon('inbox-stack')->collapsable();
    }

    /**
     * Get menu items for the Task Board: default board + one per workflow.
     *
     * @return array<MenuItem>
     */
    protected function taskBoardItems(): array
    {
        $items = [
            MenuItem::link(__('Default'), '/nova-service-desk'),
        ];

        if (! Schema::hasTable('service_desk_workflows')) {
            return $items;
        }

        WorkflowModel::orderBy('name')->get()->each(function (WorkflowModel $workflow) use (&$items) {
            $items[] = MenuItem::link(
                $workflow->name,
                '/nova-service-desk?workflow='.$workflow->slug,
            );
        });

        return $items;
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
