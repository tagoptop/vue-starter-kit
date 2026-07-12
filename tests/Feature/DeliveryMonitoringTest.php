<?php

use App\Models\Order;
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
});

it('allows staff to update delivery status and notes', function () {
    Storage::fake('public');

    $staff = User::factory()->create(['role' => 'staff']);
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
            'driver_name' => 'Rogelio Cruz',
            'driver_phone' => '09171230000',
            'proof_of_delivery' => UploadedFile::fake()->create('pod.pdf', 120, 'application/pdf'),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Delivery updated.');

    expect($order->fresh())
        ->status->toBe('delivered')
        ->notes->toBe('Initial customer request')
        ->delivery_notes->toBe('Delivered to site foreman.')
        ->driver_name->toBe('Rogelio Cruz')
        ->driver_phone->toBe('09171230000')
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