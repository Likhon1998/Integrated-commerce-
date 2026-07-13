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
