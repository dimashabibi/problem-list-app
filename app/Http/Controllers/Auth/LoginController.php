<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect()->intended('/admin/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = Str::lower('login:' . $request->input('username') . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['username' => 'Too many login attempts. Please try again later.'])->withInput();
        }

        if (Auth::attempt(['username' => $request->input('username'), 'password' => $request->input('password')])) {
            RateLimiter::clear($key);
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        RateLimiter::hit($key, 60);
        return back()->withErrors(['username' => 'Invalid credentials'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
