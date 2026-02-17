<?php

namespace Opscale\NovaServiceDesk\Nova;

use Illuminate\Http\Request as HttpRequest;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaDynamicResources\Nova\Concerns\UsesTemplate;
use Opscale\NovaServiceDesk\Models\Account;
use Opscale\NovaServiceDesk\Models\Category;
use Opscale\NovaServiceDesk\Models\Request as Model;
use Opscale\NovaServiceDesk\Models\Subcategory;
use Opscale\NovaServiceDesk\Nova\Metrics\OpenRequests;
use Opscale\NovaServiceDesk\Nova\Metrics\RequestActivity;
use Opscale\NovaServiceDesk\Services\Actions\AssignTask;

/**
 * @extends Resource<Model>
 */
class Request extends Resource
{
    use UsesTemplate;

    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\Opscale\NovaServiceDesk\Models\Request>
     */
    public static $model = Model::class;

    /**
     * Determine if the current user can create new resources.
     */
    public static function authorizedToCreate(HttpRequest $request): bool
    {
        return isset(static::$template);
    }

    /**
     * Determine if the current user can update the given resource.
     */
    public function authorizedToUpdate(HttpRequest $request): bool
    {
        return isset(static::$template);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<mixed>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ...$this->categorizationFields($request),

            ...$this->renderTemplateFields(),

            DateTime::make(__('Created At'), 'created_at')
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->sortable()
                ->exceptOnForms(),

            Boolean::make(__('Assigned'), 'assigned')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the actions available for the resource.
     */
    public function actions(NovaRequest $request): array
    {
        return [
            ...$this->renderTemplateActions(),
            AssignTask::make(),
        ];
    }

    /**
     * Get the cards available for the resource.
     */
    public function cards(NovaRequest $request): array
    {
        return [
            new OpenRequests(static::uriKey()),
            new RequestActivity(static::uriKey()),
        ];
    }

    /**
     * Get the categorization fields.
     *
     * @return array<mixed>
     */
    protected function categorizationFields(NovaRequest $request): array
    {
        $accountId = isset(static::$template) ? static::$template->getData('account_id') : null;
        $categoryId = isset(static::$template) ? static::$template->getData('category_id') : null;
        $subcategoryId = isset(static::$template) ? static::$template->getData('subcategory_id') : null;

        if ($accountId && $categoryId && $subcategoryId) {
            return [
                Hidden::make('account_id')->default($accountId),
                Hidden::make('category_id')->default($categoryId),
                Hidden::make('subcategory_id')->default($subcategoryId),
            ];
        } else {

            return [
                Select::make(__('Account'), 'account_id')
                    ->options(Account::with('customer')->get()->pluck('customer.name', 'id'))
                    ->displayUsingLabels()
                    ->required()
                    ->hideFromIndex(),

                Select::make(__('Category'), 'category_id')
                    ->options(Category::pluck('name', 'id'))
                    ->displayUsingLabels()
                    ->required()
                    ->hideFromIndex(),

                Select::make(__('Subcategory'), 'subcategory_id')
                    ->options(Subcategory::pluck('name', 'id'))
                    ->dependsOn(['category_id'], function (Select $field, NovaRequest $request, $formData) {
                        $categoryId = $formData->get('category_id');

                        if ($categoryId) {
                            $field->options(Subcategory::where('category_id', $categoryId)->pluck('name', 'id'));
                        } else {
                            $field->options([]);
                        }
                    })
                    ->displayUsingLabels()
                    ->required()
                    ->hideFromIndex(),
            ];
        }
    }
}
