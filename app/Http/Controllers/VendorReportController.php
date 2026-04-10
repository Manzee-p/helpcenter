<?php

namespace App\Http\Controllers;

use App\Models\VendorReport;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VendorReportController extends Controller
{
    /**
     * Get vendor reports (Admin can see all, Vendor can see own)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $periodType = $request->get('period_type', 'monthly');

        $query = VendorReport::with('vendor');

        // kalau vendor → cuma lihat milik sendiri
        if ($user->isVendor()) {
            $query->where('vendor_id', $user->id);
        }

        // kalau admin → bisa filter vendor
        if ($user->isAdmin() && $request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // filter period
        if ($request->has('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        $reports = $query->orderBy('period_start', 'desc')
            ->paginate(15);

        // ambil report sekarang (yang paling atas)
        $currentReport = $reports->first();

        // trend dummy (WAJIB ADA biar ga error blade)
        $monthlyPerformance = VendorReport::where('vendor_id', $user->id)
            ->orderBy('period_start')
            ->take(6)
            ->get()
            ->map(function ($r) {
                return [
                    'month' => \Carbon\Carbon::parse($r->period_start)->format('M'),
                    'resolved' => $r->resolved_tickets
                ];
            });

        return view('vendor.reports.index', compact(
            'reports',
            'currentReport',
            'monthlyPerformance',
            'periodType'
        ));
    }

    /**
     * Generate report for vendor
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:users,id',
                'period_type' => 'required|in:weekly,monthly',
                'period_start' => 'required|date',
                'period_end' => 'required|date|after:period_start',
            ]);

            // Check if vendor role
            $vendor = User::findOrFail($validated['vendor_id']);
            if (!$vendor->isVendor() && !$vendor->isAdmin()) {
                return response()->json([
                    'message' => 'User is not a vendor'
                ], 400);
            }

            $periodStart = Carbon::parse($validated['period_start'])->startOfDay();
            $periodEnd = Carbon::parse($validated['period_end'])->endOfDay();

            // Get tickets in period
            $tickets = Ticket::where('assigned_to', $validated['vendor_id'])
                ->whereBetween('assigned_at', [$periodStart, $periodEnd])
                ->with(['category', 'slaTracking'])
                ->get();

            $totalTickets = $tickets->count();
            $resolvedTickets = $tickets->whereIn('status', ['resolved', 'closed'])->count();
            $pendingTickets = $tickets->whereNotIn('status', ['resolved', 'closed'])->count();

            // Calculate average response time
            $ticketsWithResponse = $tickets->filter(fn($t) => $t->slaTracking && $t->slaTracking->actual_response_time);
            $avgResponseTime = $ticketsWithResponse->count() > 0 
                ? round($ticketsWithResponse->avg(fn($t) => $t->slaTracking->actual_response_time), 2)
                : null;

            // Calculate average resolution time
            $ticketsWithResolution = $tickets->filter(fn($t) => $t->slaTracking && $t->slaTracking->actual_resolution_time);
            $avgResolutionTime = $ticketsWithResolution->count() > 0
                ? round($ticketsWithResolution->avg(fn($t) => $t->slaTracking->actual_resolution_time), 2)
                : null;

            // SLA compliance - check both response AND resolution SLA
            $ticketsWithSla = $tickets->filter(function($t) {
                return $t->slaTracking && 
                       $t->slaTracking->response_sla_met !== null;
            });

            $slaCompliance = null;
            if ($ticketsWithSla->count() > 0) {
                // Count tickets that met BOTH response and resolution SLA (if resolved)
                $slaMetCount = $ticketsWithSla->filter(function($t) {
                    $responseMet = $t->slaTracking->response_sla_met;
                    
                    // If ticket is resolved, check resolution SLA too
                    if (in_array($t->status, ['resolved', 'closed']) && $t->slaTracking->resolution_sla_met !== null) {
                        return $responseMet && $t->slaTracking->resolution_sla_met;
                    }
                    
                    // Otherwise, just check response SLA
                    return $responseMet;
                })->count();
                
                $slaCompliance = round(($slaMetCount / $ticketsWithSla->count()) * 100, 2);
            }

            // Tickets by category
            $ticketsByCategory = $tickets->groupBy(function($ticket) {
                return $ticket->category ? $ticket->category->name : 'Uncategorized';
            })->map->count()->toArray();

            // Tickets by priority
            $ticketsByPriority = $tickets->groupBy('priority')->map->count()->toArray();

            // Create or update report
            $report = VendorReport::updateOrCreate(
                [
                    'vendor_id' => $validated['vendor_id'],
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                ],
                [
                    'period_type' => $validated['period_type'],
                    'total_tickets' => $totalTickets,
                    'resolved_tickets' => $resolvedTickets,
                    'pending_tickets' => $pendingTickets,
                    'avg_response_time' => $avgResponseTime,
                    'avg_resolution_time' => $avgResolutionTime,
                    'sla_compliance_rate' => $slaCompliance,
                    'tickets_by_category' => $ticketsByCategory,
                    'tickets_by_priority' => $ticketsByPriority,
                ]
            );

            Log::info('Report generated successfully for vendor: ' . $validated['vendor_id']);

            return response()->json([
                'message' => 'Report generated successfully',
                'report' => $report->load('vendor')
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate report',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get current period report for vendor
     */
    public function current(Request $request)
    {
        try {
            $periodType = $request->period_type ?? 'monthly';
            $user = Auth::user();
            $vendorId = $user->isVendor() ? $user->id : $request->vendor_id;

            if (!$vendorId) {
                return response()->json(['message' => 'Vendor ID required'], 400);
            }

            // Calculate period dates
            if ($periodType === 'weekly') {
                $periodStart = Carbon::now()->startOfWeek();
                $periodEnd = Carbon::now()->endOfWeek();
            } else {
                $periodStart = Carbon::now()->startOfMonth();
                $periodEnd = Carbon::now()->endOfMonth();
            }

            // Try to find existing report
            $report = VendorReport::where('vendor_id', $vendorId)
                ->where('period_start', $periodStart->toDateString())
                ->where('period_end', $periodEnd->toDateString())
                ->where('period_type', $periodType)
                ->first();

            // If no report exists, generate one
            if (!$report) {
                Log::info('No current report found, generating new one for vendor: ' . $vendorId);
                
                // Create a new request for generate method
                $generateRequest = new Request([
                    'vendor_id' => $vendorId,
                    'period_type' => $periodType,
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ]);
                
                $response = $this->generate($generateRequest);
                $responseData = json_decode($response->getContent(), true);
                
                // Return the generated report
                if ($response->getStatusCode() === 201) {
                    return response()->json($responseData['report']);
                } else {
                    return $response;
                }
            }

            return response()->json($report->load('vendor'));
        } catch (\Exception $e) {
            Log::error('Error fetching current report: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch current report',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show specific report
     */
    public function show($id)
    {
        try {
            $report = VendorReport::with('vendor')->findOrFail($id);

            $user = Auth::user();
            
            // Check access
            if ($user->isVendor() && $report->vendor_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($report);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching report: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch report',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete report
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->isAdmin()) {
                return response()->json(['message' => 'Only admins can delete reports'], 403);
            }

            $report = VendorReport::findOrFail($id);
            $report->delete();

            Log::info('Report deleted by admin: ' . $id);

            return response()->json([
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting report: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete report',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}