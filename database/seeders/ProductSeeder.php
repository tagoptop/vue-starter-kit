<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        $products = [
            ['name' => 'Portland Cement 40kg', 'category' => 'Cement', 'price' => 265.00, 'stock_quantity' => 150, 'supplier' => 'BuildRight Trading'],
            ['name' => 'Rebar 10mm x 6m', 'category' => 'Hardware', 'price' => 310.00, 'stock_quantity' => 85, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'Washed Sand (1 cu.m.)', 'category' => 'Aggregates', 'price' => 1200.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'Gravel 3/4 (1 cu.m.)', 'category' => 'Aggregates', 'price' => 1450.00, 'stock_quantity' => 28, 'supplier' => 'SolidMix Industrial'],
        ];

        foreach ($products as $entry) {
            $category = Category::where('name', $entry['category'])->firstOrFail();
            $supplier = Supplier::where('name', $entry['supplier'])->firstOrFail();

            $product = Product::updateOrCreate(
                ['name' => $entry['name']],
                [
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'description' => $entry['name'],
                    'price' => $entry['price'],
                    'stock_quantity' => $entry['stock_quantity'],
                    'low_stock_threshold' => 10,
                ]
            );

            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $admin?->id,
                'type' => 'in',
                'quantity' => $entry['stock_quantity'],
                'reference' => 'SEED-STOCK-IN',
                'notes' => 'Initial stock from seeder',
            ]);
        }
    }
}
