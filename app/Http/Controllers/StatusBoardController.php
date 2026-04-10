<?php

namespace App\Http\Controllers;

use App\Models\StatusBoard;
use App\Models\StatusUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StatusBoardController extends Controller
{


    /**
     * Get public status board (NO AUTH REQUIRED)
     */
    public function publicIndex(Request $request)
    {
        try {
            $query = StatusBoard::with(['creator:id,name', 'updates.user:id,name'])
                ->where('is_public', true); // Only public

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
                $query->where(function($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                      ->orWhere('affected_area', 'ILIKE', "%{$search}%");
                });
            }

            $statuses = $query->orderBy('is_pinned', 'desc')
                             ->orderBy('started_at', 'desc')
                             ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $statuses->items(),
                'pagination' => [
                    'current_page' => $statuses->currentPage(),
                    'last_page' => $statuses->lastPage(),
                    'per_page' => $statuses->perPage(),
                    'total' => $statuses->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Public status board error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat status board'
            ], 500);
        }
    }

    /**
     * Show detail (PUBLIC/ADMIN)
     */
    public function show($id)
    {
        try {
            $statusBoard = StatusBoard::with([
                'creator:id,name',
                'assignedTo:id,name',
                'updates.user:id,name'
            ])->findOrFail($id);

            // Check if user can view
            if (!Auth::check() || (Auth::check() && Auth::user()->role !== 'admin')) {
                if (!$statusBoard->is_public) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $statusBoard
            ]);

        } catch (\Exception $e) {
            Log::error('Show status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail status'
            ], 500);
        }
    }

    /**
     * Get all (ADMIN ONLY)
     */
    public function index(Request $request)
    {
        $query = \App\Models\StatusBoard::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('incident_number', 'like', "%{$search}%");
            });
        }

        $statuses = $query->orderBy('started_at', 'desc')->paginate(10);

        // stats
        $total     = \App\Models\StatusBoard::count();
        $active    = \App\Models\StatusBoard::whereNotIn('status', ['resolved'])->count();
        $resolved  = \App\Models\StatusBoard::where('status', 'resolved')->count();
        $critical  = \App\Models\StatusBoard::where('severity', 'critical')->count();

        return view('admin.status-board.index', compact(
            'statuses',
            'total',
            'active',
            'resolved',
            'critical'
        ));
    }

    public function create()
    {
        return view('admin.status-board.create');
    }

    public function detail($id)
    {
        $status = StatusBoard::with([
            'creator:id,name',
            'assignedTo:id,name,email,role',
            'updates.user:id,name',
        ])->findOrFail($id);

        return view('admin.status-board.show', compact('status'));
    }

    public function apiIndex(Request $request)
    {
        $query = \App\Models\StatusBoard::with(['creator:id,name', 'assignedTo:id,name', 'updates']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('incident_number', 'like', "%{$search}%");
            });
        }

        $statuses = $query->orderBy('started_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $statuses->items(),
            'pagination' => [
                'current_page' => $statuses->currentPage(),
                'last_page' => $statuses->lastPage(),
                'per_page' => $statuses->perPage(),
                'total' => $statuses->total(),
            ]
        ]);
    }

    /**
     * ✅ STATISTICS - MUST BE SEPARATE METHOD
     */
    public function statistics()
    {
        try {
            Log::info('📊 Statistics endpoint called');

            $stats = [
                'total_incidents' => StatusBoard::count(),
                'active_incidents' => StatusBoard::whereIn('status', ['investigating', 'identified', 'monitoring'])->count(),
                'resolved_incidents' => StatusBoard::where('status', 'resolved')->count(),
                'critical_incidents' => StatusBoard::where('severity', 'critical')
                    ->whereIn('status', ['investigating', 'identified', 'monitoring'])
                    ->count(),
            ];

            Log::info('✅ Statistics calculated', $stats);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Statistics error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat statistik',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Create (ADMIN)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required',
            'affected_area' => 'nullable|string',
            'severity' => 'required',
            'started_at' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
            'is_public' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
        ]);

        $incidentNumber = $this->generateIncidentNumber();

        $statusBoard = StatusBoard::create([
            'incident_number' => $incidentNumber,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'affected_area' => $validated['affected_area'] ?? null,
            'severity' => $validated['severity'],
            'started_at' => $validated['started_at'],
            'created_by' => Auth::id(),
            'assigned_to' => $validated['assigned_to'] ?? null,
            'is_public' => $request->boolean('is_public'),
            'is_pinned' => $request->boolean('is_pinned'),
            'status' => 'investigating',
        ]);

        StatusUpdate::create([
            'status_board_id' => $statusBoard->id,
            'user_id' => Auth::id(),
            'message' => 'Status incident dibuat',
            'update_type' => 'investigating',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil dibuat',
                'data' => $statusBoard->load(['creator:id,name', 'assignedTo:id,name']),
            ], 201);
        }

        return redirect()
            ->route('admin.status-board.index')
            ->with('success', 'Status berhasil dibuat');
    }

    /**
     * Update (ADMIN)
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'category' => 'sometimes|in:power_outage,technical_issue,facility_issue,network_issue,other',
                'affected_area' => 'nullable|string|max:255',
                'severity' => 'sometimes|in:critical,high,medium,low',
                'status' => 'sometimes|in:investigating,identified,monitoring,resolved',
                'assigned_to' => 'nullable|exists:users,id',
                'is_public' => 'boolean',
                'is_pinned' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $statusBoard = StatusBoard::findOrFail($id);
            $validated = $validator->validated();

            if (isset($validated['status']) && $validated['status'] === 'resolved' && !$statusBoard->resolved_at) {
                $validated['resolved_at'] = now();
            }

            $statusBoard->update($validated);
            $statusBoard->load(['creator', 'assignedTo', 'updates.user']);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diupdate',
                'data' => $statusBoard
            ]);

        } catch (\Exception $e) {
            Log::error('Update status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status'
            ], 500);
        }
    }

    /**
     * Add Update (ADMIN)
     */
    public function addUpdate(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'update_type' => 'required|in:investigating,update,resolved',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $statusBoard = StatusBoard::findOrFail($id);

            $update = StatusUpdate::create([
                'status_board_id' => $statusBoard->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'update_type' => $request->update_type,
            ]);

            if ($request->update_type === 'resolved') {
                $statusBoard->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                ]);
            }

            $update->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Update berhasil ditambahkan',
                'data' => $update
            ], 201);

        } catch (\Exception $e) {
            Log::error('Add update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan update'
            ], 500);
        }
    }

    /**
     * Delete (ADMIN)
     */
    public function destroy($id)
    {
        try {
            $statusBoard = StatusBoard::findOrFail($id);
            $statusBoard->delete();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus status'
            ], 500);
        }
    }

    /**
     * Generate incident number
     */
    private function generateIncidentNumber()
    {
        $year = now()->year;
        $lastIncident = StatusBoard::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastIncident ? ((int)substr($lastIncident->incident_number, -3)) + 1 : 1;

        return sprintf('INC-%d-%03d', $year, $number);
    }
}
