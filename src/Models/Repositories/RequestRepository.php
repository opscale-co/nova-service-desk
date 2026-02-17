<?php

namespace Opscale\NovaServiceDesk\Models\Repositories;

trait RequestRepository
{
    public static function bootRequestRepository(): void
    {
        static::creating(function ($request) {
            $request->tracking_code = static::generateTrackingCode();
        });
    }

    public static function generateTrackingCode(): string
    {
        do {
            $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 5);
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }
}
