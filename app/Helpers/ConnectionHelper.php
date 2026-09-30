<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Exception;

class ConnectionHelper
{
    /**
     * Memeriksa status koneksi ke database Supabase
     */
    public static function checkDatabaseConnection(): array
    {
        $startTime = microtime(true);
        try {
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'connected',
                'database' => DB::connection()->getDatabaseName(),
                'latency_ms' => $latency,
                'message' => 'Koneksi ke Supabase PostgreSQL Berhasil'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'disconnected',
                'database' => config('database.connections.pgsql.database'),
                'error' => $e->getMessage(),
                'message' => 'Gagal terhubung ke Supabase Database'
            ];
        }
    }
}