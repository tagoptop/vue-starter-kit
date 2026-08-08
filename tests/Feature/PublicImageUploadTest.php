<?php

use App\Models\Category;
use App\Models\AppSetting;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->publicStorageOriginallyExisted = file_exists(public_path('storage')) || is_link(public_path('storage'));
    $this->temporaryUploadPaths = [];
});

afterEach(function () {
    foreach ([$this->brandingLogoPath ?? null, $this->productImagePath ?? null] as $path) {
        if (! $path) {
            continue;
        }

        Storage::disk('public')->delete($path);

        $mirroredPath = public_path('storage/' . $path);
        if (file_exists($mirroredPath) && ! is_link(public_path('storage'))) {
            app(Filesystem::class)->delete($mirroredPath);
        }
    }

    $publicStoragePath = public_path('storage');
    if (! $this->publicStorageOriginallyExisted && is_link($publicStoragePath)) {
        unlink($publicStoragePath);
    }

    if (! $this->publicStorageOriginallyExisted && is_dir($publicStoragePath)) {
        app(Filesystem::class)->deleteDirectory($publicStoragePath);
    }

    foreach ($this->temporaryUploadPaths as $temporaryUploadPath) {
        if (file_exists($temporaryUploadPath)) {
            unlink($temporaryUploadPath);
        }
    }
});

function fakePngUpload(string $name): UploadedFile
{
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9p2ZYxQAAAAASUVORK5CYII=');
    $path = tempnam(sys_get_temp_dir(), 'upload-');

    file_put_contents($path, $png);

    $temporaryUploadPaths = test()->temporaryUploadPaths;
    $temporaryUploadPaths[] = $path;
    test()->temporaryUploadPaths = $temporaryUploadPaths;

    return new UploadedFile($path, $name, 'image/png', null, true);
}

it('stores branding logos in a publicly accessible location', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->post(route('branding.update'), [
            'company_name' => 'Updated Company',
            'logo' => fakePngUpload('logo.png'),
        ]);

    $response
        ->assertRedirect(route('settings.branding'))
        ->assertSessionHas('status', 'Company branding updated successfully!');

    $logoUrl = AppSetting::query()->where('key', 'logo_url')->value('value');

    expect($logoUrl)->toStartWith('/storage/branding/');

    $this->brandingLogoPath = str_replace('/storage/', '', $logoUrl);

    Storage::disk('public')->assertExists($this->brandingLogoPath);
    expect(file_exists(public_path('storage/' . $this->brandingLogoPath)))->toBeTrue();
});

it('stores product images in a publicly accessible location', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $category = Category::create([
        'name' => 'Blocks',
        'description' => 'Construction blocks',
    ]);

    $supplier = Supplier::create([
        'name' => 'Concrete Supply Co.',
        'contact_person' => 'Maria Santos',
        'email' => 'supplier@example.com',
        'phone' => '09170000000',
        'address' => 'Quezon City',
    ]);

    $response = $this
        ->actingAs($staff)
        ->post(route('products.store'), [
            'name' => 'Hollow Blocks',
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'description' => 'Standard size hollow blocks',
            'price' => '25.50',
            'stock_quantity' => 100,
            'low_stock_threshold' => 10,
            'image' => fakePngUpload('product.png'),
        ]);

    $response
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success', 'Product created successfully.');

    $product = Product::query()->firstOrFail();

    expect($product->image_path)->not->toBeNull();

    $this->productImagePath = $product->image_path;

    Storage::disk('public')->assertExists($this->productImagePath);
    expect(file_exists(public_path('storage/' . $this->productImagePath)))->toBeTrue();
});