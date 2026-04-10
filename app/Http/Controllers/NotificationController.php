<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'total' => 0,
                'unread' => 0
            ]);
        }

        return view('notifications.index');
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $count = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get unread count error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung notifikasi',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $notification = Notification::where('user_id', Auth::id())
                ->findOrFail($id);

            if (!$notification->read_at) {
                $notification->read_at = now();
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sudah dibaca',
                'notification' => $notification
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
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
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $updated = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca',
                'updated' => $updated
            ]);
            
        } catch (\Exception $e) {
            Log::error('Mark all as read error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai semua notifikasi',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $notification = Notification::where('user_id', Auth::id())
                ->findOrFail($id);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Delete notification error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Create notification (helper method)
     * PENTING: Method ini static agar bisa dipanggil dari controller lain
     */
    public static function createNotification(
        $userId, 
        $type, 
        $title, 
        $message, 
        $relatedId = null,
        $relatedType = 'ticket'
    ) {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'related_id' => $relatedId,
                'related_type' => $relatedType,
            ]);

            Log::info('Notification created successfully', [
                'notification_id' => $notification->id,
                'user_id' => $userId,
                'type' => $type,
                'related_id' => $relatedId,
                'related_type' => $relatedType
            ]);

            return $notification;
            
        } catch (\Exception $e) {
            Log::error('Create notification failed', [
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
}