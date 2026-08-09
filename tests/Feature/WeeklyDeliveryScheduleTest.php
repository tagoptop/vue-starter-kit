<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

it('shows weekly deliveries with drivers and items for admin users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $driver = User::factory()->create(['role' => 'driver', 'name' => 'Weekly Driver']);
    $customer = User::factory()->create(['role' => 'customer', 'name' => 'Weekly Customer']);

    $category = Category::create([
        'name' => 'Weekly Schedule Category',
        'description' => 'Category for weekly schedule test',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => '10mm Rebar',
        'price' => 320.00,
        'stock_quantity' => 500,
    ]);

    $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();

    $order = Order::create([
        'order_number' => 'ORD-WEEKLY-001',
        'customer_id' => $customer->id,
        'driver_id' => $driver->id,
        'driver_name' => $driver->name,
        'driver_phone' => $driver->phone,
        'status' => 'approved',
        'total_amount' => 1600.00,
        'delivery_address' => 'Padre Garcia, Batangas',
        'scheduled_for' => $weekStart,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 320.00,
        'subtotal' => 1600.00,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('deliveries.weekly', ['week_start' => $weekStart]));

    $response->assertOk();
    $response->assertSee('Weekly Delivery Schedule');
    $response->assertSee('ORD-WEEKLY-001');
    $response->assertSee('Weekly Driver');
    $response->assertSee('10mm Rebar x 5');
});

it('forbids customers from viewing the weekly delivery schedule', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this
        ->actingAs($customer)
        ->get(route('deliveries.weekly'))
        ->assertForbidden();
});
