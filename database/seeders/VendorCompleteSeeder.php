<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VendorCompleteSeeder extends Seeder
{
    /**
     * Seeder kompatibilitas lama.
     *
     * Tetap disediakan agar perintah lama `--class=VendorCompleteSeeder`
     * tidak error, namun sekarang data vendor dikelola oleh UserSeeder + VendorInfoSeeder.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            VendorInfoSeeder::class,
        ]);

        $this->command?->info('VendorCompleteSeeder selesai (mode kompatibilitas).');
    }
}
