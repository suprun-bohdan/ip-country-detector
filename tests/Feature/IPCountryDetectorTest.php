<?php

namespace IpCountryDetector\Tests\Feature;

use IpCountryDetector\Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

class IPCountryDetectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Route::get('/test-ip', function () {
            return app()->make(\IpCountryDetector\Services\IPCheckService::class)
                ->getCountryByIp(request()->ip());
        });

        Route::get('/test-vpn', function () {
            return app()->make(\IpCountryDetector\Services\IPCheckService::class)
                ->isVpnIp(request()->ip());
        });

        Route::get('/test-timezone', function () {
            return app()->make(\IpCountryDetector\Services\IPCheckService::class)
                ->getCountryByTimezone(request()->input('timezone'));
        });
    }

    public function testServiceProviderRegistersServices()
    {
        $this->assertTrue(app()->bound(\IpCountryDetector\Services\IPCheckService::class));
        $this->assertTrue(app()->bound(\IpCountryDetector\Services\Interfaces\CacheServiceInterface::class));
    }

    public function testConfigurationIsPublished()
    {
        $this->assertTrue(Config::has('ipcountry'));
        $this->assertEquals('test_ip_country', Config::get('ipcountry.redis.prefix'));
        $this->assertEquals(3600, Config::get('ipcountry.redis.ttl'));
    }

    public function testGetCountryByIpEndpoint()
    {
        $response = $this->get('/test-ip');
        $response->assertStatus(200);
        $response->assertJsonStructure(['country']);
    }

    public function testGetVpnStatusEndpoint()
    {
        $response = $this->get('/test-vpn');
        $response->assertStatus(200);
        $response->assertJsonStructure(['is_vpn']);
    }

    public function testGetCountryByTimezoneEndpoint()
    {
        $response = $this->get('/test-timezone?timezone=America/New_York');
        $response->assertStatus(200);
        $response->assertJsonStructure(['country']);
    }

    public function testInvalidTimezoneReturnsNull()
    {
        $response = $this->get('/test-timezone?timezone=Invalid/Timezone');
        $response->assertStatus(200);
        $response->assertJson(['country' => null]);
    }

    public function testServiceContainerBindings()
    {
        $this->assertInstanceOf(
            \IpCountryDetector\Services\IPCheckService::class,
            app()->make(\IpCountryDetector\Services\IPCheckService::class)
        );

        $this->assertInstanceOf(
            \IpCountryDetector\Services\Interfaces\CacheServiceInterface::class,
            app()->make(\IpCountryDetector\Services\Interfaces\CacheServiceInterface::class)
        );
    }

    public function testDatabaseMigration()
    {
        $this->artisan('migrate');
        
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasTable('ip_country')
        );

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('ip_country');
        $this->assertContains('first_ip', $columns);
        $this->assertContains('last_ip', $columns);
        $this->assertContains('country', $columns);
    }
} 