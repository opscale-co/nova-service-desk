<?php

namespace Opscale\NovaServiceDesk\Nova;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaServiceDesk\Contracts\RequiresService;
use Opscale\NovaServiceDesk\Models\Account as Model;

class Account extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'profile',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Accounts');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Account');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'accounts';
    }

    /**
     * Get the value that should be displayed to represent the resource.
     */
    public function title(): string
    {
        return $this->customer?->name ?? $this->id;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Tab::group(__('Account'), [
                Tab::make(__('Details'), [
                    'customer' => MorphTo::make(__('Customer'), 'customer')
                        ->types($this->getServiceableResources())
                        ->required(),

                    'profile' => Trix::make(__('Profile'), 'profile')
                        ->rules('required')
                        ->alwaysShow(),

                    'policies' => Tag::make(__('SLA Policies'), 'policies', SLAPolicy::class)
                        ->preload()
                        ->required(),

                    'categories' => Tag::make(__('Categories'), 'categories', Category::class)
                        ->preload()
                        ->required(),
                ]),

                Tab::make(__('Insights'), [
                    'insights' => HasMany::make(__('Insights'), 'insights', Insight::class),
                ]),
            ]),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get resources whose models implement the RequiresService contract.
     *
     * @return array<class-string<\Laravel\Nova\Resource<\Illuminate\Database\Eloquent\Model>>>
     */
    private function getServiceableResources(): array
    {
        /** @var array<class-string<\Laravel\Nova\Resource<\Illuminate\Database\Eloquent\Model>>> $resources */
        $resources = (new Collection(Nova::$resources))
            ->filter(function (string $resource): bool {
                /** @var class-string<\Laravel\Nova\Resource> $resource */
                /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
                $model = $resource::$model;

                return is_subclass_of($model, RequiresService::class);
            })
            ->toArray();

        return $resources;
    }
}
