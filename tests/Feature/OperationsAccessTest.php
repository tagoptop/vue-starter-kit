<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

it('forbids customers from opening dashboard', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this
        ->actingAs($customer)
        ->get(route('dashboard'))
        ->assertForbidden();
});

it('allows warehouseman to access warehouse preparation page with items', function () {
    $warehouseman = User::factory()->create(['role' => 'warehouseman']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Warehouse Category',
        'description' => 'Warehouse test category',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Portland Cement',
        'price' => 285.00,
        'stock_quantity' => 200,
    ]);

    $order = Order::create([
        'order_number' => 'ORD-WH-001',
        'customer_id' => $customer->id,
        'status' => 'approved',
        'total_amount' => 855.00,
        'delivery_address' => 'Padre Garcia, Batangas',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => 285.00,
        'subtotal' => 855.00,
    ]);

    $response = $this
        ->actingAs($warehouseman)
        ->get(route('warehouse.preparation'));

    $response->assertOk();
    $response->assertSee('Warehouse Preparation');
    $response->assertSee('ORD-WH-001');
    $response->assertSee('Portland Cement');
});

it('allows checker to access spot checks page with delivery items', function () {
    $checker = User::factory()->create(['role' => 'checker']);
    $driver = User::factory()->create(['role' => 'driver']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Checker Category',
        'description' => 'Checker test category',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => '4in CHB',
        'price' => 22.00,
        'stock_quantity' => 1000,
    ]);

    $order = Order::create([
        'order_number' => 'ORD-CHK-001',
        'customer_id' => $customer->id,
        'driver_id' => $driver->id,
        'driver_name' => $driver->name,
        'driver_phone' => $driver->phone,
        'status' => 'approved',
        'total_amount' => 220.00,
        'delivery_address' => 'Padre Garcia, Batangas',
        'delivery_latitude' => 13.8799350,
        'delivery_longitude' => 121.2138880,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 22.00,
        'subtotal' => 220.00,
    ]);

    $response = $this
        ->actingAs($checker)
        ->get(route('checker.spot-checks'));

    $response->assertOk();
    $response->assertSee('Checker Spot Checks');
    $response->assertSee('ORD-CHK-001');
    $response->assertSee('4in CHB x 10');
});

it('forbids checker from warehouse page and warehouseman from checker page', function () {
    $checker = User::factory()->create(['role' => 'checker']);
    $warehouseman = User::factory()->create(['role' => 'warehouseman']);

    $this
        ->actingAs($checker)
        ->get(route('warehouse.preparation'))
        ->assertForbidden();

    $this
        ->actingAs($warehouseman)
        ->get(route('checker.spot-checks'))
        ->assertForbidden();
});

it('allows warehouseman to mark an order item as prepared', function () {
    $warehouseman = User::factory()->create(['role' => 'warehouseman']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Mark Prepared Category',
        'description' => 'Mark prepared category',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => '10mm Rebar',
        'price' => 350.00,
        'stock_quantity' => 500,
    ]);

    $order = Order::create([
        'order_number' => 'ORD-WH-002',
        'customer_id' => $customer->id,
        'status' => 'approved',
        'total_amount' => 700.00,
        'delivery_address' => 'Lipa City, Batangas',
    ]);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 350.00,
        'subtotal' => 700.00,
    ]);

    $response = $this
        ->actingAs($warehouseman)
        ->patch(route('warehouse.preparation.items.mark-prepared', $item));

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Item marked as prepared.');

    $this->assertDatabaseHas('order_items', [
        'id' => $item->id,
        'is_prepared' => 1,
    ]);
});

it('forbids non-warehouseman from marking an order item as prepared', function () {
    $checker = User::factory()->create(['role' => 'checker']);
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Prepared Access Category',
        'description' => 'Prepared access category',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Hollow Blocks',
        'price' => 18.00,
        'stock_quantity' => 1000,
    ]);

    $order = Order::create([
        'order_number' => 'ORD-WH-003',
        'customer_id' => $customer->id,
        'status' => 'approved',
        'total_amount' => 180.00,
        'delivery_address' => 'Sto. Tomas, Batangas',
    ]);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 18.00,
        'subtotal' => 180.00,
    ]);

    $this
        ->actingAs($checker)
        ->patch(route('warehouse.preparation.items.mark-prepared', $item))
        ->assertForbidden();
});
