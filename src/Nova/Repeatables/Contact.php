<?php

namespace Opscale\NovaServiceDesk\Nova\Repeatables;

use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Contact extends Repeatable
{
    /**
     * Get the label for the repeatable.
     */
    public static function label(): string
    {
        return __('Contact');
    }

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required'),

            Text::make(__('Role'), 'role')
                ->rules('required'),

            Email::make(__('Email'), 'email')
                ->rules('required', 'email'),

            Text::make(__('Phone'), 'phone')
                ->rules('required'),
        ];
    }
}
