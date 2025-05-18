<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:auth,email', // This should match your collection name
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Log audit trail for registration
        AuditTrail::create([
            'user_id'   => (string) $user->_id, // Use the newly created user's ID
            'action'    => 'User registered',
            'timestamp' => now(),
            'ip'        => $request->ip(),
            'agent'     => $request->header('User-Agent'),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => [
                'id' => $user->_id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Get logged-in user
            $user = Auth::user();

            // Log audit trail for login
            AuditTrail::create([
                'user_id'   => (string) $user->_id,
                'action'    => 'Logged in',
                'timestamp' => now(),
                'ip'        => $request->ip(),
                'agent'     => $request->header('User-Agent'),
            ]);

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->_id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function me(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'id' => $user->_id,
            'name' => $user->name,
            'email' => $user->email
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        AuditTrail::create([
            'user_id'   => (string) $user->_id,
            'action'    => 'Logged out',
            'timestamp' => now(),
            'ip'        => $request->ip(),
            'agent'     => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'Logged out successfully']);
    }
}
