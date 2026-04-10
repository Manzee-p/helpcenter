<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Feedback;
use App\Models\TicketCategory;
use App\Models\TicketAdditionalInfo;
use App\Models\TicketDeletionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:client']);
    }

    // ─── My Tickets (active) ──────────────────────────────────────────
    public function myTickets(Request $request)
    {
        $userId = Auth::id();
        $query  = Ticket::with(['category', 'assignedVendor', 'feedback', 'latestDeletionRequest'])
                    ->where('user_id', $userId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query
            ->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Pending feedback — for the top panel
        $pendingFeedbackTickets = Ticket::where('user_id', $userId)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDoesntHave('feedback')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ],
                'pending_feedback' => $pendingFeedbackTickets,
            ]);
        }

        return view('client.tickets', compact('tickets', 'pendingFeedbackTickets'));
    }

    // ─── Create ticket form ───────────────────────────────────────────
    public function create()
    {
        $categories = TicketCategory::orderBy('name')->get();
        return view('client.create', compact('categories'));
    }

    // ─── Store new ticket ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'category_id'   => 'required|exists:ticket_categories,id',
            'description'   => 'required|string|max:2000',
            'urgency_level' => 'nullable|in:low,medium,high,critical',
            'event_name'    => 'nullable|string|max:255',
            'venue'         => 'nullable|string|max:255',
            'area'          => 'nullable|string|max:255',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $ticketNumber = 'TKT-' . strtoupper(uniqid());

        DB::beginTransaction();

        try {
            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'title'         => $validated['title'],
                'category_id'   => $validated['category_id'],
                'description'   => $validated['description'],
                'urgency_level' => $validated['urgency_level'] ?? null,
                'event_name'    => $validated['event_name']    ?? null,
                'venue'         => $validated['venue']         ?? null,
                'area'          => $validated['area']          ?? null,
                'user_id'       => Auth::id(),
                'status'        => 'new',
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments/' . $ticket->id, 'public');
                    $ticket->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('client.tickets.index')
                ->with('success', 'Tiket berhasil dibuat! Nomor tiket: ' . $ticketNumber);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Create ticket failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'title' => $validated['title'] ?? null,
            ]);

            return back()->withInput()->with('error', 'Tiket gagal dibuat. Coba lagi.');
        }
    }

    // ─── Show single ticket ───────────────────────────────────────────
    public function show(Request $request, $id)
    {
        $ticket = Ticket::with(['category', 'assignedVendor', 'feedback', 'attachments', 'additionalInfos.user', 'user', 'assignedTo', 'deletionRequests.reviewer'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $ticket,
            ]);
        }

        return view('client.show', compact('ticket'));
    }

    // ─── Ticket history (all, with filtering) ────────────────────────
    public function ticketHistory(Request $request)
    {
        $userId = Auth::id();
        $query  = Ticket::with(['category', 'assignedVendor', 'feedback'])
                    ->where('user_id', $userId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(12);

        // Pending feedback items for the highlight panel
        $pendingFeedbackItems = Ticket::where('user_id', $userId)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDoesntHave('feedback')
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                ],
                'pending_feedback' => $pendingFeedbackItems,
            ]);
        }

        return view('client.history', compact('tickets', 'pendingFeedbackItems'));
    }

    // ─── Pending ratings ──────────────────────────────────────────────
    public function pendingRatings()
    {
        $tickets = Ticket::with(['category', 'assignedVendor'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDoesntHave('feedback')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('client.pending_ratings', compact('tickets'));
    }

    // ─── Submit feedback ──────────────────────────────────────────────
    public function storeFeedback(Request $request, $ticketId)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $ticket = Ticket::where('user_id', Auth::id())
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDoesntHave('feedback')
            ->findOrFail($ticketId);

        Feedback::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'rating'    => $request->rating,
            'comment'   => $request->comment,
        ]);

        return back()->with('success', 'Rating berhasil dikirim. Terima kasih!');
    }

    public function submitFeedback(Request $request, $ticketId)
    {
        try {
            $request->validate([
                'rating'  => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            $ticket = Ticket::where('user_id', Auth::id())->findOrFail($ticketId);

            if (!in_array($ticket->status, ['resolved', 'closed'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feedback hanya bisa untuk tiket resolved/closed.',
                ], 400);
            }

            if ($ticket->feedback) {
                return response()->json([
                    'success' => false,
                    'message' => 'Feedback untuk tiket ini sudah pernah dikirim.',
                ], 400);
            }

            $feedback = Feedback::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'rating'    => $request->rating,
                'comment'   => $request->comment,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback berhasil dikirim.',
                'data' => $feedback,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Submit feedback error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim feedback.',
            ], 500);
        }
    }

    public function submitAdditionalInfo(Request $request, $ticketId)
    {
        try {
            $ticket = Ticket::where('user_id', Auth::id())->findOrFail($ticketId);

            if ($ticket->status !== 'waiting_response') {
                return response()->json([
                    'success' => false,
                    'message' => 'Informasi tambahan hanya dapat dikirim saat status waiting_response.',
                ], 400);
            }

            $validated = $request->validate([
                'note' => 'nullable|string|max:2000',
                'photo' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'photos' => 'nullable|array|max:5',
                'photos.*' => 'file|mimes:jpg,jpeg,png,webp|max:5120',
            ]);

            $note = trim((string) ($validated['note'] ?? ''));
            $photos = $request->hasFile('photos')
                ? $request->file('photos')
                : ($request->hasFile('photo') ? [$request->file('photo')] : []);

            if ($note === '' && empty($photos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Isi catatan atau upload foto terlebih dahulu.',
                ], 422);
            }

            $created = [];
            if (!empty($photos)) {
                foreach ($photos as $index => $file) {
                    $path = $file->store('ticket-additional-info', 'public');
                    $created[] = TicketAdditionalInfo::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => Auth::id(),
                        'note' => $index === 0 && $note !== '' ? $note : null,
                        'photo_path' => $path,
                        'photo_name' => $file->getClientOriginalName(),
                        'photo_type' => $file->getClientMimeType(),
                        'photo_size' => $file->getSize(),
                    ]);
                }
            } else {
                $created[] = TicketAdditionalInfo::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => Auth::id(),
                    'note' => $note,
                ]);
            }

            if ($ticket->assigned_to) {
                NotificationController::createNotification(
                    $ticket->assigned_to,
                    'additional_info_submitted',
                    'Informasi Tambahan Masuk',
                    "Klien mengirim informasi tambahan untuk tiket {$ticket->ticket_number}.",
                    $ticket->id
                );
            }

            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Informasi tambahan berhasil dikirim.',
                    'data' => $created,
                ], 201);
            }

            return back()->with('success', 'Informasi tambahan berhasil dikirim.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (!$this->isApiRequest($request)) {
                return back()->withErrors($e->errors())->withInput();
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Submit additional info error: ' . $e->getMessage());

            if (!$this->isApiRequest($request)) {
                return back()->with('error', 'Gagal mengirim informasi tambahan.');
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim informasi tambahan.',
            ], 500);
        }
    }

    public function submitDeletionRequest(Request $request, $ticketId)
    {
        $ticket = Ticket::where('user_id', Auth::id())->findOrFail($ticketId);

        $validated = $request->validate([
            'reasons' => 'required|array|min:1|max:5',
            'reasons.*' => 'required|string|in:' . implode(',', TicketDeletionRequest::REASONS),
            'custom_reason' => 'required|string|min:10|max:1500',
        ]);

        $existingPending = TicketDeletionRequest::where('ticket_id', $ticket->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            return back()->with('warning', 'Permintaan penghapusan tiket ini masih menunggu persetujuan admin.');
        }

        TicketDeletionRequest::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'reasons' => array_values(array_unique($validated['reasons'])),
            'custom_reason' => $validated['custom_reason'],
            'status' => 'pending',
        ]);

        $adminIds = User::where('role', 'admin')->pluck('id');
        foreach ($adminIds as $adminId) {
            NotificationController::createNotification(
                $adminId,
                'ticket_deletion_request',
                'Permintaan Hapus Tiket Baru',
                "Client mengajukan penghapusan tiket {$ticket->ticket_number}.",
                $ticket->id
            );
        }

        return back()->with('success', 'Permintaan penghapusan tiket berhasil dikirim ke admin.');
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson() || $request->wantsJson();
    }
}