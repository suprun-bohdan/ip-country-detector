<?php

namespace IpCountryDetector;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use IpCountryDetector\Console\InstallIpCountryDetectorCommand;
use IpCountryDetector\Database\Seeders\IpCountrySeeder;
use IpCountryDetector\Services\Interfaces\IpCountryServiceInterface;
use IpCountryDetector\Services\IpApiService;

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

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallIpCountryDetectorCommand::class,
            ]);

            $this->loadSeeds();
        }

        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
    }

    /**
     * @throws BindingResolutionException
     */
    protected function loadSeeds(): void
    {
        $this->app->make('Illuminate\Database\Seeder')->call(IpCountrySeeder::class);
    }
}
