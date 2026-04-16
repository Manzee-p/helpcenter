<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:255',
                'company' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $before = DB::table('users')->where('id', $user->id)->first();

            $avatarPath = $user->avatar;
            if ($request->hasFile('avatar')) {
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $request->input('phone'),
                'position' => $request->input('position'),
                'company' => $request->input('company'),
                'bio' => $request->input('bio'),
                'avatar' => $avatarPath,
                'updated_at' => now(),
            ];

            DB::table('users')
                ->where('id', $user->id)
                ->update($updateData);

            $freshUser = DB::table('users')
                ->where('id', $user->id)
                ->first();

            if (!$freshUser) {
                throw new \Exception('Failed to fetch updated user');
            }

            $this->logActivity(
                $user->id,
                'profile_updated',
                'Profil akun diperbarui',
                $request,
                $this->extractChangedFields($before, $freshUser, [
                    'name', 'email', 'phone', 'position', 'company', 'bio', 'avatar'
                ])
            );

            $userData = [
                'id' => $freshUser->id,
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'role' => $freshUser->role,
                'phone' => $freshUser->phone,
                'position' => $freshUser->position,
                'company' => $freshUser->company,
                'bio' => $freshUser->bio,
                'avatar' => $freshUser->avatar,
                'avatar_url' => $freshUser->avatar ? url('storage/' . $freshUser->avatar) : null,
                'is_active' => (bool) $freshUser->is_active,
                'created_at' => $freshUser->created_at,
                'updated_at' => $freshUser->updated_at,
            ];

            if (!$request->expectsJson()) {
                return redirect()->back()->with('success', 'Profil berhasil diperbarui');
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'user' => $userData,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[SETTINGS] updateProfile error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAvatar(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            DB::table('users')->where('id', $user->id)->update([
                'avatar' => null,
                'updated_at' => now(),
            ]);

            $this->logActivity($user->id, 'avatar_deleted', 'Foto profil dihapus', $request);

            if (!$request->expectsJson()) {
                return redirect()->back()->with('success', 'Avatar berhasil dihapus');
            }

            return response()->json([
                'success' => true,
                'message' => 'Avatar berhasil dihapus',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => null,
                        'avatar_url' => null,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('[SETTINGS] deleteAvatar error: ' . $e->getMessage());

            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Failed to delete avatar');
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar',
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8',
                'new_password_confirmation' => 'required|same:new_password',
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini tidak cocok',
                ], 422);
            }

            DB::table('users')->where('id', $user->id)->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);

            $this->logActivity($user->id, 'password_changed', 'Password akun diubah', $request);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah',
            ]);
        } catch (\Exception $e) {
            Log::error('[SETTINGS] changePassword error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
            ], 500);
        }
    }

    public function getLoginHistory(Request $request)
    {
        if (!Schema::hasTable('login_history')) {
            return response()->json([
                'success' => true,
                'data' => ['login_history' => []],
            ]);
        }

        $items = LoginHistory::query()
            ->where('user_id', $request->user()->id)
            ->latest('logged_in_at')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'device' => $item->device,
                    'browser' => $item->browser,
                    'ip_address' => $item->ip_address,
                    'location' => $item->location,
                    'success' => (bool) $item->success,
                    'logged_in_at' => optional($item->logged_in_at)->timezone(config('app.timezone'))->format('d M Y H:i'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => ['login_history' => $items],
        ]);
    }

    public function getSessions(Request $request)
    {
        if (!Schema::hasTable('sessions')) {
            return response()->json([
                'success' => true,
                'data' => ['sessions' => []],
            ]);
        }

        $currentSessionId = $request->session()->getId();

        $items = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->limit(20)
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_activity' => Carbon::createFromTimestamp((int) $session->last_activity)
                        ->timezone(config('app.timezone'))
                        ->format('d M Y H:i'),
                    'is_current' => $session->id === $currentSessionId,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => ['sessions' => $items],
        ]);
    }

    public function getActivityLogs(Request $request)
    {
        if (!Schema::hasTable('activity_logs')) {
            return response()->json([
                'success' => true,
                'data' => ['activity_logs' => []],
            ]);
        }

        $items = ActivityLog::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'action' => $item->action,
                    'description' => $item->description,
                    'ip_address' => $item->ip_address,
                    'created_at' => optional($item->created_at)->timezone(config('app.timezone'))->format('d M Y H:i'),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => ['activity_logs' => $items],
        ]);
    }

    protected function logActivity(int $userId, string $action, string $description, Request $request, array $changes = []): void
    {
        if (!Schema::hasTable('activity_logs')) {
            return;
        }

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'changes' => !empty($changes) ? $changes : null,
        ]);
    }

    protected function extractChangedFields($before, $after, array $fields): array
    {
        if (!$before || !$after) {
            return [];
        }

        $changes = [];
        foreach ($fields as $field) {
            $old = $before->{$field} ?? null;
            $new = $after->{$field} ?? null;
            if ($old != $new) {
                $changes[$field] = ['old' => $old, 'new' => $new];
            }
        }

        return $changes;
    }
}
