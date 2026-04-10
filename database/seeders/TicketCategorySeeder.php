<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Sound System', 'description' => 'Gangguan audio, speaker, mic, mixer.'],
            ['name' => 'Lighting', 'description' => 'Gangguan lampu, kontrol DMX, rigging lampu.'],
            ['name' => 'Jaringan', 'description' => 'Internet lambat, wifi putus, konfigurasi network.'],
            ['name' => 'Venue', 'description' => 'Kendala fasilitas lokasi atau ruangan.'],
            ['name' => 'Perangkat', 'description' => 'Laptop, proyektor, kabel, perangkat pendukung.'],
            ['name' => 'Lainnya', 'description' => 'Kebutuhan lain di luar kategori utama.'],
        ];

        foreach ($categories as $category) {
            TicketCategory::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('TicketCategorySeeder selesai: kategori tiket diperbarui.');
    }
}
