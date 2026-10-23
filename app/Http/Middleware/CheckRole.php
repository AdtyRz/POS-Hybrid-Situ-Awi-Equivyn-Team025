<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $inginJson = $request->expectsJson() || $request->is('api/*');

        if (! $request->user()) {
            if ($inginJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles, true)) {
            if ($inginJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses.',
                ], 403);
            }

            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
