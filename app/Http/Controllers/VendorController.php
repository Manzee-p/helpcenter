<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class VendorController extends Controller
{
    public function myTickets(Request $request)
    {
        try {
            Log::info('Fetching vendor tickets for user: ' . Auth::id());
            
            $query = Ticket::with(['user', 'category', 'slaTracking'])
                ->where('assigned_to', Auth::id())
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
                Log::info('Filtering by status: ' . $request->status);
            }

            // Filter by priority - FIXED: removed typo '-'
            if ($request->has('priority') && $request->priority !== '') {
                $query->where('priority', $request->priority);
                Log::info('Filtering by priority: ' . $request->priority);
            }

            // Search
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                      ->orWhere('ticket_number', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%");
                });
                Log::info('Searching for: ' . $search);
            }

            // Sorting
            if ($request->has('sort')) {
                switch ($request->sort) {
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'priority':
                        $query->orderByRaw("
                            CASE priority
                                WHEN 'critical' THEN 1
                                WHEN 'high' THEN 2
                                WHEN 'medium' THEN 3
                                WHEN 'low' THEN 4
                            END
                        ");
                        break;
                    case 'sla':
                        $query->leftJoin('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                              ->orderBy('sla_trackings.response_deadline', 'asc')
                              ->select('tickets.*');
                        break;
                    default: // newest
                        $query->orderBy('created_at', 'desc');
                }
            }

            // Check if pagination is requested
            if ($request->has('per_page')) {
                $perPage = $request->per_page ?? 10;
                $tickets = $query->paginate($perPage);
                
                Log::info('Found ' . $tickets->total() . ' tickets for vendor (paginated)');

                return response()->json([
                    'data' => $tickets->items(),
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem()
                ]);
            }

            // Return all tickets without pagination (for frontend filtering)
            $tickets = $query->get();
            
            Log::info('Found ' . $tickets->count() . ' tickets for vendor');

            return response()->json($tickets);
            
        } catch (\Exception $e) {
            Log::error('Error fetching vendor tickets: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to fetch tickets',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show($ticketId)
    {
        try {
            Log::info('Fetching ticket detail for vendor. Ticket ID: ' . $ticketId . ', User ID: ' . Auth::id());
            
            $ticket = Ticket::with(['user', 'category', 'slaTracking', 'attachments'])
                ->where('id', $ticketId)
                ->where('assigned_to', Auth::id())
                ->firstOrFail();
            
            Log::info('Ticket found: ' . $ticket->ticket_number);
            
            return response()->json($ticket);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Ticket not found or not assigned to vendor: ' . $ticketId);
            return response()->json([
                'message' => 'Ticket not found or you are not assigned to this ticket'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching ticket detail: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to fetch ticket details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     */
    public function getTicketComments($ticketId)
    {
        try {
            $ticket = Ticket::where('id', $ticketId)
                ->where('assigned_to', Auth::id())
                ->firstOrFail();

            $vendorId = Auth::id();

            // Menggunakan Comment (bukan TicketComment)
            $comments = Comment::with(['user'])
                ->where('ticket_id', $ticketId)
                ->where(function($query) use ($vendorId) {
                    $query
                        ->where(function($q) {
                            $q->whereHas('user', function($userQuery) {
                                $userQuery->where('role', 'client');
                            })
                            ->where('is_internal', false);
                        })
                        ->orWhere(function($q) use ($vendorId) {
                            $q->where('user_id', $vendorId);
                        });
                })
                ->orderBy('created_at', 'asc')
                ->get();

            Log::info('Fetched ' . $comments->count() . ' comments for vendor on ticket ' . $ticketId);

            return response()->json($comments);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Ticket not found or access denied'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching comments for vendor: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch comments',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function updateTicketStatus(Request $request, $ticketId)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:in_progress,waiting_response,resolved,closed',
            ]);

            $ticket = Ticket::findOrFail($ticketId);

            // Check if vendor is assigned to this ticket
            if ($ticket->assigned_to !== Auth::id()) {
                return response()->json([
                    'message' => 'You are not assigned to this ticket'
                ], 403);
            }

            $oldStatus = $ticket->status;
            $newStatus = $validated['status'];

            // Update status
            $ticket->status = $newStatus;

            // Set first_response_at if transitioning from 'new'
            if ($oldStatus === 'new' && !$ticket->first_response_at) {
                $ticket->first_response_at = now();
            }

            // Set resolved_at if status is 'resolved'
            if ($newStatus === 'resolved' && !$ticket->resolved_at) {
                $ticket->resolved_at = now();
            }

            // Set closed_at if status is 'closed'
            if ($newStatus === 'closed' && !$ticket->closed_at) {
                $ticket->closed_at = now();
            }

            $ticket->save();

            // Update SLA tracking
            if ($ticket->slaTracking) {
                $sla = $ticket->slaTracking;
                
                // Update response time
                if ($ticket->first_response_at && !$sla->actual_response_time) {
                    $actualResponseTime = (int) round(
                        $ticket->created_at->diffInMinutes($ticket->first_response_at)
                    );
                    $sla->actual_response_time = $actualResponseTime;
                    $sla->response_sla_met = $actualResponseTime <= $sla->response_time_sla;
                }
                
                // Update resolution time
                if ($ticket->resolved_at && !$sla->actual_resolution_time) {
                    $actualResolutionTime = (int) round(
                        $ticket->created_at->diffInMinutes($ticket->resolved_at)
                    );
                    $sla->actual_resolution_time = $actualResolutionTime;
                    $sla->resolution_sla_met = $actualResolutionTime <= $sla->resolution_time_sla;
                }
                
                $sla->save();
            }

            // Notify client about status change
            $statusText = str_replace('_', ' ', $newStatus);
            NotificationController::createNotification(
                $ticket->user_id,
                'ticket_status_changed',
                'Ticket Status Updated',
                "Your ticket status changed to {$statusText}: {$ticket->title}",
                $ticket->id
            );

            // If resolved, send special notification
            if ($newStatus === 'resolved') {
                NotificationController::createNotification(
                    $ticket->user_id,
                    'ticket_resolved',
                    'Ticket Resolved',
                    "Your ticket has been resolved: {$ticket->title}. Please provide feedback.",
                    $ticket->id
                );
            }

            return response()->json([
                'message' => 'Ticket status updated successfully',
                'ticket' => $ticket->load(['user', 'category', 'slaTracking']),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating ticket status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update ticket status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function performance(Request $request)
    {
        try {
            $vendorId = Auth::id();
            Log::info('Fetching performance for vendor: ' . $vendorId);
            
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $totalAssigned = Ticket::where('assigned_to', $vendorId)->count();

            $resolvedThisMonth = Ticket::where('assigned_to', $vendorId)
                ->where('status', 'resolved')
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

            $slaCompliance = $totalWithSla > 0 
                ? round(($slaMet / $totalWithSla) * 100, 2) 
                : 0;

            $ticketsByStatus = Ticket::where('assigned_to', $vendorId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status');

            $monthlyPerformance = [];
            for ($i = 5; $i >= 0; $i--) {
                $start = Carbon::now()->subMonths($i)->startOfMonth();
                $end = Carbon::now()->subMonths($i)->endOfMonth();
                
                $resolved = Ticket::where('assigned_to', $vendorId)
                    ->where('status', 'resolved')
                    ->whereBetween('resolved_at', [$start, $end])
                    ->count();
                
                $monthlyPerformance[] = [
                    'month' => $start->format('M Y'),
                    'resolved' => $resolved,
                ];
            }

            $performance = [
                'total_assigned' => $totalAssigned,
                'resolved_this_month' => $resolvedThisMonth,
                'avg_response_time' => round($avgResponseTime ?? 0, 2),
                'avg_resolution_time' => round($avgResolutionTime ?? 0, 2),
                'sla_compliance' => $slaCompliance,
                'tickets_by_status' => $ticketsByStatus,
                'monthly_performance' => $monthlyPerformance,
            ];

            return response()->json($performance);
            
        } catch (\Exception $e) {
            Log::error('Error fetching vendor performance: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch performance data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'total_assigned' => 0,
                'resolved_this_month' => 0,
                'avg_response_time' => 0,
                'avg_resolution_time' => 0,
                'sla_compliance' => 0,
                'tickets_by_status' => [],
                'monthly_performance' => [],
            ], 500);
        }
    }

    public function history(Request $request)
    {
        try {
            Log::info('Fetching vendor history for user: ' . Auth::id());

            $query = Ticket::with(['user', 'category', 'slaTracking'])
                ->where('assigned_to', Auth::id())
                ->whereIn('status', ['resolved', 'closed'])
                ->orderBy('resolved_at', 'desc');

            // Filter by date range
            if ($request->has('start_date') && $request->start_date && 
                $request->has('end_date') && $request->end_date) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
                
                $query->whereBetween('resolved_at', [$startDate, $endDate]);
            }

            // Filter by priority
            if ($request->has('priority') && $request->priority !== '') {
                $query->where('priority', $request->priority);
            }

            $perPage = $request->per_page ?? 15;
            $tickets = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching vendor history: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch history',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
                'data' => []
            ], 500);
        }
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

            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();

            $resolvedThisWeek = Ticket::where('assigned_to', $vendorId)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereBetween('resolved_at', [$weekStart, $weekEnd])
                ->count();

            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $totalWithSla = DB::table('tickets')
                ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                ->where('tickets.assigned_to', $vendorId)
                ->whereBetween('tickets.assigned_at', [$monthStart, $monthEnd])
                ->whereNotNull('sla_trackings.response_sla_met')
                ->count();

            $slaMet = DB::table('tickets')
                ->join('sla_trackings', 'tickets.id', '=', 'sla_trackings.ticket_id')
                ->where('tickets.assigned_to', $vendorId)
                ->whereBetween('tickets.assigned_at', [$monthStart, $monthEnd])
                ->where('sla_trackings.response_sla_met', true)
                ->count();

            $slaCompliance = $totalWithSla > 0 
                ? round(($slaMet / $totalWithSla) * 100, 2) 
                : 0;

            $recentTickets = Ticket::with(['user', 'category'])
                ->where('assigned_to', $vendorId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $urgentTickets = Ticket::with(['user', 'category'])
                ->where('assigned_to', $vendorId)
                ->whereIn('priority', ['high', 'critical'])
                ->whereNotIn('status', ['resolved', 'closed'])
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get();

            return response()->json([
                'stats' => [
                    'active_tickets' => $activeTickets,
                    'new_tickets' => $newTickets,
                    'in_progress' => $inProgress,
                    'resolved_this_week' => $resolvedThisWeek,
                    'sla_compliance' => $slaCompliance,
                ],
                'recent_tickets' => $recentTickets,
                'urgent_tickets' => $urgentTickets,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching vendor dashboard: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch dashboard data'
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $vendor = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string',
            'company_name' => 'sometimes|nullable|string',
            'company_address' => 'sometimes|nullable|string',
            'company_phone' => 'sometimes|nullable|string',
            'specialization' => 'sometimes|nullable|string',
        ]);

        $vendor->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $vendor
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
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    public function ticketStats(Request $request)
    {
        try {
            $vendorId = Auth::id();
            $period = $request->period ?? 'monthly';

            $data = [];

            if ($period === 'weekly') {
                for ($i = 7; $i >= 0; $i--) {
                    $start = Carbon::now()->subWeeks($i)->startOfWeek();
                    $end = Carbon::now()->subWeeks($i)->endOfWeek();
                    
                    $count = Ticket::where('assigned_to', $vendorId)
                        ->whereBetween('assigned_at', [$start, $end])
                        ->count();
                    
                    $resolved = Ticket::where('assigned_to', $vendorId)
                        ->whereIn('status', ['resolved', 'closed'])
                        ->whereBetween('resolved_at', [$start, $end])
                        ->count();
                    
                    $data[] = [
                        'period' => 'Week ' . $start->format('W'),
                        'date' => $start->format('M d'),
                        'total' => $count,
                        'resolved' => $resolved
                    ];
                }
            } else {
                for ($i = 5; $i >= 0; $i--) {
                    $start = Carbon::now()->subMonths($i)->startOfMonth();
                    $end = Carbon::now()->subMonths($i)->endOfMonth();
                    
                    $count = Ticket::where('assigned_to', $vendorId)
                        ->whereBetween('assigned_at', [$start, $end])
                        ->count();
                    
                    $resolved = Ticket::where('assigned_to', $vendorId)
                        ->whereIn('status', ['resolved', 'closed'])
                        ->whereBetween('resolved_at', [$start, $end])
                        ->count();
                    
                    $data[] = [
                        'period' => $start->format('M Y'),
                        'total' => $count,
                        'resolved' => $resolved
                    ];
                }
            }

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error fetching ticket stats: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch ticket stats',
                'data' => []
            ], 500);
        }
    }
}