<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Admin
        User::create([
            'name' => 'Admin Helpdesk',
            'email' => 'admin@helpdesk.com',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Vendors
        User::create([
            'name' => 'Vendor Sound System',
            'email' => 'vendor.sound@helpdesk.com',
            'phone' => '081234567891',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        User::create([
            'name' => 'Vendor Lighting',
            'email' => 'vendor.lighting@helpdesk.com',
            'phone' => '081234567892',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        // Clients
        User::create([
            'name' => 'Rina Pratama',
            'email' => 'rina@company.com',
            'phone' => '081234567893',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@company.com',
            'phone' => '081234567894',
            'password' => Hash::make('password'),
            'role' => 'client',
        ]);
    }
}
