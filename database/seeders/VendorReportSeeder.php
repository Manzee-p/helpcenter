<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorReport;
use App\Models\User;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VendorReportSeeder extends Seeder
{
    public function run()
    {
        // Clear existing reports first
        $this->command->info('Clearing existing vendor reports...');
        VendorReport::truncate();
        
        $vendors = User::where('role', 'vendor')->get();

        if ($vendors->isEmpty()) {
            $this->command->error('Please run UserSeeder first!');
            return;
        }

        $count = 0;

        foreach ($vendors as $vendor) {
            $this->command->info("Processing vendor: {$vendor->name}");
            
            // Check if vendor has any tickets at all
            $totalVendorTickets = Ticket::where('assigned_to', $vendor->id)->count();
            $this->command->info("  Total tickets assigned to {$vendor->name}: {$totalVendorTickets}");
            
            // Generate reports for last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $periodStart = Carbon::now()->subMonths($i)->startOfMonth();
                $periodEnd = Carbon::now()->subMonths($i)->endOfMonth();

                // Get tickets for this vendor in this period - CHECK created_at instead of assigned_at
                $tickets = Ticket::where('assigned_to', $vendor->id)
                    ->where(function($query) use ($periodStart, $periodEnd) {
                        // Check either assigned_at or created_at within period
                        $query->whereBetween('assigned_at', [$periodStart, $periodEnd])
                              ->orWhereBetween('created_at', [$periodStart, $periodEnd]);
                    })
                    ->with(['category', 'slaTracking'])
                    ->get();

                $this->command->info("  Found {$tickets->count()} tickets for {$periodStart->format('M Y')}");

                if ($tickets->isEmpty()) {
                    $this->command->warn("  - No tickets for {$periodStart->format('M Y')} - Creating empty report");
                    
                    // Create empty report to show in the table
                    VendorReport::updateOrCreate(
                        [
                            'vendor_id' => $vendor->id,
                            'period_start' => $periodStart,
                            'period_end' => $periodEnd,
                        ],
                        [
                            'period_type' => 'monthly',
                            'total_tickets' => 0,
                            'resolved_tickets' => 0,
                            'pending_tickets' => 0,
                            'avg_response_time' => null,
                            'avg_resolution_time' => null,
                            'sla_compliance_rate' => null,
                            'tickets_by_category' => [],
                            'tickets_by_priority' => [],
                        ]
                    );
                    $count++;
                    continue;
                }

                $totalTickets = $tickets->count();
                $resolvedTickets = $tickets->whereIn('status', ['resolved', 'closed'])->count();
                $pendingTickets = $totalTickets - $resolvedTickets;

                // Calculate averages
                $ticketsWithResponse = $tickets->filter(fn($t) => $t->slaTracking && $t->slaTracking->actual_response_time);
                $avgResponseTime = $ticketsWithResponse->count() > 0 
                    ? round($ticketsWithResponse->avg(fn($t) => $t->slaTracking->actual_response_time), 2)
                    : null;

                $ticketsWithResolution = $tickets->filter(fn($t) => $t->slaTracking && $t->slaTracking->actual_resolution_time);
                $avgResolutionTime = $ticketsWithResolution->count() > 0
                    ? round($ticketsWithResolution->avg(fn($t) => $t->slaTracking->actual_resolution_time), 2)
                    : null;

                // SLA compliance - FIXED: Use filter instead of where
                $ticketsWithSla = $tickets->filter(fn($t) => $t->slaTracking && $t->slaTracking->response_sla_met !== null);
                
                $slaCompliance = null;
                if ($ticketsWithSla->count() > 0) {
                    $slaMetCount = $ticketsWithSla->filter(function($t) {
                        $responseMet = $t->slaTracking->response_sla_met;
                        
                        // If ticket is resolved, check resolution SLA too
                        if (in_array($t->status, ['resolved', 'closed']) && $t->slaTracking->resolution_sla_met !== null) {
                            return $responseMet && $t->slaTracking->resolution_sla_met;
                        }
                        
                        return $responseMet;
                    })->count();
                    
                    $slaCompliance = round(($slaMetCount / $ticketsWithSla->count()) * 100, 2);
                }

                // Group by category
                $ticketsByCategory = $tickets->groupBy(function($ticket) {
                    return $ticket->category ? $ticket->category->name : 'Uncategorized';
                })->map->count()->toArray();

                // Group by priority
                $ticketsByPriority = $tickets->groupBy('priority')->map->count()->toArray();

                // Use updateOrCreate instead of create to avoid duplicates
                VendorReport::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                    ],
                    [
                        'period_type' => 'monthly',
                        'total_tickets' => $totalTickets,
                        'resolved_tickets' => $resolvedTickets,
                        'pending_tickets' => $pendingTickets,
                        'avg_response_time' => $avgResponseTime,
                        'avg_resolution_time' => $avgResolutionTime,
                        'sla_compliance_rate' => $slaCompliance,
                        'tickets_by_category' => $ticketsByCategory,
                        'tickets_by_priority' => $ticketsByPriority,
                    ]
                );

                $count++;
                $this->command->info("  ✓ {$periodStart->format('M Y')}: {$totalTickets} tickets (Resolved: {$resolvedTickets}, Pending: {$pendingTickets})");
            }
            
            $this->command->info(""); // Empty line for readability
        }

        $this->command->info("✅ Created/Updated {$count} vendor reports");
        
        // Show summary
        $this->command->table(
            ['Vendor', 'Total Reports', 'Reports with Data'],
            $vendors->map(function($vendor) {
                $totalReports = VendorReport::where('vendor_id', $vendor->id)->count();
                $reportsWithData = VendorReport::where('vendor_id', $vendor->id)
                    ->where('total_tickets', '>', 0)
                    ->count();
                return [
                    $vendor->name,
                    $totalReports,
                    $reportsWithData
                ];
            })
        );
    }
}