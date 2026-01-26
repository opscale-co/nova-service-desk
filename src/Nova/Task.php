<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\Task as Model;
use Opscale\NovaServiceDesk\Nova\Actions\ChangeStatus;
use Opscale\NovaServiceDesk\Nova\Metrics\AverageTime;
use Opscale\NovaServiceDesk\Nova\Metrics\TaskActivity;
use Opscale\NovaServiceDesk\Nova\Metrics\TasksByStatus;

class Task extends Resource
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
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'key',
        'title',
    ];

    /**
     * Get the displayable label of the resource.
     */
    public static function label(): string
    {
        return __('Tasks');
    }

    /**
     * Get the displayable singular label of the resource.
     */
    public static function singularLabel(): string
    {
        return __('Task');
    }

    /**
     * Get the URI key for the resource.
     */
    public static function uriKey(): string
    {
        return 'tasks';
    }

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->orderByPriority();
    }

    /**
     * Get the status options for the task.
     */
    public static function statusOptions(): array
    {
        return [
            TaskStatus::Open->value => 'warning',
            TaskStatus::InProgress->value => 'info',
            TaskStatus::Blocked->value => 'danger',
            TaskStatus::Resolved->value => 'success',
            TaskStatus::Closed->value => 'success',
            TaskStatus::Cancelled->value => 'danger',
        ];
    }

    /**
     * Get the status alias options for the task.
     */
    public function statusAliasOptions(): array
    {
        $templateKey = strtoupper(substr($this->resource->key ?? '', 0, 3));
        $resolvers = config('nova-service-desk.status_alias_resolvers', []);

        if (isset($resolvers[$templateKey])) {
            $resolver = app($resolvers[$templateKey]);
            $options = $resolver->getOptions();
            $statusOptions = self::statusOptions();

            return collect($options)
                ->flatMap(fn ($aliases, $status) => collect($aliases)
                    ->mapWithKeys(fn ($alias) => [$alias => $statusOptions[$status] ?? 'info'])
                )
                ->toArray();
        }

        return [];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $userClass = Nova::resourceForModel(config('auth.providers.users.model'));

        return [
            BelongsTo::make(__('Request'), 'request', Request::class)
                ->searchable()
                ->required()
                ->sortable()
                ->filterable()
                ->hideFromIndex(),

            BelongsTo::make(__('Assignee'), 'assignee', $userClass)
                ->searchable()
                ->required()
                ->sortable()
                ->filterable(),

            BelongsTo::make(__('Assigner'), 'assigner', $userClass)
                ->searchable()
                ->required()
                ->sortable()
                ->filterable()
                ->hideFromIndex(),

            Text::make(__('Key'), 'key')
                ->rules('required')
                ->sortable()
                ->exceptOnForms(),

            Text::make(__('Title'), 'title')
                ->rules('required')
                ->sortable()
                ->showOnIndex(),

            Textarea::make(__('Description'), 'description')
                ->rules('required')
                ->alwaysShow()
                ->hideFromIndex(),

            Badge::make(__('Status'), 'status')
                ->map(self::statusOptions())
                ->sortable()
                ->filterable(),

            Badge::make(__('Alias'), 'status_alias')
                ->map($this->statusAliasOptions())
                ->hideFromIndex()
                ->canSee(fn () => ! empty($this->statusAliasOptions()))
                ->sortable()
                ->filterable(),

            Badge::make(__('Priority'), 'priority')
                ->map([
                    SLAPriority::Critical->value => 'danger',
                    SLAPriority::High->value => 'warning',
                    SLAPriority::Medium->value => 'info',
                    SLAPriority::Low->value => 'success',
                    SLAPriority::Planning->value => 'success',
                ])
                ->sortable()
                ->filterable(),

            DateTime::make(__('Due Date'), 'due_date')
                ->nullable()
                ->sortable()
                ->filterable()
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->showOnIndex(),

            DateTime::make(__('Contacted At'), 'contacted_at')
                ->nullable()
                ->sortable()
                ->filterable()
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->onlyOnDetail()
                ->canSee(fn () => $this->resource->status !== TaskStatus::Open),

            DateTime::make(__('Closed At'), 'closed_at')
                ->nullable()
                ->sortable()
                ->filterable()
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->onlyOnDetail()
                ->canSee(fn () => $this->resource->status === TaskStatus::Closed),

            DateTime::make(__('Created At'), 'created_at')
                ->sortable()
                ->filterable()
                ->displayUsing(fn ($value) => $value?->diffForHumans())
                ->onlyOnDetail(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [
            new TasksByStatus,
            new AverageTime,
            new TaskActivity,
        ];
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
        return [
            ChangeStatus::make(),
        ];
    }
}
