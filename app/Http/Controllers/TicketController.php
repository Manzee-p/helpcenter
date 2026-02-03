<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\SlaTracking;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\NotificationController;


class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'category', 'assignedTo', 'createdByAdmin']);

        $user = $request->user();

        // Filter by role
        if ($user->isClient()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isVendor()) {
            $query->where('assigned_to', $user->id);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Created By Filter
        if ($request->filled('created_by')) {
            if ($request->created_by === 'admin') {
                $query->whereNotNull('created_by_admin');
            } elseif ($request->created_by === 'user') {
                $query->whereNull('created_by_admin');
            }
        }

        // Search Filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('ticket_number', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->input('sort', 'newest');
        
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'priority':
                $query->orderByRaw("
                    CASE priority
                        WHEN 'urgent' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                        ELSE 5
                    END
                ")->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // ✅ GET STATS - Apply same filters as main query
        $statsQuery = clone $query;
        
        $stats = [
            'user_created_count' => (clone $statsQuery)->whereNull('created_by_admin')->count(),
            'admin_created_count' => (clone $statsQuery)->whereNotNull('created_by_admin')->count(),
            'assigned_count' => (clone $statsQuery)->whereNotNull('assigned_to')->count(),
        ];

        // Get paginated tickets
        $tickets = $query->paginate($request->per_page ?? 15);

        // ✅ RETURN WITH PROPER STRUCTURE
        return response()->json([
            'data' => $tickets->items(),
            'current_page' => $tickets->currentPage(),
            'last_page' => $tickets->lastPage(),
            'per_page' => $tickets->perPage(),
            'total' => $tickets->total(),
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating new ticket', [
                'user_id' => $request->user()->id,
                'data' => $request->except('attachments')
            ]);

            // Validation - NO PRIORITY, only urgency_level
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:2000',
                'category_id' => 'required|exists:ticket_categories,id',
                'urgency_level' => 'nullable|in:low,medium,high,critical', // Client's urgency indication
                'event_name' => 'nullable|string|max:255',
                'venue' => 'nullable|string|max:255',
                'area' => 'nullable|string|max:255',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            // Create ticket WITHOUT priority (will be set by admin)
            $ticket = Ticket::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'urgency_level' => $validated['urgency_level'] ?? null, // Save client's urgency indication
                // priority will be auto-set by model boot() method as 'medium' by default
                'event_name' => $validated['event_name'] ?? null,
                'venue' => $validated['venue'] ?? null,
                'area' => $validated['area'] ?? null,
                'status' => 'new',
            ]);

            Log::info('Ticket created', [
                'ticket_id' => $ticket->id, 
                'auto_priority' => $ticket->priority
            ]);

            // Create SLA Tracking based on auto-assigned priority
            SlaTracking::create([
                'ticket_id' => $ticket->id,
                'response_time_sla' => $this->getResponseTimeSla($ticket->priority),
                'resolution_time_sla' => $this->getResolutionTimeSla($ticket->priority),
            ]);

            Log::info('SLA tracking created');

            // Handle attachments
            if ($request->hasFile('attachments')) {
                Log::info('Processing attachments', ['count' => count($request->file('attachments'))]);
                
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

                        Log::info('Attachment saved', ['file_name' => $file->getClientOriginalName()]);
                    } catch (\Exception $e) {
                        Log::error('Failed to save attachment', [
                            'error' => $e->getMessage(),
                            'file_name' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }

            // Notify all admins about new ticket
            try {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    NotificationController::createNotification(
                        $admin->id,
                        'ticket_created',
                        'New Ticket Created',
                        "New ticket (Priority: {$ticket->priority}, Client Urgency: " . ($ticket->urgency_level ?? 'Not specified') . "): {$validated['title']}",
                        $ticket->id
                    );
                }
                Log::info('Admin notifications sent');
            } catch (\Exception $e) {
                Log::warning('Failed to send notifications', ['error' => $e->getMessage()]);
            }

            // Load relationships
            $ticket->load(['attachments', 'category', 'user']);

            Log::info('Ticket creation completed successfully', ['ticket_id' => $ticket->id]);

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
            Log::error('Ticket creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to create ticket',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // Helper methods in TicketController
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

    public function show($id)
    {
        try {
            $ticket = Ticket::with([
                'user',
                'category',
                'assignedTo',
                'attachments',
                'comments' => function($query) {
                    $query->with('user')->orderBy('created_at', 'asc');
                },
                'feedback',
                'slaTracking'
            ])->find($id);

            if (!$ticket) {
                return response()->json([
                    'message' => 'Ticket not found'
                ], 404);
            }

            // Check access
            $user = request()->user();
            
            if ($user->isClient() && $ticket->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            if ($user->isVendor() && $ticket->assigned_to !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($ticket);
            
        } catch (\Throwable $e) {
            Log::error('Ticket Detail Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to load ticket details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $user = $request->user();

            Log::info('Updating ticket', [
                'ticket_id' => $id,
                'user_id' => $user->id,
                'user_role' => $user->role,
                'data' => $request->all()
            ]);

            // Check permissions
            if ($user->isClient()) {
                Log::warning('Client attempted to update ticket', ['user_id' => $user->id]);
                return response()->json(['message' => 'Clients cannot update tickets'], 403);
            }

            // For vendors, check if assigned
            if ($user->isVendor() && $ticket->assigned_to !== $user->id) {
                Log::warning('Vendor not assigned to ticket', [
                    'vendor_id' => $user->id,
                    'ticket_assigned_to' => $ticket->assigned_to
                ]);
                return response()->json(['message' => 'You are not assigned to this ticket'], 403);
            }

            // Validate input
            $validated = $request->validate([
                'status' => 'sometimes|in:new,in_progress,waiting_response,resolved,closed',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'assigned_to' => 'sometimes|nullable|exists:users,id',
            ]);

            // Update SLA tracking for status changes
            if (isset($validated['status'])) {
                // First response time
                if ($validated['status'] === 'in_progress' && !$ticket->first_response_at) {
                    $ticket->first_response_at = now();
                    $this->updateSlaResponseTime($ticket);
                    Log::info('First response time recorded', ['ticket_id' => $id]);
                }

                // Resolution time
                if (in_array($validated['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
                    $ticket->resolved_at = now();
                    $this->updateSlaResolutionTime($ticket);
                    Log::info('Resolution time recorded', ['ticket_id' => $id]);
                }
            }

            // Update assignment time
            if (isset($validated['assigned_to']) && !$ticket->assigned_at) {
                $ticket->assigned_at = now();
                Log::info('Assignment time recorded', ['ticket_id' => $id]);
            }

            // Update ticket
            $ticket->update($validated);

            // Reload relationships
            $ticket->load(['slaTracking', 'user', 'category', 'assignedTo']);

            Log::info('Ticket updated successfully', ['ticket_id' => $id]);

            return response()->json([
                'message' => 'Ticket updated successfully',
                'ticket' => $ticket,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed on update', [
                'ticket_id' => $id,
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Throwable $e) {
            Log::error('Ticket Update Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Failed to update ticket',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = request()->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Only admins can delete tickets'], 403);
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully'
        ]);
    }

    private function updateSlaResponseTime($ticket)
    {
        $sla = $ticket->slaTracking;
        if ($sla) {
            $actualTime = (int) round($ticket->created_at->diffInMinutes($ticket->first_response_at));
            $sla->update([
                'actual_response_time' => $actualTime,
                'response_sla_met' => $actualTime <= $sla->response_time_sla,
            ]);
        }
    }

    private function updateSlaResolutionTime($ticket)
    {
        $sla = $ticket->slaTracking;
        if ($sla) {
            $actualTime = (int) round($ticket->created_at->diffInMinutes($ticket->resolved_at));
            $sla->update([
                'actual_resolution_time' => $actualTime,
                'resolution_sla_met' => $actualTime <= $sla->resolution_time_sla,
            ]);
        }
    }

    public function getComments($ticketId)
    {
        try {
            Log::info('Fetching comments for ticket: ' . $ticketId);
            
            $ticket = Ticket::findOrFail($ticketId);
            $user = Auth::user();
            
            if ($user->role === 'client' && $ticket->user_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have access to this ticket'
                ], 403);
            }
            
            if ($user->role === 'vendor' && $ticket->assigned_to !== $user->id) {
                return response()->json([
                    'message' => 'You are not assigned to this ticket'
                ], 403);
            }
            
            if (!class_exists(Comment::class)) {
                Log::warning('Comment model does not exist, returning empty array');
                return response()->json([]);
            }
            
            $comments = Comment::with('user')
                ->where('ticket_id', $ticketId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            if ($user->role === 'client') {
                $comments = $comments->filter(function($comment) {
                    return !$comment->is_internal;
                })->values();
            }
            
            Log::info('Found ' . $comments->count() . ' comments');
            
            return response()->json($comments);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Ticket not found: ' . $ticketId);
            return response()->json([
                'message' => 'Ticket not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching comments: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function addComment(Request $request, $ticketId)
    {
        try {
            Log::info('Adding comment to ticket: ' . $ticketId);
            
            $validated = $request->validate([
                'comment' => 'required|string|min:1',
                'is_internal' => 'sometimes|boolean'
            ]);
            
            $ticket = Ticket::findOrFail($ticketId);
            $user = Auth::user();
            
            if ($user->role === 'client' && $ticket->user_id !== $user->id) {
                return response()->json([
                    'message' => 'You do not have access to this ticket'
                ], 403);
            }
            
            if ($user->role === 'vendor' && $ticket->assigned_to !== $user->id) {
                return response()->json([
                    'message' => 'You are not assigned to this ticket'
                ], 403);
            }
            
            if (!class_exists(Comment::class)) {
                Log::error('Comment model does not exist');
                return response()->json([
                    'message' => 'Comment feature is not available. Please contact administrator.'
                ], 500);
            }
            
            if ($user->role === 'client') {
                $validated['is_internal'] = false;
            }
            
            $comment = Comment::create([
                'ticket_id' => $ticketId,
                'user_id' => $user->id,
                'comment' => $validated['comment'],
                'is_internal' => $validated['is_internal'] ?? false
            ]);
            
            $comment->load('user');
            
            if (in_array($user->role, ['admin', 'vendor']) && !$ticket->first_response_at) {
                $ticket->first_response_at = now();
                $ticket->save();
                
                if ($ticket->slaTracking) {
                    $sla = $ticket->slaTracking;
                    $actualResponseTime = (int) round(
                        $ticket->created_at->diffInMinutes($ticket->first_response_at)
                    );
                    $sla->actual_response_time = $actualResponseTime;
                    $sla->response_sla_met = $actualResponseTime <= $sla->response_time_sla;
                    $sla->save();
                }
            }
            
            try {
                if ($user->role === 'client') {
                    if ($ticket->assigned_to) {
                        NotificationController::createNotification(
                            $ticket->assigned_to,
                            'new_comment',
                            'New Comment on Ticket',
                            "Client added a comment on ticket: {$ticket->title}",
                            $ticket->id
                        );
                    }
                } else {
                    if (!($validated['is_internal'] ?? false)) {
                        NotificationController::createNotification(
                            $ticket->user_id,
                            'new_comment',
                            'New Response to Your Ticket',
                            "There's a new response on your ticket: {$ticket->title}",
                            $ticket->id
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send notification: ' . $e->getMessage());
            }
            
            return response()->json([
                'message' => 'Comment added successfully',
                'comment' => $comment
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to add comment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}