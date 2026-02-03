<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class VendorInfoSeeder extends Seeder
{
    public function run()
    {
        $vendors = User::where('role', 'vendor')->get();

        $companies = [
            [
                'name' => 'Tech Solutions Indonesia',
                'address' => 'Jl. Sudirman No. 123, Jakarta Selatan',
                'phone' => '021-1234567',
                'specialization' => 'Sound System, Audio Engineering'
            ],
            [
                'name' => 'Event Pro Services',
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta',
                'phone' => '021-9876543',
                'specialization' => 'Lighting, Stage Setup'
            ],
            [
                'name' => 'Digital Event Management',
                'address' => 'Jl. Rasuna Said No. 78, Jakarta',
                'phone' => '021-5551234',
                'specialization' => 'Technical Support, IT Infrastructure'
            ],
            [
                'name' => 'Premium Audio Visual',
                'address' => 'Jl. HR Rasuna Said No. 90, Jakarta',
                'phone' => '021-7778888',
                'specialization' => 'Audio Visual, Multimedia'
            ],
            [
                'name' => 'Complete Event Solutions',
                'address' => 'Jl. Thamrin No. 56, Jakarta Pusat',
                'phone' => '021-3334455',
                'specialization' => 'Full Event Management, Logistics'
            ],
        ];

        foreach ($vendors as $index => $vendor) {
            $companyData = $companies[$index % count($companies)];
            
            $vendor->update([
                'company_name' => $companyData['name'],
                'company_address' => $companyData['address'],
                'company_phone' => $companyData['phone'],
                'specialization' => $companyData['specialization'],
            ]);
        }

        $this->command->info('✅ Updated ' . $vendors->count() . ' vendors with company information');
    }
}
