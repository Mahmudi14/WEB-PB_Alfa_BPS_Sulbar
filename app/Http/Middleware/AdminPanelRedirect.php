<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mempertahankan konteks panel pada redirect dari controller lama.
 * Tidak mengubah query, validasi, atau operasi database controller.
 */
class AdminPanelRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response instanceof RedirectResponse) {
            return $response;
        }

        $target = $response->getTargetUrl();

        // Hanya ubah redirect internal yang berasal dari URL resmi aplikasi.
        $pairs = [
            url('/pemain') => url('/admin/pemain'),
            url('/pertandingan') => url('/admin/pertandingan'),
            url('/turnamen') => url('/admin/turnamen'),
            url('/kas') => url('/admin/keuangan'),
        ];

        foreach ($pairs as $old => $new) {
            if (
                $target === $old ||
                str_starts_with($target, $old . '/') ||
                str_starts_with($target, $old . '?')
            ) {
                $response->setTargetUrl(
                    $new . substr($target, strlen($old))
                );
                break;
            }
        }

        return $response;
    }
}
