<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class VendorDashboardController extends Controller
{
    public function index()
    {
        $vendor = Auth::user();
        $vendorId = $vendor->id;

        // ── Stats ──────────────────────────────────────────────
        $activeTickets     = Ticket::where('assigned_to', $vendorId)
                                   ->whereIn('status', ['new','in_progress','waiting_response'])
                                   ->count();
        $newTickets        = Ticket::where('assigned_to', $vendorId)->where('status','new')->count();
        $resolvedThisWeek  = Ticket::where('assigned_to', $vendorId)
                                   ->where('status','resolved')
                                   ->whereBetween('resolved_at',[now()->startOfWeek(), now()->endOfWeek()])
                                   ->count();

        // SLA compliance (resolved tickets where resolved_at <= sla deadline — simplified)
        $totalResolved = Ticket::where('assigned_to', $vendorId)
                               ->whereIn('status',['resolved','closed'])->count();
        $slaCompliance = 0;
        if ($totalResolved > 0) {
            $onTime = Ticket::where('assigned_to', $vendorId)
                            ->whereIn('status',['resolved','closed'])
                            ->whereHas('slaTracking', fn($q) => $q->where('resolution_sla_met', true))
                            ->count();
            $slaCompliance = round(($onTime / $totalResolved) * 100);
        }

        $stats = [
            'active_tickets'      => $activeTickets,
            'new_tickets'         => $newTickets,
            'resolved_this_week'  => $resolvedThisWeek,
            'sla_compliance'      => $slaCompliance,
        ];

        // ── Performance ────────────────────────────────────────
        $resolvedThisMonth = Ticket::where('assigned_to', $vendorId)
                                   ->where('status','resolved')
                                   ->whereMonth('resolved_at', now()->month)
                                   ->count();

        $avgResponseTime   = Ticket::where('assigned_to', $vendorId)
                                   ->whereNotNull('first_response_at')
                                   ->whereNotNull('assigned_at')
                                   ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, first_response_at)) as avg_resp')
                                   ->value('avg_resp');

        $avgResolutionTime = Ticket::where('assigned_to', $vendorId)
                                   ->whereNotNull('resolved_at')
                                   ->whereNotNull('assigned_at')
                                   ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, resolved_at)) as avg_res')
                                   ->value('avg_res');

        $ticketsByStatus = Ticket::where('assigned_to', $vendorId)
                                 ->selectRaw('status, COUNT(*) as total')
                                 ->groupBy('status')
                                 ->pluck('total','status');

        // Monthly performance — last 6 months
        $monthlyPerformance = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyPerformance[] = [
                'month'    => $month->format('M Y'),
                'resolved' => Ticket::where('assigned_to', $vendorId)
                                    ->where('status','resolved')
                                    ->whereYear('resolved_at',  $month->year)
                                    ->whereMonth('resolved_at', $month->month)
                                    ->count(),
            ];
        }

        $performance = [
            'resolved_this_month'  => $resolvedThisMonth,
            'avg_response_time'    => round((float)$avgResponseTime),
            'avg_resolution_time'  => round((float)$avgResolutionTime),
            'tickets_by_status'    => $ticketsByStatus,
            'monthly_performance'  => $monthlyPerformance,
        ];

        // ── Recent & Urgent ────────────────────────────────────
        $recentTickets = Ticket::with(['user','category'])
                               ->where('assigned_to', $vendorId)
                               ->latest()
                               ->take(5)
                               ->get();

        $urgentTickets = Ticket::with(['user'])
                               ->where('assigned_to', $vendorId)
                               ->whereIn('priority', ['high','urgent','critical'])
                               ->whereIn('status', ['new','in_progress','waiting_response'])
                               ->latest()
                               ->take(5)
                               ->get();

        // ── Trend (monthly default) ────────────────────────────
        $trendData = $this->buildTrend($vendorId, 'monthly');

        $donutData = [
            'labels' => ['New','In Progress','Waiting Response','Resolved','Closed'],
            'values' => [
                $performance['tickets_by_status']['new'] ?? 0,
                $performance['tickets_by_status']['in_progress'] ?? 0,
                $performance['tickets_by_status']['waiting_response'] ?? 0,
                $performance['tickets_by_status']['resolved'] ?? 0,
                $performance['tickets_by_status']['closed'] ?? 0,
            ],
            'colors' => ['#378ADD','#BA7517','#E24B4A','#639922','#888780'],
        ];

        return view('vendor.dashboard', compact(
            'stats', 'performance', 'recentTickets', 'urgentTickets', 'trendData', 'donutData'
        ));
    }

    // ──────────────────────────────────────────────────────────
    private function buildTrend(int $vendorId, string $period): array
    {
        $items = [];
        if ($period === 'weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $day = now()->subDays($i);
                $items[] = [
                    'period'   => $day->format('D, d M'),
                    'total'    => Ticket::where('assigned_to', $vendorId)
                                       ->whereDate('created_at', $day)->count(),
                    'resolved' => Ticket::where('assigned_to', $vendorId)
                                       ->whereDate('resolved_at', $day)->count(),
                ];
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $items[] = [
                    'period'   => $month->format('M Y'),
                    'total'    => Ticket::where('assigned_to', $vendorId)
                                       ->whereYear('created_at',  $month->year)
                                       ->whereMonth('created_at', $month->month)->count(),
                    'resolved' => Ticket::where('assigned_to', $vendorId)
                                       ->whereYear('resolved_at',  $month->year)
                                       ->whereMonth('resolved_at', $month->month)->count(),
                ];
            }
        }
        return $items;
    }
}