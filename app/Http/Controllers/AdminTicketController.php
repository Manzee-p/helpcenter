<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketDeletionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Swal; // opsional, jika pakai Laravel SweetAlert package

class AdminTicketController extends Controller
{
    /**
     * Tampilkan daftar semua tiket (Blade view).
     */
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'assignedTo']);

        // ── Filter Status ──
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Filter Priority ──
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // ── Search ──
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // ── Sort ──
        $query->orderBy('created_at', 'desc');

        // ── Paginate ──
        $tickets = $query->paginate(15)->withQueryString();

        // ── Stats (dari semua tiket, tidak terpengaruh filter halaman) ──
        $stats = [
            'new_count'        => Ticket::where('status', 'new')->count(),
            'in_progress_count'=> Ticket::where('status', 'in_progress')->count(),
            'assigned_count'   => Ticket::whereNotNull('assigned_to')->count(),
        ];

        // ── Vendor list untuk modal assign ──
        $vendors = User::where('role', 'vendor')->where('is_active', true)->orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'stats', 'vendors'));
    }

    /**
     * Tampilkan detail satu tiket (Blade view).
     */
    public function show($id)
    {
        $ticket = Ticket::with([
            'user',
            'category',
            'assignedTo',
            'attachments',
            'feedback',
            'slaTracking',
        ])->findOrFail($id);

        $vendors = User::where('role', 'vendor')->where('is_active', true)->orderBy('name')->get();

        return view('admin.tickets.show', compact('ticket', 'vendors'));
    }

    /**
     * Tugaskan vendor ke tiket.
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'vendor')->where('is_active', true)),
            ],
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'assigned_at' => $ticket->assigned_at ?? now(),
        ]);

        return redirect()
            ->route('admin.tickets.show', $id)
            ->with('success', 'Vendor berhasil ditugaskan.');
    }

    /**
     * Perbarui status tiket.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,waiting_response,resolved,closed',
        ]);

        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status;

        // Catat first_response_at saat pertama kali in_progress
        if ($request->status === 'in_progress' && !$ticket->first_response_at) {
            $ticket->first_response_at = now();
            $this->updateSlaResponseTime($ticket);
        }

        // Catat resolved_at
        if (in_array($request->status, ['resolved', 'closed']) && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
            $this->updateSlaResolutionTime($ticket);
        }

        // Catat closed_at
        if ($request->status === 'closed' && !$ticket->closed_at) {
            $ticket->closed_at = now();
        }

        $ticket->status = $request->status;
        $ticket->save();

        return redirect()
            ->route('admin.tickets.show', $id)
            ->with('success', 'Status tiket berhasil diperbarui.');
    }

    /**
     * Perbarui prioritas tiket.
     */
    public function updatePriority(Request $request, $id)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update(['priority' => $request->priority]);

        return redirect()
            ->route('admin.tickets.show', $id)
            ->with('success', 'Prioritas tiket berhasil diperbarui.');
    }

    /**
     * Hapus tiket.
     */
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()
            ->route('admin.tickets.index')
            ->with('success', 'Tiket berhasil dihapus.');
    }

    public function deletionRequests(Request $request)
    {
        $query = TicketDeletionRequest::with(['ticket', 'user', 'reviewer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.tickets.deletion-requests.index', compact('requests'));
    }

    public function showDeletionRequest($id)
    {
        $requestItem = TicketDeletionRequest::with(['ticket.user', 'user', 'reviewer'])->findOrFail($id);
        return view('admin.tickets.deletion-requests.show', compact('requestItem'));
    }

    public function processDeletionRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:1500',
        ]);

        $requestItem = TicketDeletionRequest::with('ticket')->findOrFail($id);
        if ($requestItem->status !== 'pending') {
            return back()->with('warning', 'Permintaan ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($validated, $requestItem) {
            $isApprove = $validated['action'] === 'approve';

            $requestItem->update([
                'status' => $isApprove ? 'approved' : 'rejected',
                'reviewed_by' => Auth::id(),
                'admin_note' => $validated['admin_note'] ?? null,
                'reviewed_at' => now(),
            ]);

            if ($isApprove && $requestItem->ticket) {
                $requestItem->ticket->delete();
            }
        });

        NotificationController::createNotification(
            $requestItem->user_id,
            'ticket_deletion_request_' . $requestItem->status,
            $requestItem->status === 'approved' ? 'Permintaan Hapus Disetujui' : 'Permintaan Hapus Ditolak',
            $requestItem->status === 'approved'
                ? "Permintaan hapus tiket {$requestItem->ticket?->ticket_number} disetujui admin."
                : "Permintaan hapus tiket {$requestItem->ticket?->ticket_number} ditolak admin.",
            $requestItem->ticket_id
        );

        return redirect()->route('admin.ticket-deletion-requests.index')
            ->with('success', 'Permintaan penghapusan tiket berhasil diproses.');
    }

    // ── Private helpers ──

    private function updateSlaResponseTime(Ticket $ticket): void
    {
        $sla = $ticket->slaTracking;
        if ($sla && $ticket->first_response_at) {
            $actual = (int) round($ticket->created_at->diffInMinutes($ticket->first_response_at));
            $sla->update([
                'actual_response_time' => $actual,
                'response_sla_met'     => $actual <= $sla->response_time_sla,
            ]);
        }
    }

    private function updateSlaResolutionTime(Ticket $ticket): void
    {
        $sla = $ticket->slaTracking;
        if ($sla && $ticket->resolved_at) {
            $actual = (int) round($ticket->created_at->diffInMinutes($ticket->resolved_at));
            $sla->update([
                'actual_resolution_time' => $actual,
                'resolution_sla_met'     => $actual <= $sla->resolution_time_sla,
            ]);
        }
    }
}
