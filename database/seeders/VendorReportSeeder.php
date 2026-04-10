<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use App\Models\VendorReport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VendorReportSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::query()->where('role', 'vendor')->where('is_active', true)->get();

        if ($vendors->isEmpty()) {
            $this->command?->warn('VendorReportSeeder dilewati: vendor aktif belum tersedia.');
            return;
        }

        foreach ($vendors as $vendor) {
            for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {
                $start = Carbon::now()->subMonths($monthOffset)->startOfMonth();
                $end = Carbon::now()->subMonths($monthOffset)->endOfMonth();

                $tickets = Ticket::query()
                    ->where('assigned_to', $vendor->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->with(['category', 'slaTracking'])
                    ->get();

                $total = $tickets->count();
                $resolved = $tickets->whereIn('status', ['resolved', 'closed'])->count();
                $pending = $tickets->whereIn('status', ['new', 'in_progress', 'waiting_response'])->count();

                $avgResponse = $tickets
                    ->filter(fn ($t) => $t->slaTracking && $t->slaTracking->actual_response_time !== null)
                    ->avg(fn ($t) => $t->slaTracking->actual_response_time);

                $avgResolution = $tickets
                    ->filter(fn ($t) => $t->slaTracking && $t->slaTracking->actual_resolution_time !== null)
                    ->avg(fn ($t) => $t->slaTracking->actual_resolution_time);

                $slaRows = $tickets->filter(fn ($t) => $t->slaTracking && $t->slaTracking->response_sla_met !== null);
                $slaMet = $slaRows->filter(fn ($t) => (bool) $t->slaTracking->response_sla_met)->count();
                $slaCompliance = $slaRows->count() > 0 ? round(($slaMet / $slaRows->count()) * 100, 2) : null;

                $byCategory = $tickets
                    ->groupBy(fn ($t) => $t->category?->name ?? 'Tanpa Kategori')
                    ->map(fn ($rows) => $rows->count())
                    ->toArray();

                $byPriority = $tickets
                    ->groupBy('priority')
                    ->map(fn ($rows) => $rows->count())
                    ->toArray();

                VendorReport::query()->updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'period_start' => $start->toDateString(),
                        'period_end' => $end->toDateString(),
                    ],
                    [
                        'period_type' => 'monthly',
                        'total_tickets' => $total,
                        'resolved_tickets' => $resolved,
                        'pending_tickets' => $pending,
                        'avg_response_time' => $avgResponse !== null ? round($avgResponse, 2) : null,
                        'avg_resolution_time' => $avgResolution !== null ? round($avgResolution, 2) : null,
                        'sla_compliance_rate' => $slaCompliance,
                        'tickets_by_category' => $byCategory,
                        'tickets_by_priority' => $byPriority,
                    ]
                );
            }
        }

        $this->command?->info('VendorReportSeeder selesai: laporan bulanan vendor (6 bulan) diperbarui.');
    }
}
