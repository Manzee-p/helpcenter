<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketTimelineSeeder extends Seeder
{
    /**
     * Normalisasi data timeline tiket agar urutannya valid.
     * - Tanpa vendor: tidak boleh punya assigned_at / first_response_at / resolved_at / closed_at
     * - Status selain "new" harus punya vendor (jika tidak, fallback ke "new")
     */
    public function run(): void
    {
        $tickets = Ticket::query()->with('assignedTo')->get();
        $fixedCount = 0;

        foreach ($tickets as $ticket) {
            $payload = [];
            $hasVendor = !empty($ticket->assigned_to);
            $status = (string) $ticket->status;

            if (!$hasVendor) {
                if (in_array($status, ['in_progress', 'waiting_response', 'resolved', 'closed'], true)) {
                    $payload['status'] = 'new';
                }

                if (!is_null($ticket->assigned_at)) {
                    $payload['assigned_at'] = null;
                }
                if (!is_null($ticket->first_response_at)) {
                    $payload['first_response_at'] = null;
                }
                if (!is_null($ticket->resolved_at)) {
                    $payload['resolved_at'] = null;
                }
                if (!is_null($ticket->closed_at)) {
                    $payload['closed_at'] = null;
                }
            } else {
                if ($status === 'new') {
                    if (!is_null($ticket->first_response_at)) {
                        $payload['first_response_at'] = null;
                    }
                    if (!is_null($ticket->resolved_at)) {
                        $payload['resolved_at'] = null;
                    }
                    if (!is_null($ticket->closed_at)) {
                        $payload['closed_at'] = null;
                    }
                }

                if ($ticket->assigned_at && $ticket->first_response_at && $ticket->first_response_at->lt($ticket->assigned_at)) {
                    $payload['first_response_at'] = $ticket->assigned_at->copy()->addMinutes(5);
                }

                if ($ticket->resolved_at && $ticket->first_response_at && $ticket->resolved_at->lt($ticket->first_response_at)) {
                    $payload['resolved_at'] = $ticket->first_response_at->copy()->addMinutes(30);
                }

                if ($ticket->closed_at && $ticket->resolved_at && $ticket->closed_at->lt($ticket->resolved_at)) {
                    $payload['closed_at'] = $ticket->resolved_at->copy()->addMinutes(10);
                }
            }

            if (!empty($payload)) {
                $ticket->update($payload);
                $fixedCount++;
            }
        }

        $this->command?->info("TicketTimelineSeeder selesai: {$fixedCount} tiket dinormalisasi.");
    }
}


