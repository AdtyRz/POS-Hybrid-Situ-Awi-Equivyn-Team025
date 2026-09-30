<?php

namespace App\Http\Controllers;

use App\Helpers\ConnectionHelper;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function index(): JsonResponse
    {
        $dbStatus = ConnectionHelper::checkDatabaseConnection();
        $isHealthy = $dbStatus['status'] === 'connected';

        return response()->json([
            'status' => $isHealthy ? 'OK' : 'ERROR',
            'app_name' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'database' => $dbStatus
        ], $isHealthy ? 200 : 500);
    }
}