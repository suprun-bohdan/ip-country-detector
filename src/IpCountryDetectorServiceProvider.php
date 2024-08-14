<?php

namespace wtg\IpCountryDetector;

use Illuminate\Support\ServiceProvider;
use wtg\IpCountryDetector\Console\InstallIpCountryDetectorCommand;
use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;
use wtg\IpCountryDetector\Services\JWTService;
use wtg\IpCountryDetector\Services\ErrorHandlerService;

class IpCountryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ipcountry.php', 'ipcountry'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/jwt.php', 'jwt'
        );

        // Реєстрація сервісів в контейнері
        $this->app->singleton(JWTService::class, function ($app) {
            return new JWTService(storage_path('app/keys/public.pem'));
        });

        $this->app->singleton(ErrorHandlerService::class, function ($app) {
            return new ErrorHandlerService();
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/ipcountry.php' => config_path('ipcountry.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/config/jwt.php' => config_path('jwt.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallIpCountryDetectorCommand::class,
            ]);
        }

        $this->app['router']->aliasMiddleware('ip.authorization', IpAuthorization::class);
    }
}
