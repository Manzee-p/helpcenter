<?php

namespace Database\Seeders;

use App\Models\SlaTracking;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Ticket::query()->count() > 0) {
            $this->command?->info('TicketSeeder dilewati: data tiket sudah ada.');
            return;
        }

        $clients = User::query()->where('role', 'client')->pluck('id')->values();
        $vendors = User::query()->where('role', 'vendor')->where('is_active', true)->pluck('id')->values();
        $categories = TicketCategory::query()->pluck('id', 'name');

        if ($clients->isEmpty() || $vendors->isEmpty() || $categories->isEmpty()) {
            $this->command?->warn('TicketSeeder gagal: pastikan UserSeeder dan TicketCategorySeeder sudah jalan.');
            return;
        }

        $statusPattern = [
            'new',
            'in_progress',
            'waiting_response',
            'resolved',
            'closed',
            'in_progress',
            'resolved',
            'closed',
        ];

        $priorityPattern = ['low', 'medium', 'medium', 'high', 'urgent', 'medium', 'high', 'low'];
        $urgencyPattern = ['low', 'medium', 'high', 'critical', 'medium', 'high', 'low', 'medium'];

        $titlesByCategory = [
            'Sound System' => [
                'Mic utama tidak bersuara',
                'Output speaker kanan berdengung',
                'Mixer kehilangan channel input',
            ],
            'Lighting' => [
                'Lampu panggung tidak sinkron',
                'Controller lighting gagal kirim preset',
                'Area backstage terlalu gelap',
            ],
            'Jaringan' => [
                'Internet event drop saat live stream',
                'Wi-Fi tamu tidak bisa login',
                'Latency tinggi pada jaringan FOH',
            ],
            'Venue' => [
                'AC ruangan utama kurang dingin',
                'Akses listrik area booth bermasalah',
                'Area registrasi terlalu padat',
            ],
            'Perangkat' => [
                'Proyektor tidak mendeteksi HDMI',
                'Laptop operator restart berulang',
                'Kabel extender display rusak',
            ],
            'Lainnya' => [
                'Butuh penyesuaian layout kabel',
                'Koordinasi teknis antar tim terlambat',
                'Permintaan bantuan teknis tambahan',
            ],
        ];

        $venues = ['Hall A', 'Hall B', 'Ballroom Utama', 'Meeting Room 1', 'Meeting Room 2', 'Auditorium'];
        $areas = ['Main Stage', 'FOH', 'Backstage', 'Area Registrasi', 'VIP Lounge', 'Ruang Kontrol'];

        $totalTickets = 90;
        $now = Carbon::now();

        for ($i = 0; $i < $totalTickets; $i++) {
            $createdAt = $now->copy()->subDays(rand(0, 75))->subMinutes(rand(0, 1440));
            $status = $statusPattern[$i % count($statusPattern)];
            $priority = $priorityPattern[$i % count($priorityPattern)];
            $urgency = $urgencyPattern[$i % count($urgencyPattern)];

            $categoryName = array_keys($titlesByCategory)[$i % count($titlesByCategory)];
            $categoryId = (int) ($categories[$categoryName] ?? $categories->first());
            $titleOptions = $titlesByCategory[$categoryName] ?? $titlesByCategory['Lainnya'];
            $title = $titleOptions[$i % count($titleOptions)];

            $clientId = (int) $clients[$i % $clients->count()];
            $assignedTo = in_array($status, ['in_progress', 'waiting_response', 'resolved', 'closed'], true)
                ? (int) $vendors[$i % $vendors->count()]
                : null;

            $ticket = Ticket::create([
                'ticket_number' => $this->generateTicketNumber($createdAt, $i + 1),
                'title' => $title,
                'description' => "Laporan dari client terkait {$title}. Mohon ditangani sesuai SOP operasional.",
                'user_id' => $clientId,
                'category_id' => $categoryId,
                'assigned_to' => $assignedTo,
                'status' => $status,
                'priority' => $priority,
                'urgency_level' => $urgency,
                'event_name' => 'Event Operasional ' . $createdAt->format('M Y'),
                'venue' => $venues[$i % count($venues)],
                'area' => $areas[$i % count($areas)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $assignedAt = null;
            $firstResponseAt = null;
            $resolvedAt = null;
            $closedAt = null;

            if ($assignedTo) {
                $assignedAt = $createdAt->copy()->addMinutes(rand(5, 40));
                $ticket->assigned_at = $assignedAt;
            }

            if (in_array($status, ['in_progress', 'waiting_response', 'resolved', 'closed'], true)) {
                $firstResponseAt = ($assignedAt ?? $createdAt)->copy()->addMinutes(rand(10, 60));
                $ticket->first_response_at = $firstResponseAt;
            }

            if (in_array($status, ['resolved', 'closed'], true)) {
                $resolvedAt = $createdAt->copy()->addMinutes(rand(120, 1800));
                $ticket->resolved_at = $resolvedAt;
            }

            if ($status === 'closed') {
                $closedAt = ($resolvedAt ?? $createdAt)->copy()->addMinutes(rand(30, 240));
                $ticket->closed_at = $closedAt;
            }

            $ticket->save();

            $this->createSlaTracking($ticket, $firstResponseAt, $resolvedAt);
        }

        $this->command?->info("TicketSeeder selesai: {$totalTickets} tiket berhasil dibuat.");
    }

    private function generateTicketNumber(Carbon $createdAt, int $sequence): string
    {
        return 'TKT-' . $createdAt->format('Ymd') . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function createSlaTracking(Ticket $ticket, ?Carbon $firstResponseAt, ?Carbon $resolvedAt): void
    {
        $responseSla = match ($ticket->priority) {
            'urgent' => 15,
            'high' => 30,
            'medium' => 60,
            default => 120,
        };

        $resolutionSla = match ($ticket->priority) {
            'urgent' => 240,
            'high' => 480,
            'medium' => 1440,
            default => 2880,
        };

        $payload = [
            'ticket_id' => $ticket->id,
            'response_time_sla' => $responseSla,
            'resolution_time_sla' => $resolutionSla,
        ];

        if ($firstResponseAt) {
            $actualResponse = (int) $ticket->created_at->diffInMinutes($firstResponseAt);
            $payload['actual_response_time'] = $actualResponse;
            $payload['response_sla_met'] = $actualResponse <= $responseSla;
        }

        if ($resolvedAt) {
            $actualResolution = (int) $ticket->created_at->diffInMinutes($resolvedAt);
            $payload['actual_resolution_time'] = $actualResolution;
            $payload['resolution_sla_met'] = $actualResolution <= $resolutionSla;
        }

        SlaTracking::create($payload);
    }
}
