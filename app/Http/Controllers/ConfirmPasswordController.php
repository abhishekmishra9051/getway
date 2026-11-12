<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ConfirmPasswordController extends Controller
{
    public function show()
    {
        return view('auth.confirm-password');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);

        // Per-user rate limit: 5 attempts per minute to prevent brute-force.
        $key = 'confirm-password:'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors([
                'password' => 'Too many attempts. Please wait before trying again.',
            ]);
        }

        if (! Hash::check($request->password, $request->user()->password)) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }

        RateLimiter::clear($key);

        $request->session()->put('auth.password_confirmed_at', time());

        // Regenerate session ID after privilege elevation to prevent session
        // fixation attacks that could grant an attacker confirmed-password state.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
