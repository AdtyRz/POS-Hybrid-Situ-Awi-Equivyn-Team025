<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyIotToken
{
    /**
     * Semua endpoint perangkat IoT wajib membawa token server yang sama.
     * Token tidak boleh ditaruh di URL agar tidak tercatat di access log.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tokenServer = trim((string) config('services.iot.token'));

        if ($tokenServer === '') {
            return response()->json([
                'success' => false,
                'message' => 'Integrasi IoT belum dikonfigurasi. Atur IOT_API_TOKEN di server.',
            ], 503);
        }

        $tokenDikirim = trim((string) (
            $request->header('X-IoT-Token')
            ?: $request->bearerToken()
            ?: ''
        ));

        if ($tokenDikirim === '' || ! hash_equals($tokenServer, $tokenDikirim)) {
            return response()->json([
                'success' => false,
                'message' => 'Token IoT tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
