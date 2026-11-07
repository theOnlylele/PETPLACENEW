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
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();
        $intendedRole = $request->input('role');

        // Allow admin accounts through either login page.
        if (
            !$user->isAdmin() &&
            $intendedRole &&
            $user->role !== $intendedRole
        ) {
            Auth::logout();

            $accountType = $intendedRole === 'vet'
                ? 'Veterinarian'
                : 'Pet Owner';

            return back()->withErrors([
                'email' => 'This account is not registered as a ' . $accountType . '.',
            ]);
        }

        // Prevent unapproved vets and groomers from logging in.
        if ($user->role === 'vet') {
            $approvalStatus = $user->vetProfile()->value('approval_status');

            if ($approvalStatus !== 'approved') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Your staff application is awaiting administrator approval or has not been approved.',
                ]);
            }
        }

        return redirect()->route($user->dashboardRouteName());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
