<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaServiceDesk\Models\Category as Model;

class Category extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array<string>
     */
    public static $search = [
        'name',
        'key',
        'description',
    ];

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'categories';
    }

    /**
     * Get the singular label for the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Category');
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Categories');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Tab::group(__('Category'), [
                Tab::make(__('Details'), [
                    Text::make(__('Name'), 'name')
                        ->required()
                        ->rules($this->model()?->validationRules['name'])
                        ->sortable(),

                    Text::make(__('Identifier'), 'key')
                        ->rules(['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'])
                        ->help(__('3 uppercase letters'))
                        ->creationRules('unique:service_desk_categories,key')
                        ->updateRules('unique:service_desk_categories,key,{{resourceId}}'),

                    Textarea::make(__('Description'), 'description')
                        ->alwaysShow()
                        ->rules($this->model()?->validationRules['description']),

                    Select::make(__('Impact Options'), 'impact_options')
                        ->options($this->getCategoryOptions())
                        ->nullable()
                        ->hideFromIndex()
                        ->displayUsing(fn ($value) => $value ?? __('Default'))
                        ->help(__('Select a catalog to use for impact options.')),

                    Select::make(__('Urgency Options'), 'urgency_options')
                        ->options($this->getCategoryOptions())
                        ->nullable()
                        ->hideFromIndex()
                        ->displayUsing(fn ($value) => $value ?? __('Default'))
                        ->help(__('Select a catalog to use for urgency options.')),
                ]),

                Tab::make(__('Subcategories'), [
                    HasMany::make(__('Subcategories'), 'subcategories', Subcategory::class),
                ]),
            ]),
        ];
    }

    /**
     * Get category options for select fields.
     */
    protected function getCategoryOptions(): array
    {
        return Model::query()
            ->pluck('name', 'key')
            ->toArray();
    }
}
