<?php

namespace YourName\PriceFormatter;

use Illuminate\Support\ServiceProvider;

class PriceFormatterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('price-formatter', function ($app) {
            return new PriceFormatter();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/price-formatter.php', 'price-formatter'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/price-formatter.php' => config_path('price-formatter.php'),
        ], 'config');
    }
}
