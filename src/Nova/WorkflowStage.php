<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\WorkflowStage as Model;

class WorkflowStage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
    ];

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Workflow Stages');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Workflow Stage');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'workflow-stages';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            BelongsTo::make(__('Workflow'), 'workflow', Workflow::class)
                ->required()
                ->sortable(),

            Text::make(__('Name'), 'name')
                ->rules('required', 'string', 'max:256')
                ->sortable(),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string', 'max:512')
                ->alwaysShow()
                ->hideFromIndex(),

            Select::make(__('Color'), 'color')
                ->options([
                    'warning' => __('Warning'),
                    'info' => __('Info'),
                    'danger' => __('Danger'),
                    'success' => __('Success'),
                ])
                ->nullable()
                ->displayUsingLabels(),

            Select::make(__('Maps to Status'), 'maps_to_status')
                ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($case) => [$case->value => __($case->value)])->toArray())
                ->rules('required')
                ->displayUsingLabels(),
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
