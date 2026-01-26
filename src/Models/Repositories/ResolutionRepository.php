<?php

namespace Opscale\NovaServiceDesk\Models\Repositories;

trait ResolutionRepository
{
    public static function bootResolutionRepository(): void
    {
        static::creating(function ($resolution) {
            $resolution->author = auth()->user()?->name;
            $resolution->last_modified = now();
        });
    }
}
