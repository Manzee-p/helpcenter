<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class VendorCompleteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->command->info('🚀 Creating vendors with complete data...');

        $vendors = [
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad.rizki@techsolutions.id',
                'phone' => '081234567890',
                'password' => 'password123',
                'company_name' => 'Tech Solutions Indonesia',
                'company_address' => 'Jl. Sudirman No. 123, Jakarta Selatan, DKI Jakarta 12190',
                'company_phone' => '021-1234567',
                'specialization' => 'Sound System, Audio Engineering, PA System',
                'is_active' => true,
                'performance_category' => 'fastest',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@eventpro.id',
                'phone' => '082345678901',
                'password' => 'password123',
                'company_name' => 'Event Pro Services',
                'company_address' => 'Jl. Gatot Subroto No. 45, Jakarta Pusat, DKI Jakarta 10270',
                'company_phone' => '021-9876543',
                'specialization' => 'Lighting, Stage Setup, LED Display',
                'is_active' => true,
                'performance_category' => 'fastest',
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'citra.dewi@digitalevent.id',
                'phone' => '083456789012',
                'password' => 'password123',
                'company_name' => 'Digital Event Management',
                'company_address' => 'Jl. Rasuna Said No. 78, Jakarta Selatan, DKI Jakarta 12940',
                'company_phone' => '021-5551234',
                'specialization' => 'Technical Support, IT Infrastructure, Network Setup',
                'is_active' => true,
                'performance_category' => 'average',
            ],
            [
                'name' => 'Dedi Kurniawan',
                'email' => 'dedi.kurniawan@premiumav.id',
                'phone' => '084567890123',
                'password' => 'password123',
                'company_name' => 'Premium Audio Visual',
                'company_address' => 'Jl. HR Rasuna Said No. 90, Jakarta Selatan, DKI Jakarta 12920',
                'company_phone' => '021-7778888',
                'specialization' => 'Audio Visual, Multimedia, Projection System',
                'is_active' => true,
                'performance_category' => 'average',
            ],
            [
                'name' => 'Eka Prasetyo',
                'email' => 'eka.prasetyo@completeevent.id',
                'phone' => '085678901234',
                'password' => 'password123',
                'company_name' => 'Complete Event Solutions',
                'company_address' => 'Jl. Thamrin No. 56, Jakarta Pusat, DKI Jakarta 10350',
                'company_phone' => '021-3334455',
                'specialization' => 'Full Event Management, Logistics, Equipment Rental',
                'is_active' => true,
                'performance_category' => 'average',
            ],
            [
                'name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@soundmaster.id',
                'phone' => '086789012345',
                'password' => 'password123',
                'company_name' => 'Sound Master Pro',
                'company_address' => 'Jl. Kuningan No. 33, Jakarta Selatan, DKI Jakarta 12950',
                'company_phone' => '021-5559999',
                'specialization' => 'Professional Audio, Recording, Live Sound',
                'is_active' => true,
                'performance_category' => 'slowest',
            ],
            [
                'name' => 'Gani Wijaya',
                'email' => 'gani.wijaya@lightstudio.id',
                'phone' => '087890123456',
                'password' => 'password123',
                'company_name' => 'Light Studio Indonesia',
                'company_address' => 'Jl. Senopati No. 12, Jakarta Selatan, DKI Jakarta 12190',
                'company_phone' => '021-7776666',
                'specialization' => 'Stage Lighting, Moving Lights, DMX Control',
                'is_active' => true,
                'performance_category' => 'slowest',
            ],
            [
                'name' => 'Hendra Gunawan',
                'email' => 'hendra.gunawan@megaevent.id',
                'phone' => '088901234567',
                'password' => 'password123',
                'company_name' => 'Mega Event Organizer',
                'company_address' => 'Jl. Sudirman Kav. 52, Jakarta Pusat, DKI Jakarta 10210',
                'company_phone' => '021-5554444',
                'specialization' => 'Event Planning, Stage Design, Decoration',
                'is_active' => true,
                'performance_category' => 'fastest',
            ],
            [
                'name' => 'Indah Permata',
                'email' => 'indah.permata@technicalsupport.id',
                'phone' => '089012345678',
                'password' => 'password123',
                'company_name' => 'Technical Support Services',
                'company_address' => 'Jl. MT Haryono No. 88, Jakarta Selatan, DKI Jakarta 12810',
                'company_phone' => '021-3332222',
                'specialization' => 'IT Support, Computer Setup, Network Engineering',
                'is_active' => true,
                'performance_category' => 'average',
            ],
            [
                'name' => 'Joko Susilo',
                'email' => 'joko.susilo@proaudio.id',
                'phone' => '081123456789',
                'password' => 'password123',
                'company_name' => 'Pro Audio Systems',
                'company_address' => 'Jl. Casablanca No. 88, Jakarta Selatan, DKI Jakarta 12870',
                'company_phone' => '021-2221111',
                'specialization' => 'Professional Sound, Concert Audio, Wireless Microphone',
                'is_active' => false,
                'performance_category' => 'average',
            ],
        ];

        $createdVendors = [];

        foreach ($vendors as $vendorData) {
            $existingVendor = User::where('email', $vendorData['email'])->first();
            
            if ($existingVendor) {
                $this->command->warn("⚠️  Vendor {$vendorData['email']} already exists. Skipping...");
                $existingVendor->performance_category = $vendorData['performance_category'];
                $createdVendors[] = $existingVendor;
                continue;
            }

            $vendor = User::create([
                'name' => $vendorData['name'],
                'email' => $vendorData['email'],
                'phone' => $vendorData['phone'],
                'password' => Hash::make($vendorData['password']),
                'role' => 'vendor',
                'company_name' => $vendorData['company_name'],
                'company_address' => $vendorData['company_address'],
                'company_phone' => $vendorData['company_phone'],
                'specialization' => $vendorData['specialization'],
                'is_active' => $vendorData['is_active'],
                'email_verified_at' => now(),
            ]);

            $vendor->performance_category = $vendorData['performance_category'];
            $createdVendors[] = $vendor;
            $this->command->info("✅ Created vendor: {$vendor->name} ({$vendor->email}) - Performance: {$vendorData['performance_category']}");
        }

        $this->assignTicketsToVendors($createdVendors);

        $activeCount = count(array_filter($createdVendors, fn($v) => $v->is_active));
        $inactiveCount = count($createdVendors) - $activeCount;
        
        $fastCount = count(array_filter($createdVendors, fn($v) => $v->performance_category === 'fastest'));
        $avgCount = count(array_filter($createdVendors, fn($v) => $v->performance_category === 'average'));
        $slowCount = count(array_filter($createdVendors, fn($v) => $v->performance_category === 'slowest'));

        $this->command->info("
╔════════════════════════════════════════╗
║   Vendor Seeding Completed! ✨        ║
╠════════════════════════════════════════╣
║ Total Vendors: " . str_pad(count($createdVendors), 20) . "║
║ Active: " . str_pad($activeCount, 27) . "║
║ Inactive: " . str_pad($inactiveCount, 25) . "║
╠════════════════════════════════════════╣
║ Performance Distribution:              ║
║ 🚀 Fast (< 4h): " . str_pad($fastCount, 20) . "║
║ ⏱️  Average (4-8h): " . str_pad($avgCount, 16) . "║
║ 🐌 Slow (≥ 8h): " . str_pad($slowCount, 20) . "║
╚════════════════════════════════════════╝

📧 All vendors use password: password123
        ");
    }

    /**
     * STRATEGI BARU: Pastikan SETIAP BULAN punya ticket dari SEMUA kategori
     */
    private function assignTicketsToVendors($vendors)
    {
        $this->command->info('📊 Assigning tickets to vendors with GUARANTEED category distribution...');

        $unassignedTickets = Ticket::whereNull('assigned_to')
            ->orWhere('assigned_to', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($unassignedTickets->isEmpty()) {
            $this->command->warn('⚠️  No unassigned tickets found.');
            return;
        }

        $activeVendors = array_filter($vendors, fn($v) => $v->is_active);
        
        if (empty($activeVendors)) {
            $this->command->error('❌ No active vendors found!');
            return;
        }

        // Group vendors by category
        $vendorsByCategory = [
            'fastest' => array_values(array_filter($activeVendors, fn($v) => $v->performance_category === 'fastest')),
            'average' => array_values(array_filter($activeVendors, fn($v) => $v->performance_category === 'average')),
            'slowest' => array_values(array_filter($activeVendors, fn($v) => $v->performance_category === 'slowest')),
        ];

        $this->command->info("📋 Vendors: Fast=" . count($vendorsByCategory['fastest']) . 
                           ", Avg=" . count($vendorsByCategory['average']) . 
                           ", Slow=" . count($vendorsByCategory['slowest']));

        // Group tickets by month
        $ticketsByMonth = [];
        foreach ($unassignedTickets as $ticket) {
            $monthKey = Carbon::parse($ticket->created_at)->format('Y-m');
            if (!isset($ticketsByMonth[$monthKey])) {
                $ticketsByMonth[$monthKey] = [];
            }
            $ticketsByMonth[$monthKey][] = $ticket;
        }

        $assignedCount = 0;
        $categoryCounters = ['fastest' => 0, 'average' => 0, 'slowest' => 0];

        // Process each month
        foreach ($ticketsByMonth as $monthKey => $monthTickets) {
            $this->command->info("📅 Month {$monthKey}: " . count($monthTickets) . " tickets");
            
            // Pastikan minimal 3 ticket per kategori per bulan (jika ada cukup ticket)
            $ticketsPerCategory = max(3, floor(count($monthTickets) / 3));
            
            $categories = ['fastest', 'average', 'slowest'];
            $categoryIndex = 0;
            
            foreach ($monthTickets as $ticket) {
                // Pilih kategori secara round-robin
                $category = $categories[$categoryIndex % 3];
                
                // Ambil vendor dari kategori yang dipilih
                if (!empty($vendorsByCategory[$category])) {
                    $vendorIndex = $categoryCounters[$category] % count($vendorsByCategory[$category]);
                    $vendor = $vendorsByCategory[$category][$vendorIndex];
                    $categoryCounters[$category]++;
                } else {
                    // Fallback jika kategori kosong
                    $vendor = $activeVendors[array_rand($activeVendors)];
                    $category = $vendor->performance_category;
                }
                
                // Assign ticket dengan waktu yang sesuai kategori
                $this->assignTicketWithTiming($ticket, $vendor, $category);
                
                $assignedCount++;
                $categoryIndex++;
            }
        }

        $this->command->info("✅ Assigned {$assignedCount} tickets");
        $this->showCategoryDistribution();
    }

    /**
     * Assign ticket dengan timing yang tepat sesuai kategori
     */
    private function assignTicketWithTiming($ticket, $vendor, $category)
    {
        $createdAt = Carbon::parse($ticket->created_at);
        
        // Set assigned_at = created_at untuk konsistensi
        $assignedAt = $createdAt->copy();
        
        // Tentukan resolution time berdasarkan kategori
        // fastest: 60-210 menit (1-3.5 jam) - PASTI < 240 menit
        // average: 250-450 menit (4.2-7.5 jam) - PASTI dalam 240-480 menit
        // slowest: 500-700 menit (8.3-11.7 jam) - PASTI > 480 menit
        
        switch ($category) {
            case 'fastest':
                $resolutionMinutes = rand(60, 210); // 1-3.5 hours
                break;
            case 'average':
                $resolutionMinutes = rand(250, 450); // 4.2-7.5 hours
                break;
            case 'slowest':
                $resolutionMinutes = rand(500, 700); // 8.3-11.7 hours
                break;
            default:
                $resolutionMinutes = rand(240, 360);
        }
        
        // Calculate timestamps
        $responseMinutes = rand(10, 30); // Response cepat (10-30 menit)
        $firstResponseAt = $assignedAt->copy()->addMinutes($responseMinutes);
        $resolvedAt = $createdAt->copy()->addMinutes($resolutionMinutes);
        
        // Update ticket
        $ticket->update([
            'assigned_to' => $vendor->id,
            'assigned_at' => $assignedAt,
            'status' => 'resolved', // Semua resolved untuk bisa dihitung
            'first_response_at' => $firstResponseAt,
            'resolved_at' => $resolvedAt,
        ]);

        // Update SLA if exists
        if ($ticket->slaTracking) {
            $sla = $ticket->slaTracking;
            
            $actualResponseTime = (int) round($createdAt->diffInMinutes($firstResponseAt));
            $actualResolutionTime = (int) round($createdAt->diffInMinutes($resolvedAt));
            
            $sla->actual_response_time = $actualResponseTime;
            $sla->response_sla_met = $actualResponseTime <= $sla->response_time_sla;
            $sla->actual_resolution_time = $actualResolutionTime;
            $sla->resolution_sla_met = $actualResolutionTime <= $sla->resolution_time_sla;
            $sla->save();
        }
    }

    /**
     * Show distribution summary
     */
    private function showCategoryDistribution()
    {
        $this->command->info("\n📊 Final Distribution by Category:");
        
        $categories = [
            'fastest' => ['min' => 0, 'max' => 239],
            'average' => ['min' => 240, 'max' => 479],
            'slowest' => ['min' => 480, 'max' => 999999]
        ];
        
        foreach ($categories as $category => $range) {
            $count = Ticket::whereNotNull('resolved_at')
                ->whereNotNull('assigned_to')
                ->get()
                ->filter(function($ticket) use ($range) {
                    $minutes = $ticket->created_at->diffInMinutes($ticket->resolved_at);
                    return $minutes >= $range['min'] && $minutes <= $range['max'];
                })
                ->count();
            
            $icon = $category === 'fastest' ? '🚀' : ($category === 'average' ? '⏱️' : '🐌');
            $this->command->info("   {$icon} {$category}: {$count} tickets");
        }
    }
}