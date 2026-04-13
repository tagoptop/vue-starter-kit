<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['customer', 'items.product'])
            ->when($request->user()->role === 'customer', function ($query) use ($request) {
                $query->where('customer_id', $request->user()->id);
            })
            ->latest()
            ->paginate(12);

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        $products = Product::with(['category', 'supplier'])->orderBy('name')->get();
        $cart = $this->getCart();

        return view('orders.create', [
            'products' => $products,
            'cart' => $cart,
            'cartCount' => collect($cart)->sum('quantity'),
            'cartTotal' => collect($cart)->sum(fn ($item) => $item['quantity'] * $item['price']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:delivery_longitude'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:delivery_latitude'],
            'notes' => ['nullable', 'string'],
        ]);

        $cart = $this->getCart();

        if ($cart === []) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        try {
            DB::transaction(function () use ($validated, $request, $cart): void {
                $order = Order::create([
                    'order_number' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                    'customer_id' => $request->user()->id,
                    'status' => 'pending',
                    'delivery_address' => $validated['delivery_address'],
                    'delivery_latitude' => $validated['delivery_latitude'] ?? null,
                    'delivery_longitude' => $validated['delivery_longitude'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $total = 0;

                foreach ($cart as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                    $quantity = (int) $item['quantity'];

                    if ($product->stock_quantity < $quantity) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'cart' => "Insufficient stock for {$product->name}.",
                        ]);
                    }

                    $subtotal = $quantity * (float) $product->price;
                    $total += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    $product->decrement('stock_quantity', $quantity);

                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'user_id' => $request->user()->id,
                        'type' => 'out',
                        'quantity' => $quantity,
                        'reference' => $order->order_number,
                        'notes' => 'Auto deduction from order placement',
                    ]);
                }

                $order->update(['total_amount' => $total]);
            });
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        }

        $request->session()->forget('cart');

        return redirect()->route('orders.index')->with('success', 'Order placed successfully.');
    }

    public function addToCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $cart = $this->getCart();
        $currentQuantity = $cart[$product->id]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + (int) $validated['quantity'];

        if ($newQuantity > $product->stock_quantity) {
            return back()->withErrors([
                'cart' => "Only {$product->stock_quantity} items available for {$product->name}.",
            ]);
        }

        $cart[$product->id] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => $newQuantity,
            'stock_quantity' => $product->stock_quantity,
            'category' => $product->category?->name,
            'image_path' => $product->image_path,
        ];

        $this->putCart($request, $cart);

        return back()->with('success', 'Item added to cart.');
    }

    public function updateCart(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = $this->getCart();

        if (! array_key_exists($product->id, $cart)) {
            return back();
        }

        $quantity = (int) $validated['quantity'];

        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            if ($quantity > $product->stock_quantity) {
                return back()->withErrors([
                    'cart' => "Only {$product->stock_quantity} items available for {$product->name}.",
                ]);
            }

            $cart[$product->id]['quantity'] = $quantity;
            $cart[$product->id]['stock_quantity'] = $product->stock_quantity;
        }

        $this->putCart($request, $cart);

        return back()->with('success', 'Cart updated.');
    }

    public function removeFromCart(Request $request, Product $product): RedirectResponse
    {
        $cart = $this->getCart();
        unset($cart[$product->id]);
        $this->putCart($request, $cart);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clearCart(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    public function show(Order $order, Request $request): View
    {
        if ($request->user()->role === 'customer' && $order->customer_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['customer', 'items.product']);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,delivered'],
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated.');
    }

    private function getCart(): array
    {
        return session('cart', []);
    }

    private function putCart(Request $request, array $cart): void
    {
        $request->session()->put('cart', $cart);
    }
}
