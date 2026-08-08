<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const PAYMENT_METHODS = ['cash', 'cod', 'check', 'credit_card', 'gcash', 'bank_transfer', 'other'];
    private const PAYMENT_STATUSES = ['unpaid', 'pending', 'paid'];

    public function deliveryMonitoring(Request $request): View
    {
        $status = $request->input('status');
        $search = trim((string) $request->input('search', ''));

        $baseQuery = Order::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($orderQuery) use ($search) {
                    $orderQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('delivery_address', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        });
                });
            });

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $orders = (clone $baseQuery)
            ->with('customer')
            ->withCount('items')
            ->when(in_array($status, ['pending', 'approved', 'delivered'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderByRaw("case status when 'pending' then 1 when 'approved' then 2 when 'delivered' then 3 else 4 end")
            ->orderBy('delivery_priority')
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        $calendarEvents = (clone $baseQuery)
            ->with('customer')
            ->when(in_array($status, ['pending', 'approved', 'delivered'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('delivery_priority')
            ->latest('updated_at')
            ->limit(300)
            ->get()
            ->map(function (Order $order): array {
                $scheduleDate = $order->scheduled_for?->toDateString()
                    ?? $order->delivered_at?->toDateString()
                    ?? $order->created_at->toDateString();

                return [
                    'id' => (string) $order->id,
                    'title' => $order->order_number . ' - ' . ($order->customer?->name ?? 'Unknown customer'),
                    'start' => $scheduleDate,
                    'url' => route('orders.show', $order),
                    'extendedProps' => [
                        'status' => $order->status,
                        'total' => number_format((float) $order->total_amount, 2),
                    ],
                    'classNames' => ['delivery-status-' . $order->status],
                ];
            })
            ->values();

        $customerLocations = (clone $baseQuery)
            ->with('customer')
            ->when(in_array($status, ['pending', 'approved', 'delivered'], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->whereNotNull('delivery_latitude')
            ->whereNotNull('delivery_longitude')
            ->latest('updated_at')
            ->limit(500)
            ->get()
            ->map(function (Order $order): array {
                return [
                    'id' => (int) $order->id,
                    'orderNumber' => $order->order_number,
                    'customerName' => $order->customer?->name ?? 'Unknown customer',
                    'address' => $order->delivery_address ?: 'Not provided',
                    'latitude' => (float) $order->delivery_latitude,
                    'longitude' => (float) $order->delivery_longitude,
                    'scheduledFor' => $order->scheduled_for?->format('M d, Y') ?? 'Not scheduled',
                ];
            })
            ->unique(function (array $location): string {
                return implode('|', [
                    $location['customerName'],
                    $location['address'],
                    $location['latitude'],
                    $location['longitude'],
                ]);
            })
            ->values();

        $summary = [
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'approved' => (int) ($statusCounts['approved'] ?? 0),
            'delivered' => (int) ($statusCounts['delivered'] ?? 0),
        ];

        $drivers = User::query()
            ->where('role', 'driver')
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return view('deliveries.index', compact('orders', 'status', 'search', 'summary', 'calendarEvents', 'customerLocations', 'drivers'));
    }

    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->role === 'driver') {
            return redirect()->route('driver.deliveries.index');
        }

        $orders = Order::with(['customer', 'items.product'])
            ->when($request->user()->role === 'customer', function ($query) use ($request) {
                $query->where('customer_id', $request->user()->id);
            })
            ->latest()
            ->paginate(12);

        return view('orders.index', compact('orders'));
    }

    public function driverDeliveries(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $deliveries = Order::with(['customer', 'items.product'])
            ->where('driver_id', $request->user()->id)
            ->whereIn('status', ['pending', 'approved'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($deliveryQuery) use ($search) {
                    $deliveryQuery
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhere('delivery_address', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items.product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw("case status when 'approved' then 1 when 'pending' then 2 else 3 end")
            ->orderBy('scheduled_for')
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('deliveries.driver', compact('deliveries', 'search'));
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
            'scheduled_for' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:' . implode(',', self::PAYMENT_METHODS)],
            'payment_other_method' => ['nullable', 'string', 'max:80', 'required_if:payment_method,other'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'payment_notes' => ['nullable', 'string'],
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
                    'delivery_priority' => ((int) Order::where('status', 'pending')->max('delivery_priority')) + 1,
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => in_array($validated['payment_method'], ['check', 'credit_card', 'gcash', 'bank_transfer'], true) ? 'pending' : 'unpaid',
                    'payment_other_method' => $validated['payment_method'] === 'other' ? ($validated['payment_other_method'] ?? null) : null,
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'payment_notes' => $validated['payment_notes'] ?? null,
                    'delivery_address' => $validated['delivery_address'],
                    'delivery_latitude' => $validated['delivery_latitude'] ?? null,
                    'delivery_longitude' => $validated['delivery_longitude'] ?? null,
                    'scheduled_for' => $validated['scheduled_for'] ?? null,
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
        $this->authorizeOrderAccess($request, $order);

        $order->load(['customer', 'items.product']);

        return view('orders.show', compact('order'));
    }

    public function deliveryReceipt(Order $order, Request $request): View
    {
        $this->authorizeOrderAccess($request, $order);

        $order->load(['customer', 'items.product']);

        $receiptNumber = str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);

        return view('orders.receipt', compact('order', 'receiptNumber'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,delivered'],
            'scheduled_for' => ['nullable', 'date'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
            'driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'delivery_notes' => ['nullable', 'string'],
            'payment_method' => ['nullable', 'in:' . implode(',', self::PAYMENT_METHODS)],
            'payment_other_method' => ['nullable', 'string', 'max:80', 'required_if:payment_method,other'],
            'payment_status' => ['nullable', 'in:' . implode(',', self::PAYMENT_STATUSES)],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_notes' => ['nullable', 'string'],
            'proof_of_delivery' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $updates = [
            'status' => $validated['status'],
        ];

        if ($order->status !== $validated['status']) {
            $updates['delivery_priority'] = ((int) Order::where('status', $validated['status'])->max('delivery_priority')) + 1;
        }

        if ($request->has('scheduled_for')) {
            $updates['scheduled_for'] = $validated['scheduled_for'] ?? null;
        }

        if ($request->has('driver_name')) {
            $updates['driver_name'] = $validated['driver_name'] ?? null;
        }

        if ($request->has('driver_phone')) {
            $updates['driver_phone'] = $validated['driver_phone'] ?? null;
        }

        if ($request->has('driver_id')) {
            $driverId = $validated['driver_id'] ?? null;

            if ($driverId === null) {
                $updates['driver_id'] = null;
                $updates['driver_name'] = null;
                $updates['driver_phone'] = null;
            } else {
                $driver = User::query()
                    ->where('id', $driverId)
                    ->where('role', 'driver')
                    ->first();

                if (! $driver) {
                    return back()->withErrors(['driver_id' => 'Selected user is not a driver.']);
                }

                $updates['driver_id'] = $driver->id;
                $updates['driver_name'] = $driver->name;
                $updates['driver_phone'] = $driver->phone;
            }
        }

        if ($request->has('delivery_notes')) {
            $updates['delivery_notes'] = $validated['delivery_notes'];
        }

        if ($request->has('payment_method')) {
            $paymentMethod = $validated['payment_method'] ?? $order->payment_method ?? 'cod';
            $updates['payment_method'] = $paymentMethod;
            $updates['payment_other_method'] = $paymentMethod === 'other' ? ($validated['payment_other_method'] ?? null) : null;
        }

        if ($request->has('payment_status')) {
            $updates['payment_status'] = $validated['payment_status'] ?? $order->payment_status ?? 'unpaid';
        }

        if ($request->has('payment_reference')) {
            $updates['payment_reference'] = $validated['payment_reference'] ?? null;
        }

        if ($request->has('payment_notes')) {
            $updates['payment_notes'] = $validated['payment_notes'] ?? null;
        }

        if ($request->has('paid_amount')) {
            $updates['paid_amount'] = $validated['paid_amount'] ?? null;
        }

        if (($updates['payment_status'] ?? $order->payment_status) === 'paid') {
            $updates['paid_at'] = $order->paid_at ?? now();
            if (! isset($updates['paid_amount']) || $updates['paid_amount'] === null) {
                $updates['paid_amount'] = $order->total_amount;
            }
        } elseif ($request->has('payment_status')) {
            $updates['paid_at'] = null;
            if (! $request->has('paid_amount')) {
                $updates['paid_amount'] = null;
            }
        }

        if ($validated['status'] === 'delivered') {
            $updates['delivered_at'] = $order->delivered_at ?? now();
        } else {
            $updates['delivered_at'] = null;
        }

        if ($request->hasFile('proof_of_delivery')) {
            $path = $request->file('proof_of_delivery')->store('proof-of-delivery', 'public');
            $updates['proof_of_delivery_path'] = '/storage/' . $path;

            if ($order->proof_of_delivery_path && str_starts_with($order->proof_of_delivery_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $order->proof_of_delivery_path);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        $order->update($updates);

        $hasDeliveryUpdate = $request->hasAny([
            'delivery_notes',
            'driver_id',
            'driver_name',
            'driver_phone',
            'scheduled_for',
            'payment_method',
            'payment_other_method',
            'payment_status',
            'payment_reference',
            'paid_amount',
            'payment_notes',
        ]) || $request->hasFile('proof_of_delivery');

        return back()->with('success', $hasDeliveryUpdate ? 'Delivery updated.' : 'Order status updated.');
    }

    public function reorderDeliveries(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,delivered'],
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer', 'distinct', 'exists:orders,id'],
        ]);

        $status = $validated['status'];
        $orderedIds = $validated['ordered_ids'];

        $matchingCount = Order::whereIn('id', $orderedIds)
            ->where('status', $status)
            ->count();

        if ($matchingCount !== count($orderedIds)) {
            return response()->json([
                'message' => 'The provided list includes cards that do not match the selected lane.',
            ], 422);
        }

        DB::transaction(function () use ($orderedIds): void {
            foreach ($orderedIds as $priority => $orderId) {
                Order::whereKey($orderId)->update([
                    'delivery_priority' => $priority + 1,
                ]);
            }
        });

        return response()->json([
            'message' => 'Delivery order saved.',
        ]);
    }

    private function getCart(): array
    {
        return session('cart', []);
    }

    private function authorizeOrderAccess(Request $request, Order $order): void
    {
        if ($request->user()->role === 'customer' && $order->customer_id !== $request->user()->id) {
            abort(403);
        }

        if ($request->user()->role === 'driver' && $order->driver_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function putCart(Request $request, array $cart): void
    {
        $request->session()->put('cart', $cart);
    }
}
