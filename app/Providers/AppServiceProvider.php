<?php

namespace App\Providers;

use App\Broadcasting\ReverbBroadcaster;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {

    }

    public function boot(): void
    {
        Broadcast::extend('reverb', function ($app, array $config) {
            return new ReverbBroadcaster($this->pusher($config), $config['jsonp'] ?? false);
        });
    }
}
