<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TicketCategorySeeder::class,
            TicketSeeder::class,
            TicketTimelineSeeder::class,
            VendorInfoSeeder::class,
            VendorRatingSeeder::class,
            TicketAdditionalInfoSeeder::class,
            NotificationSeeder::class,
            VendorReportSeeder::class,
            StatusBoardSeeder::class,
            TicketReassignRequestSeeder::class,
        ]);
    }
}
