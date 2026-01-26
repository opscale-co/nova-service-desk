<?php

namespace Opscale\NovaServiceDesk\Models\Repositories;

trait InsightRepository
{
    public static function bootInsightRepository(): void
    {
        static::creating(function ($insight) {
            $insight->author = auth()->user()?->name;
        });
    }
}
