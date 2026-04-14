<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketDeletionRequest;
use App\Models\TicketReassignRequest;
use App\Models\VendorReport;
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
            if ($request->priority === 'unset') {
                $query->whereNull('priority');
            } else {
                $query->where('priority', $request->priority);
            }
        }

        if ($request->get('filter') === 'no_priority') {
            $query->whereNull('priority');
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
            'additionalInfos.user',
            'feedback',
            'slaTracking',
            'latestReassignRequest.vendor',
            'latestReassignRequest.reviewer',
        ])->findOrFail($id);

        $vendors = User::where('role', 'vendor')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $vendorLoads = Ticket::query()
            ->selectRaw('assigned_to, COUNT(*) as active_count')
            ->whereNotNull('assigned_to')
            ->whereIn('status', ['new', 'in_progress', 'waiting_response'])
            ->groupBy('assigned_to')
            ->pluck('active_count', 'assigned_to');

        return view('admin.tickets.show', compact('ticket', 'vendors', 'vendorLoads'));
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
        $selectedVendorId = (int) $request->assigned_to;

        $activeLoad = Ticket::query()
            ->where('assigned_to', $selectedVendorId)
            ->whereIn('status', ['new', 'in_progress', 'waiting_response'])
            ->when($ticket->assigned_to === $selectedVendorId, fn ($q) => $q->where('id', '!=', $ticket->id))
            ->count();

        if ($activeLoad >= 5) {
            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('warning', 'Vendor sedang sibuk (maksimal 5 tiket aktif). Pilih vendor lain atau tunggu persetujuan reassign.');
        }

        $ticket->update([
            'assigned_to' => $selectedVendorId,
            'assigned_at' => $ticket->assigned_at ?? now(),
        ]);

        return redirect()
            ->route('admin.tickets.show', $id)
            ->with('success', 'Vendor berhasil ditugaskan.');
    }

    public function reassignRequests(Request $request)
    {
        $query = TicketReassignRequest::with(['ticket.user', 'vendor', 'reviewer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15)->withQueryString();
        return view('admin.tickets.reassign-requests.index', compact('requests'));
    }

    public function showReassignRequest($id)
    {
        $requestItem = TicketReassignRequest::with([
            'ticket.user', 'ticket.category',
            'ticket.assignedTo', 'vendor', 'reviewer',
        ])->findOrFail($id);

        $vendorWorkload = $this->buildVendorWorkload(
            $requestItem->vendor_id,
            $requestItem->ticket_id
        );

        // ✅ TAMBAHKAN INI — untuk AJAX dari popup
        if (request()->expectsJson()) {
            return response()->json(['vendorWorkload' => $vendorWorkload]);
        }

        return view('admin.tickets.reassign-requests.show', compact('requestItem', 'vendorWorkload'));
    }
    
    public function processReassignRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $reassignRequest = TicketReassignRequest::with(['ticket', 'vendor'])->findOrFail($id);
        if ($reassignRequest->status !== 'pending') {
            return back()->with('warning', 'Permintaan ini sudah diproses.');
        }

        DB::transaction(function () use ($validated, $reassignRequest) {
            $approved = $validated['action'] === 'approve';
            $reassignRequest->update([
                'status' => $approved ? 'approved' : 'rejected',
                'reviewed_by' => Auth::id(),
                'admin_note' => $validated['admin_note'] ?? null,
                'reviewed_at' => now(),
            ]);

            if ($approved && $reassignRequest->ticket) {
                $reassignRequest->ticket->update([
                    'assigned_to' => null,
                    'assigned_at' => null,
                    'status' => 'new',
                ]);
            }
        });

        NotificationController::createNotification(
            $reassignRequest->vendor_id,
            'vendor_reassign_request_' . $reassignRequest->status,
            $reassignRequest->status === 'approved' ? 'Permintaan Reassign Disetujui' : 'Permintaan Reassign Ditolak',
            $reassignRequest->status === 'approved'
                ? "Permintaan reassign untuk tiket {$reassignRequest->ticket?->ticket_number} disetujui admin."
                : "Permintaan reassign untuk tiket {$reassignRequest->ticket?->ticket_number} ditolak admin.",
            $reassignRequest->ticket_id
        );

        return redirect()->route('admin.reassign-requests.index')
            ->with('success', 'Permintaan reassign berhasil diproses.');
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
        $nextStatus = $request->status;

        if (in_array($nextStatus, ['in_progress', 'waiting_response', 'resolved', 'closed'], true) && empty($ticket->assigned_to)) {
            return redirect()
                ->route('admin.tickets.show', $id)
                ->with('warning', 'Tiket belum ditugaskan ke vendor. Tugaskan vendor terlebih dahulu sebelum mengubah status ini.');
        }

        // Catat first_response_at saat pertama kali in_progress
        if ($nextStatus === 'in_progress' && !$ticket->first_response_at) {
            $ticket->first_response_at = now();
            $this->updateSlaResponseTime($ticket);
        }

        // Catat resolved_at
        if (in_array($nextStatus, ['resolved', 'closed'], true) && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
            $this->updateSlaResolutionTime($ticket);
        }

        // Catat closed_at
        if ($nextStatus === 'closed' && !$ticket->closed_at) {
            $ticket->closed_at = now();
        }

        $ticket->status = $nextStatus;
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

    private function buildVendorWorkload(?int $vendorId, ?int $relatedTicketId = null): array
    {
        $empty = [
            'active_tickets' => 0,
            'active_tickets_excluding_related' => 0,
            'total_tickets' => 0,
            'resolved_tickets' => 0,
            'total_reports' => 0,
            'can_take_new_assignment' => true,
            'assignment_limit' => 5,
            'progress_percent' => 0,
            'status_counts' => [
                'new' => 0,
                'in_progress' => 0,
                'waiting_response' => 0,
                'resolved' => 0,
                'closed' => 0,
            ],
            'active_tickets_list' => collect(),
        ];

        if (!$vendorId) {
            return $empty;
        }

        $activeStatuses = ['new', 'in_progress', 'waiting_response'];

        $totalTickets = Ticket::query()
            ->where('assigned_to', $vendorId)
            ->count();

        $resolvedTickets = Ticket::query()
            ->where('assigned_to', $vendorId)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        $activeTickets = Ticket::query()
            ->where('assigned_to', $vendorId)
            ->whereIn('status', $activeStatuses)
            ->count();

        $relatedTicketIsActive = false;
        if ($relatedTicketId) {
            $relatedTicketIsActive = Ticket::query()
                ->where('id', $relatedTicketId)
                ->where('assigned_to', $vendorId)
                ->whereIn('status', $activeStatuses)
                ->exists();
        }

        $activeTicketsExcludingRelated = max($activeTickets - ($relatedTicketIsActive ? 1 : 0), 0);

        $totalReports = VendorReport::query()
            ->where('vendor_id', $vendorId)
            ->count();

        $statusCounts = Ticket::query()
            ->selectRaw('status, COUNT(*) as total')
            ->where('assigned_to', $vendorId)
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeTicketsList = Ticket::query()
            ->select(['id', 'ticket_number', 'title', 'status'])
            ->where('assigned_to', $vendorId)
            ->whereIn('status', $activeStatuses)
            ->latest('created_at')
            ->take(10)
            ->get();

        $assignmentLimit = 5;
        $progressPercent = $totalTickets > 0
            ? (int) round(($resolvedTickets / $totalTickets) * 100)
            : 0;

        return [
            'active_tickets' => $activeTickets,
            'active_tickets_excluding_related' => $activeTicketsExcludingRelated,
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'total_reports' => $totalReports,
            'can_take_new_assignment' => $activeTicketsExcludingRelated < $assignmentLimit,
            'assignment_limit' => $assignmentLimit,
            'progress_percent' => $progressPercent,
            'status_counts' => [
                'new' => (int) ($statusCounts['new'] ?? 0),
                'in_progress' => (int) ($statusCounts['in_progress'] ?? 0),
                'waiting_response' => (int) ($statusCounts['waiting_response'] ?? 0),
                'resolved' => (int) ($statusCounts['resolved'] ?? 0),
                'closed' => (int) ($statusCounts['closed'] ?? 0),
            ],
            'active_tickets_list' => $activeTicketsList,
        ];
    }

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


