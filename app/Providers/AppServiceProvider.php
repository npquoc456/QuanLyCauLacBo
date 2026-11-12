<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Thêm dòng này

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Thêm dòng ép HTTPS này vào
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}