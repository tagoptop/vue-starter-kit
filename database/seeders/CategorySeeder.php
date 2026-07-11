<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Cement', 'description' => 'Portland and blended cement products'],
            ['name' => 'Hardware', 'description' => 'Steel bars, fasteners, and general hardware'],
            ['name' => 'Electrical', 'description' => 'Electrical construction supplies'],
            ['name' => 'Plumbing', 'description' => 'Pipes, fittings, and plumbing accessories'],
            ['name' => 'Aggregates', 'description' => 'Sand, gravel and crushed stones'],
            ['name' => 'Lumber', 'description' => 'Wood sheets and plywood products'],
            ['name' => 'Roofing', 'description' => 'Roofing materials and accessories'],
            ['name' => 'Fencing', 'description' => 'Fencing materials and wire'],
            ['name' => 'Sanitary', 'description' => 'Sanitary ware and fixtures'],
            ['name' => 'Tools', 'description' => 'Hand tools and accessories'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
