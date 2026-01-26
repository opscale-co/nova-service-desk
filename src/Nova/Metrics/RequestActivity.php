<?php

namespace Opscale\NovaServiceDesk\Nova\Metrics;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Opscale\NovaServiceDesk\Models\Request;

class RequestActivity extends Trend
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
        $query = Request::query();

        // Filter by uri_key if provided
        if ($this->uriKey) {
            $query->whereHas('template', function ($q) {
                $q->where('uri_key', $this->uriKey);
            });
        }

        return $this->countByDays($request, $query)->showSumValue();
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            7 => __('7 Days'),
            30 => __('30 Days'),
            60 => __('60 Days'),
            90 => __('90 Days'),
        ];
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'request-activity';
    }

    /**
     * Get the displayable name of the metric.
     *
     * @return string
     */
    public function name()
    {
        return __('Request Activity');
    }
}
