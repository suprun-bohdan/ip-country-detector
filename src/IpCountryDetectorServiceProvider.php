<?php

namespace IpCountryDetector;

use Illuminate\Support\ServiceProvider;
use IpCountryDetector\Http\Controllers\IPCheckController;
use IpCountryDetector\Services\IPCheckService;
use IpCountryDetector\Console\InstallIpCountryDetectorCommand;
use IpCountryDetector\Services\Interfaces\ErrorHandlerInterface;
use IpCountryDetector\Services\Interfaces\IpCountryServiceInterface;
use IpCountryDetector\Services\IpApiService;
use IpCountryDetector\Services\ErrorHandlerService;

class IpCountryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ipcountry.php', 'ipcountry'
        );

        $this->app->singleton(ErrorHandlerService::class, function ($app) {
            return new ErrorHandlerService();
        });

        $this->app->bind(ErrorHandlerInterface::class, ErrorHandlerService::class);
        $this->app->bind(IpCountryServiceInterface::class, IpApiService::class);

        $this->app->singleton('ip-detector', function ($app) {
            return new IPCheckController($app->make(IPCheckService::class));
        });
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
        }

        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
    }
}
