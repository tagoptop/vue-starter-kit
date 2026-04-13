<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $transactions = InventoryTransaction::with(['product', 'user'])->latest()->paginate(15);
        $products = Product::orderBy('name')->get();
        $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->get();

        return view('inventory.index', compact('transactions', 'products', 'lowStockProducts'));
    }

    public function stockIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);
            $product->increment('stock_quantity', $validated['quantity']);

            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'reference' => 'MANUAL-STOCK-IN',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock in transaction recorded.');
    }

    public function stockOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            if ($product->stock_quantity < $validated['quantity']) {
                abort(422, 'Insufficient stock quantity.');
            }

            $product->decrement('stock_quantity', $validated['quantity']);

            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $request->user()?->id,
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'reference' => 'MANUAL-STOCK-OUT',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock out transaction recorded.');
    }
}
