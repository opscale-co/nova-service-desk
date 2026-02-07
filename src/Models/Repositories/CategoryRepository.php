<?php

namespace Opscale\NovaServiceDesk\Models\Repositories;

use Illuminate\Support\Facades\Cache;

trait CategoryRepository
{
    public static function fromKey(string $key): ?static
    {
        $cacheKey = 'opscale.service-desk.categories.' . $key;

        return Cache::rememberForever($cacheKey, function () use ($key) {
            return static::with('subcategories')->whereHas('subcategories', function ($query) {
                $query->orderBy('name');
            })->where('key', $key)->first();
        });
    }

    public static function options(string $key): array
    {
        $catalog = static::fromKey($key);

        return $catalog == null ? [] :
            $catalog->subcategories->pluck('name', 'key')->toArray();
    }

    public static function filteredOptions(string $key, callable $filter): array
    {
        $catalog = static::fromKey($key);

        $collection = $catalog == null ? [] :
            $catalog->subcategories->filter(function ($item) use ($filter) {
                return $filter($item);
            });

        return $collection->pluck('name', 'key')->toArray();
    }
}
