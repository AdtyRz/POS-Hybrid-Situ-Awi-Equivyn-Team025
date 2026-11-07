<?php

namespace App\Http\Controllers;

use App\Support\Realtime;
use Illuminate\Http\JsonResponse;

class RealtimeController extends Controller
{




    public function version(): JsonResponse
    {
        return response()
            ->json(['version' => Realtime::version()])
            ->header('Cache-Control', 'no-store, max-age=0');
    }
}
