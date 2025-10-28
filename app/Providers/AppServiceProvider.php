<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            // Force HTTPS di production
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));

            // Set trusted proxies (untuk Vercel dan proxy lain)
            Request::setTrustedProxies(
                ['0.0.0.0/0'],
                SymfonyRequest::HEADER_X_FORWARDED_ALL
            );
        }

        // Deteksi HTTPS dari header proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $this->app['request']->server->set('HTTPS', 'on');
            $_SERVER['HTTPS'] = 'on';
        }
    }
}
