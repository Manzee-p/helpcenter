<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin HelpCenter',
                'email' => 'admin@helpcenter.test',
                'phone' => '081200000001',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Vendor Sound System',
                'email' => 'vendor.sound@helpcenter.test',
                'phone' => '081200000101',
                'role' => 'vendor',
                'is_active' => true,
                'company_name' => 'Sound Works Indonesia',
                'company_address' => 'Jakarta Selatan',
                'company_phone' => '021-500101',
                'specialization' => 'Sound System, Audio',
            ],
            [
                'name' => 'Vendor Lighting Pro',
                'email' => 'vendor.lighting@helpcenter.test',
                'phone' => '081200000102',
                'role' => 'vendor',
                'is_active' => true,
                'company_name' => 'Lighting Pro Nusantara',
                'company_address' => 'Jakarta Barat',
                'company_phone' => '021-500102',
                'specialization' => 'Lighting, Rigging',
            ],
            [
                'name' => 'Vendor Network Team',
                'email' => 'vendor.network@helpcenter.test',
                'phone' => '081200000103',
                'role' => 'vendor',
                'is_active' => true,
                'company_name' => 'NetCare Indonesia',
                'company_address' => 'Jakarta Timur',
                'company_phone' => '021-500103',
                'specialization' => 'Network, Infrastruktur IT',
            ],
            [
                'name' => 'Rina Pratama',
                'email' => 'rina@helpcenter.test',
                'phone' => '081200000201',
                'role' => 'client',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@helpcenter.test',
                'phone' => '081200000202',
                'role' => 'client',
                'is_active' => true,
            ],
            [
                'name' => 'Sinta Maharani',
                'email' => 'sinta@helpcenter.test',
                'phone' => '081200000203',
                'role' => 'client',
                'is_active' => true,
            ],
            [
                'name' => 'Dimas Saputra',
                'email' => 'dimas@helpcenter.test',
                'phone' => '081200000204',
                'role' => 'client',
                'is_active' => true,
            ],
        ];

        foreach ($users as $row) {
            User::updateOrCreate(
                ['email' => $row['email']],
                array_merge($row, [
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ])
            );
        }

        $this->command?->info('UserSeeder selesai: admin, vendor, dan client siap digunakan (password: password).');
    }
}
