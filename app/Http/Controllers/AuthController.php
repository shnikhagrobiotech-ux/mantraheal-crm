<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'email' => strtolower(trim($request->input('email'))),
            'password' => trim($request->input('password')),
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account is deactivated. Please contact administrator.']);
            }

            $user->last_login_at = now();
            $user->last_login_ip = $request->ip();
            $user->saveQuietly();

            AuditLog::log('login', $user, "User {$user->name} logged in from {$request->ip()}");

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records. Please verify email and password.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::log('logout', $user, "User {$user->name} logged out");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function profile()
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}
