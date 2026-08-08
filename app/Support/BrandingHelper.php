<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

class BrandingHelper
{
    public const DEFAULT_COMPANY_NAME = 'Construction Supply';
    public const DEFAULT_LOGO_URL = '/logo.svg';

    private static ?array $settingsCache = null;

    public static function getCompanyName(): string
    {
        return self::getSetting('company_name', config('branding.company_name', self::DEFAULT_COMPANY_NAME));
    }

    public static function getLogoUrl(): string
    {
        return self::getSetting('logo_url', config('branding.logo_url', self::DEFAULT_LOGO_URL));
    }

    /**
     * Get the company logo URL with a cache-busting version query string.
     */
    public static function getVersionedLogoUrl(): string
    {
        $logoUrl = self::getLogoUrl();

        if (str_starts_with($logoUrl, '/storage/')) {
            $storagePath = storage_path('app/public/' . str_replace('/storage/', '', $logoUrl));
            if (file_exists($storagePath)) {
                return $logoUrl . '?v=' . filemtime($storagePath);
            }
        }

        if ($logoUrl === self::DEFAULT_LOGO_URL) {
            $defaultLogoPath = public_path('logo.svg');
            if (file_exists($defaultLogoPath)) {
                return $logoUrl . '?v=' . filemtime($defaultLogoPath);
            }
        }

        return $logoUrl;
    }

    public static function set(string $key, ?string $value): void
    {
        if (! self::canReadFromDatabase()) {
            return;
        }

        AppSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        if (self::$settingsCache !== null) {
            self::$settingsCache[$key] = $value;
        }
    }

    private static function getSetting(string $key, string $default): string
    {
        if (! self::canReadFromDatabase()) {
            return $default;
        }

        if (self::$settingsCache === null) {
            self::$settingsCache = AppSetting::query()
                ->whereIn('key', ['company_name', 'logo_url'])
                ->pluck('value', 'key')
                ->all();
        }

        $value = self::$settingsCache[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $default;
    }

    private static function canReadFromDatabase(): bool
    {
        try {
            return Schema::hasTable('app_settings');
        } catch (\Throwable) {
            return false;
        }
    }
}