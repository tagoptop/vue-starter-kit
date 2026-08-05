<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class BrandingHelper
{
    /**
     * Get the company logo URL with a cache-busting version query string.
     */
    public static function getVersionedLogoUrl(): string
    {
        $logoUrl = config('branding.logo_url', '/logo.svg');

        if (str_starts_with($logoUrl, '/storage/')) {
            $storagePath = storage_path('app/public/' . str_replace('/storage/', '', $logoUrl));
            if (file_exists($storagePath)) {
                return $logoUrl . '?v=' . filemtime($storagePath);
            }
        }

        if ($logoUrl === '/logo.svg') {
            $defaultLogoPath = public_path('logo.svg');
            if (file_exists($defaultLogoPath)) {
                return $logoUrl . '?v=' . filemtime($defaultLogoPath);
            }
        }

        return $logoUrl;
    }
}