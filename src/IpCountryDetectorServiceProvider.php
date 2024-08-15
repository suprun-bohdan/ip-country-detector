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

        $this->app->singleton(Configuration::class, function ($app) {
            $publicKeyPath = config('jwt.keys.public');
            $privateKeyPath = config('jwt.keys.private');

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
