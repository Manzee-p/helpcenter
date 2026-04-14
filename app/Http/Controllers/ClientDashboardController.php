<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:client']);
    }

    public function index()
    {
        try {
            $userId = Auth::id();

            // ─── Stats ───
            $stats = [
                'total'       => Ticket::where('user_id', $userId)->count(),
                'in_progress' => Ticket::where('user_id', $userId)
                                    ->whereIn('status', ['new', 'in_progress', 'waiting_response'])
                                    ->count(),
                'resolved'    => Ticket::where('user_id', $userId)
                                    ->whereIn('status', ['resolved', 'closed'])
                                    ->count(),
            ];

            // ─── Tiket yang sudah selesai tapi belum ada feedback ───
            $pendingFeedbackItems = Ticket::with(['assignedVendor'])
                ->where('user_id', $userId)
                ->whereIn('status', ['resolved', 'closed'])
                ->whereDoesntHave('feedback')
                ->orderBy('updated_at', 'desc')
                ->get();

            $pendingFeedbackCount = $pendingFeedbackItems->count();

            // ─── Recent Tickets ───
            $recentTickets = Ticket::with(['category', 'assignedVendor', 'feedback'])
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();

            return view('client.dashboard', compact(
                'stats',
                'pendingFeedbackCount',
                'pendingFeedbackItems',
                'recentTickets'
            ));

        } catch (\Exception $e) {
            Log::error('Client dashboard blade error: ' . $e->getMessage());

            return view('client.dashboard', [
                'stats'                => ['total' => 0, 'in_progress' => 0, 'resolved' => 0],
                'pendingFeedbackCount' => 0,
                'pendingFeedbackItems' => collect(),
                'recentTickets'        => collect(),
            ])->with('error', 'Gagal memuat data dashboard: ' . $e->getMessage());
        }
    }
}


