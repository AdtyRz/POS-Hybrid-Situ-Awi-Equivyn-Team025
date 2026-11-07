<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKasirUnlocked
{




    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'kasir' && $request->session()->get('kasir_locked')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'locked' => true,
                    'message' => 'Terminal kasir sedang terkunci.',
                ], 423);
            }

            return redirect()->route('kasir.locked');
        }

        return $next($request);
    }
}
