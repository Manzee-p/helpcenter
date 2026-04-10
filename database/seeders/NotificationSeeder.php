<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = Ticket::query()->with(['user', 'assignedTo'])->latest()->take(60)->get();

        if ($tickets->isEmpty()) {
            $this->command?->warn('NotificationSeeder dilewati: tiket belum tersedia.');
            return;
        }

        Notification::query()->delete();

        foreach ($tickets as $index => $ticket) {
            if ($ticket->assigned_to) {
                Notification::query()->create([
                    'user_id' => $ticket->assigned_to,
                    'type' => 'ticket_assigned',
                    'title' => 'Tiket Ditugaskan',
                    'message' => "Anda mendapat tiket baru: {$ticket->ticket_number} - {$ticket->title}",
                    'related_id' => $ticket->id,
                    'related_type' => 'ticket',
                    'read_at' => $index % 3 === 0 ? now()->subHours(rand(1, 72)) : null,
                    'created_at' => $ticket->created_at->copy()->addMinutes(rand(1, 30)),
                    'updated_at' => now(),
                ]);
            }

            Notification::query()->create([
                'user_id' => $ticket->user_id,
                'type' => 'ticket_status_changed',
                'title' => 'Status Tiket Diperbarui',
                'message' => "Status tiket {$ticket->ticket_number} saat ini: {$ticket->status}",
                'related_id' => $ticket->id,
                'related_type' => 'ticket',
                'read_at' => $index % 2 === 0 ? now()->subHours(rand(1, 72)) : null,
                'created_at' => $ticket->updated_at,
                'updated_at' => now(),
            ]);

            if ($ticket->status === 'resolved') {
                Notification::query()->create([
                    'user_id' => $ticket->user_id,
                    'type' => 'ticket_resolved',
                    'title' => 'Tiket Selesai',
                    'message' => "Tiket {$ticket->ticket_number} telah selesai. Silakan beri penilaian vendor.",
                    'related_id' => $ticket->id,
                    'related_type' => 'ticket',
                    'read_at' => null,
                    'created_at' => $ticket->resolved_at ?: now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command?->info('NotificationSeeder selesai: notifikasi client/vendor diperbarui.');
    }
}
