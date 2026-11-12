<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', $this->passwordRule()],
        ], [
            'password.confirmed' => 'The new passwords do not match.',
        ]);

        // Verify current password manually — Auth::logoutOtherDevices requires it.
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->current_password,
        ])) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password and invalidate all OTHER sessions.
        // Auth::logoutOtherDevices updates the password, logs out all sessions
        // that do not match the current one, and rotates the remember_token.
        Auth::logoutOtherDevices($request->current_password);
        $request->user()->forceFill(['password' => $request->password])->save();

        // Regenerate current session token to bind it to the new password hash.
        $request->session()->regenerate();

        return redirect()->route('settings')
            ->with('status', 'Password changed. All other sessions have been signed out.');
    }

    private function passwordRule(): Password
    {
        $rule = Password::min(10);
        if (app()->isProduction()) {
            $rule = $rule->uncompromised();
        }

        return $rule;
    }
}
