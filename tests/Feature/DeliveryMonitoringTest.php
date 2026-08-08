<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('allows admin users to monitor deliveries', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    $order = Order::create([
        'order_number' => 'ORD-DELIVERY-001',
        'customer_id' => $customer->id,
        'status' => 'approved',
        'total_amount' => 2450.50,
        'delivery_address' => 'Zone 4, Riverside, Manila',
        'delivery_latitude' => 14.5995,
        'delivery_longitude' => 120.9842,
        'notes' => 'Call before arrival',
        'delivery_notes' => 'Driver should use side gate.',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('deliveries.index'));

    $response->assertOk();
    $response->assertSee('Delivery Monitoring');
    $response->assertSee($order->order_number);
    $response->assertSee('Zone 4, Riverside, Manila');
    $response->assertSee('Call before arrival');
    $response->assertSee('Driver should use side gate.');
    $response->assertSee('Customer Delivery Locations');
});

it('allows staff to update delivery status and notes', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'staff']);
    $driver = User::factory()->create(['role' => 'driver']);
    $customer = User::factory()->create(['role' => 'customer']);

    $order = Order::create([
        'order_number' => 'ORD-DELIVERY-002',
        'customer_id' => $customer->id,
        'status' => 'approved',
        'total_amount' => 990.00,
        'delivery_address' => 'Warehouse Road, Cebu',
        'notes' => 'Initial customer request',
        'delivery_notes' => 'Initial delivery note',
    ]);

    $response = $this
        ->actingAs($staff)
        ->patch(route('orders.update-status', $order), [
            'status' => 'delivered',
            'delivery_notes' => 'Delivered to site foreman.',
            'driver_id' => $driver->id,
            'proof_of_delivery' => UploadedFile::fake()->create('pod.pdf', 120, 'application/pdf'),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Delivery updated.');

    expect($order->fresh())
        ->status->toBe('delivered')
        ->notes->toBe('Initial customer request')
        ->delivery_notes->toBe('Delivered to site foreman.')
        ->driver_id->toBe($driver->id)
        ->driver_name->toBe($driver->name)
        ->driver_phone->toBe($driver->phone)
        ->proof_of_delivery_path->not->toBeNull()
        ->delivered_at->not->toBeNull();

    Storage::disk('public')->assertExists(str_replace('/storage/', '', $order->fresh()->proof_of_delivery_path));
});

it('forbids customers from opening the delivery monitoring page', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this
        ->actingAs($customer)
        ->get(route('deliveries.index'))
        ->assertForbidden();
});

it('allows drivers to see delivery items and delivery addresses', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $otherDriver = User::factory()->create(['role' => 'driver']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Aggregates',
        'description' => 'Construction aggregate materials',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Washed Sand',
        'price' => 1250.00,
        'stock_quantity' => 100,
    ]);

    $order = Order::create([
        'order_number' => 'ORD-DRIVER-001',
        'customer_id' => $customer->id,
        'driver_id' => $driver->id,
        'status' => 'approved',
        'total_amount' => 2500.00,
        'delivery_address' => 'Block 7, Lot 12, Green Valley, Davao',
    ]);

    $otherOrder = Order::create([
        'order_number' => 'ORD-DRIVER-002',
        'customer_id' => $customer->id,
        'driver_id' => $otherDriver->id,
        'status' => 'approved',
        'total_amount' => 1700.00,
        'delivery_address' => 'Phase 1, Riverside, Cagayan de Oro',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 1250.00,
        'subtotal' => 2500.00,
    ]);

    $response = $this
        ->actingAs($driver)
        ->get(route('driver.deliveries.index'));

    $response->assertOk();
    $response->assertSee('My Deliveries');
    $response->assertSee('Washed Sand x 2');
    $response->assertSee('Block 7, Lot 12, Green Valley, Davao');
    $response->assertDontSee($otherOrder->order_number);
});

it('forbids non-drivers from opening the driver deliveries page', function () {
    $staff = User::factory()->create(['role' => 'staff']);

    $this
        ->actingAs($staff)
        ->get(route('driver.deliveries.index'))
        ->assertForbidden();
});

it('redirects drivers away from the general orders page', function () {
    $driver = User::factory()->create(['role' => 'driver']);

    $this
        ->actingAs($driver)
        ->get(route('orders.index'))
        ->assertRedirect(route('driver.deliveries.index'));
});

it('forbids drivers from viewing orders assigned to another driver', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $otherDriver = User::factory()->create(['role' => 'driver']);
    $customer = User::factory()->create(['role' => 'customer']);

    $order = Order::create([
        'order_number' => 'ORD-DRIVER-003',
        'customer_id' => $customer->id,
        'driver_id' => $otherDriver->id,
        'status' => 'approved',
        'total_amount' => 2000.00,
        'delivery_address' => 'Purok 2, Butuan City',
    ]);

    $this
        ->actingAs($driver)
        ->get(route('orders.show', $order))
        ->assertForbidden();
});

it('renders delivery receipt with plotted order items and address', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Blocks',
        'description' => 'Concrete blocks',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => '4in CHB',
        'price' => 22.00,
        'stock_quantity' => 1000,
    ]);

    $order = Order::create([
        'order_number' => 'ORD-RECEIPT-001',
        'customer_id' => $customer->id,
        'status' => 'approved',
        'total_amount' => 220.00,
        'delivery_address' => 'Sitio Malinis, Padre Garcia, Batangas',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 22.00,
        'subtotal' => 220.00,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('orders.receipt', $order));

    $response->assertOk();
    $response->assertSee('DELIVERY RECEIPT');
    $response->assertSee('Sitio Malinis, Padre Garcia, Batangas');
    $response->assertSee('4in CHB');
    $response->assertSee('220.00');
});

it('forbids unassigned drivers from opening the order receipt', function () {
    $driver = User::factory()->create(['role' => 'driver']);
    $otherDriver = User::factory()->create(['role' => 'driver']);
    $customer = User::factory()->create(['role' => 'customer']);

    $order = Order::create([
        'order_number' => 'ORD-RECEIPT-002',
        'customer_id' => $customer->id,
        'driver_id' => $otherDriver->id,
        'status' => 'approved',
        'total_amount' => 500.00,
        'delivery_address' => 'Barangay Luntal, Taal, Batangas',
    ]);

    $this
        ->actingAs($driver)
        ->get(route('orders.receipt', $order))
        ->assertForbidden();
});