<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

it('applies discount to order total during checkout', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Discount Test Category',
        'description' => 'Category used for discount tests',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Discount Test Product',
        'price' => 150.00,
        'stock_quantity' => 100,
    ]);

    $cart = [
        $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 2,
            'stock_quantity' => $product->stock_quantity,
            'category' => $category->name,
            'image_path' => null,
        ],
    ];

    $response = $this
        ->actingAs($customer)
        ->withSession(['cart' => $cart])
        ->post(route('orders.store'), [
            'delivery_address' => 'Sample Delivery Address',
            'payment_method' => 'cash',
            'discount_amount' => 25,
        ]);

    $response->assertRedirect(route('orders.index'));
    $response->assertSessionHas('success', 'Order placed successfully.');

    $order = Order::latest('id')->first();

    expect($order)->not->toBeNull();
    expect((float) $order->subtotal_amount)->toBe(300.00);
    expect((float) $order->discount_amount)->toBe(25.00);
    expect((float) $order->total_amount)->toBe(275.00);
});

it('rejects discount greater than cart subtotal', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $category = Category::create([
        'name' => 'Discount Validation Category',
        'description' => 'Category used for discount validation tests',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Discount Validation Product',
        'price' => 100.00,
        'stock_quantity' => 100,
    ]);

    $cart = [
        $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 1,
            'stock_quantity' => $product->stock_quantity,
            'category' => $category->name,
            'image_path' => null,
        ],
    ];

    $response = $this
        ->actingAs($customer)
        ->from(route('orders.create'))
        ->withSession(['cart' => $cart])
        ->post(route('orders.store'), [
            'delivery_address' => 'Sample Delivery Address',
            'payment_method' => 'cash',
            'discount_amount' => 101,
        ]);

    $response->assertRedirect(route('orders.create'));
    $response->assertSessionHasErrors('discount_amount');

    expect(Order::count())->toBe(0);
});
