<?php

namespace Database\Seeders;

use App\Models\StatusBoard;
use App\Models\StatusUpdate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StatusBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', 'admin')->first();

        if (!$admin) {
            $this->command?->warn('StatusBoardSeeder dilewati: admin tidak ditemukan.');
            return;
        }

        $currentYear = Carbon::now()->year;

        $boards = [
            [
                'incident_number' => "INC-{$currentYear}-001",
                'title' => 'Gangguan Jaringan Internet Area Hall A',
                'description' => 'Koneksi internet pada area Hall A tidak stabil dan mempengaruhi proses registrasi digital.',
                'category' => 'network_issue',
                'affected_area' => 'Hall A',
                'status' => 'monitoring',
                'severity' => 'high',
                'started_at' => Carbon::now()->subHours(3),
                'resolved_at' => null,
                'is_public' => true,
                'is_pinned' => true,
                'updates' => [
                    ['message' => 'Incident dibuat dan tim network melakukan pengecekan router inti.', 'type' => 'investigating', 'at' => Carbon::now()->subHours(3)],
                    ['message' => 'Konfigurasi ulang gateway selesai, trafik mulai normal.', 'type' => 'update', 'at' => Carbon::now()->subHours(1)],
                ],
            ],
            [
                'incident_number' => "INC-{$currentYear}-002",
                'title' => 'Masalah Pencahayaan di Main Stage',
                'description' => 'Beberapa lampu moving head tidak sinkron dengan kontrol utama.',
                'category' => 'technical_issue',
                'affected_area' => 'Main Stage',
                'status' => 'investigating',
                'severity' => 'medium',
                'started_at' => Carbon::now()->subHours(2),
                'resolved_at' => null,
                'is_public' => true,
                'is_pinned' => false,
                'updates' => [
                    ['message' => 'Tim lighting mengecek jalur DMX dan power distribusi.', 'type' => 'investigating', 'at' => Carbon::now()->subHours(2)],
                ],
            ],
            [
                'incident_number' => "INC-{$currentYear}-003",
                'title' => 'Perawatan AC Ruang VIP Selesai',
                'description' => 'Perawatan berkala unit AC ruang VIP sudah selesai dan suhu ruangan kembali normal.',
                'category' => 'facility_issue',
                'affected_area' => 'Ruang VIP',
                'status' => 'resolved',
                'severity' => 'low',
                'started_at' => Carbon::now()->subDay(),
                'resolved_at' => Carbon::now()->subHours(18),
                'is_public' => true,
                'is_pinned' => false,
                'updates' => [
                    ['message' => 'Maintenance dimulai sesuai jadwal operasional.', 'type' => 'investigating', 'at' => Carbon::now()->subDay()],
                    ['message' => 'Unit AC kembali berfungsi normal.', 'type' => 'resolved', 'at' => Carbon::now()->subHours(18)],
                ],
            ],
        ];

        foreach ($boards as $payload) {
            $updates = $payload['updates'];
            unset($payload['updates']);

            $board = StatusBoard::query()->updateOrCreate(
                ['incident_number' => $payload['incident_number']],
                array_merge($payload, [
                    'created_by' => $admin->id,
                    'assigned_to' => $admin->id,
                ])
            );

            foreach ($updates as $update) {
                StatusUpdate::query()->updateOrCreate(
                    [
                        'status_board_id' => $board->id,
                        'message' => $update['message'],
                    ],
                    [
                        'user_id' => $admin->id,
                        'update_type' => $update['type'],
                        'created_at' => $update['at'],
                        'updated_at' => $update['at'],
                    ]
                );
            }
        }

        $this->command?->info('StatusBoardSeeder selesai: status board dan update publik siap.');
    }
}
