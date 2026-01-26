<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaCatalogs\Nova\Catalog;
use Opscale\NovaServiceDesk\Models\Category as Model;

class Category extends Catalog
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static $model = Model::class;

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
                    ...array_values($this->defaultFields($request)),

                    Select::make(__('Impact Options'), 'impact_options')
                        ->options($this->getCatalogOptions())
                        ->nullable()
                        ->hideFromIndex()
                        ->displayUsing(fn ($value) => $value ?? __('Default'))
                        ->help(__('Select a catalog to use for impact options.')),

                    Select::make(__('Urgency Options'), 'urgency_options')
                        ->options($this->getCatalogOptions())
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
     * Get catalog options for select fields.
     */
    protected function getCatalogOptions(): array
    {
        return Model::query()
            ->pluck('name', 'key')
            ->toArray();
    }

    /**
     * Get the default fields for the resource.
     */
    protected function defaultFields(NovaRequest $request): array
    {
        $fields = parent::defaultFields($request);
        unset($fields['catalogable']);
        unset($fields['data']);

        $fields['key'] = Text::make(__('Identifier'), 'key')
            ->rules(['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'])
            ->help(__('3 uppercase letters'))
            ->creationRules('unique:catalogs,key')
            ->updateRules('unique:catalogs,key,{{resourceId}}');

        return $fields;
    }
}
