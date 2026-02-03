<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use App\Models\SlaTracking;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            $stats = [
                'total_tickets' => Ticket::count(),
                'new_tickets' => Ticket::where('status', 'new')->count(),
                'in_progress' => Ticket::where('status', 'in_progress')->count(),
                'resolved' => Ticket::whereIn('status', ['resolved', 'closed'])->count(),
                'tickets_without_priority' => Ticket::whereNull('priority')->count(),
                'total_users' => User::count(),
                'vendors' => User::where('role', 'vendor')->count(),
                'clients' => User::where('role', 'client')->count(),
            ];

            $slaTotal = SlaTracking::whereHas('ticket', function($query) {
                $query->whereNotNull('priority')
                      ->whereNotNull('resolved_at');
            })->count();

            $slaMet = SlaTracking::whereHas('ticket', function($query) {
                $query->whereNotNull('priority')
                      ->whereNotNull('resolved_at');
            })->where('resolution_sla_met', true)->count();

            $slaMissed = $slaTotal - $slaMet;
            $slaPercentage = $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100) : 0;

            $slaPerformance = [
                'total' => $slaTotal,
                'met' => $slaMet,
                'missed' => $slaMissed,
                'percentage' => $slaPercentage
            ];

            $recentTickets = Ticket::with(['user', 'category', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'stats' => $stats,
                'sla_performance' => $slaPerformance,
                'recent_tickets' => $recentTickets
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to load dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * NEW: Get first ticket without priority
     * For quick navigation from dashboard
     */
    public function getFirstNoPriorityTicket()
    {
        try {
            $ticket = Ticket::whereNull('priority')
                ->with(['user', 'category', 'assignedTo'])
                ->orderBy('created_at', 'asc') // Oldest first
                ->first();

            if (!$ticket) {
                return response()->json([
                    'message' => 'No tickets without priority found',
                    'ticket' => null
                ], 404);
            }

            return response()->json([
                'message' => 'Ticket found',
                'ticket' => $ticket
            ]);

        } catch (\Exception $e) {
            Log::error('Get first no priority ticket error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch ticket',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get analytics data for charts
     * FIXED FOR POSTGRESQL
     */
    public function getAnalytics(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

            // Convert to Carbon
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            Log::info("Analytics request - From: {$startDate}, To: {$endDate}");

            // Tickets by Status
            $ticketsByStatus = Ticket::selectRaw('status, COUNT(*) as count')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('status')
                ->get();

            // Tickets by Priority
            $ticketsByPriority = Ticket::selectRaw('priority, COUNT(*) as count')
                ->whereNotNull('priority')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('priority')
                ->get();

            // Tickets by Category - Use Query Builder for PostgreSQL compatibility
            $categoryData = DB::table('tickets as t')
                ->join('ticket_categories as tc', 't.category_id', '=', 'tc.id')
                ->selectRaw('tc.name, COUNT(t.id) as count')
                ->whereBetween('t.created_at', [$startDate, $endDate])
                ->whereNull('t.deleted_at')
                ->groupBy('tc.name')
                ->get();

            $ticketsByCategory = [];
            foreach ($categoryData as $item) {
                $ticketsByCategory[$item->name] = (int) $item->count;
            }

            // Monthly Tickets - Use TO_CHAR for PostgreSQL
            $monthlyData = DB::table('tickets')
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count")
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlyTickets = [];
            foreach ($monthlyData as $item) {
                $monthlyTickets[$item->month] = (int) $item->count;
            }

            // Average Resolution Time
            $avgResolutionTime = DB::table('sla_trackings as s')
                ->join('tickets as t', 's.ticket_id', '=', 't.id')
                ->whereBetween('t.created_at', [$startDate, $endDate])
                ->whereNotNull('s.actual_resolution_time')
                ->where('s.actual_resolution_time', '>', 0)
                ->whereNull('t.deleted_at')
                ->avg('s.actual_resolution_time');

            Log::info("Analytics generated successfully", [
                'status_count' => $ticketsByStatus->count(),
                'priority_count' => $ticketsByPriority->count(),
                'category_count' => count($ticketsByCategory),
                'monthly_count' => count($monthlyTickets)
            ]);

            return response()->json([
                'tickets_by_status' => $ticketsByStatus,
                'tickets_by_priority' => $ticketsByPriority,
                'tickets_by_category' => $ticketsByCategory,
                'monthly_tickets' => $monthlyTickets,
                'avg_resolution_time_minutes' => round($avgResolutionTime ?? 0, 2),
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to load analytics data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get system reports with resolution time trend
     * FIXED FOR POSTGRESQL - Complete working version
     */
    public function getSystemReports(Request $request)
    {
        try {
            $periodType = $request->input('period_type', 'monthly');
            $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d')); 

            // Convert to Carbon
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            Log::info("Reports request - Period: {$periodType}, From: {$startDate}, To: {$endDate}");

            // Overall Summary
            $totalTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();
            $resolvedTickets = Ticket::whereIn('status', ['resolved', 'closed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $summary = [
                'total_tickets' => $totalTickets,
                'resolved_tickets' => $resolvedTickets,
                'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 2) : 0,
            ];

            // Top Vendors - FIXED for PostgreSQL
            $topVendorsData = Ticket::selectRaw("
                    assigned_to,
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) as resolved_count
                ")
                ->whereNotNull('assigned_to')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('assigned_to')
                ->havingRaw("SUM(CASE WHEN status IN ('resolved', 'closed') THEN 1 ELSE 0 END) > 0")
                ->orderByDesc('resolved_count')
                ->limit(5)
                ->get();

            // Get vendor details
            $topVendors = [];
            foreach ($topVendorsData as $data) {
                $vendor = User::find($data->assigned_to);
                if ($vendor) {
                    $topVendors[] = [
                        'id' => $vendor->id,
                        'name' => $vendor->name,
                        'company_name' => $vendor->company_name,
                        'resolved_count' => (int) $data->resolved_count
                    ];
                }
            }

            // Resolution Time Trend - PostgreSQL compatible
            $dateFormat = $periodType === 'monthly' ? 'YYYY-MM' : 'IYYY-IW';
            
            $resolutionTrendRaw = DB::table('tickets as t')
                ->join('sla_trackings as s', 't.id', '=', 's.ticket_id')
                ->selectRaw("
                    TO_CHAR(t.created_at, '{$dateFormat}') as period,
                    AVG(s.actual_resolution_time) as avg_resolution_time,
                    COUNT(DISTINCT t.id) as ticket_count
                ")
                ->whereBetween('t.created_at', [$startDate, $endDate])
                ->whereNotNull('s.actual_resolution_time')
                ->where('s.actual_resolution_time', '>', 0)
                ->whereNull('t.deleted_at')
                ->groupByRaw("TO_CHAR(t.created_at, '{$dateFormat}')")
                ->orderByRaw("TO_CHAR(t.created_at, '{$dateFormat}')")
                ->get();

            $resolutionTrend = [];
            foreach ($resolutionTrendRaw as $item) {
                $minutes = abs($item->avg_resolution_time);
                
                // Categorize: Fast < 12h, Average 12-24h, Slow >= 24h
                if ($minutes < 720) {
                    $category = 'fastest';
                } elseif ($minutes < 1440) {
                    $category = 'average';
                } else {
                    $category = 'slowest';
                }
                
                $resolutionTrend[] = [
                    'period' => $item->period,
                    'avg_resolution_time' => round($item->avg_resolution_time, 2),
                    'ticket_count' => (int) $item->ticket_count,
                    'category' => $category
                ];
            }

            Log::info("Reports generated successfully", [
                'summary' => $summary,
                'vendors_count' => count($topVendors),
                'trend_count' => count($resolutionTrend)
            ]);

            return response()->json([
                'summary' => $summary,
                'top_vendors' => $topVendors,
                'resolution_trend' => $resolutionTrend,
                'period_type' => $periodType,
                'date_range' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Reports error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to load reports',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8',
            'role' => 'required|in:client,admin,vendor',
            'company_name' => 'nullable|string',
            'company_address' => 'nullable|string',
            'company_phone' => 'nullable|string',
            'specialization' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'company_phone' => $validated['company_phone'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function assignTicket(Request $request, $ticketId)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ticket = Ticket::findOrFail($ticketId);
        
        $vendor = User::findOrFail($validated['assigned_to']);
        if (!$vendor->isVendor() && !$vendor->isAdmin()) {
            return response()->json([
                'message' => 'Can only assign to vendor or admin users'
            ], 400);
        }

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
            'status' => 'in_progress',
        ]);

        NotificationController::createNotification(
            $validated['assigned_to'],
            'ticket_assigned',
            'New Ticket Assigned',
            "You have been assigned to ticket: {$ticket->title}",
            $ticket->id
        );

        NotificationController::createNotification(
            $ticket->user_id,
            'ticket_assigned',
            'Ticket Assigned to Vendor',
            "Your ticket has been assigned to a vendor: {$ticket->title}",
            $ticket->id
        );

        return response()->json([
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket->load('assignedTo'),
        ]);
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
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);
        
        $user->is_active = $validated['is_active'];
        $user->save();

        return response()->json([
            'message' => 'User status updated successfully',
            'user' => $user
        ]);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string',
            'password' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|in:client,admin,vendor',
            'is_active' => 'sometimes|boolean',
            'company_name' => 'sometimes|nullable|string',
            'company_address' => 'sometimes|nullable|string',
            'company_phone' => 'sometimes|nullable|string',
            'specialization' => 'sometimes|nullable|string',
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function getVendors(Request $request)
    {
        try {
            Log::info('Get Vendors Request', $request->all());

            $query = User::where('role', 'vendor');

            if ($request->filled('is_active')) {
                $isActive = $request->is_active == '1' || $request->is_active === 'true' || $request->is_active === true;
                $query->where('is_active', $isActive);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            $vendors = $query->orderBy('created_at', 'desc')->get();

            $vendors->each(function($vendor) {
                $vendor->performance = $this->getVendorPerformance($vendor->id);
            });

            return response()->json($vendors);
        } catch (\Exception $e) {
            Log::error('Get Vendors Error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch vendors',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function getVendor($id)
    {
        $vendor = User::where('role', 'vendor')->findOrFail($id);
        $performance = $this->getVendorPerformanceDetailed($id);
        
        return response()->json([
            'vendor' => $vendor,
            'performance' => $performance
        ]);
    }

    private function getVendorPerformance($vendorId)
    {
        $totalTickets = Ticket::where('assigned_to', $vendorId)->count();
        $resolvedTickets = Ticket::where('assigned_to', $vendorId)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();
        
        $avgResponseTime = DB::table('tickets')
            ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
            ->where('tickets.assigned_to', $vendorId)
            ->whereNotNull('sla_trackings.actual_response_time')
            ->avg('sla_trackings.actual_response_time');

        $slaCompliance = DB::table('tickets')
            ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
            ->where('tickets.assigned_to', $vendorId)
            ->whereNotNull('sla_trackings.response_sla_met')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN response_sla_met = true THEN 1 ELSE 0 END) as met
            ')
            ->first();

        $slaRate = $slaCompliance && $slaCompliance->total > 0
            ? round(($slaCompliance->met / $slaCompliance->total) * 100, 2)
            : 0;

        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'pending_tickets' => $totalTickets - $resolvedTickets,
            'avg_response_time' => round($avgResponseTime ?? 0, 2),
            'sla_compliance_rate' => $slaRate,
            'resolution_rate' => $totalTickets > 0 
                ? round(($resolvedTickets / $totalTickets) * 100, 2) 
                : 0,
        ];
    }

    private function getVendorPerformanceDetailed($vendorId)
    {
        $basic = $this->getVendorPerformance($vendorId);

        $ticketsByStatus = Ticket::where('assigned_to', $vendorId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $ticketsByPriority = Ticket::where('assigned_to', $vendorId)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();
            
            $resolved = Ticket::where('assigned_to', $vendorId)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereBetween('resolved_at', [$start, $end])
                ->count();
            
            $monthlyTrend[] = [
                'month' => $start->format('M Y'),
                'resolved' => $resolved,
            ];
        }

        $recentTickets = Ticket::where('assigned_to', $vendorId)
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return array_merge($basic, [
            'tickets_by_status' => $ticketsByStatus,
            'tickets_by_priority' => $ticketsByPriority,
            'monthly_trend' => $monthlyTrend,
            'recent_tickets' => $recentTickets,
        ]);
    }

    public function updateVendorInfo(Request $request, $id)
    {
        $vendor = User::where('role', 'vendor')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string',
            'company_name' => 'sometimes|nullable|string',
            'company_address' => 'sometimes|nullable|string',
            'company_phone' => 'sometimes|nullable|string',
            'specialization' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $vendor->update($validated);

        return response()->json([
            'message' => 'Vendor information updated successfully',
            'vendor' => $vendor
        ]);
    }

    public function updateTicketPriority(Request $request, $ticketId)
    {
        try {
            $validated = $request->validate([
                'priority' => 'required|in:low,medium,high,urgent'
            ]);

            $ticket = Ticket::findOrFail($ticketId);
            
            $oldPriority = $ticket->priority;
            $ticket->priority = $validated['priority'];
            $ticket->save();

            if ($ticket->slaTracking && $oldPriority !== $validated['priority']) {
                $sla = $ticket->slaTracking;
                
                $sla->response_time_sla = $this->getResponseTimeSla($validated['priority']);
                $sla->resolution_time_sla = $this->getResolutionTimeSla($validated['priority']);
                
                if ($ticket->first_response_at) {
                    $actualResponseTime = (int) round($ticket->created_at->diffInMinutes($ticket->first_response_at));
                    $sla->response_sla_met = $actualResponseTime <= $sla->response_time_sla;
                }
                
                if ($ticket->resolved_at) {
                    $actualResolutionTime = (int) round($ticket->created_at->diffInMinutes($ticket->resolved_at));
                    $sla->resolution_sla_met = $actualResolutionTime <= $sla->resolution_time_sla;
                }
                
                $sla->save();
            }

            try {
                NotificationController::createNotification(
                    $ticket->user_id,
                    'priority_updated',
                    'Ticket Priority Updated',
                    "Priority for your ticket has been set to: {$validated['priority']}",
                    $ticket->id
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send priority update notification: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Priority updated successfully',
                'ticket' => $ticket->fresh(['slaTracking', 'user', 'category', 'assignedTo'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update priority: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update priority',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    private function getResponseTimeSla($priority)
    {
        return match($priority) {
            'urgent' => 15,
            'high' => 30,
            'medium' => 60,
            'low' => 120,
            default => 60
        };
    }

    private function getResolutionTimeSla($priority)
    {
        return match($priority) {
            'urgent' => 240,
            'high' => 480,
            'medium' => 1440,
            'low' => 2880,
            default => 1440
        };
    }

    /**
     * Create ticket on behalf of user
     */
    public function createTicketForUser(Request $request)
    {
        try {
            Log::info('Admin creating ticket for user', [
                'admin_id' => Auth::id(),
                'data' => $request->except('attachments')
            ]);

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
                'category_id' => 'required|exists:ticket_categories,id',
                'priority' => 'required|in:low,medium,high,urgent',
                'event_name' => 'nullable|string|max:255',
                'venue' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'admin_notes' => 'nullable|string|max:1000',
                'assigned_to' => 'nullable|exists:users,id',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            ]);

            // Verify user is client
            $client = User::findOrFail($validated['user_id']);
            if (!$client->isClient()) {
                return response()->json([
                    'message' => 'Ticket can only be created for client users'
                ], 400);
            }

            // Create ticket
            $ticket = Ticket::create([
                'user_id' => $validated['user_id'],
                'created_by_admin' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'],
                'admin_notes' => $validated['admin_notes'] ?? null,
                'category_id' => $validated['category_id'],
                'priority' => $validated['priority'],
                'event_name' => $validated['event_name'] ?? null,
                'venue' => $validated['venue'] ?? null,
                'area' => $validated['area'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'status' => isset($validated['assigned_to']) ? 'in_progress' : 'new',
                'assigned_at' => isset($validated['assigned_to']) ? now() : null,
            ]);

            Log::info('Ticket created by admin', ['ticket_id' => $ticket->id]);

            // Create SLA Tracking
            SlaTracking::create([
                'ticket_id' => $ticket->id,
                'response_time_sla' => $this->getResponseTimeSla($ticket->priority),
                'resolution_time_sla' => $this->getResolutionTimeSla($ticket->priority),
            ]);

            // Handle attachments
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
                        Log::error('Failed to save attachment', [
                            'error' => $e->getMessage(),
                            'file_name' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }

            // Notify client
            try {
                NotificationController::createNotification(
                    $validated['user_id'],
                    'ticket_created',
                    'New Ticket Created',
                    "A support ticket has been created for you: {$validated['title']}",
                    $ticket->id
                );

                // Notify assigned vendor if exists
                if (isset($validated['assigned_to'])) {
                    NotificationController::createNotification(
                        $validated['assigned_to'],
                        'ticket_assigned',
                        'New Ticket Assigned',
                        "You have been assigned to ticket: {$validated['title']}",
                        $ticket->id
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send notifications', ['error' => $e->getMessage()]);
            }

            // Load relationships
            $ticket->load(['attachments', 'category', 'user', 'assignedTo', 'createdByAdmin']);

            return response()->json([
                'message' => 'Ticket created successfully',
                'ticket' => $ticket,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except('attachments')
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ticket creation by admin failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to create ticket',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all clients for ticket creation
     */
    public function getClients(Request $request)
    {
        try {
            $query = User::where('role', 'client')->where('is_active', true);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            $clients = $query->select('id', 'name', 'email', 'company_name', 'phone')
                            ->orderBy('name', 'asc')
                            ->get();

            return response()->json($clients);
        } catch (\Exception $e) {
            Log::error('Get Clients Error: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch clients',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}