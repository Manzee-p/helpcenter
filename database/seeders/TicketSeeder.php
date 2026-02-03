<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketCategory;
use App\Models\SlaTracking;
use Carbon\Carbon;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Get users
        $clients = User::where('role', 'client')->pluck('id')->toArray();
        $vendors = User::whereIn('role', ['vendor', 'admin'])->pluck('id')->toArray();
        $categories = TicketCategory::pluck('id')->toArray();

        if (empty($clients) || empty($vendors) || empty($categories)) {
            $this->command->error('Please run UserSeeder and TicketCategorySeeder first!');
            return;
        }

        $statuses = ['new', 'in_progress', 'waiting_response', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        
        $titles = [
            'Sound System' => [
                'Mic tidak menyala',
                'Speaker berdengung',
                'Audio delay saat streaming',
                'Volume terlalu kecil',
                'Feedback dari speaker'
            ],
            'Lighting' => [
                'Lampu panggung mati',
                'LED screen berkedip',
                'Lighting controller error',
                'Warna lampu tidak sesuai',
                'DMX controller tidak respond'
            ],
            'Technical' => [
                'Proyektor tidak menyala',
                'Laptop tidak connect ke Wi-Fi',
                'HDMI cable rusak',
                'Power outlet tidak berfungsi',
                'Internet connection unstable'
            ],
            'Venue' => [
                'AC ruangan terlalu dingin',
                'Kursi peserta kurang',
                'Toilet tidak bersih',
                'Parkir penuh',
                'Ruangan terlalu sempit'
            ],
            'Logistik' => [
                'Barang belum sampai',
                'Pengiriman terlambat',
                'Barang rusak saat pengiriman',
                'Kurang material',
                'Setup equipment terlambat'
            ]
        ];

        $venues = ['Ballroom A', 'Ballroom B', 'Convention Hall', 'Meeting Room 1', 'Meeting Room 2', 'Auditorium'];
        $areas = ['Stage', 'Registration Desk', 'Exhibition Area', 'VIP Lounge', 'Main Hall', 'Backstage'];

        // ✅ FIX: Track daily ticket counters to prevent duplicates
        $dailyCounters = [];
        
        // Generate tickets for the last 12 months
        $ticketCount = 0;
        
        // Loop through last 12 months
        for ($month = 11; $month >= 0; $month--) {
            $startDate = Carbon::now()->subMonths($month)->startOfMonth();
            $endDate = Carbon::now()->subMonths($month)->endOfMonth();
            
            // Generate 20-40 tickets per month (varied distribution)
            // More tickets in recent months for realistic trend
            if ($month <= 2) {
                $ticketsThisMonth = rand(35, 50); // Recent months: more tickets
            } elseif ($month <= 5) {
                $ticketsThisMonth = rand(25, 35); // Mid-range
            } else {
                $ticketsThisMonth = rand(15, 25); // Older months: fewer tickets
            }
            
            $this->command->info("📅 Generating {$ticketsThisMonth} tickets for " . $startDate->format('F Y'));
            
            for ($i = 0; $i < $ticketsThisMonth; $i++) {
                // Random date within the month
                $createdAt = Carbon::createFromTimestamp(
                    rand($startDate->timestamp, $endDate->timestamp)
                );
                
                // ✅ FIX: Generate unique ticket number with proper counter
                $dateKey = $createdAt->format('Ymd');
                if (!isset($dailyCounters[$dateKey])) {
                    // Check if there are existing tickets for this date
                    $existingCount = Ticket::whereDate('created_at', $createdAt->toDateString())->count();
                    $dailyCounters[$dateKey] = $existingCount;
                }
                $dailyCounters[$dateKey]++;
                $ticketNumber = 'TKT-' . $dateKey . '-' . str_pad($dailyCounters[$dateKey], 4, '0', STR_PAD_LEFT);
                
                // Random category
                $categoryId = $categories[array_rand($categories)];
                $categoryName = TicketCategory::find($categoryId)->name;
                
                // Get relevant titles
                $categoryTitles = $titles[$categoryName] ?? $titles['Technical'];
                $title = $categoryTitles[array_rand($categoryTitles)];
                
                // Random priority with weighted distribution
                $priorityRand = rand(1, 100);
                if ($priorityRand <= 10) {
                    $priority = 'urgent'; // 10%
                } elseif ($priorityRand <= 30) {
                    $priority = 'high'; // 20%
                } elseif ($priorityRand <= 70) {
                    $priority = 'medium'; // 40%
                } else {
                    $priority = 'low'; // 30%
                }
                
                // Random status with realistic distribution
                $statusRand = rand(1, 100);
                if ($month >= 3) { // Older tickets more likely to be closed
                    if ($statusRand <= 70) {
                        $status = 'closed';
                    } elseif ($statusRand <= 85) {
                        $status = 'resolved';
                    } elseif ($statusRand <= 95) {
                        $status = 'in_progress';
                    } else {
                        $status = 'new';
                    }
                } else { // Recent tickets more likely to be open
                    if ($statusRand <= 15) {
                        $status = 'closed';
                    } elseif ($statusRand <= 35) {
                        $status = 'resolved';
                    } elseif ($statusRand <= 70) {
                        $status = 'in_progress';
                    } else {
                        $status = 'new';
                    }
                }
                
                // Create ticket
                $ticket = Ticket::create([
                    'ticket_number' => $ticketNumber,
                    'user_id' => $clients[array_rand($clients)],
                    'category_id' => $categoryId,
                    'assigned_to' => in_array($status, ['in_progress', 'resolved', 'closed'])
                        ? $vendors[array_rand($vendors)]
                        : null,
                    'title' => $title,
                    'description' => $this->generateDescription($title),
                    'priority' => $priority,
                    'status' => $status,
                    'event_name' => 'Event ' . $createdAt->format('M Y'),
                    'venue' => $venues[array_rand($venues)],
                    'area' => $areas[array_rand($areas)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Set timestamps based on status
                if (in_array($status, ['in_progress', 'resolved', 'closed'])) {
                    $assignedAt = $createdAt->copy()->addMinutes(rand(5, 30));
                    $ticket->update(['assigned_at' => $assignedAt]);
                }

                if (in_array($status, ['in_progress', 'resolved', 'closed'])) {
                    $firstResponseAt = $createdAt->copy()->addMinutes(rand(10, 60));
                    $ticket->update(['first_response_at' => $firstResponseAt]);
                }

                if (in_array($status, ['resolved', 'closed'])) {
                    $resolvedAt = $createdAt->copy()->addHours(rand(2, 48));
                    $ticket->update(['resolved_at' => $resolvedAt]);
                }

                if ($status === 'closed') {
                    $closedAt = $ticket->resolved_at 
                        ? $ticket->resolved_at->copy()->addHours(rand(1, 24))
                        : $createdAt->copy()->addHours(rand(24, 72));
                    $ticket->update(['closed_at' => $closedAt]);
                }

                // Create SLA Tracking
                $this->createSlaTracking($ticket);

                $ticketCount++;
            }
        }

        $this->command->info("✅ Created {$ticketCount} tickets successfully across 12 months!");
        
        // Show monthly breakdown
        $this->command->info("\n📊 Monthly Breakdown:");
        for ($month = 11; $month >= 0; $month--) {
            $monthStart = Carbon::now()->subMonths($month)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($month)->endOfMonth();
            $count = Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $this->command->info("   " . $monthStart->format('F Y') . ": {$count} tickets");
        }
    }

    /**
     * Generate realistic description based on title
     */
    private function generateDescription($title)
    {
        $descriptions = [
            'default' => "Mohon segera ditangani. Issue ini mempengaruhi jalannya acara. Terima kasih.",
        ];

        return $descriptions['default'];
    }

    /**
     * Create SLA Tracking for ticket
     */
    private function createSlaTracking($ticket)
    {
        $responseTimeSla = match($ticket->priority) {
            'urgent' => 15,
            'high' => 30,
            'medium' => 60,
            'low' => 120,
        };

        $resolutionTimeSla = match($ticket->priority) {
            'urgent' => 240,
            'high' => 480,
            'medium' => 1440,
            'low' => 2880,
        };

        $slaData = [
            'ticket_id' => $ticket->id,
            'response_time_sla' => $responseTimeSla,
            'resolution_time_sla' => $resolutionTimeSla,
        ];

        // Calculate actual times if ticket has progressed
        if ($ticket->first_response_at) {
            $actualResponseTime = (int) round(
                $ticket->created_at->diffInMinutes($ticket->first_response_at)
            );
            $slaData['actual_response_time'] = $actualResponseTime;
            $slaData['response_sla_met'] = $actualResponseTime <= $responseTimeSla;
        }

        if ($ticket->resolved_at) {
            $actualResolutionTime = (int) round(
                $ticket->created_at->diffInMinutes($ticket->resolved_at)
            );
            $slaData['actual_resolution_time'] = $actualResolutionTime;
            $slaData['resolution_sla_met'] = $actualResolutionTime <= $resolutionTimeSla;
        }

        SlaTracking::create($slaData);
    }
}