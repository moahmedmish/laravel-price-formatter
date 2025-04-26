<?php

namespace MoahmedMish\PriceFormatter;

use Illuminate\Support\ServiceProvider;

class PriceFormatterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/price-formatter.php' => config_path('price-formatter.php'),
        ], 'config');

        // Register Blade directives
        if (class_exists('Blade')) {
            $this->registerBladeDirectives();
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../config/price-formatter.php', 'price-formatter'
        );

        // Register the service
        $this->app->singleton('price-formatter', function ($app) {
            return new PriceFormatter();
        });
    }

    /**
     * Register Blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        \Illuminate\Support\Facades\Blade::directive('money', function ($expression) {
            return "<?php echo app('price-formatter')->format($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('moneyAccounting', function ($expression) {
            return "<?php echo app('price-formatter')->formatAccounting($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('moneyCompact', function ($expression) {
            return "<?php echo app('price-formatter')->formatCompact($expression); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('moneyLocalized', function ($expression) {
            return "<?php echo app('price-formatter')->formatLocalized($expression); ?>";
        });
    }
}
