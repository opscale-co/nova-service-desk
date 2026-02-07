<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaServiceDesk\Models\Category as CategoryModel;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Subcategory as Model;
use Opscale\NovaServiceDesk\Services\Actions\GetSubcategorySequence;

class Subcategory extends Resource
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
    ];

    /**
     * Indicates if the resource should be displayed in the sidebar.
     *
     * @var bool
     */
    public static $displayInNavigation = false;

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'subcategories';
    }

    /**
     * Get the singular label for the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('Subcategory');
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('Subcategories');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            BelongsTo::make(__('Category'), 'category', Category::class)
                ->required()
                ->sortable()
                ->filterable(),

            Text::make(__('Name'), 'name')
                ->required()
                ->rules($this->model()?->validationRules['name'])
                ->sortable(),

            Text::make(__('Key'), 'key')
                ->immutable()
                ->dependsOn(['category'], function (Text $field, NovaRequest $request, $formData) {
                    if (! empty($formData['category'])) {
                        $result = GetSubcategorySequence::run(['category_id' => $formData['category']]);
                        if ($result['success']) {
                            $field->setValue($result['sequence']);
                        }
                    }
                }),

            Textarea::make(__('Description'), 'description')
                ->rules($this->model()?->validationRules['description'])
                ->nullable(),

            Select::make(__('Impact'), 'impact')
                ->options($this->getDefaultOptions('impact'))
                ->dependsOn(['category'], function (Select $field, NovaRequest $request, $formData) {
                    $field->options($this->getCustomOptions($formData['category'], 'impact'));
                })
                ->rules(['required'])
                ->displayUsingLabels()
                ->hideFromIndex(),

            Select::make(__('Urgency'), 'urgency')
                ->options($this->getDefaultOptions('urgency'))
                ->dependsOn(['category'], function (Select $field, NovaRequest $request, $formData) {
                    $field->options($this->getCustomOptions($formData['category'], 'urgency'));
                })
                ->rules(['required'])
                ->displayUsingLabels()
                ->hideFromIndex(),
        ];
    }

    /**
     * Get custom options from category metadata.
     */
    protected function getCustomOptions(?string $categoryId, string $field): array
    {
        $options = $this->getDefaultOptions($field);

        if ($categoryId) {
            $category = CategoryModel::find($categoryId);
            if ($category) {
                $catalogKey = $category->{"{$field}_options"};

                if ($catalogKey) {
                    $catalog = CategoryModel::fromKey($catalogKey);
                    if ($catalog && $catalog->subcategories()->exists()) {
                        $options = $catalog->subcategories()->pluck('name', 'key')->toArray();
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Get default options for a field.
     */
    protected function getDefaultOptions(string $field): array
    {
        $options = [
            'impact' => [
                SLAPriority::High->value => __('Affects entire operation'),
                SLAPriority::Medium->value => __('Affects a process'),
                SLAPriority::Low->value => __('Affects a specific case'),
            ],
            'urgency' => [
                SLAPriority::High->value => __('Time-sensitive'),
                SLAPriority::Medium->value => __('Will require attention soon'),
                SLAPriority::Low->value => __('Can be planned'),
            ],
        ];

        return $options[$field] ?? [];
    }
}
