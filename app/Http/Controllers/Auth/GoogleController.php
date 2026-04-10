<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class GoogleController extends Controller
{
    public function handleGoogle(Request $request)
    {
        $token = $request->credential;

        if (!$token) {
            return response()->json(['error' => 'No token'], 400);
        }

        // Ambil public key Google
        $keys = Http::get('https://www.googleapis.com/oauth2/v3/certs')->json();

        try {
            $decoded = JWT::decode($token, JWK::parseKeySet($keys));

            $email = $decoded->email;
            $name  = $decoded->name ?? 'User';

            // cek user
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt(str()->random(16)), // random password
                ]);
            }

            Auth::login($user);

            return redirect('/home');

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => $e->getMessage()
            ], 401);
        }
    }
}