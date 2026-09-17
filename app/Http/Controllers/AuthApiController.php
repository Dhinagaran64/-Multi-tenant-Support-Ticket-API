<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        Auth::login($user);

        $token = $user->createToken('access_token')->plainTextToken;
        setcookie('access_token', $token, 0, '/');

        return response()->json([
            'message' => 'Login successful.',
            'access_token' => $token,
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            if (method_exists($user, 'tokens')) {
                $user->tokens->each(function ($token) {
                    $token->delete();
                });
            }
            
            Auth::guard('web')->logout();
        }

        setcookie('access_token', '', time() - 3600, '/');

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}