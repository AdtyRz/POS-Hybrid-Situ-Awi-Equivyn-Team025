<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;






class AdminAjaxResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->perluDiubah($request, $response)) {
            return $response;
        }

        $session = $request->session();
        $pesanSukses = $session->pull('sukses') ?? $session->pull('success');
        $pesanGalat = $session->pull('error');
        $galatValidasi = $session->pull('errors');

        if ($galatValidasi instanceof ViewErrorBag && $galatValidasi->any()) {
            return $this->responGalat(
                $session,
                $galatValidasi->getBag('default')->getMessages()
            );
        }

        if ($galatValidasi instanceof MessageBag && $galatValidasi->any()) {
            return $this->responGalat($session, $galatValidasi->getMessages());
        }

        if (filled($pesanGalat)) {
            return $this->responGalat($session, ['umum' => [(string) $pesanGalat]]);
        }

        return response()
            ->json([
                'success' => true,
                'message' => (string) ($pesanSukses ?: 'Perubahan berhasil disimpan.'),
                'redirect' => $response->getTargetUrl(),
            ])
            ->header('Cache-Control', 'no-store, private, max-age=0');
    }

    private function perluDiubah(Request $request, Response $response): bool
    {
        return $response instanceof RedirectResponse
            && $request->header('X-Admin-Ajax') === '1'
            && $request->expectsJson()
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function responGalat($session, array $errors): Response
    {
        $session->forget('_old_input');

        $pesan = collect($errors)->flatten()->first() ?: 'Periksa kembali isian Anda.';

        return response()
            ->json([
                'success' => false,
                'message' => (string) $pesan,
                'errors' => $errors,
            ], 422)
            ->header('Cache-Control', 'no-store, private, max-age=0');
    }
}
