<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tabs\Tab;
use Opscale\NovaDynamicResources\Models\Enums\TemplateType;
use Opscale\NovaDynamicResources\Nova\Field;
use Opscale\NovaDynamicResources\Nova\Template as BaseTemplate;
use Opscale\NovaServiceDesk\Models\Template as Model;

/**
 * @extends BaseTemplate<Model>
 */
class Template extends BaseTemplate
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaServiceDesk\Models\Template>
     */
    public static $model = Model::class;

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'request-templates';
    }

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Templates');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Template');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Tab::group('Template', [
                Tab::make('Details', [
                    Hidden::make(__('Type'), 'type')
                        ->default(TemplateType::Inherited->value),

                    Hidden::make(__('Related class'), 'related_class')
                        ->default(Request::class),

                    Text::make(__('Name'), 'singular_label')
                        ->rules(['required', 'string', 'max:255'])
                        ->sortable(),

                    Text::make(__('Identifier'), 'identifier')
                        ->rules(['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'])
                        ->help(__('3 uppercase letters'))
                        ->creationRules('unique:dynamic_resources_templates,identifier')
                        ->updateRules('unique:dynamic_resources_templates,identifier,{{resourceId}}'),

                    Textarea::make(__('Description'), 'description')
                        ->rules(['nullable', 'string'])
                        ->alwaysShow(),

                    parent::defaultFields($request)['fields'],
                ]),

                Tab::make(__('Fields'), [
                    'fields' => HasMany::make(__('Fields'), 'fields', Field::class),
                ]),
            ]),
        ];
    }
}
