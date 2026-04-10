<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Feedback;
use App\Models\SlaTracking;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        try {
            // ─── Stats ───
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

            // ─── SLA Performance ───
            $slaTotal = SlaTracking::whereHas('ticket', fn($q) =>
                $q->whereNotNull('priority')->whereNotNull('resolved_at')
            )->count();

            $slaMet = SlaTracking::whereHas('ticket', fn($q) =>
                $q->whereNotNull('priority')->whereNotNull('resolved_at')
            )->where('resolution_sla_met', true)->count();

            $slaPerformance = [
                'total'      => $slaTotal,
                'met'        => $slaMet,
                'missed'     => $slaTotal - $slaMet,
                'percentage' => $slaTotal > 0 ? round(($slaMet / $slaTotal) * 100) : 0,
            ];

            // ─── Rating Data ───
            $ratings     = Feedback::all();
            $total       = $ratings->count();
            $average     = $total ? round($ratings->avg('rating'), 1) : 0.0;
            $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
            foreach ($ratings as $r) {
                $val = (int) round($r->rating);
                if (isset($distribution[$val])) $distribution[$val]++;
            }
            $ratingData = [
                'average'      => number_format($average, 1),
                'total'        => $total,
                'distribution' => $distribution,
            ];

            // ─── Recent Tickets ───
            $recentTickets = Ticket::with(['user', 'category', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(9)
                ->get();

            return view('admin.dashboard', compact(
                'stats',
                'slaPerformance',
                'ratingData',
                'recentTickets'
            ));

        } catch (\Exception $e) {
            Log::error('Admin dashboard blade error: ' . $e->getMessage());

            // Return view with empty data rather than crashing
            return view('admin.dashboard', [
                'stats'          => [],
                'slaPerformance' => ['total' => 0, 'met' => 0, 'missed' => 0, 'percentage' => 0],
                'ratingData'     => ['average' => '0.0', 'total' => 0, 'distribution' => [5=>0,4=>0,3=>0,2=>0,1=>0]],
                'recentTickets'  => collect(),
            ])->with('error', 'Gagal memuat data dashboard: ' . $e->getMessage());
        }
    }
}