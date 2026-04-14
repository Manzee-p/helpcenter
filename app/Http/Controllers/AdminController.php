<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Feedback;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use App\Models\SlaTracking;
use App\Models\VendorWarning;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            $stats = [
                'total_tickets'            => Ticket::count(),
                'new_tickets'              => Ticket::where('status', 'new')->count(),
                'in_progress'              => Ticket::where('status', 'in_progress')->count(),
                'resolved'                 => Ticket::whereIn('status', ['resolved', 'closed'])->count(),
                'tickets_without_priority' => Ticket::whereNull('priority')->count(),
                'total_users'              => User::count(),
                'vendors'                  => User::where('role', 'vendor')->count(),
                'clients'                  => User::where('role', 'client')->count(),
            ];

            $slaTotal = SlaTracking::whereHas('ticket', function ($query) {
                $query->whereNotNull('priority')->whereNotNull('resolved_at');
            })->count();

            $slaMet = SlaTracking::whereHas('ticket', function ($query) {
                $query->whereNotNull('priority')->whereNotNull('resolved_at');
            })->where('resolution_sla_met', true)->count();

            $slaMissed     = $slaTotal - $slaMet;
            $slaPercentage = $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100) : 0;

            $slaPerformance = [
                'total'      => $slaTotal,
                'met'        => $slaMet,
                'missed'     => $slaMissed,
                'percentage' => $slaPercentage,
            ];

            $recentTickets = Ticket::with(['user', 'category', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Rating data untuk panel dashboard
            $ratingData = $this->getRatingData();

            return view('admin.dashboard', compact('stats', 'slaPerformance', 'recentTickets', 'ratingData'));

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memuat data dashboard.']);
        }
    }

    private function getRatingData(): array
    {
        $feedbacks    = Feedback::all();
        $total        = $feedbacks->count();
        $average      = $total > 0 ? round($feedbacks->avg('rating'), 1) : 0;
        $distribution = [];
        for ($s = 1; $s <= 5; $s++) {
            $distribution[$s] = $feedbacks->where('rating', $s)->count();
        }

        return compact('total', 'average', 'distribution');
    }

    // ─────────────────────────────────────────────────────────────
    //  ANALYTICS  ← satu-satunya method untuk /admin/analytics
    // ─────────────────────────────────────────────────────────────
    /**
     * GET /admin/analytics
     * Semua kalkulasi di PHP, pass langsung ke Blade.
     * Tidak ada AJAX / endpoint JSON terpisah.
     */
    public function getAnalytics(Request $request)
    {
        try {
            [$startDate, $endDate] = $this->normalizeDateRange(
                $request->input('start_date'),
                $request->input('end_date')
            );

            /* ── 1. By status ── */
            $ticketsByStatus = Ticket::selectRaw('status, COUNT(*) as count')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->get()
                ->map(fn ($i) => ['status' => $i->status, 'count' => (int) $i->count])
                ->values()
                ->toArray();

            /* ── 2. By priority ── */
            $ticketsByPriority = Ticket::selectRaw('priority, COUNT(*) as count')
                ->whereNotNull('priority')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('priority')
                ->get()
                ->map(fn ($i) => ['priority' => $i->priority, 'count' => (int) $i->count])
                ->values()
                ->toArray();

            /* ── 3. By category ── */
            $ticketsByCategory = [];
            if (Schema::hasColumn('tickets', 'category_id')) {
                $rows = DB::table('tickets as t')
                    ->join('ticket_categories as tc', 't.category_id', '=', 'tc.id')
                    ->selectRaw('tc.name, COUNT(t.id) as count')
                    ->whereBetween('t.created_at', [$startDate, $endDate])
                    ->whereNull('t.deleted_at')
                    ->groupBy('tc.id', 'tc.name')
                    ->get();

                foreach ($rows as $row) {
                    $ticketsByCategory[$row->name] = (int) $row->count;
                }
            }

            /* ── 4. Monthly tickets ── */
            $monthlyRaw = DB::table('tickets')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                ->orderByRaw("DATE_FORMAT(created_at, '%Y-%m')")
                ->get();

            $monthlyTickets = [];
            foreach ($monthlyRaw as $row) {
                $monthlyTickets[$row->month] = (int) $row->count;
            }
            if (empty($monthlyTickets)) {
                $monthlyTickets[$startDate->format('Y-m')] = 0;
            }

            /* ── 5. Avg resolution time ── */
            $avgResolution = DB::table('sla_trackings as s')
                ->join('tickets as t', 's.ticket_id', '=', 't.id')
                ->whereBetween('t.created_at', [$startDate, $endDate])
                ->whereNotNull('s.actual_resolution_time')
                ->where('s.actual_resolution_time', '>', 0)
                ->whereNull('t.deleted_at')
                ->avg('s.actual_resolution_time');

            /* ── Raw analytics array (di-inject ke JS via @json) ── */
            $analytics = [
                'tickets_by_status'           => $ticketsByStatus,
                'tickets_by_priority'         => $ticketsByPriority,
                'tickets_by_category'         => $ticketsByCategory,
                'monthly_tickets'             => $monthlyTickets,
                'avg_resolution_time_minutes' => round($avgResolution ?? 0, 2),
                'date_range'                  => [
                    'start' => $startDate->toDateString(),
                    'end'   => $endDate->toDateString(),
                ],
            ];

            /* ── Computed values untuk Blade template ── */

            $totalTickets = collect($ticketsByStatus)->sum('count');

            $newItem         = collect($ticketsByStatus)->firstWhere('status', 'new');
            $newTicketsCount = $newItem ? $newItem['count'] : 0;
            $newTicketsPct   = $totalTickets > 0
                ? round(($newTicketsCount / $totalTickets) * 100, 1)
                : 0;

            $mostUsedCategory = '-';
            if (!empty($ticketsByCategory)) {
                $mostUsedCategory = (string) array_search(max($ticketsByCategory), $ticketsByCategory);
            }

            $mostUsedPriority = '-';
            if (!empty($ticketsByPriority)) {
                $mostUsedPriority = collect($ticketsByPriority)->sortByDesc('count')->first()['priority'] ?? '-';
            }

            $monthlyCounts  = array_values($monthlyTickets);
            $monthlyAverage = count($monthlyCounts)
                ? (int) round(array_sum($monthlyCounts) / count($monthlyCounts))
                : 0;
            $peakMonthCount = count($monthlyCounts) ? max($monthlyCounts) : 0;

            $trendIndicator = 0;
            if (count($monthlyCounts) >= 2) {
                $mid    = (int) floor(count($monthlyCounts) / 2);
                $first  = array_slice($monthlyCounts, 0, $mid);
                $second = array_slice($monthlyCounts, $mid);
                $fAvg   = array_sum($first) / count($first);
                $sAvg   = array_sum($second) / count($second);
                $trendIndicator = $fAvg > 0
                    ? (int) round((($sAvg - $fAvg) / $fAvg) * 100)
                    : 0;
            }

            $hasData = !empty($ticketsByStatus)
                || !empty($ticketsByPriority)
                || !empty($ticketsByCategory);

            return view('admin.analytics.index', compact(
                'analytics',
                'startDate',
                'endDate',
                'hasData',
                'totalTickets',
                'newTicketsCount',
                'newTicketsPct',
                'mostUsedCategory',
                'mostUsedPriority',
                'monthlyAverage',
                'peakMonthCount',
                'trendIndicator'
            ));

        } catch (\Exception $e) {
            Log::error('Analytics error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memuat data analitik.']);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  GET FIRST TICKET WITHOUT PRIORITY
    // ─────────────────────────────────────────────────────────────
    public function getFirstNoPriorityTicket()
    {
        try {
            $ticket = Ticket::whereNull('priority')
                ->with(['user', 'category', 'assignedTo'])
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$ticket) {
                return response()->json(['message' => 'No tickets without priority found', 'ticket' => null], 404);
            }

            return response()->json(['message' => 'Ticket found', 'ticket' => $ticket]);

        } catch (\Exception $e) {
            Log::error('Get first no priority ticket error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch ticket', 'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  USER MANAGEMENT
    // ─────────────────────────────────────────────────────────────
    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users',
            'phone'            => 'nullable|string',
            'password'         => 'required|string|min:8',
            'role'             => 'required|in:client,admin,vendor',
            'company_name'     => 'nullable|string',
            'company_address'  => 'nullable|string',
            'company_phone'    => 'nullable|string',
            'specialization'   => 'nullable|string',
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'phone'           => $validated['phone'] ?? null,
            'password'        => Hash::make($validated['password']),
            'role'            => $validated['role'],
            'company_name'    => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'company_phone'   => $validated['company_phone'] ?? null,
            'specialization'  => $validated['specialization'] ?? null,
            'is_active'       => true,
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function listUsers(Request $request)
    {
        $query = User::query();
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }
        $users = $query->orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    public function updateUserStatus(Request $request, $id)
    {
        $user      = User::findOrFail($id);
        $validated = $request->validate(['is_active' => 'required|boolean']);
        $user->is_active = $validated['is_active'];
        $user->save();
        return response()->json(['message' => 'User status updated successfully', 'user' => $user]);
    }

    public function updateUser(Request $request, $id)
    {
        $user      = User::findOrFail($id);
        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'email'           => 'sometimes|email|unique:users,email,' . $id,
            'phone'           => 'sometimes|nullable|string',
            'password'        => 'sometimes|nullable|string|min:8',
            'role'            => 'sometimes|in:client,admin,vendor',
            'is_active'       => 'sometimes|boolean',
            'company_name'    => 'sometimes|nullable|string',
            'company_address' => 'sometimes|nullable|string',
            'company_phone'   => 'sometimes|nullable|string',
            'specialization'  => 'sometimes|nullable|string',
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot delete your own account'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    // ─────────────────────────────────────────────────────────────
    //  TICKET MANAGEMENT
    // ─────────────────────────────────────────────────────────────
    public function assignTicket(Request $request, $ticketId)
    {
        $validated = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $ticket    = Ticket::findOrFail($ticketId);

        if ($ticket->status === 'closed') {
            return response()->json(['message' => 'Tiket yang sudah ditutup tidak bisa ditugaskan ulang.'], 422);
        }

        $vendor = User::findOrFail($validated['assigned_to']);
        if (!$vendor->isVendor() && !$vendor->isAdmin()) {
            return response()->json(['message' => 'Can only assign to vendor or admin users'], 400);
        }

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
            'status'      => 'in_progress',
        ]);

        NotificationController::createNotification($validated['assigned_to'], 'ticket_assigned', 'New Ticket Assigned', "You have been assigned to ticket: {$ticket->title}", $ticket->id);
        NotificationController::createNotification($ticket->user_id, 'ticket_assigned', 'Ticket Assigned to Vendor', "Your ticket has been assigned to a vendor: {$ticket->title}", $ticket->id);

        return response()->json(['message' => 'Tiket berhasil ditugaskan dan status otomatis menjadi diproses.', 'ticket' => $ticket->load('assignedTo')]);
    }

    public function updateTicketPriority(Request $request, $ticketId)
    {
        try {
            $validated = $request->validate(['priority' => 'required|in:low,medium,high,urgent']);
            $ticket    = Ticket::findOrFail($ticketId);
            $oldPriority = $ticket->priority;
            $ticket->priority = $validated['priority'];
            $ticket->save();

            if ($ticket->slaTracking && $oldPriority !== $validated['priority']) {
                $sla = $ticket->slaTracking;
                $sla->response_time_sla   = $this->getResponseTimeSla($validated['priority']);
                $sla->resolution_time_sla = $this->getResolutionTimeSla($validated['priority']);

                if ($ticket->first_response_at) {
                    $actual = (int) round($ticket->created_at->diffInMinutes($ticket->first_response_at));
                    $sla->response_sla_met = $actual <= $sla->response_time_sla;
                }
                if ($ticket->resolved_at) {
                    $actual = (int) round($ticket->created_at->diffInMinutes($ticket->resolved_at));
                    $sla->resolution_sla_met = $actual <= $sla->resolution_time_sla;
                }
                $sla->save();
            }

            try {
                NotificationController::createNotification($ticket->user_id, 'priority_updated', 'Ticket Priority Updated', "Priority for your ticket has been set to: {$validated['priority']}", $ticket->id);
            } catch (\Exception $e) {
                Log::warning('Failed to send priority update notification: ' . $e->getMessage());
            }

            return response()->json(['message' => 'Priority updated successfully', 'ticket' => $ticket->fresh(['slaTracking', 'user', 'category', 'assignedTo'])]);

        } catch (\Exception $e) {
            Log::error('Failed to update priority: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update priority', 'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'], 500);
        }
    }

    public function createTicketForUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id'      => 'required|exists:users,id',
                'title'        => 'required|string|max:255',
                'description'  => 'required|string|max:2000',
                'category_id'  => 'required|exists:ticket_categories,id',
                'priority'     => 'required|in:low,medium,high,urgent',
                'event_name'   => 'nullable|string|max:255',
                'venue'        => 'nullable|string|max:255',
                'area'         => 'nullable|string|max:255',
                'admin_notes'  => 'nullable|string|max:1000',
                'assigned_to'  => 'nullable|exists:users,id',
                'attachments'  => 'nullable|array',
                'attachments.*'=> 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            ]);

            $client = User::findOrFail($validated['user_id']);
            if (!$client->isClient()) {
                return response()->json(['message' => 'Ticket can only be created for client users'], 400);
            }

            $ticket = Ticket::create([
                'user_id'          => $validated['user_id'],
                'created_by_admin' => Auth::id(),
                'title'            => $validated['title'],
                'description'      => $validated['description'],
                'admin_notes'      => $validated['admin_notes'] ?? null,
                'category_id'      => $validated['category_id'],
                'priority'         => $validated['priority'],
                'event_name'       => $validated['event_name'] ?? null,
                'venue'            => $validated['venue'] ?? null,
                'area'             => $validated['area'] ?? null,
                'assigned_to'      => $validated['assigned_to'] ?? null,
                'status'           => isset($validated['assigned_to']) ? 'in_progress' : 'new',
                'assigned_at'      => isset($validated['assigned_to']) ? now() : null,
            ]);

            SlaTracking::create([
                'ticket_id'          => $ticket->id,
                'response_time_sla'  => $this->getResponseTimeSla($ticket->priority),
                'resolution_time_sla'=> $this->getResolutionTimeSla($ticket->priority),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        $path = $file->store('ticket-attachments', 'public');
                        TicketAttachment::create([
                            'ticket_id' => $ticket->id,
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'file_type' => $file->getClientMimeType(),
                            'file_size' => $file->getSize(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to save attachment', ['error' => $e->getMessage()]);
                    }
                }
            }

            try {
                NotificationController::createNotification($validated['user_id'], 'ticket_created', 'New Ticket Created', "A support ticket has been created for you: {$validated['title']}", $ticket->id);
                if (isset($validated['assigned_to'])) {
                    NotificationController::createNotification($validated['assigned_to'], 'ticket_assigned', 'New Ticket Assigned', "You have been assigned to ticket: {$validated['title']}", $ticket->id);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send notifications', ['error' => $e->getMessage()]);
            }

            $ticket->load(['attachments', 'category', 'user', 'assignedTo', 'createdByAdmin']);
            return response()->json(['message' => 'Ticket created successfully', 'ticket' => $ticket], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Ticket creation by admin failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create ticket', 'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  VENDOR MANAGEMENT
    // ─────────────────────────────────────────────────────────────
    public function getVendors(Request $request)
    {
        $query = User::query()
            ->where('role', 'vendor')
            ->select(['id','name','email','phone','company_name','company_address','company_phone','specialization','is_active']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%")->orWhere('company_name','like',"%{$s}%"));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $vendors   = $query->orderBy('name')->paginate(12)->withQueryString();
        $vendorIds = $vendors->pluck('id');

        $totalMap    = Ticket::whereIn('assigned_to', $vendorIds)->selectRaw('assigned_to, COUNT(*) as total')->groupBy('assigned_to')->pluck('total','assigned_to');
        $resolvedMap = Ticket::whereIn('assigned_to', $vendorIds)->whereIn('status',['resolved','closed'])->selectRaw('assigned_to, COUNT(*) as total')->groupBy('assigned_to')->pluck('total','assigned_to');
        $pendingMap  = Ticket::whereIn('assigned_to', $vendorIds)->whereNotIn('status',['resolved','closed'])->selectRaw('assigned_to, COUNT(*) as total')->groupBy('assigned_to')->pluck('total','assigned_to');
        $slaMetMap   = DB::table('tickets')->join('sla_trackings','tickets.id','=','sla_trackings.ticket_id')->whereIn('tickets.assigned_to',$vendorIds)->where('sla_trackings.response_sla_met',true)->selectRaw('tickets.assigned_to, COUNT(*) as total')->groupBy('tickets.assigned_to')->pluck('total','assigned_to');
        $slaTotalMap = DB::table('tickets')->join('sla_trackings','tickets.id','=','sla_trackings.ticket_id')->whereIn('tickets.assigned_to',$vendorIds)->whereNotNull('sla_trackings.response_sla_met')->selectRaw('tickets.assigned_to, COUNT(*) as total')->groupBy('tickets.assigned_to')->pluck('total','assigned_to');

        $vendors->each(function ($v) use ($totalMap,$resolvedMap,$pendingMap,$slaMetMap,$slaTotalMap) {
            $slaT = $slaTotalMap[$v->id] ?? 0;
            $slaM = $slaMetMap[$v->id]   ?? 0;
            $v->performance = [
                'total_tickets'      => $totalMap[$v->id]    ?? 0,
                'resolved_tickets'   => $resolvedMap[$v->id] ?? 0,
                'pending_tickets'    => $pendingMap[$v->id]  ?? 0,
                'sla_compliance_rate'=> $slaT > 0 ? round(($slaM/$slaT)*100) : 0,
            ];
        });

        $totalVendors    = User::where('role','vendor')->count();
        $activeVendors   = User::where('role','vendor')->where('is_active',true)->count();
        $inactiveVendors = $totalVendors - $activeVendors;
        $totalTickets    = Ticket::whereIn('assigned_to', User::where('role','vendor')->pluck('id'))->count();

        $vendorsJson = $vendors->map(fn ($v) => [
            'id'=>$v->id,'name'=>$v->name,'email'=>$v->email,'phone'=>$v->phone,
            'company_name'=>$v->company_name,'company_address'=>$v->company_address,
            'company_phone'=>$v->company_phone,'specialization'=>$v->specialization,
            'is_active'=>$v->is_active,'performance'=>$v->performance,
        ])->values();

        return view('admin.vendors.index', compact('vendors','vendorsJson','totalVendors','activeVendors','inactiveVendors','totalTickets'));
    }

    public function getVendor($id)
    {
        $vendor      = User::where('role','vendor')->findOrFail($id);
        $performance = $this->getVendorPerformanceDetailed($id);
        return response()->json(['vendor'=>$vendor,'performance'=>$performance]);
    }

    public function getVendorDetail($id)
    {
        return $this->getVendor($id);
    }

    public function updateVendorInfo(Request $request, $id)
    {
        $vendor    = User::where('role','vendor')->findOrFail($id);
        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'email'           => 'sometimes|email|unique:users,email,'.$id,
            'phone'           => 'sometimes|nullable|string',
            'company_name'    => 'sometimes|nullable|string',
            'company_address' => 'sometimes|nullable|string',
            'company_phone'   => 'sometimes|nullable|string',
            'specialization'  => 'sometimes|nullable|string',
            'is_active'       => 'sometimes|boolean',
        ]);
        $vendor->update($validated);
        return response()->json(['message'=>'Vendor information updated successfully','vendor'=>$vendor]);
    }

    private function getVendorPerformance($vendorId): array
    {
        $total    = Ticket::where('assigned_to',$vendorId)->count();
        $resolved = Ticket::where('assigned_to',$vendorId)->whereIn('status',['resolved','closed'])->count();

        $avgResp = DB::table('tickets')->join('sla_trackings','tickets.id','=','sla_trackings.ticket_id')
            ->where('tickets.assigned_to',$vendorId)->whereNotNull('sla_trackings.actual_response_time')->avg('sla_trackings.actual_response_time');

        $sla = DB::table('tickets')->join('sla_trackings','tickets.id','=','sla_trackings.ticket_id')
            ->where('tickets.assigned_to',$vendorId)->whereNotNull('sla_trackings.response_sla_met')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN response_sla_met = true THEN 1 ELSE 0 END) as met')->first();

        $slaRate = $sla && $sla->total > 0 ? round(($sla->met/$sla->total)*100,2) : 0;

        return [
            'total_tickets'       => $total,
            'resolved_tickets'    => $resolved,
            'pending_tickets'     => $total - $resolved,
            'avg_response_time'   => round($avgResp ?? 0, 2),
            'sla_compliance_rate' => $slaRate,
            'resolution_rate'     => $total > 0 ? round(($resolved/$total)*100,2) : 0,
        ];
    }

    private function getVendorPerformanceDetailed($vendorId): array
    {
        $basic = $this->getVendorPerformance($vendorId);

        $ticketsByStatus   = Ticket::where('assigned_to',$vendorId)->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count','status');
        $ticketsByPriority = Ticket::where('assigned_to',$vendorId)->selectRaw('priority, COUNT(*) as count')->groupBy('priority')->pluck('count','priority');

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $monthlyTrend[] = ['month' => $start->format('M Y'), 'resolved' => Ticket::where('assigned_to',$vendorId)->whereIn('status',['resolved','closed'])->whereBetween('resolved_at',[$start,$end])->count()];
        }

        $recentTickets = Ticket::where('assigned_to',$vendorId)->with(['user','category'])->orderBy('created_at','desc')->take(5)->get();

        return array_merge($basic, compact('ticketsByStatus','ticketsByPriority','monthlyTrend','recentTickets'));
    }

    // ─────────────────────────────────────────────────────────────
    //  REPORTS
    // ─────────────────────────────────────────────────────────────
    public function getSystemReports(Request $request)
    {
        $periodType = $request->input('period_type','monthly');
        [$startDate, $endDate] = $this->normalizeDateRange(
            $request->input('start_date'),
            $request->input('end_date')
        );

        $totalTickets    = Ticket::whereBetween('created_at',[$startDate,$endDate])->count();
        $resolvedTickets = Ticket::whereBetween('created_at',[$startDate,$endDate])->whereIn('status',['resolved','closed'])->count();
        $resolutionRate  = $totalTickets > 0 ? round(($resolvedTickets/$totalTickets)*100,1) : 0;
        $avgSatisfaction = Feedback::whereBetween('created_at',[$startDate,$endDate])->avg('rating') ?? 0;
        $lowRatingTotal  = Feedback::whereBetween('created_at',[$startDate,$endDate])->where('rating','<=',2)->count();

        $vendorSatisfaction = User::where('role','vendor')->select('id','name','company_name')->get()
            ->map(function ($v) use ($startDate,$endDate) {
                $fb = Feedback::whereHas('ticket', fn($q)=>$q->where('assigned_to',$v->id))->whereBetween('feedbacks.created_at',[$startDate,$endDate])->get();
                return ['id'=>$v->id,'name'=>$v->name,'company_name'=>$v->company_name,'average_rating'=>round($fb->avg('rating')??0,2),'total_feedbacks'=>$fb->count(),'low_rating_count'=>$fb->where('rating','<=',2)->count()];
            })->filter(fn($v)=>$v['total_feedbacks']>0)->sortByDesc('average_rating')->values()->all();

        $resolutionTrend = [];
        $cursor = $periodType === 'weekly' ? $startDate->copy()->startOfWeek() : $startDate->copy()->startOfMonth();
        while ($cursor->lte($endDate)) {
            $periodEnd = $periodType === 'weekly' ? $cursor->copy()->endOfWeek() : $cursor->copy()->endOfMonth();
            $avg = DB::table('tickets')->join('sla_trackings','tickets.id','=','sla_trackings.ticket_id')
                ->whereBetween('tickets.resolved_at',[$cursor,$periodEnd])->whereNotNull('sla_trackings.actual_resolution_time')
                ->avg('sla_trackings.actual_resolution_time');
            if ($avg !== null) {
                $mins = abs(round($avg));
                $resolutionTrend[] = [
                    'period' => $periodType === 'weekly' ? $cursor->format('Y-\WW') : $cursor->format('Y-m'),
                    'avg_resolution_time' => $mins,
                ];
            }
            $periodType === 'weekly' ? $cursor->addWeek() : $cursor->addMonth();
        }

        if (!empty($resolutionTrend)) {
            $sorted = collect($resolutionTrend)->sortBy('avg_resolution_time')->values();
            $categoryMap = [];

            if ($sorted->count() === 1) {
                $categoryMap[$sorted[0]['period']] = 'fastest';
            } else {
                $fastestPeriod = $sorted->first()['period'] ?? null;
                $slowestPeriod = $sorted->last()['period'] ?? null;

                foreach ($sorted as $item) {
                    $categoryMap[$item['period']] = 'average';
                }

                if ($fastestPeriod !== null) {
                    $categoryMap[$fastestPeriod] = 'fastest';
                }
                if ($slowestPeriod !== null && $slowestPeriod !== $fastestPeriod) {
                    $categoryMap[$slowestPeriod] = 'slowest';
                }
            }

            $resolutionTrend = collect($resolutionTrend)
                ->map(function ($item) use ($categoryMap) {
                    $item['category'] = $categoryMap[$item['period']] ?? 'average';
                    return $item;
                })
                ->values()
                ->all();
        }

        $warnSummary = ['total_warnings'=>0,'system_warnings'=>0,'admin_warnings'=>0];
        if (Schema::hasTable('vendor_warnings')) {
            $warnSummary['system_warnings'] = VendorWarning::where('warning_type','system')->count();
            $warnSummary['admin_warnings']  = VendorWarning::where('warning_type','admin')->count();
            $warnSummary['total_warnings']  = $warnSummary['system_warnings'] + $warnSummary['admin_warnings'];
        }

        $reportData = [
            'summary'             => compact('totalTickets','resolvedTickets','resolutionRate') + ['average_satisfaction'=>round($avgSatisfaction,2),'low_rating_total'=>$lowRatingTotal],
            'vendor_satisfaction' => $vendorSatisfaction,
            'resolution_trend'    => $resolutionTrend,
            'warning_summary'     => $warnSummary,
        ];

        return view('admin.reports.index', compact('reportData'));
    }

    // ─────────────────────────────────────────────────────────────
    //  CLIENTS
    // ─────────────────────────────────────────────────────────────
    public function getClients(Request $request)
    {
        try {
            $query = User::where('role','client')->where('is_active',true);
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn($q)=>$q->where('name','like',"%{$s}%")->orWhere('email','like',"%{$s}%")->orWhere('company_name','like',"%{$s}%"));
            }
            return response()->json($query->select('id','name','email','company_name','phone')->orderBy('name')->get());
        } catch (\Exception $e) {
            Log::error('Get Clients Error: '.$e->getMessage());
            return response()->json(['message'=>'Failed to fetch clients','error'=>config('app.debug')?$e->getMessage():'Internal server error'],500);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  SLA HELPERS
    // ─────────────────────────────────────────────────────────────
    private function getResponseTimeSla(string $priority): int
    {
        return match($priority) { 'urgent'=>15, 'high'=>30, 'medium'=>60, 'low'=>120, default=>60 };
    }

    private function normalizeDateRange(?string $startInput, ?string $endInput): array
    {
        $defaultStart = now()->subMonths(6)->startOfDay();
        $defaultEnd = now()->endOfDay();

        try {
            if ($startInput) {
                $startDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startInput)
                    ? Carbon::createFromFormat('Y-m-d', $startInput)->startOfDay()
                    : Carbon::parse($startInput)->startOfDay();
            } else {
                $startDate = $defaultStart->copy();
            }
        } catch (\Throwable $e) {
            $startDate = $defaultStart->copy();
        }

        try {
            if ($endInput) {
                $endDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $endInput)
                    ? Carbon::createFromFormat('Y-m-d', $endInput)->endOfDay()
                    : Carbon::parse($endInput)->endOfDay();
            } else {
                $endDate = $defaultEnd->copy();
            }
        } catch (\Throwable $e) {
            $endDate = $defaultEnd->copy();
        }

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
    }

    private function getResolutionTimeSla(string $priority): int
    {
        return match($priority) { 'urgent'=>240, 'high'=>480, 'medium'=>1440, 'low'=>2880, default=>1440 };
    }
}


