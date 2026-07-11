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
            // Existing products
            ['name' => 'Portland Cement 40kg', 'category' => 'Cement', 'price' => 265.00, 'stock_quantity' => 150, 'supplier' => 'BuildRight Trading'],
            ['name' => 'Rebar 10mm x 6m', 'category' => 'Hardware', 'price' => 310.00, 'stock_quantity' => 85, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'Washed Sand (1 cu.m.)', 'category' => 'Aggregates', 'price' => 1200.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'Gravel 3/4 (1 cu.m.)', 'category' => 'Aggregates', 'price' => 1450.00, 'stock_quantity' => 28, 'supplier' => 'SolidMix Industrial'],

            // Deformed Bars
            ['name' => 'DEFORMED BAR 7mm', 'category' => 'Hardware', 'price' => 70.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'DEFORMED BAR 8mm', 'category' => 'Hardware', 'price' => 85.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'DEFORMED BAR 9mm', 'category' => 'Hardware', 'price' => 95.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'DEFORMED BAR 10mm', 'category' => 'Hardware', 'price' => 135.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'DEFORMED BAR 12mm', 'category' => 'Hardware', 'price' => 190.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'DEFORMED BAR 16mm', 'category' => 'Hardware', 'price' => 330.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],

            // Round Bars
            ['name' => 'ROUND BAR 9mm', 'category' => 'Hardware', 'price' => 125.00, 'stock_quantity' => 80, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ROUND BAR 10mm', 'category' => 'Hardware', 'price' => 138.00, 'stock_quantity' => 80, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ROUND BAR 12mm', 'category' => 'Hardware', 'price' => 245.00, 'stock_quantity' => 80, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ROUND BAR 16mm', 'category' => 'Hardware', 'price' => 340.00, 'stock_quantity' => 80, 'supplier' => 'SolidMix Industrial'],

            // Square Bars
            ['name' => 'SQUARE BAR 9mm', 'category' => 'Hardware', 'price' => 140.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'SQUARE BAR 10mm', 'category' => 'Hardware', 'price' => 180.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'SQUARE BAR 12mm', 'category' => 'Hardware', 'price' => 220.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],

            // Angle Bars
            ['name' => 'ANGLE BAR 1/4 X 1 (Green) 3.5mm', 'category' => 'Hardware', 'price' => 400.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'ANGLE BAR 3/16 X 1 (Red)', 'category' => 'Hardware', 'price' => 280.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'ANGLE BAR 3/16 X 1 3mm', 'category' => 'Hardware', 'price' => 350.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'ANGLE BAR 3/16 X 1.5 (Red)', 'category' => 'Hardware', 'price' => 440.00, 'stock_quantity' => 60, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ANGLE BAR 1/4 X 1.5 (Yellow) 3mm', 'category' => 'Hardware', 'price' => 490.00, 'stock_quantity' => 60, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ANGLE BAR 1/4 X 1.5 (Orange) 4mm', 'category' => 'Hardware', 'price' => 600.00, 'stock_quantity' => 60, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ANGLE BAR 1/4 X 1.5 (White) 5mm', 'category' => 'Hardware', 'price' => 850.00, 'stock_quantity' => 50, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ANGLE BAR 3/16 X 2 (Red)', 'category' => 'Hardware', 'price' => 545.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'ANGLE BAR 1/4 X 2 (Yellow) 3mm', 'category' => 'Hardware', 'price' => 570.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'ANGLE BAR 1/4 X 2 (White) 5mm', 'category' => 'Hardware', 'price' => 950.00, 'stock_quantity' => 50, 'supplier' => 'SolidMix Industrial'],

            // Flat Bars
            ['name' => 'FLAT BAR 1/4 X 1 (White)', 'category' => 'Hardware', 'price' => 335.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],
            ['name' => 'FLAT BAR 3/16 X 1 (Yellow)', 'category' => 'Hardware', 'price' => 170.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],
            ['name' => 'FLAT BAR 3/16 X 1 (Red)', 'category' => 'Hardware', 'price' => 230.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],
            ['name' => 'FLAT BAR 1/4 X 1.5 (Orange)', 'category' => 'Hardware', 'price' => 585.00, 'stock_quantity' => 60, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'FLAT BAR 3/16 X 1.5 (Red)', 'category' => 'Hardware', 'price' => 225.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],
            ['name' => 'FLAT BAR 1/4 X 2 (White)', 'category' => 'Hardware', 'price' => 950.00, 'stock_quantity' => 50, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'FLAT BAR 3/16 X 2 (Red)', 'category' => 'Hardware', 'price' => 545.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],

            // Tubular
            ['name' => 'TUBULAR 3/4 x 3/4', 'category' => 'Hardware', 'price' => 260.00, 'stock_quantity' => 50, 'supplier' => 'BuildRight Trading'],
            ['name' => 'TUBULAR 1 x 1', 'category' => 'Hardware', 'price' => 330.00, 'stock_quantity' => 50, 'supplier' => 'BuildRight Trading'],
            ['name' => 'TUBULAR 1.5 x 1.5', 'category' => 'Hardware', 'price' => 450.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'TUBULAR 1 x 1.5', 'category' => 'Hardware', 'price' => 420.00, 'stock_quantity' => 40, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'TUBULAR 1 x 2', 'category' => 'Hardware', 'price' => 450.00, 'stock_quantity' => 40, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'TUBULAR 1 x 3', 'category' => 'Hardware', 'price' => 540.00, 'stock_quantity' => 35, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'TUBULAR 2 x 2', 'category' => 'Hardware', 'price' => 570.00, 'stock_quantity' => 35, 'supplier' => 'BuildRight Trading'],
            ['name' => 'TUBULAR 2 x 3', 'category' => 'Hardware', 'price' => 670.00, 'stock_quantity' => 35, 'supplier' => 'BuildRight Trading'],
            ['name' => 'TUBULAR 2 x 4', 'category' => 'Hardware', 'price' => 720.00, 'stock_quantity' => 30, 'supplier' => 'SolidMix Industrial'],

            // GI Pipes
            ['name' => 'GI PIPE 0.5', 'category' => 'Hardware', 'price' => 420.00, 'stock_quantity' => 45, 'supplier' => 'BuildRight Trading'],
            ['name' => 'GI PIPE 3/4', 'category' => 'Hardware', 'price' => 500.00, 'stock_quantity' => 45, 'supplier' => 'BuildRight Trading'],
            ['name' => 'GI PIPE 1', 'category' => 'Hardware', 'price' => 870.00, 'stock_quantity' => 40, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'GI PIPE 1 1/4', 'category' => 'Hardware', 'price' => 1000.00, 'stock_quantity' => 35, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'GI PIPE 1.5', 'category' => 'Hardware', 'price' => 1150.00, 'stock_quantity' => 35, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'GI PIPE 2', 'category' => 'Hardware', 'price' => 1580.00, 'stock_quantity' => 30, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'GI PIPE 3', 'category' => 'Hardware', 'price' => 2900.00, 'stock_quantity' => 25, 'supplier' => 'SolidMix Industrial'],

            // C-Purlins & Channels
            ['name' => 'C-PURLINS 2x3', 'category' => 'Hardware', 'price' => 560.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'C-PURLINS 2x4', 'category' => 'Hardware', 'price' => 620.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'C-PURLINS 2x6', 'category' => 'Hardware', 'price' => 700.00, 'stock_quantity' => 35, 'supplier' => 'BuildRight Trading'],
            ['name' => 'CHANNEL BAR 2x3 Manipis', 'category' => 'Hardware', 'price' => 900.00, 'stock_quantity' => 30, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'CHANNEL BAR 2x3 Makapal', 'category' => 'Hardware', 'price' => 1000.00, 'stock_quantity' => 30, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'CHANNEL BAR 2x4', 'category' => 'Hardware', 'price' => 1300.00, 'stock_quantity' => 25, 'supplier' => 'SolidMix Industrial'],

            // Metal Framing & Hardware
            ['name' => 'METAL PARRING', 'category' => 'Hardware', 'price' => 130.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'WALL ANGLE 10FT', 'category' => 'Hardware', 'price' => 70.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'CARRYING CHANNEL', 'category' => 'Hardware', 'price' => 100.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'WALL CLIP', 'category' => 'Hardware', 'price' => 5.00, 'stock_quantity' => 500, 'supplier' => 'BuildRight Trading'],
            ['name' => 'Z BAR', 'category' => 'Hardware', 'price' => 350.00, 'stock_quantity' => 50, 'supplier' => 'SolidMix Industrial'],

            // Fencing Materials
            ['name' => 'STEEL MATTING GI Makapal', 'category' => 'Fencing', 'price' => 550.00, 'stock_quantity' => 30, 'supplier' => 'BuildRight Trading'],
            ['name' => 'STEEL MATTING GI Manipis', 'category' => 'Fencing', 'price' => 350.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'CYCLONE 2X2X4', 'category' => 'Fencing', 'price' => 1850.00, 'stock_quantity' => 20, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'BARBED WIRE 130M', 'category' => 'Fencing', 'price' => 1950.00, 'stock_quantity' => 15, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'HOG WIRE 9 HOLES', 'category' => 'Fencing', 'price' => 1100.00, 'stock_quantity' => 20, 'supplier' => 'BuildRight Trading'],

            // Welding & Wire
            ['name' => 'WELDING ROD NIHONWELD 6013', 'category' => 'Hardware', 'price' => 140.00, 'stock_quantity' => 50, 'supplier' => 'BuildRight Trading'],
            ['name' => 'WELDING ROD GOLDEN BRIDGE 6013', 'category' => 'Hardware', 'price' => 100.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'KAWAD #18', 'category' => 'Hardware', 'price' => 75.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'KAWAD #16', 'category' => 'Hardware', 'price' => 75.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],

            // Tools
            ['name' => 'HACKSAW', 'category' => 'Tools', 'price' => 80.00, 'stock_quantity' => 50, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PALA TULIS / LAPAD', 'category' => 'Tools', 'price' => 250.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'CUTTING DISC (Novabull)', 'category' => 'Tools', 'price' => 20.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],

            // PVC Pipes & Fittings
            ['name' => 'PVC PIPE ORANGE 2"', 'category' => 'Plumbing', 'price' => 140.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC PIPE ORANGE 3"', 'category' => 'Plumbing', 'price' => 210.00, 'stock_quantity' => 50, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC PIPE ORANGE 4"', 'category' => 'Plumbing', 'price' => 270.00, 'stock_quantity' => 45, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC PIPE BLACK 6"', 'category' => 'Plumbing', 'price' => 450.00, 'stock_quantity' => 35, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'PVC ELBOW 90 2"', 'category' => 'Plumbing', 'price' => 40.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC ELBOW 90 3"', 'category' => 'Plumbing', 'price' => 60.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC ELBOW 90 4"', 'category' => 'Plumbing', 'price' => 75.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC TEE/Y 2"', 'category' => 'Plumbing', 'price' => 45.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC TEE/Y 3"', 'category' => 'Plumbing', 'price' => 55.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PVC TEE/Y 4"', 'category' => 'Plumbing', 'price' => 75.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],

            // Blue Water Pipes
            ['name' => 'PIPE BLUE 1/2"', 'category' => 'Plumbing', 'price' => 95.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PIPE BLUE 3/4"', 'category' => 'Plumbing', 'price' => 120.00, 'stock_quantity' => 70, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PIPE BLUE 1"', 'category' => 'Plumbing', 'price' => 175.00, 'stock_quantity' => 60, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'BLUE ELBOW 1/2"', 'category' => 'Plumbing', 'price' => 15.00, 'stock_quantity' => 150, 'supplier' => 'BuildRight Trading'],
            ['name' => 'BLUE BALL VALVE 1/2"', 'category' => 'Plumbing', 'price' => 45.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],

            // PPR & PE Pipes
            ['name' => 'PPR PIPE 1/2"', 'category' => 'Plumbing', 'price' => 180.00, 'stock_quantity' => 50, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'PPR PIPE 3/4"', 'category' => 'Plumbing', 'price' => 270.00, 'stock_quantity' => 40, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'PE PIPE 1/2"', 'category' => 'Plumbing', 'price' => 27.00, 'stock_quantity' => 200, 'supplier' => 'BuildRight Trading'],
            ['name' => 'GI ELBOW 1/2"', 'category' => 'Plumbing', 'price' => 30.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'GARDEN HOSE 1/2"', 'category' => 'Plumbing', 'price' => 30.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],

            // Sanitary Ware
            ['name' => 'LAVATORY SINK (Shark) Small', 'category' => 'Sanitary', 'price' => 600.00, 'stock_quantity' => 20, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'TOILET BOWL', 'category' => 'Sanitary', 'price' => 900.00, 'stock_quantity' => 15, 'supplier' => 'SolidMix Industrial'],

            // Cement & Adhesives
            ['name' => 'EAGLE ADVANCE CEMENT (Pick-up)', 'category' => 'Cement', 'price' => 185.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'ABC ADHESIVE', 'category' => 'Cement', 'price' => 290.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
            ['name' => 'SKIMCOAT', 'category' => 'Cement', 'price' => 300.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],

            // Fasteners & Hardware
            ['name' => 'COMMON NAIL (1 to 4 inch)', 'category' => 'Hardware', 'price' => 75.00, 'stock_quantity' => 100, 'supplier' => 'BuildRight Trading'],
            ['name' => 'CONCRETE NAIL (1 to 4 inch)', 'category' => 'Hardware', 'price' => 110.00, 'stock_quantity' => 80, 'supplier' => 'BuildRight Trading'],
            ['name' => 'BLIND RIVET (All sizes)', 'category' => 'Hardware', 'price' => 0.50, 'stock_quantity' => 500, 'supplier' => 'BuildRight Trading'],

            // Lumber & Wood Products
            ['name' => 'PLYWOOD ORDINARY 1/4"', 'category' => 'Lumber', 'price' => 300.00, 'stock_quantity' => 40, 'supplier' => 'BuildRight Trading'],
            ['name' => 'PLYWOOD MARINE 1/4"', 'category' => 'Lumber', 'price' => 400.00, 'stock_quantity' => 30, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'HARDIFLEX 3/16"', 'category' => 'Lumber', 'price' => 370.00, 'stock_quantity' => 35, 'supplier' => 'BuildRight Trading'],

            // Roofing
            ['name' => 'YERO ORDINARY 8FT', 'category' => 'Roofing', 'price' => 360.00, 'stock_quantity' => 50, 'supplier' => 'BuildRight Trading'],
            ['name' => 'YERO COLORED 8FT', 'category' => 'Roofing', 'price' => 440.00, 'stock_quantity' => 45, 'supplier' => 'BuildRight Trading'],

            // Electrical
            ['name' => 'THHN WIRE #12 (Boston)', 'category' => 'Electrical', 'price' => 25.00, 'stock_quantity' => 150, 'supplier' => 'BuildRight Trading'],
            ['name' => 'THHN WIRE #14 (Boston)', 'category' => 'Electrical', 'price' => 20.00, 'stock_quantity' => 150, 'supplier' => 'BuildRight Trading'],
            ['name' => 'FLEXIBLE HOSE', 'category' => 'Electrical', 'price' => 10.00, 'stock_quantity' => 200, 'supplier' => 'BuildRight Trading'],
            ['name' => 'KOTEN SAFETY BREAKER 20/30A', 'category' => 'Electrical', 'price' => 540.00, 'stock_quantity' => 30, 'supplier' => 'SolidMix Industrial'],
            ['name' => 'ROYU OUTLET 1 GANG', 'category' => 'Electrical', 'price' => 110.00, 'stock_quantity' => 60, 'supplier' => 'BuildRight Trading'],
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
