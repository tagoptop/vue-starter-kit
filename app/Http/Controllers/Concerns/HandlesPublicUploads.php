<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

trait HandlesPublicUploads
{
    protected function storePublicUpload(UploadedFile $file, string $directory): string
    {
        $this->ensurePublicStorageLink();

        $path = $file->store($directory, 'public');

        $this->mirrorFileWhenStorageLinkIsMissing($path);

        return $path;
    }

    protected function deletePublicUpload(?string $path): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
        $this->deleteMirroredFileWhenStorageLinkIsMissing($path);
    }

    private function ensurePublicStorageLink(): void
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath) || is_dir($publicStoragePath)) {
            return;
        }

        $storagePublicPath = storage_path('app/public');

        if (! is_dir($storagePublicPath)) {
            mkdir($storagePublicPath, 0755, true);
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable) {
            // Fall back to file mirroring when the symlink cannot be created at runtime.
        }
    }

    private function mirrorFileWhenStorageLinkIsMissing(string $path): void
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath)) {
            return;
        }

        $sourcePath = Storage::disk('public')->path($path);
        $targetPath = public_path('storage/' . $path);
        $targetDirectory = dirname($targetPath);

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        app(Filesystem::class)->copy($sourcePath, $targetPath);
    }

    private function deleteMirroredFileWhenStorageLinkIsMissing(string $path): void
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath)) {
            return;
        }

        $mirroredPath = public_path('storage/' . $path);

        if (file_exists($mirroredPath)) {
            app(Filesystem::class)->delete($mirroredPath);
        }
    }
}