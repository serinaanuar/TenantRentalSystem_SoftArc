<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;
use Inertia\Inertia;
use App\Payments\PaymentUI;
use App\Payments\PaymentPage;
use App\Payments\PaymentPageAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
{
    $this->app->bind(PaymentUI::class, function ($app) {
        return new PaymentPageAdapter(new PaymentPage());
    });
}

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url)
    {
        Inertia::share('csrf_token', csrf_token());
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
