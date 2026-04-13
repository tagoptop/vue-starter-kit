<?php

namespace Database\Seeders;

use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();
        $staff = User::where('role', 'staff')->first();

        if (! $customer) {
            return;
        }

        $product = Product::first();

        if (! $product) {
            return;
        }

        DB::transaction(function () use ($customer, $staff, $product): void {
            $quantity = 3;
            $subtotal = $quantity * (float) $product->price;

            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-0001',
                'customer_id' => $customer->id,
                'status' => 'approved',
                'total_amount' => $subtotal,
                'notes' => 'Seeded sample order',
                'created_at' => now()->subDay(),
            ]);

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
                'user_id' => $staff?->id,
                'type' => 'out',
                'quantity' => $quantity,
                'reference' => $order->order_number,
                'notes' => 'Stock deduction from seeded order',
            ]);
        });
    }
}
