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
        $clients = User::query()->where('role', 'client')->pluck('id')->values();
        $vendors = User::query()->where('role', 'vendor')->where('is_active', true)->pluck('id')->values();
        $categories = TicketCategory::query()->pluck('id', 'name');

        if ($clients->isEmpty() || $vendors->isEmpty() || $categories->isEmpty()) {
            $this->command?->warn('TicketSeeder gagal: jalankan UserSeeder dan TicketCategorySeeder terlebih dahulu.');
            return;
        }

        $titlesByCategory = [
            'Sound System' => [
                'Mic tidak menyala',
                'Speaker berdengung',
                'Audio delay saat streaming',
                'Volume terlalu kecil',
                'Feedback dari speaker',
            ],
            'Lighting' => [
                'Lampu panggung mati',
                'LED screen berkedip',
                'Lighting controller error',
                'Warna lampu tidak sesuai',
                'DMX controller tidak respons',
            ],
            'Technical' => [
                'Proyektor tidak menyala',
                'Laptop tidak connect ke Wi-Fi',
                'HDMI cable rusak',
                'Power outlet tidak berfungsi',
                'Internet connection unstable',
            ],
            'Jaringan' => [
                'Internet event drop saat live stream',
                'Wi-Fi tamu tidak bisa login',
                'Latency tinggi pada jaringan FOH',
            ],
            'Venue' => [
                'AC ruangan terlalu dingin',
                'Kursi peserta kurang',
                'Area registrasi terlalu padat',
                'Parkir penuh',
                'Ruangan terlalu sempit',
            ],
            'Logistik' => [
                'Barang belum sampai',
                'Pengiriman terlambat',
                'Barang rusak saat pengiriman',
                'Kurang material',
                'Setup equipment terlambat',
            ],
            'Perangkat' => [
                'Proyektor tidak mendeteksi HDMI',
                'Laptop operator restart berulang',
                'Kabel extender display rusak',
            ],
            'Registrasi' => [
                'Antrian registrasi menumpuk',
                'Printer badge registrasi error',
                'Data peserta tidak sinkron',
            ],
            'Lainnya' => [
                'Butuh penyesuaian layout kabel',
                'Koordinasi teknis antar tim terlambat',
                'Permintaan bantuan teknis tambahan',
            ],
        ];

        $venues = ['Ballroom A', 'Ballroom B', 'Convention Hall', 'Meeting Room 1', 'Meeting Room 2', 'Auditorium'];
        $areas = ['Stage', 'Registration Desk', 'Exhibition Area', 'VIP Lounge', 'Main Hall', 'Backstage', 'FOH'];
        $now = Carbon::now();
        $generatedTicketNumbers = [];
        $dailyCounters = [];
        $createdCount = 0;

        for ($month = 11; $month >= 0; $month--) {
            $startDate = $now->copy()->subMonths($month)->startOfMonth();
            $endDate = $now->copy()->subMonths($month)->endOfMonth();

            $ticketsThisMonth = $month <= 1 ? rand(35, 50) : ($month <= 4 ? rand(25, 35) : rand(15, 25));

            for ($i = 0; $i < $ticketsThisMonth; $i++) {
                $createdAt = Carbon::createFromTimestamp(rand($startDate->timestamp, $endDate->timestamp));
                $dateKey = $createdAt->format('Ymd');

                if (!isset($dailyCounters[$dateKey])) {
                    $dailyCounters[$dateKey] = (int) Ticket::query()
                        ->whereDate('created_at', $createdAt->toDateString())
                        ->count();
                }
                $dailyCounters[$dateKey]++;

                $ticketNumber = $this->generateUniqueTicketNumber(
                    $createdAt,
                    $dailyCounters[$dateKey],
                    $generatedTicketNumbers
                );

                $categoryName = (string) $categories->keys()->random();
                $categoryId = (int) $categories[$categoryName];
                $titlePool = $titlesByCategory[$categoryName] ?? $titlesByCategory['Lainnya'];
                $baseTitle = $titlePool[array_rand($titlePool)];
                $clientId = (int) $clients->random();
                $title = $baseTitle;

                $priority = $this->pickPriorityWeighted();
                $status = $this->pickStatusWeighted($month);
                $needsAssignee = in_array($status, ['in_progress', 'waiting_response', 'resolved', 'closed'], true);
                $assignedTo = $needsAssignee ? (int) $vendors->random() : null;
                $urgency = $this->urgencyFromPriority($priority);

                $ticket = Ticket::create([
                    'ticket_number' => $ticketNumber,
                    'title' => $title,
                    'description' => 'Mohon segera ditangani. Issue ini mempengaruhi jalannya acara. Terima kasih.',
                    'user_id' => $clientId,
                    'category_id' => $categoryId,
                    'assigned_to' => $assignedTo,
                    'status' => $status,
                    'priority' => $priority,
                    'urgency_level' => $urgency,
                    'event_name' => 'Event ' . $createdAt->format('M Y'),
                    'venue' => $venues[array_rand($venues)],
                    'area' => $areas[array_rand($areas)],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $assignedAt = null;
                $firstResponseAt = null;
                $resolvedAt = null;
                $closedAt = null;

                if ($needsAssignee) {
                    $assignedAt = $createdAt->copy()->addMinutes(rand(5, 30));
                }

                if (in_array($status, ['in_progress', 'waiting_response', 'resolved', 'closed'], true)) {
                    $firstResponseAt = ($assignedAt ?? $createdAt)->copy()->addMinutes(rand(10, 60));
                }

                if (in_array($status, ['resolved', 'closed'], true)) {
                    $resolvedAt = $createdAt->copy()->addMinutes(match ($priority) {
                        'urgent' => rand(60, 240),
                        'high' => rand(120, 480),
                        'medium' => rand(240, 1440),
                        default => rand(480, 2880),
                    });
                }

                if ($status === 'closed') {
                    $closedAt = ($resolvedAt ?? $createdAt)->copy()->addMinutes(rand(30, 180));
                }

                $ticket->update([
                    'assigned_at' => $assignedAt,
                    'first_response_at' => $firstResponseAt,
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                    'updated_at' => $closedAt ?? $resolvedAt ?? $firstResponseAt ?? $assignedAt ?? $createdAt,
                ]);

                $this->createSlaTracking($ticket->fresh(), $firstResponseAt, $resolvedAt);
                $createdCount++;
            }
        }

        $this->command?->info("TicketSeeder selesai: {$createdCount} tiket berhasil dibuat (gaya data mirip helpdesk-api, tanpa duplikat).");
    }

    private function generateUniqueTicketNumber(Carbon $createdAt, int $sequence, array &$generatedTicketNumbers): string
    {
        while (true) {
            $candidate = 'TKT-' . $createdAt->format('Ymd') . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

            $existsInDb = Ticket::query()->where('ticket_number', $candidate)->exists();
            if (!isset($generatedTicketNumbers[$candidate]) && !$existsInDb) {
                $generatedTicketNumbers[$candidate] = true;
                return $candidate;
            }

            $sequence++;
        }
    }

    private function pickPriorityWeighted(): string
    {
        $rand = rand(1, 100);
        return match (true) {
            $rand <= 10 => 'urgent',
            $rand <= 30 => 'high',
            $rand <= 70 => 'medium',
            default => 'low',
        };
    }

    private function pickStatusWeighted(int $month): string
    {
        $rand = rand(1, 100);

        if ($month >= 3) {
            return match (true) {
                $rand <= 55 => 'closed',
                $rand <= 80 => 'resolved',
                $rand <= 92 => 'in_progress',
                $rand <= 97 => 'waiting_response',
                default => 'new',
            };
        }

        return match (true) {
            $rand <= 15 => 'closed',
            $rand <= 35 => 'resolved',
            $rand <= 65 => 'in_progress',
            $rand <= 80 => 'waiting_response',
            default => 'new',
        };
    }

    private function urgencyFromPriority(string $priority): string
    {
        return match ($priority) {
            'urgent' => 'critical',
            'high' => 'high',
            'medium' => 'medium',
            default => 'low',
        };
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


