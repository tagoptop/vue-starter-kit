<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesPublicUploads;
use App\Support\BrandingHelper;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyBrandingController extends Controller
{
    use HandlesPublicUploads;

    /**
     * Show the company branding settings page (admin only).
     */
    public function edit(Request $request): View
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $logoUrl = $this->getVersionedLogoUrl();
        $companyName = BrandingHelper::getCompanyName();

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
            $path = $this->storePublicUpload($request->file('logo'), 'branding');
            $logoUrl = '/storage/' . $path;

            // Delete old logo if it exists and is not the default
            $oldLogo = BrandingHelper::getLogoUrl();
            if ($oldLogo !== BrandingHelper::DEFAULT_LOGO_URL && str_contains($oldLogo, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $oldLogo);
                if (Storage::disk('public')->exists($oldPath)) {
                    $this->deletePublicUpload($oldPath);
                }
            }
        } else {
            $logoUrl = BrandingHelper::getLogoUrl();
        }

        $this->saveBrandingSettings($companyName, $logoUrl);

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
        $logo = BrandingHelper::getLogoUrl();
        if ($logo !== BrandingHelper::DEFAULT_LOGO_URL && str_contains($logo, '/storage/')) {
            $path = str_replace('/storage/', '', $logo);
            if (Storage::disk('public')->exists($path)) {
                $this->deletePublicUpload($path);
            }
        }

        $this->saveBrandingSettings(BrandingHelper::DEFAULT_COMPANY_NAME, BrandingHelper::DEFAULT_LOGO_URL);

        return redirect()->route('settings.branding')
            ->with('status', 'Company branding reset to defaults!')
            ->with('status_type', 'reset');
    }

    /**
     * Save branding configuration.
     */
    private function saveBrandingSettings(string $companyName, string $logoUrl): void
    {
        BrandingHelper::set('company_name', $companyName);
        BrandingHelper::set('logo_url', $logoUrl);
    }

    /**
     * Get a logo URL with a cache-busting version query when possible.
     */
    private function getVersionedLogoUrl(): string
    {
        $logoUrl = BrandingHelper::getLogoUrl();

        if (str_starts_with($logoUrl, '/storage/')) {
            $storagePath = storage_path('app/public/' . str_replace('/storage/', '', $logoUrl));
            if (file_exists($storagePath)) {
                return $logoUrl . '?v=' . filemtime($storagePath);
            }
        }

        if ($logoUrl === BrandingHelper::DEFAULT_LOGO_URL) {
            $defaultLogoPath = public_path('logo.svg');
            if (file_exists($defaultLogoPath)) {
                return $logoUrl . '?v=' . filemtime($defaultLogoPath);
            }
        }

        return $logoUrl;
    }
}
