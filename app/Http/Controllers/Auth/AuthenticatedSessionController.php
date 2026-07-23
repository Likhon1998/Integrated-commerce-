<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Check if the password is correct
        $request->authenticate();

        // 🚨 THE TRAP: FREEZE THE SCREEN AND SHOW THE DATABASE VALUE
        // If this prints "FALSE", your Suspend button did not update the database!
        // If this prints "TRUE", the system will kick them out perfectly.
        /* dd([
            'User Email' => $request->user()->email,
            'Is the database saying they are suspended?' => $request->user()->is_suspended ? 'YES (TRUE)' : 'NO (FALSE)'
        ]);
        */

        // 2. THE BOUNCER: Check if the user is suspended
        if ($request->user()->is_suspended) {
            
            // Immediately log them out and destroy the session
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Kick them back to the login page with the exact red error message
            return redirect()->route('login')->withErrors([
                'email' => 'You are suspended by admin. Please connect with admin.',
            ]);
        }

        // 3. If they are NOT suspended, let them into the dashboard safely
        $request->session()->regenerate();

        $user = $request->user();

        // Website shoppers use the storefront account — never the staff panel.
        if ($user->isStorefrontCustomer()) {
            return redirect()->intended(route('website.account'));
        }

        if ($user->requiresDailyOpeningBalance() && ! $user->hasTodayOpenSession()) {
            return redirect()->route('counters.sessions.open-today');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}