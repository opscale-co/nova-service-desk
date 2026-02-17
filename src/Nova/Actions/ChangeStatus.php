<?php

namespace Opscale\NovaServiceDesk\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaServiceDesk\Models\Enums\TaskStatus;

class ChangeStatus extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Get the displayable name of the action.
     *
     * @return string
     */
    public function name()
    {
        return __('Change Status');
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $task) {
            $task->update([
                'status' => $fields->status,
            ]);
        }

        return Action::message(__('Status updated successfully'));
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        $statuses = collect(TaskStatus::cases())->mapWithKeys(function ($status) {
            return [$status->value => __($status->value)];
        })->toArray();

        return [
            Select::make(__('Status'), 'status')
                ->options($statuses)
                ->rules('required'),
        ];
    }
}
