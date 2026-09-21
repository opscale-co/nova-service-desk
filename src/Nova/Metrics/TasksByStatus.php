<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Opscale\NovaServiceDesk\Models\Task;

class TasksByStatus extends Partition
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $novaRequest): PartitionResult
    {
        return $this->count($novaRequest, Task::class, 'status');
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    #[\Override]
    public function uriKey()
    {
        return 'tasks-by-status';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    #[\Override]
    public function name()
    {
        return __('Tasks by Status');
    }
}
