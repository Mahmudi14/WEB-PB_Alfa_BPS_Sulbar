<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordSecurity
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = $request->user();

        $sessionVersion = $request->session()
            ->get('pb_alfa_password_version');

        $currentVersion = (int) $user->password_version;

        // Kompatibilitas sesi lama

        if ($sessionVersion === null && $currentVersion === 0) {
            $request->session()->put(
                'pb_alfa_password_version',
                0
            );

            $sessionVersion = 0;
        }

        // Putuskan sesi dengan password lama

        if (
            $sessionVersion === null
            || (int) $sessionVersion !== $currentVersion
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Password akun telah diperbarui. Silakan login kembali.',
                ]);
        }

        // Wajib ganti password sementara

        if (
            $user->role === 'pengelola'
            && $user->must_change_password
            && !$request->routeIs(
                'profile.password.form',
                'profile.password.update',
                'logout'
            )
        ) {
            return redirect()->route('profile.password.form');
        }

        return $next($request);
    }
}
