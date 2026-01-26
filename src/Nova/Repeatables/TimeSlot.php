<?php

namespace Opscale\NovaServiceDesk\Nova\Repeatables;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class TimeSlot extends Repeatable
{
    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Select::make(__('Day'), 'day')
                ->options([
                    'monday' => __('Monday'),
                    'tuesday' => __('Tuesday'),
                    'wednesday' => __('Wednesday'),
                    'thursday' => __('Thursday'),
                    'friday' => __('Friday'),
                    'saturday' => __('Saturday'),
                    'sunday' => __('Sunday'),
                ])
                ->displayUsingLabels()
                ->rules('required'),

            Select::make(__('Start Time'), 'start_time')
                ->options($this->getTimeOptions())
                ->rules('required'),

            Select::make(__('End Time'), 'end_time')
                ->options($this->getTimeOptions())
                ->rules('required'),
        ];
    }

    /**
     * Get time options in 30-minute intervals.
     */
    protected function getTimeOptions(): array
    {
        $options = [];

        for ($hour = 0; $hour < 24; $hour++) {
            foreach (['00', '30'] as $minute) {
                $time = sprintf('%02d:%s', $hour, $minute);
                $options[$time] = $time;
            }
        }

        return $options;
    }
}
