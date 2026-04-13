<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Nova\Repeatables;

use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;
use Opscale\NovaServiceDesk\Models\WorkflowStage;

class Stage extends Repeatable
{
    /**
     * The associated model class for the block.
     *
     * @var string
     */
    public static $model = WorkflowStage::class;

    /**
     * Get the fields displayed by the repeatable.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            Text::make(__('Name'), 'name')
                ->rules('required', 'string', 'max:256'),

            Textarea::make(__('Description'), 'description')
                ->rules('nullable', 'string', 'max:512'),

            Select::make(__('Color'), 'color')
                ->options([
                    'warning' => __('Warning'),
                    'info' => __('Info'),
                    'danger' => __('Danger'),
                    'success' => __('Success'),
                ])
                ->nullable()
                ->displayUsingLabels(),

            Select::make(__('Maps to Status'), 'maps_to_status')
                ->options(collect(TaskStatus::cases())->mapWithKeys(fn ($case) => [$case->value => __($case->value)])->toArray())
                ->rules('required')
                ->displayUsingLabels(),
        ];
    }
}
