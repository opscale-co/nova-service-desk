<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaCatalogs\Nova\CatalogItem;
use Opscale\NovaServiceDesk\Models\Category as CategoryModel;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Subcategory as Model;
use Opscale\NovaServiceDesk\Services\Actions\GetSubcategorySequence;

class Subcategory extends CatalogItem
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
        return array_values($this->defaultFields($request));
    }

    /**
     * Get the default fields for the resource.
     */
    protected function defaultFields(NovaRequest $request): array
    {
        $fields = parent::defaultFields($request);
        unset($fields['data']);

        $fields['catalog'] = BelongsTo::make(__('Category'), 'category', Category::class)
            ->required()
            ->sortable()
            ->filterable();

        $fields['key'] = Text::make(__('Key'), 'key')
            ->immutable()
            ->dependsOn(['category'], function (Text $field, NovaRequest $request, $formData) {
                if (! empty($formData['category'])) {
                    $result = GetSubcategorySequence::run(['category_id' => $formData['category']]);
                    if ($result['success']) {
                        $field->setValue($result['sequence']);
                    }
                }
            });

        $fields['impact'] = Select::make(__('Impact'), 'impact')
            ->options($this->getDefaultOptions('impact'))
            ->dependsOn(['category'], function (Select $field, NovaRequest $request, $formData) {
                $field->options($this->getCustomOptions($formData['category'], 'impact'));
            })
            ->rules(['required'])
            ->displayUsingLabels()
            ->hideFromIndex();

        $fields['urgency'] = Select::make(__('Urgency'), 'urgency')
            ->options($this->getDefaultOptions('urgency'))
            ->dependsOn(['category'], function (Select $field, NovaRequest $request, $formData) {
                $field->options($this->getCustomOptions($formData['category'], 'urgency'));
            })
            ->rules(['required'])
            ->displayUsingLabels()
            ->hideFromIndex();

        return $fields;
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
