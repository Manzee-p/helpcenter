<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    /**
     * Get user notifications (with filter, search, pagination)
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        if (!Schema::hasTable('notifications')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'data' => [], 'total' => 0, 'unread' => 0]);
            }
            return view('notifications.index');
        }

        // ── Params ──────────────────────────────────────────────
        $perPage = max(1, min((int) $request->query('per_page', 20), 100));
        $page    = max(1, (int) $request->query('page', 1));
        $filter  = $request->query('filter', 'all');   // all | unread | read
        $search  = trim($request->query('search', ''));

        // ── Query ────────────────────────────────────────────────
        $query = Notification::query()
            ->where('user_id', Auth::id());

        // Filter by read status
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Search on title or message
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        $total  = $query->count();
        $offset = ($page - 1) * $perPage;

        $items = $query
            ->latest('created_at')
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(fn($n) => [
                'id'           => $n->id,
                'type'         => $n->type,
                'title'        => $n->title,
                'message'      => $n->message,
                'related_id'   => $n->related_id,
                'related_type' => $n->related_type,
                'ticket_id'    => $n->related_type === 'ticket' ? $n->related_id : null,
                'is_read'      => !is_null($n->read_at),
                'read_at'      => $n->read_at,
                'created_at'   => $n->created_at,
            ])
            ->values();

        $unread = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'data'         => $items,
                'total'        => $total,
                'unread'       => $unread,
                'current_page' => $page,
                'per_page'     => $perPage,
                'last_page'    => max(1, (int) ceil($total / $perPage)),
            ]);
        }

        return view('notifications.index');
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $count = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count();

            return response()->json(['success' => true, 'count' => $count]);

        } catch (\Exception $e) {
            Log::error('Get unread count error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung notifikasi',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);

            if (!$notification->read_at) {
                $notification->read_at = now();
                $notification->save();
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Notifikasi ditandai sudah dibaca',
                'is_read'      => true,
                'ticket_id'    => $notification->related_type === 'ticket' ? $notification->related_id : null,
                'related_id'   => $notification->related_id,
                'related_type' => $notification->related_type,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);

        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $updated = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca',
                'updated' => $updated,
            ]);

        } catch (\Exception $e) {
            Log::error('Mark all as read error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
            $notification->delete();

            return response()->json(['success' => true, 'message' => 'Notifikasi berhasil dihapus']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Notifikasi tidak ditemukan'], 404);

        } catch (\Exception $e) {
            Log::error('Delete notification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Create a notification (static helper – callable from other controllers)
     */
    public static function createNotification(
        $userId,
        $type,
        $title,
        $message,
        $relatedId   = null,
        $relatedType = 'ticket'
    ) {
        try {
            $notification = Notification::create([
                'user_id'      => $userId,
                'type'         => $type,
                'title'        => $title,
                'message'      => $message,
                'related_id'   => $relatedId,
                'related_type' => $relatedType,
            ]);

            Log::info('Notification created', [
                'notification_id' => $notification->id,
                'user_id'         => $userId,
                'type'            => $type,
                'related_id'      => $relatedId,
                'related_type'    => $relatedType,
            ]);

            return $notification;

        } catch (\Exception $e) {
            Log::error('Create notification failed', [
                'user_id' => $userId,
                'type'    => $type,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}