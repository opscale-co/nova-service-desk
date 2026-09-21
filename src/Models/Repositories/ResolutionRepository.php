<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Models\Repositories;

trait ResolutionRepository
{
    public static function bootResolutionRepository(): void
    {
        static::creating(function ($resolution): void {
            $resolution->author = auth()->user()?->name;
            $resolution->last_modified = now();
        });
    }
}
