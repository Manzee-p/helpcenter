<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:client']);
    }

    public function index()
    {
        $user = Auth::user();

        $lastLogin = null;
        try {
            $lastLogin = DB::table('login_history')
                ->where('user_id', $user->id)
                ->orderBy('logged_in_at', 'desc')
                ->first();
        } catch (\Exception $e) {
            // Table might not exist yet.
        }

        return view('client.settings', compact('user', 'lastLogin'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            Log::info('[CLIENT] Updating profile', [
                'user_id' => $user->id,
                'name' => $request->input('name'),
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'emergency_contact' => 'nullable|string|max:20',
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_relation' => 'nullable|string|max:100',
                'address' => 'nullable|string|max:500',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:10',
                'gender' => 'nullable|in:male,female',
                'birth_date' => 'nullable|date',
                'nik' => 'nullable|string|max:16',
                'bio' => 'nullable|string|max:1000',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

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
                'emergency_contact' => $request->input('emergency_contact'),
                'emergency_contact_name' => $request->input('emergency_contact_name'),
                'emergency_contact_relation' => $request->input('emergency_contact_relation'),
                'address' => $request->input('address'),
                'city' => $request->input('city'),
                'province' => $request->input('province'),
                'postal_code' => $request->input('postal_code'),
                'gender' => $request->input('gender'),
                'birth_date' => $request->input('birth_date'),
                'nik' => $request->input('nik'),
                'bio' => $request->input('bio'),
                'avatar' => $avatarPath,
                'updated_at' => now(),
            ];

            DB::table('users')->where('id', $user->id)->update($updateData);

            $fresh = DB::table('users')->where('id', $user->id)->first();

            if (!$request->expectsJson()) {
                return redirect()->back()->with('success', 'Profil berhasil diperbarui');
            }

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'user' => [
                        'id' => $fresh->id,
                        'name' => $fresh->name,
                        'email' => $fresh->email,
                        'avatar' => $fresh->avatar,
                        'avatar_url' => $fresh->avatar ? url('storage/' . $fresh->avatar) : null,
                    ],
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[CLIENT] updateProfile error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan'], 500);
        }
    }

    public function deleteAvatar(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            DB::table('users')->where('id', $user->id)->update([
                'avatar' => null,
                'updated_at' => now(),
            ]);

            if (!$request->expectsJson()) {
                return redirect()->back()->with('success', 'Avatar berhasil dihapus');
            }

            return response()->json(['success' => true, 'message' => 'Avatar berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('[CLIENT] deleteAvatar error: ' . $e->getMessage());
            if (!$request->expectsJson()) {
                return redirect()->back()->with('error', 'Gagal menghapus avatar');
            }
            return response()->json(['success' => false, 'message' => 'Gagal menghapus avatar'], 500);
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

            $user = Auth::user();

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

            Log::info('[CLIENT] Password changed for user ' . $user->id);

            return response()->json(['success' => true, 'message' => 'Password berhasil diubah']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[CLIENT] changePassword error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengubah password'], 500);
        }
    }

    public function updateAvatar(Request $request)
    {
        return $this->updateProfile($request);
    }

    public function updatePassword(Request $request)
    {
        return $this->changePassword($request);
    }

    public function getLastLogin()
    {
        try {
            $lastLogin = DB::table('login_history')
                ->where('user_id', Auth::id())
                ->orderBy('logged_in_at', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'last_login' => $lastLogin ? [
                        'logged_in_at' => $lastLogin->logged_in_at,
                        'location' => $lastLogin->location ?? 'Unknown',
                        'device' => $lastLogin->device ?? 'Desktop',
                        'browser' => $lastLogin->browser ?? 'Unknown',
                        'ip_address' => $lastLogin->ip_address ?? '-',
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'data' => ['last_login' => null]]);
        }
    }
}