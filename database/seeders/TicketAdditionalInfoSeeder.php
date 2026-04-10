<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketAdditionalInfo;
use Illuminate\Database\Seeder;

class TicketAdditionalInfoSeeder extends Seeder
{
    public function run(): void
    {
        $waitingTickets = Ticket::query()
            ->where('status', 'waiting_response')
            ->with('user')
            ->take(20)
            ->get();

        if ($waitingTickets->isEmpty()) {
            $this->command?->warn('TicketAdditionalInfoSeeder dilewati: tiket waiting_response tidak ditemukan.');
            return;
        }

        foreach ($waitingTickets as $index => $ticket) {
            TicketAdditionalInfo::query()->firstOrCreate(
                [
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->user_id,
                    'note' => "Info tambahan dari client untuk tiket {$ticket->ticket_number} (simulasi seeder #{$index}).",
                ],
                [
                    'created_at' => now()->subHours(rand(1, 48)),
                    'updated_at' => now()->subHours(rand(1, 48)),
                ]
            );
        }

        $this->command?->info('TicketAdditionalInfoSeeder selesai: contoh info tambahan client berhasil dibuat.');
    }
}

