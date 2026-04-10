<?php

namespace App\Http\Controllers;

use App\Models\StatusBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StatusPageController extends Controller
{
    /**
     * Public Status Board - return Blade view
     */
    public function index(Request $request)
    {
        try {
            $query = StatusBoard::with(['creator:id,name', 'updates'])
                ->where('is_public', true);

            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->whereIn('status', ['investigating', 'identified', 'monitoring']);
                } elseif ($request->status === 'resolved') {
                    $query->where('status', 'resolved');
                }
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('affected_area', 'like', "%{$search}%");
                });
            }

            $statuses = $query->orderBy('is_pinned', 'desc')
                              ->orderBy('started_at', 'desc')
                              ->paginate(15)
                              ->withQueryString();

            $pinnedStatuses  = $statuses->filter(fn($s) => $s->is_pinned);
            $regularStatuses = $statuses->filter(fn($s) => !$s->is_pinned);

            $activeIncidents = $statuses->filter(fn($s) =>
                in_array($s->status, ['investigating', 'identified', 'monitoring'])
            );

            // Overall system status
            $criticalCount = $activeIncidents->filter(fn($s) => $s->severity === 'critical')->count();
            $highCount     = $activeIncidents->filter(fn($s) => $s->severity === 'high')->count();

            if ($criticalCount > 0) {
                $overallStatus = ['class' => 'status-critical', 'icon' => 'bx-error-circle', 'text' => 'Gangguan Kritis'];
            } elseif ($highCount > 0) {
                $overallStatus = ['class' => 'status-warning', 'icon' => 'bx-error', 'text' => 'Gangguan Tinggi'];
            } elseif ($activeIncidents->count() > 0) {
                $overallStatus = ['class' => 'status-info', 'icon' => 'bx-info-circle', 'text' => 'Gangguan Minor'];
            } else {
                $overallStatus = ['class' => 'status-success', 'icon' => 'bx-check-circle', 'text' => 'Semua Sistem Normal'];
            }

            return view('status.index', compact(
                'statuses',
                'pinnedStatuses',
                'regularStatuses',
                'activeIncidents',
                'overallStatus'
            ));

        } catch (\Exception $e) {
            Log::error('Status page error: ' . $e->getMessage());
            abort(500);
        }
    }

    /**
     * Public Status Detail - return Blade view
     */
    public function show($id)
    {
        try {
            $status = StatusBoard::with([
                'creator:id,name',
                'updates.user:id,name'
            ])->findOrFail($id);

            // Check public access
            if (!Auth::check() || (Auth::check() && Auth::user()->role !== 'admin')) {
                if (!$status->is_public) {
                    abort(403, 'Status ini tidak tersedia untuk publik');
                }
            }

            $updates = $status->updates->sortByDesc('created_at');

            return view('status.show', compact('status', 'updates'));

        } catch (\Exception $e) {
            Log::error('Status detail page error: ' . $e->getMessage());
            if ($e->getCode() == 403) abort(403);
            abort(404);
        }
    }
}