<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        $user = $request->user();

        if (!$user->isActive()) {
            Auth::guard('web')->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact administrator.'
                ], 403);
            }
            return back()->withErrors([
                'email' => 'Your account is inactive. Please contact administrator.'
            ]);
        }

        if ($request->expectsJson()) {
            $token = $user->createApiToken();

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'status' => $user->status
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        }

        $request->session()->regenerate();

        if ($user->hasRole('superadmin')) {
            return redirect()->intended(route('superadmin.dashboard', absolute: false));
        } elseif ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        } else {
            return redirect()->intended(route('user.deliveryIndex', absolute: false));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('login');
    }
}
