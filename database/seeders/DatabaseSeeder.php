<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            TicketCategorySeeder::class,
            TicketSeeder::class,
            VendorInfoSeeder::class,  // NEW
            NotificationSeeder::class, // NEW
            VendorReportSeeder::class, // NEW
            VendorCompleteSeeder::class,
            StatusBoardSeeder::class,
        ]);
    }
}
