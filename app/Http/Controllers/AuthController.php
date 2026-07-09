<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $email = $request->input('email');
            $username = $request->input('username');
            $password = $request->input('password');

            // Validate inputs
            if (!$email || !$username || !$password) {
                return response()->json(['message' => 'Missing fields'], 400);
            }

            if (strlen($password) < 8) {
                return response()->json(['message' => 'Password must be at least 8 characters'], 400);
            }

            if (strlen($username) < 2) {
                return response()->json(['message' => 'Username must be at least 2 characters'], 400);
            }

            // Check if user exists
            $exists = User::where('email', $email)->orWhere('name', $username)->first();
            if ($exists) {
                return response()->json(['message' => 'User already exists'], 400);
            }

            // Create user with simple token
            $token = Str::random(60);
            $user = User::create([
                'name' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'token' => $token,
            ]);

            return response()->json([
                'message' => 'Registered successfully',
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage() . ' at line ' . $e->getLine());
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Server error',
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            if (!$email || !$password) {
                return response()->json(['message' => 'Missing credentials'], 400);
            }

            $user = User::where('email', $email)->first();
            if (!$user || !Hash::check($password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Generate new token
            $token = Str::random(60);
            $user->update(['token' => $token]);

            return response()->json([
                'message' => 'Logged in',
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login error',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        return response()->json(['message' => 'Logged out'], 200);
    }

    public function firebaseLogin(Request $request)
    {
        try {
            $idToken = $request->input('idToken');

            if (!$idToken) {
                return response()->json(['message' => 'Missing idToken'], 400);
            }

            // Decode the JWT token (basic decode without verification for now)
            $tokenParts = explode('.', $idToken);
            if (count($tokenParts) !== 3) {
                return response()->json(['message' => 'Invalid token format'], 400);
            }

            // Decode the payload
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);
            
            if (!$payload || !isset($payload['email'])) {
                return response()->json(['message' => 'Invalid token payload'], 400);
            }

            $email = $payload['email'];
            $name = $payload['name'] ?? explode('@', $email)[0];
            $firebaseUid = $payload['user_id'] ?? null;

            // Find or create user
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                // Create new user with Firebase auth
                $token = Str::random(60);
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(32)), // Random password for Firebase users
                    'token' => $token,
                ]);
            } else {
                // Generate new token for existing user
                $token = Str::random(60);
                $user->update(['token' => $token]);
            }

            return response()->json([
                'message' => 'Logged in successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Firebase login error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        return response()->json(['message' => 'OK'], 200);
    }

    public function updateProfile(Request $request)
    {
        return response()->json(['message' => 'Updated'], 200);
    }
}
