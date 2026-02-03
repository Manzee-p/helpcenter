<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            
            if (!$freshUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found in database'
                ], 404);
            }
            
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
                'position' => $freshUser->position ?? null,
                'company' => $freshUser->company ?? null,
                'department' => $freshUser->department ?? null,
                'avatar' => $freshUser->avatar,
                'avatar_url' => $freshUser->avatar ? url('storage/' . $freshUser->avatar) : null,
                'is_active' => (bool)$freshUser->is_active,
                'email_verified_at' => $freshUser->email_verified_at,
                'preferences' => $freshUser->preferences ? json_decode($freshUser->preferences, true) : null,
                'notification_settings' => $freshUser->notification_settings ? json_decode($freshUser->notification_settings, true) : null,
                'created_at' => $freshUser->created_at,
                'updated_at' => $freshUser->updated_at,
            ];
            
            return response()->json([
                'success' => true,
                'data' => ['user' => $userData]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ /me error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user data'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Hubungi administrator.'
                ], 403);
            }

            $token = $user->createToken('auth-token')->plainTextToken;
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            
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
                'position' => $freshUser->position ?? null,
                'company' => $freshUser->company ?? null,
                'department' => $freshUser->department ?? null,
                'avatar' => $freshUser->avatar,
                'avatar_url' => $freshUser->avatar ? url('storage/' . $freshUser->avatar) : null,
                'is_active' => (bool)$freshUser->is_active,
                'email_verified_at' => $freshUser->email_verified_at,
                'preferences' => $freshUser->preferences ? json_decode($freshUser->preferences, true) : null,
                'notification_settings' => $freshUser->notification_settings ? json_decode($freshUser->notification_settings, true) : null,
                'created_at' => $freshUser->created_at,
                'updated_at' => $freshUser->updated_at,
            ];

            DB::table('login_history')->insert([
                'user_id' => $user->id,
                'logged_in_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $this->getDeviceType($request->userAgent()),
                'browser' => $this->getBrowser($request->userAgent()),
                'location' => 'Unknown',
                'created_at' => now()
            ]);

            Log::info('✅ Login successful', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $userData
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('❌ Login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Login gagal. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * ✅ Google Login - CLEAN VERSION
     */
    public function googleLogin(Request $request)
    {
        try {
            Log::info('🔐 Google login started');

            $request->validate([
                'credential' => 'required|string'
            ]);

            $payload = $this->decodeGoogleToken($request->credential);
            
            if (!$payload) {
                Log::error('❌ Invalid token');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google token'
                ], 401);
            }

            Log::info('✅ Token decoded', ['email' => $payload['email']]);

            // Create or update user with password
            $user = User::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'name' => $payload['name'],
                    'google_id' => $payload['sub'],
                    'avatar' => $payload['picture'] ?? null,
                    'password' => Hash::make(Str::random(32)),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'role' => 'client',
                ]
            );

            Log::info('✅ User created', ['user_id' => $user->id]);

            $token = $user->createToken('google-auth')->plainTextToken;
            $freshUser = DB::table('users')->where('id', $user->id)->first();
            
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
                'position' => $freshUser->position ?? null,
                'company' => $freshUser->company ?? null,
                'department' => $freshUser->department ?? null,
                'avatar' => $freshUser->avatar,
                'avatar_url' => $freshUser->avatar ? url('storage/' . $freshUser->avatar) : null,
                'is_active' => (bool)$freshUser->is_active,
                'email_verified_at' => $freshUser->email_verified_at,
                'google_id' => $freshUser->google_id ?? null,
                'preferences' => $freshUser->preferences ? json_decode($freshUser->preferences, true) : null,
                'notification_settings' => $freshUser->notification_settings ? json_decode($freshUser->notification_settings, true) : null,
                'created_at' => $freshUser->created_at,
                'updated_at' => $freshUser->updated_at,
            ];

            DB::table('login_history')->insert([
                'user_id' => $user->id,
                'logged_in_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device' => $this->getDeviceType($request->userAgent()),
                'browser' => $this->getBrowser($request->userAgent()),
                'location' => 'Unknown',
                'created_at' => now()
            ]);

            Log::info('✅ Google login success', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Login dengan Google berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $userData
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('❌ Google login error: ' . $e->getMessage());
            Log::error('Stack: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Login dengan Google gagal. Silakan coba lagi.'
            ], 500);
        }
    }

    private function decodeGoogleToken($token)
    {
        try {
            $clientId = config('services.google.client_id');
            
            if (!$clientId) {
                Log::error('❌ GOOGLE_CLIENT_ID not configured');
                return null;
            }
            
            $client = new \Google_Client(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($token);
            
            return $payload ?: null;
            
        } catch (\Exception $e) {
            Log::error('❌ Token decode error: ' . $e->getMessage());
            return null;
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            Log::info('👋 Logout successful', ['user_id' => $request->user()->id]);

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Logout error: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ]);
        }
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/mobile|android|iphone|ipad|phone/i', $userAgent)) {
            return 'Mobile';
        }
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'Tablet';
        }
        return 'Desktop';
    }

    private function getBrowser($userAgent)
    {
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';
        if (preg_match('/Opera|OPR/i', $userAgent)) return 'Opera';
        return 'Unknown';
    }
}