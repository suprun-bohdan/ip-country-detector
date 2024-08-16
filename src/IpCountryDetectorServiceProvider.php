<?php

namespace wtg\IpCountryDetector;

use Illuminate\Support\ServiceProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use wtg\IpCountryDetector\Console\InstallIpCountryDetectorCommand;
use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;
use wtg\IpCountryDetector\Services\Interfaces\ErrorHandlerInterface;
use wtg\IpCountryDetector\Services\Interfaces\IpCountryServiceInterface;
use wtg\IpCountryDetector\Services\Interfaces\JWTServiceInterface;
use wtg\IpCountryDetector\Services\IpApiService;
use wtg\IpCountryDetector\Services\JWTService;
use wtg\IpCountryDetector\Services\ErrorHandlerService;
use wtg\IpCountryDetector\Services\RedisCacheService;

class IpCountryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ipcountry.php', 'ipcountry'
        );

        $this->app->singleton(RedisCacheService::class, function ($app) {
            return new RedisCacheService();
        });

        $this->app->singleton(Configuration::class, function ($app) {
            $publicKeyPath = config('ipcountry.keys.public');
            $privateKeyPath = config('ipcountry.keys.private');

            $publicKey = InMemory::file($publicKeyPath);
            $privateKey = InMemory::file($privateKeyPath);

            return Configuration::forAsymmetricSigner(
                new Sha256(),
                $privateKey,
                $publicKey
            );
        });

        $this->app->singleton(JWTService::class, function ($app) {
            return new JWTService($app->make(Configuration::class));
        });

        $this->app->singleton(ErrorHandlerService::class, function ($app) {
            return new ErrorHandlerService();
        });

        $this->app->bind(JWTServiceInterface::class, JWTService::class);
        $this->app->bind(ErrorHandlerInterface::class, ErrorHandlerService::class);
        $this->app->bind(IpCountryServiceInterface::class, IpApiService::class);
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
        $this->app['router']->aliasMiddleware('ip.authorization', IpAuthorization::class);
    }
}
