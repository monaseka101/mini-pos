<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()->active) {
            Auth::logout();

            // Redirect to login with error message
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['email' => 'Your account is not active. Please contact administrator.']);
        }

        return $next($request);
    }
}
