<?php

namespace App\Providers;

use App\Models\Polisies;
use App\Observers\PolisiesObserver;
use Illuminate\Support\ServiceProvider;

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
        Polisies::observe(PolisiesObserver::class);
    }
}
