<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Ticket;
use App\Models\User;
use App\Models\VendorWarning;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class VendorRatingSeeder extends Seeder
{
    public function run(): void
    {
        $completedTickets = Ticket::query()
            ->whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('assigned_to')
            ->orderBy('resolved_at')
            ->get();

        if ($completedTickets->isEmpty()) {
            $this->command?->warn('VendorRatingSeeder dilewati: tiket selesai belum tersedia.');
            return;
        }

        $ratings = [5, 4, 5, 3, 4, 5, 2, 4, 3, 5, 4, 2];
        $comments = [
            'Penanganan cepat dan hasilnya sesuai kebutuhan.',
            'Cukup baik, hanya koordinasi awal perlu dipercepat.',
            'Teknisi membantu dan komunikasi jelas.',
            'Masalah selesai, tapi waktu tunggu agak lama.',
            'Secara umum memuaskan untuk operasional acara.',
        ];

        foreach ($completedTickets as $index => $ticket) {
            // Supaya tidak semua tiket punya rating (lebih realistis)
            if ($index % 4 === 0) {
                continue;
            }

            Feedback::query()->firstOrCreate(
                ['ticket_id' => $ticket->id],
                [
                    'user_id' => $ticket->user_id,
                    'rating' => $ratings[$index % count($ratings)],
                    'comment' => $comments[$index % count($comments)],
                    'created_at' => $ticket->closed_at ?: $ticket->resolved_at ?: now(),
                    'updated_at' => $ticket->closed_at ?: $ticket->resolved_at ?: now(),
                ]
            );
        }

        if (Schema::hasTable('vendor_warnings')) {
            $this->seedVendorWarnings();
        }

        $this->command?->info('VendorRatingSeeder selesai: feedback dan warning vendor disiapkan.');
    }

    private function seedVendorWarnings(): void
    {
        $lowRatedVendorId = Feedback::query()
            ->join('tickets', 'tickets.id', '=', 'feedbacks.ticket_id')
            ->selectRaw('tickets.assigned_to as vendor_id, AVG(feedbacks.rating) as avg_rating')
            ->whereNotNull('tickets.assigned_to')
            ->groupBy('tickets.assigned_to')
            ->orderBy('avg_rating')
            ->value('vendor_id');

        if (!$lowRatedVendorId) {
            return;
        }

        VendorWarning::query()->firstOrCreate(
            [
                'vendor_id' => $lowRatedVendorId,
                'warning_type' => 'system',
                'message' => 'Sistem mendeteksi performa vendor menurun. Tingkatkan kecepatan respons dan kualitas penyelesaian.',
            ]
        );

        $adminId = User::query()->where('role', 'admin')->value('id');
        if ($adminId) {
            VendorWarning::query()->firstOrCreate(
                [
                    'vendor_id' => $lowRatedVendorId,
                    'warning_type' => 'admin',
                    'message' => 'Admin meminta evaluasi proses kerja vendor agar sesuai SOP HelpCenter.',
                ]
            );
        }
    }
}
