<?php

namespace Opscale\NovaServiceDesk\Nova;

use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\MultiSelect;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaServiceDesk\Models\Enums\ServiceChannel;
use Opscale\NovaServiceDesk\Models\Enums\SLAPriority;
use Opscale\NovaServiceDesk\Models\SLAPolicy as Model;
use Opscale\NovaServiceDesk\Nova\Repeatables\TimeSlot;

class SLAPolicy extends Resource
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
        'name',
        'description',
    ];

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label()
    {
        return __('SLA Policies');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel()
    {
        return __('SLA Policy');
    }

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey()
    {
        return 'sla-policies';
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
                ->rules('required', 'max:255')
                ->sortable(),

            Textarea::make(__('Description'), 'description')
                ->alwaysShow()
                ->nullable(),

            Select::make(__('Priority'), 'priority')
                ->options(collect(SLAPriority::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value]))
                ->required()
                ->sortable()
                ->filterable(),

            Badge::make(__('Status'), 'status')
                ->map([
                    'Active' => 'success',
                    'Inactive' => 'danger',
                ])
                ->sortable()
                ->filterable(),

            Select::make(__('Contact Time'), 'max_contact_time')
                ->options($this->getTimeOptions())
                ->displayUsingLabels()
                ->rules('required')
                ->sortable(),

            Select::make(__('Resolution Time'), 'max_resolution_time')
                ->options($this->getTimeOptions())
                ->displayUsingLabels()
                ->rules('required')
                ->sortable(),

            Select::make(__('Update Frequency'), 'update_frequency')
                ->options($this->getTimeOptions())
                ->displayUsingLabels()
                ->nullable()
                ->hideFromIndex(),

            MultiSelect::make(__('Supported Channels'), 'supported_channels')
                ->options(collect(ServiceChannel::cases())->mapWithKeys(fn ($case) => [$case->value => $case->value]))
                ->rules('required')
                ->hideFromIndex(),

            Select::make(__('Service Timezone'), 'service_timezone')
                ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz]))
                ->searchable()
                ->rules('required')
                ->hideFromIndex(),

            Repeater::make(__('Service Time'), 'service_time')
                ->repeatables([
                    TimeSlot::make(),
                ])
                ->asJson()
                ->default($this->getDefaultServiceTime())
                ->required(),

            Repeater::make(__('Service Exceptions'), 'service_exceptions')
                ->repeatables([
                    TimeSlot::make(),
                ])
                ->asJson()
                ->help(__('Add any holidays, scheduled off days, or any other time for unavailability.')),
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

    protected function getTimeOptions(): array
    {
        return [
            1 => __('1 hour'),
            2 => __('2 hours'),
            4 => __('4 hours'),
            8 => __('8 hours'),
            16 => __('16 hours'),
            24 => __('1 day'),
            48 => __('2 days'),
            96 => __('4 days'),
            192 => __('8 days'),
            384 => __('16 days'),
            768 => __('32 days'),
        ];
    }

    protected function getDefaultServiceTime(): array
    {
        $type = 'service_time';

        return [
            ['type' => $type, 'field' => ['day' => 'monday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'field' => ['day' => 'tuesday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'field' => ['day' => 'wednesday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'field' => ['day' => 'thursday', 'start_time' => '08:00', 'end_time' => '17:00']],
            ['type' => $type, 'field' => ['day' => 'friday', 'start_time' => '08:00', 'end_time' => '17:00']],
        ];
    }
}
