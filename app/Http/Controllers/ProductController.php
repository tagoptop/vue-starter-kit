<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesPublicUploads;
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
    use HandlesPublicUploads;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $categoryId = $request->integer('category_id');
        $supplierId = $request->integer('supplier_id');
        $stock = $request->input('stock');

        $products = Product::query()
            ->with(['category', 'supplier'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($productQuery) use ($search) {
                    $productQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                            $supplierQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($categoryId > 0, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($supplierId > 0, function ($query) use ($supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when(in_array($stock, ['in_stock', 'low_stock', 'out_of_stock'], true), function ($query) use ($stock) {
                if ($stock === 'in_stock') {
                    $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
                }

                if ($stock === 'low_stock') {
                    $query
                        ->where('stock_quantity', '>', 0)
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                }

                if ($stock === 'out_of_stock') {
                    $query->where('stock_quantity', '<=', 0);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('products.index', compact('products', 'categories', 'suppliers', 'search', 'categoryId', 'supplierId', 'stock'));
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
            $validated['image_path'] = $this->storePublicUpload($request->file('image'), 'products');
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
                $this->deletePublicUpload($product->image_path);
            }

            $validated['image_path'] = $this->storePublicUpload($request->file('image'), 'products');
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
            $this->deletePublicUpload($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
