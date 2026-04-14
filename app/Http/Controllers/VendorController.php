<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Ticket;
use App\Models\TicketReassignRequest;
use App\Models\User;
use Carbon\Carbon;

class VendorController extends Controller
{
    // ── Blade: Ticket List ──────────────────────────────────────
    public function myTickets(Request $request)
    {
        $vendorId = Auth::id();
        $query = Ticket::with(['user', 'category', 'latestReassignRequest'])
                       ->where('assigned_to', $vendorId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereNotIn('status', ['closed', 'resolved']);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($w) => $w->where('title', 'like', "%$q%")
                                      ->orWhere('ticket_number', 'like', "%$q%"));
        }

        $tickets = $query->latest()->paginate($request->get('per_page', 15))->withQueryString();

        $all = Ticket::where('assigned_to', $vendorId)
                     ->whereNotIn('status', ['closed', 'resolved'])
                     ->get();
        $stats = [
            'new'         => $all->where('status', 'new')->count(),
            'in_progress' => $all->where('status', 'in_progress')->count(),
            'waiting'     => $all->where('status', 'waiting_response')->count(),
        ];

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ],
                'stats' => $stats,
            ]);
        }

        return view('vendor.tickets.index', compact('tickets', 'stats'));
    }

    // ── Blade: Ticket Detail ────────────────────────────────────
    public function show(Request $request, $id)
    {
        $ticket = Ticket::with([
            'user', 'category', 'attachments', 'additionalInfos.user', 'feedback', 'slaTracking', 'latestReassignRequest.reviewer'
        ])->where('assigned_to', Auth::id())
          ->findOrFail($id);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $ticket,
            ]);
        }

        return view('vendor.tickets.show', compact('ticket'));
    }

    // ── Blade: Update Status ────────────────────────────────────
    public function updateTicketStatus(Request $request, $id)
    {
        $ticket = Ticket::where('assigned_to', Auth::id())->findOrFail($id);

        $request->validate([
            'status' => 'required|in:new,in_progress,waiting_response,resolved,closed',
            'completion_note' => 'nullable|string|max:1500',
            'completion_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $old = $ticket->status;
        $new = $request->status;
        $ticket->status = $new;

        if ($new === 'resolved' && !$ticket->resolved_at) {
            $ticket->resolved_at = now();
            $ticket->completion_reported_at = now();

            if ($request->hasFile('completion_photo')) {
                if ($ticket->completion_photo_path && Storage::disk('public')->exists($ticket->completion_photo_path)) {
                    Storage::disk('public')->delete($ticket->completion_photo_path);
                }
                $file = $request->file('completion_photo');
                $path = $file->store('vendor-completion-proofs', 'public');
                $ticket->completion_photo_path = $path;
                $ticket->completion_photo_name = $file->getClientOriginalName();
                $ticket->completion_photo_type = $file->getMimeType();
            }

            if ($request->filled('completion_note')) {
                $ticket->completion_note = $request->completion_note;
            }
        }
        if ($new === 'closed' && !$ticket->closed_at) {
            $ticket->closed_at = now();
        }
        if ($new === 'in_progress' && !$ticket->assigned_at) {
            $ticket->assigned_at = now();
        }
        if ($new !== 'new' && !$ticket->first_response_at && $old === 'new') {
            $ticket->first_response_at = now();
        }

        $ticket->save();

        if ($ticket->user_id) {
            $statusLabel = str_replace('_', ' ', $new);
            NotificationController::createNotification(
                $ticket->user_id,
                'ticket_status_changed',
                'Status Tiket Diperbarui',
                "Status tiket {$ticket->ticket_number} berubah menjadi {$statusLabel}.",
                $ticket->id
            );

            if ($new === 'waiting_response') {
                NotificationController::createNotification(
                    $ticket->user_id,
                    'additional_info_requested',
                    'Vendor Membutuhkan Informasi Tambahan',
                    "Vendor meminta informasi tambahan untuk tiket {$ticket->ticket_number}.",
                    $ticket->id
                );
            }
        }

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Status tiket berhasil diperbarui.',
                'data' => $ticket->load(['user', 'category', 'slaTracking']),
            ]);
        }

        return redirect()->route('vendor.tickets.show', $ticket->id)
            ->with('success', 'Status tiket berhasil diperbarui.');
    }

    public function requestReassign(Request $request, $id)
    {
        $ticket = Ticket::where('assigned_to', Auth::id())->findOrFail($id);
        if ($ticket->status !== 'new') {
            return back()->with('warning', 'Request reassign hanya bisa diajukan saat tiket baru ditugaskan.');
        }
        if (in_array($ticket->status, ['resolved', 'closed'], true)) {
            return back()->with('warning', 'Tiket yang sudah selesai/ditutup tidak bisa diajukan reassign.');
        }

        $validated = $request->validate([
            'reason_option' => 'required|in:beban_tinggi,di_luar_spesialisasi,lokasi_tidak_terjangkau,jadwal_bentrok,butuh_peralatan_khusus,lainnya',
            'reason_detail' => 'required|string|min:10|max:1500',
        ]);

        $existingPending = TicketReassignRequest::where('ticket_id', $ticket->id)
            ->where('vendor_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('warning', 'Permintaan reassign untuk tiket ini masih menunggu persetujuan admin.');
        }

        TicketReassignRequest::create([
            'ticket_id' => $ticket->id,
            'vendor_id' => Auth::id(),
            'reason_option' => $validated['reason_option'],
            'reason_detail' => $validated['reason_detail'],
            'status' => 'pending',
        ]);

        $adminIds = User::where('role', 'admin')->pluck('id');
        foreach ($adminIds as $adminId) {
            NotificationController::createNotification(
                $adminId,
                'vendor_reassign_request_pending',
                'Vendor Mengajukan Reassign',
                "Vendor " . Auth::user()->name . " mengajukan reassign untuk tiket {$ticket->ticket_number}.",
                $ticket->id
            );
        }

        return back()->with('success', 'Permintaan reassign berhasil dikirim. Status: Menunggu Persetujuan Admin.');
    }

    // ── Blade: History ──────────────────────────────────────────
    public function history(Request $request)
    {
        $vendorId = Auth::id();

        $query = Ticket::with(['user', 'category', 'feedback', 'slaTracking'])
                       ->where('assigned_to', $vendorId)
                       ->whereIn('status', ['resolved', 'closed']);

        if ($request->filled('start_date')) {
            $query->whereDate('resolved_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('resolved_at', '<=', $request->end_date);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->latest('resolved_at')->paginate(15)->withQueryString();

        // Summary counts (all, not just page)
        $allHistory = Ticket::where('assigned_to', $vendorId)
                            ->whereIn('status', ['resolved', 'closed']);

        $summary = [
            'total'    => (clone $allHistory)->count(),
            'resolved' => (clone $allHistory)->where('status', 'resolved')->count(),
            'closed'   => (clone $allHistory)->where('status', 'closed')->count(),
        ];

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ],
                'summary' => $summary,
            ]);
        }

        return view('vendor.history', compact('tickets', 'summary'));
    }

    // ── API: Ticket Stats (AJAX for chart) ──────────────────────
    public function ticketStats(Request $request)
    {
        $vendorId = Auth::id();
        $period   = $request->get('period', 'monthly');
        $items    = [];

        if ($period === 'weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $items[] = [
                    'period'   => $day->format('D, d M'),
                    'total'    => Ticket::where('assigned_to', $vendorId)->whereDate('created_at', $day)->count(),
                    'resolved' => Ticket::where('assigned_to', $vendorId)->whereDate('resolved_at', $day)->count(),
                ];
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $items[] = [
                    'period'   => $month->format('M Y'),
                    'total'    => Ticket::where('assigned_to', $vendorId)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count(),
                    'resolved' => Ticket::where('assigned_to', $vendorId)->whereYear('resolved_at', $month->year)->whereMonth('resolved_at', $month->month)->count(),
                ];
            }
        }

        return response()->json(['data' => $items]);
    }

    public function dashboard()
    {
        try {
            $vendorId = Auth::id();

            $activeTickets = Ticket::where('assigned_to', $vendorId)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();

            $newTickets = Ticket::where('assigned_to', $vendorId)
                ->where('status', 'new')
                ->count();

            $inProgress = Ticket::where('assigned_to', $vendorId)
                ->where('status', 'in_progress')
                ->count();

            $resolvedThisWeek = Ticket::where('assigned_to', $vendorId)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereBetween('resolved_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $totalWithSla = DB::table('tickets')
                ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                ->where('tickets.assigned_to', $vendorId)
                ->whereNotNull('sla_trackings.response_sla_met')
                ->count();

            $slaMet = DB::table('tickets')
                ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                ->where('tickets.assigned_to', $vendorId)
                ->where('sla_trackings.response_sla_met', true)
                ->count();

            $slaCompliance = $totalWithSla > 0 ? round(($slaMet / $totalWithSla) * 100, 2) : 0;

            $recentTickets = Ticket::with(['user', 'category'])
                ->where('assigned_to', $vendorId)
                ->latest()
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'stats' => [
                    'active_tickets' => $activeTickets,
                    'new_tickets' => $newTickets,
                    'in_progress' => $inProgress,
                    'resolved_this_week' => $resolvedThisWeek,
                    'sla_compliance' => $slaCompliance,
                ],
                'recent_tickets' => $recentTickets,
            ]);
        } catch (\Throwable $e) {
            Log::error('Vendor dashboard error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard vendor.',
            ], 500);
        }
    }

    public function performance()
    {
        try {
            $vendorId = Auth::id();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $totalAssigned = Ticket::where('assigned_to', $vendorId)->count();
            $resolvedThisMonth = Ticket::where('assigned_to', $vendorId)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereBetween('resolved_at', [$monthStart, $monthEnd])
                ->count();

            $avgResponseTime = DB::table('tickets')
                ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                ->where('tickets.assigned_to', $vendorId)
                ->whereNotNull('sla_trackings.actual_response_time')
                ->avg('sla_trackings.actual_response_time');

            $avgResolutionTime = DB::table('tickets')
                ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                ->where('tickets.assigned_to', $vendorId)
                ->whereNotNull('sla_trackings.actual_resolution_time')
                ->avg('sla_trackings.actual_resolution_time');

            $ticketsByStatus = Ticket::where('assigned_to', $vendorId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_assigned' => $totalAssigned,
                    'resolved_this_month' => $resolvedThisMonth,
                    'avg_response_time' => round($avgResponseTime ?? 0, 2),
                    'avg_resolution_time' => round($avgResolutionTime ?? 0, 2),
                    'tickets_by_status' => $ticketsByStatus,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Vendor performance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat performa vendor.',
            ], 500);
        }
    }

    public function getProfile()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'company_name' => $user->company_name,
                    'company_phone' => $user->company_phone,
                    'company_address' => $user->company_address,
                    'specialization' => $user->specialization,
                    'avatar' => $user->avatar,
                    'avatar_url' => $user->avatar ? url('storage/' . $user->avatar) : null,
                ],
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'company_name' => 'sometimes|nullable|string|max:255',
            'company_phone' => 'sometimes|nullable|string|max:20',
            'company_address' => 'sometimes|nullable|string|max:255',
            'specialization' => 'sometimes|nullable|string|max:255',
            'avatar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $validated;
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil vendor berhasil diperbarui.',
            'data' => [
                'user' => $user->fresh(),
            ],
        ]);
    }

    public function deleteAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar vendor berhasil dihapus.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini tidak cocok.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson() || $request->wantsJson();
    }
}


