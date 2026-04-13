<?php

declare(strict_types=1);

namespace Workbench\App\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Override;

/**
 * @extends Resource<\Workbench\App\Models\Department>
 */
class Department extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Workbench\App\Models\Department>
     */
    public static $model = \Workbench\App\Models\Department::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<int, string>
     */
    public static $search = [
        'id', 'name',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field|\Laravel\Nova\Panel|\Laravel\Nova\ResourceTool|\Illuminate\Http\Resources\MergeValue>
     */
    #[Override]
    final public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'string', 'max:255'),

            Textarea::make('Description')
                ->nullable()
                ->rules('nullable', 'string', 'max:512'),

            BelongsTo::make('Manager', 'manager', User::class)
                ->nullable()
                ->searchable()
                ->sortable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    #[Override]
    final public function cards(NovaRequest $request): array
    {
        return parent::cards($request);
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    #[Override]
    final public function filters(NovaRequest $request): array
    {
        return parent::filters($request);
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    #[Override]
    final public function lenses(NovaRequest $request): array
    {
        return parent::lenses($request);
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    #[Override]
    final public function actions(NovaRequest $request): array
    {
        return parent::actions($request);
    }
}
