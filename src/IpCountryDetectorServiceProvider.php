<?php

namespace wtg\IpCountryDetector;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use wtg\IpCountryDetector\Console\InstallIpCountryDetectorCommand;
use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;

class IpCountryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ipcountry.php', 'ipcountry'
        );
    }

    public function boot(): void
    {

        $this->publishes([
            __DIR__ . '/config/ipcountry.php' => config_path('ipcountry.php'),
        ], 'config');

        Route::middleware(IpAuthorization::class)
            ->namespace('wtg\IpCountryDetector\Http\Controllers')
            ->group(function () {
                Route::get(config('ipcountry.route'), 'IPCheckController@checkIP');
            });

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallIpCountryDetectorCommand::class,
            ]);
        }
    }
}
