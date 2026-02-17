<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaServiceDesk\Models\Resolution as ResolutionModel;

class Resolution extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<ResolutionModel>
     */
    public static string $model = ResolutionModel::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'author',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Resolutions');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Resolution');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'resolutions';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(__('Subcategory'), 'subcategory', Subcategory::class)
                ->required(),

            Text::make(__('Documentation URL'), 'documentation_url')
                ->rules('required', 'url')
                ->sortable(),

            Textarea::make(__('Notes'), 'notes')
                ->rules('required')
                ->alwaysShow(),

            Text::make(__('Author'), 'author')
                ->exceptOnForms()
                ->readonly()
                ->sortable(),

            Date::make(__('Last Modified'), 'last_modified')
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->exceptOnForms()
                ->readonly()
                ->sortable(),
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
}
