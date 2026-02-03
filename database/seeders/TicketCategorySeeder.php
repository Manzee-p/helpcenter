<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TicketCategory;
use Illuminate\Support\Str;

class TicketCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = [
            ['name' => 'Sound System', 'description' => 'Masalah terkait audio dan sound system'],
            ['name' => 'Lighting', 'description' => 'Masalah terkait pencahayaan'],
            ['name' => 'Venue', 'description' => 'Masalah terkait tempat/venue'],
            ['name' => 'Technical', 'description' => 'Masalah teknis lainnya'],
            ['name' => 'Logistik', 'description' => 'Masalah pengiriman dan logistik'],
            ['name' => 'Registrasi', 'description' => 'Masalah registrasi peserta'],
            ['name' => 'Lainnya', 'description' => 'Masalah lain yang tidak terkategori'],
        ];  

        foreach ($categories as $category) {
            TicketCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }
    }
}
