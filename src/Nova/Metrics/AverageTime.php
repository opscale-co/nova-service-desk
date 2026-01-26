<?php

namespace Opscale\NovaServiceDesk\Nova\Metrics;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Opscale\NovaServiceDesk\Models\Task;

class AverageTime extends Value
{
    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $average = Task::whereNotNull('closed_at')
            ->select(DB::raw('AVG(julianday(closed_at) - julianday(created_at)) as avg_days'))
            ->value('avg_days');

        return $this->result($average ? round($average, 2) : 0)
            ->suffix(__('days'));
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'average-time';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Average Time');
    }
}
