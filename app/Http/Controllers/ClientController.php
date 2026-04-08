<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Ticket;
use App\Models\TicketAdditionalInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    /**
     * Get all tickets for the authenticated client
     */
    public function myTickets(Request $request)
    {
        try {
            Log::info('Fetching tickets for client: ' . Auth::id());

            $query = Ticket::with(['category', 'assignedTo', 'slaTracking', 'attachments', 'feedback'])
                ->where('user_id', Auth::id());

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by priority
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('ticket_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            if ($request->filled('sort')) {
                switch ($request->sort) {
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
                        ");
                        break;
                    default: // newest
                        $query->orderBy('created_at', 'desc');
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $tickets = $query->paginate($perPage);

            Log::info('Found ' . $tickets->total() . ' tickets for client');

            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching client tickets: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tickets',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get detail of a single ticket owned by the authenticated client
     */
    public function show($id)
    {
        try {
            Log::info('Fetching ticket detail', [
                'ticket_id' => $id,
                'user_id'   => Auth::id(),
            ]);

            // Validasi ID
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID tiket tidak valid',
                ], 400);
            }

            $ticket = Ticket::with([
                'category',
                'assignedTo',
                'attachments',
                'feedback',
                'slaTracking',
                'additionalInfos.user',
            ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

            if (!$ticket) {
                Log::warning('Ticket not found or unauthorized', [
                    'ticket_id' => $id,
                    'auth_user_id' => Auth::id(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Tiket tidak ditemukan atau bukan milik Anda',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $ticket,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching ticket detail: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail tiket',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Submit additional information when vendor requests more info.
     */
    public function submitAdditionalInfo(Request $request, $ticketId)
    {
        try {
            $ticket = Ticket::where('user_id', Auth::id())->findOrFail($ticketId);

            if ($ticket->status !== 'waiting_response') {
                return response()->json([
                    'success' => false,
                    'message' => 'Informasi tambahan hanya dapat dikirim saat status menunggu respons.',
                ], 400);
            }

            $validated = $request->validate([
                'note' => 'nullable|string|max:2000',
                'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'photos' => 'nullable|array|max:5',
                'photos.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            $note = trim((string) ($validated['note'] ?? ''));
            $photos = [];
            if ($request->hasFile('photos')) {
                $photos = $request->file('photos');
            } elseif ($request->hasFile('photo')) {
                $photos = [$request->file('photo')];
            }
            $hasPhoto = count($photos) > 0;

            if ($note === '' && !$hasPhoto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Isi catatan atau unggah foto terlebih dahulu.',
                ], 422);
            }

            $infos = [];
            if ($hasPhoto) {
                foreach ($photos as $index => $file) {
                    $path = $file->store('ticket-additional-info', 'public');
                    $info = TicketAdditionalInfo::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => Auth::id(),
                        // simpan catatan pada item pertama agar tidak duplikat berulang
                        'note' => $index === 0 && $note !== '' ? $note : null,
                        'photo_path' => $path,
                        'photo_name' => $file->getClientOriginalName(),
                        'photo_type' => $file->getClientMimeType(),
                        'photo_size' => $file->getSize(),
                    ]);
                    $infos[] = $info->load('user');
                }
            } else {
                $info = TicketAdditionalInfo::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'note' => $note !== '' ? $note : null,
                ]);
                $infos[] = $info->load('user');
            }

            if ($ticket->assigned_to) {
                NotificationController::createNotification(
                    $ticket->assigned_to,
                    'additional_info_submitted',
                    'Informasi Tambahan Diterima',
                    "Klien mengirim informasi tambahan untuk tiket {$ticket->ticket_number}.",
                    $ticket->id
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Informasi tambahan berhasil dikirim.',
                'data' => $infos,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan atau bukan milik Anda',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error submitting additional info: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim informasi tambahan.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Submit feedback for a resolved/closed ticket
     */
    public function submitFeedback(Request $request, $ticketId)
    {
        try {
            Log::info('Client submitting feedback', [
                'client_id' => Auth::id(),
                'ticket_id' => $ticketId
            ]);

            // Find ticket and verify ownership
            $ticket = Ticket::where('user_id', Auth::id())
                ->findOrFail($ticketId);

            // Check if ticket is resolved or closed
            if (!in_array($ticket->status, ['resolved', 'closed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only provide feedback for resolved or closed tickets'
                ], 400);
            }

            // Check if feedback already exists
            if ($ticket->feedback) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feedback already submitted for this ticket'
                ], 400);
            }

            // Validate feedback data
            $validated = $request->validate([
                'rating'  => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            // Create feedback
            $feedback = Feedback::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'rating'    => $validated['rating'],
                'comment'   => $validated['comment'] ?? null,
            ]);

            Log::info('Feedback submitted successfully', [
                'feedback_id' => $feedback->id,
                'rating'      => $feedback->rating
            ]);

            // Notify vendor if assigned
            if ($ticket->assigned_to) {
                try {
                    NotificationController::createNotification(
                        $ticket->assigned_to,
                        'feedback_received',
                        'New Feedback Received',
                        "Client provided {$validated['rating']}-star rating for ticket: {$ticket->title}",
                        $ticket->id
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send feedback notification: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Feedback submitted successfully',
                'feedback' => $feedback,
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Ticket not found or unauthorized', [
                'ticket_id' => $ticketId,
                'client_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ticket not found or you do not have access to this ticket'
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error submitting feedback: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit feedback',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get ticket history with feedback
     */
    public function ticketHistory(Request $request)
    {
        try {
            Log::info('Fetching ticket history for client: ' . Auth::id());

            $query = Ticket::with(['category', 'assignedTo', 'feedback', 'slaTracking'])
                ->where('user_id', Auth::id());

            // Filter by date range
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $endDate   = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by priority
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('ticket_number', 'like', "%{$search}%");
                });
            }

            // Order by created_at desc
            $query->orderBy('created_at', 'desc');

            // Pagination
            $perPage = $request->input('per_page', 20);
            $tickets = $query->paginate($perPage);

            Log::info('Found ' . $tickets->total() . ' tickets in history');

            return response()->json([
                'success' => true,
                'data'    => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page'    => $tickets->lastPage(),
                    'per_page'     => $tickets->perPage(),
                    'total'        => $tickets->total(),
                    'from'         => $tickets->firstItem(),
                    'to'           => $tickets->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching ticket history: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch ticket history',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get client dashboard statistics
     */
    public function dashboard()
    {
        try {
            $clientId = Auth::id();

            $stats = [
                'total_tickets' => Ticket::where('user_id', $clientId)->count(),
                'new_tickets'   => Ticket::where('user_id', $clientId)->where('status', 'new')->count(),
                'in_progress'   => Ticket::where('user_id', $clientId)->where('status', 'in_progress')->count(),
                'resolved'      => Ticket::where('user_id', $clientId)->whereIn('status', ['resolved', 'closed'])->count(),
            ];

            // Get recent tickets
            $recentTickets = Ticket::with(['category', 'assignedTo'])
                ->where('user_id', $clientId)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            // Get tickets pending feedback
            $pendingFeedback = Ticket::where('user_id', $clientId)
                ->whereIn('status', ['resolved', 'closed'])
                ->doesntHave('feedback')
                ->orderBy('resolved_at', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'success'          => true,
                'stats'            => $stats,
                'recent_tickets'   => $recentTickets,
                'pending_feedback' => $pendingFeedback,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching client dashboard: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
