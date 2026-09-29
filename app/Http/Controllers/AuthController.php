<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form (for Web SPA/Blade)
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle authentication request
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // For API / SPA using Sanctum Tokens:
            // $user = Auth::user();
            // $token = $user->createToken('auth_token')->plainTextToken;
            // return response()->json(['token' => $token, 'user' => $user]);

            return redirect()->intended('/products');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}