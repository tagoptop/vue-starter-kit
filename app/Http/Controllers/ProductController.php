<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::with(['category', 'supplier'])->latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        if ($product->stock_quantity > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'type' => 'in',
                'quantity' => $product->stock_quantity,
                'reference' => 'PRODUCT-CREATE',
                'notes' => 'Initial stock quantity',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $oldStock = $product->stock_quantity;
        $product->update($validated);

        if ($product->stock_quantity > $oldStock) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'type' => 'in',
                'quantity' => $product->stock_quantity - $oldStock,
                'reference' => 'PRODUCT-UPDATE',
                'notes' => 'Stock adjusted upward from product update',
            ]);
        }

        if ($product->stock_quantity < $oldStock) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'type' => 'out',
                'quantity' => $oldStock - $product->stock_quantity,
                'reference' => 'PRODUCT-UPDATE',
                'notes' => 'Stock adjusted downward from product update',
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
