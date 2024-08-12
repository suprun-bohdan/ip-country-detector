<?php

namespace wtg\IpCountryDetector;

use Illuminate\Support\ServiceProvider;
use wtg\IpCountryDetector\Console\InstallIpCountryDetectorCommand;

class IpCountryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ipcountry.php', 'ipcountry');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/ipcountry.php' => config_path('ipcountry.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallIpCountryDetectorCommand::class,
            ]);
        }
    }
}
