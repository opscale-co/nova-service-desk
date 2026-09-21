<?php

declare(strict_types=1);

namespace Opscale\NovaServiceDesk\Http\Middleware;

use Illuminate\Http\Request;
use Laravel\Nova\Nova;
use Opscale\NovaServiceDesk\Tool;

class Authorize
{
    public function handle(Request $request, $next)
    {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        return $tool?->authorize($request) ? $next($request) : abort(403);
    }

    public function matchesTool($tool): bool
    {
        return $tool instanceof Tool;
    }
}
