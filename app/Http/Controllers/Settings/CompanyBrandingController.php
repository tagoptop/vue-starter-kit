<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyBrandingController extends Controller
{
    /**
     * Show the company branding settings page (admin only).
     */
    public function edit(Request $request): Response
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $logoUrl = config('branding.logo_url', '/logo.svg');
        $companyName = config('branding.company_name', 'Construction Supply');

        return Inertia::render('settings/branding', [
            'logoUrl' => $logoUrl,
            'companyName' => $companyName,
        ]);
    }

    /**
     * Update the company branding settings.
     */
    public function update(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:svg,png,jpg,jpeg,gif,webp|max:2048',
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

        return redirect()->route('branding.edit')->with('status', 'Company branding updated successfully!');
    }

    /**
     * Reset to default branding.
     */
    public function reset(Request $request)
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

        return redirect()->route('branding.edit')->with('status', 'Company branding reset to defaults!');
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
    }
}
