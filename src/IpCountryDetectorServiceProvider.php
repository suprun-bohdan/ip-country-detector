<?php

namespace IpCountryDetector;

use Illuminate\Support\ServiceProvider;
use IpCountryDetector\Http\Controllers\IPCheckController;
use IpCountryDetector\Services\IPCheckService;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use IpCountryDetector\Console\InstallIpCountryDetectorCommand;
use IpCountryDetector\Http\Middleware\IpAuthorization;
use IpCountryDetector\Services\Interfaces\ErrorHandlerInterface;
use IpCountryDetector\Services\Interfaces\IpCountryServiceInterface;
use IpCountryDetector\Services\Interfaces\JWTServiceInterface;
use IpCountryDetector\Services\IpApiService;
use IpCountryDetector\Services\JWTService;
use IpCountryDetector\Services\ErrorHandlerService;

class IpCountryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/ipcountry.php', 'ipcountry'
        );

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
        $this->app['router']->aliasMiddleware('ip.authorization', IpAuthorization::class);
    }
}
