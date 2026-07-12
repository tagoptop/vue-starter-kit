<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        $brandingLogoUrl = config('branding.logo_url', '/logo.svg');

        if (str_starts_with($brandingLogoUrl, '/storage/')) {
            $storagePath = storage_path('app/public/' . str_replace('/storage/', '', $brandingLogoUrl));
            if (file_exists($storagePath)) {
                $brandingLogoUrl .= '?v=' . filemtime($storagePath);
            }
        } elseif ($brandingLogoUrl === '/logo.svg') {
            $defaultLogoPath = public_path('logo.svg');
            if (file_exists($defaultLogoPath)) {
                $brandingLogoUrl .= '?v=' . filemtime($defaultLogoPath);
            }
        }

        return array_merge(parent::share($request), [
            ...parent::share($request),
            'name' => config('app.name'),
            'branding' => [
                'companyName' => config('branding.company_name', 'Construction Supply'),
                'logoUrl' => $brandingLogoUrl,
            ],
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
        ]);
    }
}
