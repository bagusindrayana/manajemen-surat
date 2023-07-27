<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //force https
        if (env('APP_ENV') === 'production') {
            \URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();
    }
}
