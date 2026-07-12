<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class CompanyBrandingController extends Controller
{
    /**
     * Show the company branding settings page (admin only).
     */
    public function edit(Request $request): View
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $logoUrl = $this->getVersionedLogoUrl();
        $companyName = config('branding.company_name', 'Construction Supply');

        return view('settings.branding', [
            'logoUrl' => $logoUrl,
            'companyName' => $companyName,
        ]);
    }

    /**
     * Update the company branding settings.
     */
    public function update(Request $request): RedirectResponse
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|file|mimes:svg,png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $companyName = $request->input('company_name');

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('branding', 'public');
            $logoUrl = '/storage/' . $path;

            // Delete old logo if it exists and is not the default
            $oldLogo = config('branding.logo_url', '/logo.svg');
            if ($oldLogo !== '/logo.svg' && str_contains($oldLogo, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $oldLogo);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        } else {
            $logoUrl = config('branding.logo_url', '/logo.svg');
        }

        // Store settings in config file
        $this->saveBrandingConfig($companyName, $logoUrl);

        return redirect()->route('settings.branding')
            ->with('status', 'Company branding updated successfully!')
            ->with('status_type', 'saved');
    }

    /**
     * Reset to default branding.
     */
    public function reset(Request $request): RedirectResponse
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Delete uploaded logo
        $logo = config('branding.logo_url', '/logo.svg');
        if ($logo !== '/logo.svg' && str_contains($logo, '/storage/')) {
            $path = str_replace('/storage/', '', $logo);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->saveBrandingConfig('Construction Supply', '/logo.svg');

        return redirect()->route('settings.branding')
            ->with('status', 'Company branding reset to defaults!')
            ->with('status_type', 'reset');
    }

    /**
     * Save branding configuration.
     */
    private function saveBrandingConfig(string $companyName, string $logoUrl): void
    {
        $configPath = config_path('branding.php');
        $config = [
            'company_name' => $companyName,
            'logo_url' => $logoUrl,
        ];

        file_put_contents($configPath, '<?php return ' . var_export($config, true) . ';');

        // Keep runtime config in sync for the current request cycle.
        config([
            'branding.company_name' => $companyName,
            'branding.logo_url' => $logoUrl,
        ]);

        // If config was cached, remove stale cache so new branding is loaded.
        $cachedConfigPath = base_path('bootstrap/cache/config.php');
        if (file_exists($cachedConfigPath)) {
            @unlink($cachedConfigPath);
        }
    }

    /**
     * Get a logo URL with a cache-busting version query when possible.
     */
    private function getVersionedLogoUrl(): string
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
