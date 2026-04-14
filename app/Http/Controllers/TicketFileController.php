<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use App\Models\TicketAdditionalInfo;
use App\Models\Ticket;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketFileController extends Controller
{
    public function viewTicketAttachment(TicketAttachment $attachment): Response
    {
        $ticket = $attachment->ticket;
        $this->ensureCanAccessTicket($ticket?->user_id, $ticket?->assigned_to);

        return $this->streamPublicFile(
            $attachment->file_path,
            $attachment->file_name ?: basename((string) $attachment->file_path),
            $attachment->file_type
        );
    }

    public function viewAdditionalInfoAttachment(TicketAdditionalInfo $additionalInfo): Response
    {
        $ticket = $additionalInfo->ticket;
        $this->ensureCanAccessTicket($ticket?->user_id, $ticket?->assigned_to);

        abort_if(empty($additionalInfo->photo_path), 404);

        return $this->streamPublicFile(
            $additionalInfo->photo_path,
            $additionalInfo->photo_name ?: basename((string) $additionalInfo->photo_path),
            $additionalInfo->photo_type
        );
    }

    public function viewCompletionProof(Ticket $ticket): Response
    {
        $this->ensureCanAccessTicket($ticket->user_id, $ticket->assigned_to);
        abort_if(empty($ticket->completion_photo_path), 404);

        return $this->streamPublicFile(
            $ticket->completion_photo_path,
            $ticket->completion_photo_name ?: basename((string) $ticket->completion_photo_path),
            $ticket->completion_photo_type
        );
    }

    private function ensureCanAccessTicket(?int $ticketUserId, ?int $ticketVendorId): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'client' && $ticketUserId && (int) $user->id === (int) $ticketUserId) {
            return;
        }

        if ($user->role === 'vendor' && $ticketVendorId && (int) $user->id === (int) $ticketVendorId) {
            return;
        }

        abort(403);
    }

    private function streamPublicFile(string $path, string $filename, ?string $mimeType = null): Response
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        $mime = $mimeType ?: Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
