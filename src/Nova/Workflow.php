<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Nova;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaServiceDesk\Models\Workflow as Model;
use Opscale\NovaServiceDesk\Nova\Repeatables\Stage;

class Workflow extends Resource
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
        'slug',
        'key',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Workflows');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Workflow');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'workflows';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required', 'string', 'max:256')
                ->sortable(),

            Text::make(__('Slug'), 'slug')
                ->rules('nullable', 'string', 'max:64', 'regex:/^[a-z0-9-]+$/')
                ->creationRules('unique:service_desk_workflows,slug')
                ->updateRules('unique:service_desk_workflows,slug,{{resourceId}}')
                ->help(__('URL-friendly identifier. Auto-generated from the name if left blank.'))
                ->fillUsing(function (NovaRequest $request, $model, $attribute, $requestAttribute) {
                    $value = $request->input($requestAttribute);
                    $model->{$attribute} = empty($value)
                        ? Str::slug((string) $request->input('name'))
                        : Str::slug($value);
                })
                ->sortable(),

            Text::make(__('Key'), 'key')
                ->rules('required', 'string', 'max:25')
                ->sortable(),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string', 'max:512')
                ->alwaysShow()
                ->hideFromIndex(),

            Repeater::make(__('Stages'), 'stages')
                ->repeatables([Stage::make()])
                ->asHasMany(WorkflowStage::class),
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
