<?php

namespace Opscale\NovaServiceDesk\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Opscale\NovaServiceDesk\Models\Request;

class OpenRequests extends Value
{
    /**
     * The uri_key to filter requests by.
     */
    protected ?string $uriKey = null;

    /**
     * Create a new metric instance.
     *
     * @return void
     */
    public function __construct(?string $uriKey = null)
    {
        $this->uriKey = $uriKey;
    }

    /**
     * Calculate the value of the metric.
     *
     * @return mixed
     */
    public function calculate(NovaRequest $request)
    {
        $query = Request::query()
            ->whereDoesntHave('task');

        // Filter by uri_key if provided
        if ($this->uriKey) {
            $query->whereHas('template', function ($q) {
                $q->where('uri_key', $this->uriKey);
            });
        }

        $count = $query->count();

        return $this->result($count)
            ->suffix(__('requests'));
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
        return 'open-requests';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Open Requests');
    }
}
