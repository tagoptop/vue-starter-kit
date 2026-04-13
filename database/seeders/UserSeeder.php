<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@construction.local',
                'role' => 'admin',
            ],
            [
                'name' => 'Warehouse Staff',
                'email' => 'staff@construction.local',
                'role' => 'staff',
            ],
            [
                'name' => 'Sample Customer',
                'email' => 'customer@construction.local',
                'role' => 'customer',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'role' => $userData['role'],
                    'phone' => '0900000000',
                    'address' => 'Sample Address',
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
