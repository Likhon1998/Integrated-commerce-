<?php

if (! function_exists('public_storage_url')) {
    /**
     * Public disk files live under storage/app/public and are served from /storage/...
     */
    function public_storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return '/storage/' . ltrim($path, '/');
    }
}

if (! function_exists('app_timezone')) {
    function app_timezone(): string
    {
        return config('app.display_timezone', config('app.timezone', 'Asia/Dhaka'));
    }
}

if (! function_exists('asian_datetime')) {
    /**
     * Format a date/time in Asia/Dhaka (or APP_DISPLAY_TIMEZONE).
     */
    function asian_datetime($value = null, string $format = 'd M Y, h:i A'): string
    {
        if ($value === null || $value === '') {
            $value = now();
        }

        try {
            $dt = $value instanceof \Carbon\CarbonInterface
                ? $value->copy()
                : \Carbon\Carbon::parse($value);
        } catch (\Throwable $e) {
            return '';
        }

        return $dt->timezone(app_timezone())->format($format);
    }
}

if (! function_exists('asian_date')) {
    function asian_date($value = null, string $format = 'd M Y'): string
    {
        return asian_datetime($value, $format);
    }
}
