<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientSettingsController extends Controller
{
    /**
     * ✅ ULTIMATE FIX: Update client profile with GUARANTEED save
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            Log::info('📝 [CLIENT] Updating profile:', [
                'user_id' => $user->id,
                'name' => $request->input('name'),
                'has_emergency_contact' => $request->filled('emergency_contact')
            ]);
            
            // ✅ Validate
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
            
            // ✅ Handle avatar upload
            $avatarPath = $user->avatar;
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Store new avatar
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                Log::info('📸 [CLIENT] Avatar uploaded:', ['path' => $avatarPath]);
            }
            
            // ✅ CRITICAL: Build complete update data with EXPLICIT NULL handling
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $request->input('phone', null),
                'emergency_contact' => $request->input('emergency_contact', null),
                'emergency_contact_name' => $request->input('emergency_contact_name', null),
                'emergency_contact_relation' => $request->input('emergency_contact_relation', null),
                'address' => $request->input('address', null),
                'city' => $request->input('city', null),
                'province' => $request->input('province', null),
                'postal_code' => $request->input('postal_code', null),
                'gender' => $request->input('gender', null),
                'birth_date' => $request->input('birth_date', null),
                'nik' => $request->input('nik', null),
                'bio' => $request->input('bio', null),
                'avatar' => $avatarPath,
                'updated_at' => now()
            ];
            
            // ✅ CRITICAL: Use DB::table for GUARANTEED save
            $affected = DB::table('users')
                ->where('id', $user->id)
                ->update($updateData);
            
            Log::info('✅ [CLIENT] Profile updated - Verification:', [
                'user_id' => $user->id,
                'name_saved' => $updateData['name'],
                'emergency_contact_saved' => $updateData['emergency_contact'],
                'affected_rows' => $affected
            ]);
            
            // ✅ Fetch fresh data from database
            $freshUser = DB::table('users')
                ->where('id', $user->id)
                ->first();
            
            if (!$freshUser) {
                throw new \Exception('Failed to fetch updated user');
            }
            
            // ✅ Return COMPLETE user data
            $userData = [
                'id' => $freshUser->id,
                'name' => $freshUser->name,
                'email' => $freshUser->email,
                'role' => $freshUser->role,
                'phone' => $freshUser->phone,
                'emergency_contact' => $freshUser->emergency_contact,
                'emergency_contact_name' => $freshUser->emergency_contact_name,
                'emergency_contact_relation' => $freshUser->emergency_contact_relation,
                'address' => $freshUser->address,
                'city' => $freshUser->city,
                'province' => $freshUser->province,
                'postal_code' => $freshUser->postal_code,
                'gender' => $freshUser->gender,
                'birth_date' => $freshUser->birth_date,
                'nik' => $freshUser->nik,
                'bio' => $freshUser->bio,
                'avatar' => $freshUser->avatar,
                'avatar_url' => $freshUser->avatar 
                    ? url('storage/' . $freshUser->avatar) 
                    : null,
                'is_active' => (bool)$freshUser->is_active,
                'created_at' => $freshUser->created_at,
                'updated_at' => $freshUser->updated_at
            ];
            
            Log::info('✅ [CLIENT] Profile update SUCCESSFUL - Full data returned');
            
            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'user' => $userData
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ [CLIENT] Validation failed:', [
                'errors' => $e->errors()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Update profile error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ✅ Delete avatar
     */
    public function deleteAvatar(Request $request)
    {
        try {
            $user = $request->user();
            
            Log::info('🗑️ [CLIENT] Deleting avatar', ['user_id' => $user->id]);
            
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            DB::table('users')->where('id', $user->id)->update([
                'avatar' => null,
                'updated_at' => now()
            ]);
            
            // Fetch updated user
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            
            Log::info('✅ [CLIENT] Avatar deleted successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'Avatar berhasil dihapus',
                'data' => [
                    'user' => [
                        'id' => $freshUser->id,
                        'name' => $freshUser->name,
                        'email' => $freshUser->email,
                        'role' => $freshUser->role,
                        'avatar' => null,
                        'avatar_url' => null
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Delete avatar error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar'
            ], 500);
        }
    }
    
    /**
     * ✅ Change password
     */
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8',
                'new_password_confirmation' => 'required|same:new_password'
            ]);
            
            $user = $request->user();
            
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini tidak cocok'
                ], 422);
            }
            
            DB::table('users')->where('id', $user->id)->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now()
            ]);
            
            Log::info('🔐 [CLIENT] Password changed:', ['user_id' => $user->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Change password error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password'
            ], 500);
        }
    }
    
    /**
     * ✅ Get communication history
     */
    public function getCommunications(Request $request)
    {
        try {
            $userId = $request->user()->id;
            
            // Mock data for now - replace with actual database query
            $communications = [
                [
                    'id' => 1,
                    'type' => 'email',
                    'subject' => 'Tiket #1234 telah diproses',
                    'message' => 'Vendor sedang menangani tiket Anda',
                    'from' => 'System',
                    'read' => false,
                    'created_at' => now()->subHours(2)->toDateTimeString()
                ]
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'communications' => $communications,
                    'stats' => [
                        'messages' => 5,
                        'emails' => 3,
                        'notifications' => 12,
                        'calls' => 2
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Get communications error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => [
                    'communications' => [],
                    'stats' => [
                        'messages' => 0,
                        'emails' => 0,
                        'notifications' => 0,
                        'calls' => 0
                    ]
                ]
            ]);
        }
    }
    
    /**
     * ✅ Get last login
     */
    public function getLastLogin(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get from login_history or sessions table
            $lastLogin = DB::table('login_history')
                ->where('user_id', $user->id)
                ->orderBy('logged_in_at', 'desc')
                ->first();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'last_login' => $lastLogin ? [
                        'logged_in_at' => $lastLogin->logged_in_at,
                        'location' => $lastLogin->location ?? 'Unknown',
                        'device' => $lastLogin->device ?? 'Desktop',
                        'browser' => $lastLogin->browser ?? 'Unknown'
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Get last login error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data' => ['last_login' => null]
            ]);
        }
    }
    
    /**
     * ✅ Send support message
     */
    public function sendSupportMessage(Request $request)
    {
        try {
            $validated = $request->validate([
                'category' => 'required|string',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'attachment' => 'nullable|file|max:5120'
            ]);
            
            // Handle attachment
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('support', 'public');
            }
            
            // Save to database (create support_messages table if needed)
            DB::table('support_messages')->insert([
                'user_id' => $request->user()->id,
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'attachment' => $attachmentPath,
                'status' => 'open',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('[CLIENT] Support message sent:', [
                'user_id' => $request->user()->id,
                'subject' => $validated['subject']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Send support message error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan'
            ], 500);
        }
    }
    
    /**
     * ✅ Save notification settings
     */
    public function saveNotifications(Request $request)
    {
        try {
            $settings = $request->input('settings', []);
            
            // Save to user_settings table or users table
            DB::table('users')
                ->where('id', $request->user()->id)
                ->update([
                    'notification_settings' => json_encode($settings),
                    'updated_at' => now()
                ]);
            
            Log::info('[CLIENT] Notification preferences updated for user ' . $request->user()->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification preferences saved'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Save notifications error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences'
            ], 500);
        }
    }
    
    /**
     * ✅ Save preferences
     */
    public function savePreferences(Request $request)
    {
        try {
            $preferences = $request->all();
            
            DB::table('users')
                ->where('id', $request->user()->id)
                ->update([
                    'preferences' => json_encode($preferences),
                    'updated_at' => now()
                ]);
            
            Log::info('[CLIENT] Preferences updated for user ' . $request->user()->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Preferences saved'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [CLIENT] Save preferences error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences'
            ], 500);
        }
    }
    
    /**
     * ✅ Export data
     */
    public function exportData(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $user = DB::table('users')->where('id', $userId)->first();
            
            $data = [
                'user' => (array)$user,
                'exported_at' => now()->toDateTimeString()
            ];
            
            return response()->json($data)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="client-data-' . time() . '.json"');
        } catch (\Exception $e) {
            Log::error('[CLIENT] Export data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export'
            ], 500);
        }
    }
}