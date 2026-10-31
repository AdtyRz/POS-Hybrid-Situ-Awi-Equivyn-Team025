<?php

namespace App\Http\Middleware;

use App\Services\ShiftService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BatasiShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! ShiftService::bolehAktif($user)) {
            ShiftService::selesaikan($request, $user);

            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akses ditolak: saat ini di luar jam shift operasional yang ditentukan admin.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Sesi berakhir karena jam shift operasional telah selesai. Silakan login kembali pada shift berikutnya.',
                ]);
        }

        ShiftService::catatAktivitas($request, $user);

        return $next($request);
    }
}
