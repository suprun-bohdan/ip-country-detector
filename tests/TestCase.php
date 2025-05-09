<?php

namespace IpCountryDetector\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Config::set('ipcountry.redis.prefix', 'test_ip_country');
        Config::set('ipcountry.redis.ttl', 3600);
        Config::set('ipcountry.api.timeout', 5);
        Config::set('ipcountry.api.retry_attempts', 3);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function mockCacheService()
    {
        return Mockery::mock(\IpCountryDetector\Services\Interfaces\CacheServiceInterface::class);
    }

    protected function mockIpApiService()
    {
        return Mockery::mock(\IpCountryDetector\Services\IpApiService::class);
    }
}
