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
            ['name' => 'Hardware', 'description' => 'General hardware materials'],
            ['name' => 'Electrical', 'description' => 'Electrical construction supplies'],
            ['name' => 'Plumbing', 'description' => 'Pipes, fittings, and accessories'],
            ['name' => 'Aggregates', 'description' => 'Sand, gravel and crushed stones'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
