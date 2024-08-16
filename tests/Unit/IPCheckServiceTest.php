<?php

namespace IpCountryDetector\Tests\Unit;

use IpCountryDetector\Services\IPCacheService;
use IpCountryDetector\Services\IPCheckService;
use IpCountryDetector\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class IPCheckServiceTest extends TestCase
{
    /** @test
     * @throws \Exception
     */
    public function it_returns_country_from_cache_if_available()
    {
        $cacheService = $this->mock(IPCacheService::class);
        $cacheService->shouldReceive('getCountryFromCache')
            ->with('123.123.123.123')
            ->andReturn('Ukraine');

        $ipCheckService = new IPCheckService($cacheService);

        $country = $ipCheckService->ipToCountry('123.123.123.123');

        $this->assertEquals('Ukraine', $country);
    }

    /** @test
     * @throws \Exception
     */
    public function it_finds_country_by_ip_in_database()
    {
        $cacheService = $this->mock(IPCacheService::class);
        $cacheService->shouldReceive('getCountryFromCache')->andReturn(null);
        $cacheService->shouldReceive('setCountryToCache');

        DB::shouldReceive('table->where->where->select->first')
            ->andReturn((object) ['country' => 'USA']);

        $ipCheckService = new IPCheckService($cacheService);

        $country = $ipCheckService->ipToCountry('8.8.8.8');

        $this->assertEquals('USA', $country);
    }

    /** @test */
    public function it_fetches_country_from_api_if_not_in_cache_or_database()
    {
        $cacheService = $this->mock(IPCacheService::class);
        $cacheService->shouldReceive('getCountryFromCache')->andReturn(null);
        $cacheService->shouldReceive('setCountryToCache');

        DB::shouldReceive('table->where->where->select->first')
            ->andReturn(null);

        Http::fake([
            'https://ip-api.com/*' => Http::response(['country' => 'Germany'], 200),
        ]);

        $ipCheckService = new IPCheckService($cacheService);

        $country = $ipCheckService->ipToCountry('8.8.8.8');

        $this->assertEquals('Germany', $country);
    }
}
