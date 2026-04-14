<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketReassignRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketReassignRequestSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = User::where('role', 'vendor')->pluck('id');
        if ($vendors->isEmpty()) {
            $this->command?->warn('TicketReassignRequestSeeder dilewati: vendor belum tersedia.');
            return;
        }

        $ticket = Ticket::whereIn('assigned_to', $vendors)
            ->whereIn('status', ['new', 'in_progress', 'waiting_response'])
            ->latest('id')
            ->first();

        if (!$ticket) {
            $this->command?->warn('TicketReassignRequestSeeder dilewati: tidak ada tiket aktif milik vendor.');
            return;
        }

        TicketReassignRequest::updateOrCreate(
            [
                'ticket_id' => $ticket->id,
                'vendor_id' => $ticket->assigned_to,
                'status' => 'pending',
            ],
            [
                'reason_option' => 'beban_tinggi',
                'reason_detail' => 'Vendor sudah menangani banyak tiket aktif dan meminta redistribusi agar SLA tetap terjaga.',
            ]
        );

        $this->command?->info('TicketReassignRequestSeeder selesai: contoh permintaan reassign berhasil dibuat.');
    }
}



