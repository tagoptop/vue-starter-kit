<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'BuildRight Trading',
                'contact_person' => 'Maria Santos',
                'email' => 'sales@buildright.test',
                'phone' => '09171234567',
                'address' => 'Manila, Philippines',
            ],
            [
                'name' => 'SolidMix Industrial',
                'contact_person' => 'John Cruz',
                'email' => 'orders@solidmix.test',
                'phone' => '09987654321',
                'address' => 'Cebu, Philippines',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
