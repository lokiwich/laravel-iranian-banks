<?php

namespace Lokiwich\IranianBanks;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Lokiwich\IranianBanks\Commands\InstallCommand;
use Lokiwich\IranianBanks\Services\BankDetector;

class IranianBanksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/iranian-banks.php',
            'iranian-banks'
        );

        $this->app->singleton(BankDetector::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'iranian-banks');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'iranian-banks');

        Blade::componentNamespace('Lokiwich\\IranianBanks\\View\\Components', 'iranian-banks');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/iranian-banks.php' => config_path('iranian-banks.php'),
            ], 'iranian-banks-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'iranian-banks-migrations');

            $this->publishes([
                __DIR__.'/../resources/svg' => public_path('vendor/iranian-banks'),
            ], 'iranian-banks-assets');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/iranian-banks'),
            ], 'iranian-banks-lang');
        }
    }
}
