<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class VendorInfoSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::query()->where('role', 'vendor')->get();

        if ($vendors->isEmpty()) {
            $this->command?->warn('VendorInfoSeeder dilewati: vendor belum tersedia.');
            return;
        }

        $fallback = [
            ['company_name' => 'Vendor Utama A', 'company_address' => 'Jakarta Pusat', 'company_phone' => '021-600001', 'specialization' => 'Teknis Event'],
            ['company_name' => 'Vendor Utama B', 'company_address' => 'Jakarta Barat', 'company_phone' => '021-600002', 'specialization' => 'Audio Visual'],
            ['company_name' => 'Vendor Utama C', 'company_address' => 'Jakarta Selatan', 'company_phone' => '021-600003', 'specialization' => 'Jaringan'],
        ];

        foreach ($vendors as $index => $vendor) {
            $default = $fallback[$index % count($fallback)];
            $vendor->update([
                'company_name' => $vendor->company_name ?: $default['company_name'],
                'company_address' => $vendor->company_address ?: $default['company_address'],
                'company_phone' => $vendor->company_phone ?: $default['company_phone'],
                'specialization' => $vendor->specialization ?: $default['specialization'],
            ]);
        }

        $this->command?->info('VendorInfoSeeder selesai: profil vendor dipastikan terisi.');
    }
}
