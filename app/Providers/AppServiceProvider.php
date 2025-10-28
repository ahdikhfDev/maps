<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS di production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            
            // Set trusted proxies untuk Vercel
            Request::setTrustedProxies(
                ['0.0.0.0/0'], 
                \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL
            );
        }

        // Handle proxy headers
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $this->app['request']->server->set('HTTPS', 'on');
            $_SERVER['HTTPS'] = 'on';
        }
    }
}