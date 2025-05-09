<?php

namespace IpCountryDetector\Tests\Unit\Services;

use IpCountryDetector\Services\IPCheckService;
use IpCountryDetector\Tests\TestCase;
use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\DB;

class IPCheckServiceTest extends TestCase
{
    private IPCheckService $ipCheckService;
    private $cacheService;
    private $ipApiService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cacheService = $this->mockCacheService();
        $this->ipApiService = $this->mockIpApiService();
        $this->ipCheckService = new IPCheckService($this->cacheService, $this->ipApiService);
    }

    public function testGetCountryByIpReturnsCachedValue()
    {
        $ipAddress = '192.168.1.1';
        $expectedCountry = 'US';
        $cacheKey = 'test_ip_country::' . $ipAddress;

        $this->cacheService
            ->shouldReceive('get')
            ->with($cacheKey)
            ->once()
            ->andReturn($expectedCountry);

        $result = $this->ipCheckService->getCountryByIp($ipAddress);
        $this->assertEquals($expectedCountry, $result);
    }

    public function testGetCountryByIpFindsInDatabase()
    {
        $ipAddress = '192.168.1.1';
        $expectedCountry = 'US';
        $ipLong = ip2long($ipAddress);
        $cacheKey = 'test_ip_country::' . $ipAddress;

        $this->cacheService
            ->shouldReceive('get')
            ->with($cacheKey)
            ->once()
            ->andReturn(null);

        DB::shouldReceive('table')
            ->with('ip_country')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('first_ip', '<=', $ipLong)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('last_ip', '>=', $ipLong)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('value')
            ->with('country')
            ->once()
            ->andReturn($expectedCountry);

        $this->cacheService
            ->shouldReceive('set')
            ->with($cacheKey, $expectedCountry, 86400)
            ->once();

        $result = $this->ipCheckService->getCountryByIp($ipAddress);
        $this->assertEquals($expectedCountry, $result);
    }

    public function testGetCountryByIpFallsBackToApi()
    {
        $ipAddress = '192.168.1.1';
        $expectedCountry = 'US';
        $cacheKey = 'test_ip_country::' . $ipAddress;

        $this->cacheService
            ->shouldReceive('get')
            ->with($cacheKey)
            ->once()
            ->andReturn(null);

        DB::shouldReceive('table')
            ->with('ip_country')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('value')
            ->andReturn(null);

        $this->ipApiService
            ->shouldReceive('getCountry')
            ->with($ipAddress)
            ->once()
            ->andReturn($expectedCountry);

        $this->cacheService
            ->shouldReceive('set')
            ->with($cacheKey, $expectedCountry, 86400)
            ->once();

        $result = $this->ipCheckService->getCountryByIp($ipAddress);
        $this->assertEquals($expectedCountry, $result);
    }

    public function testGetCountryByIpThrowsExceptionForInvalidIp()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->ipCheckService->getCountryByIp('invalid-ip');
    }

    public function testGetCountryByIpThrowsExceptionWhenCountryNotFound()
    {
        $ipAddress = '192.168.1.1';
        $cacheKey = 'test_ip_country::' . $ipAddress;

        $this->cacheService
            ->shouldReceive('get')
            ->with($cacheKey)
            ->once()
            ->andReturn(null);

        DB::shouldReceive('table')
            ->with('ip_country')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->andReturnSelf()
            ->shouldReceive('value')
            ->andReturn(null);

        $this->ipApiService
            ->shouldReceive('getCountry')
            ->with($ipAddress)
            ->once()
            ->andReturn('Country not found');

        $this->expectException(RuntimeException::class);
        $this->ipCheckService->getCountryByIp($ipAddress);
    }

    public function testGetCountryByTimezone()
    {
        $timezone = 'America/New_York';
        $result = $this->ipCheckService->getCountryByTimezone($timezone);
        $this->assertEquals('US', $result);
    }

    public function testGetCountryByTimezoneReturnsNullForInvalidTimezone()
    {
        $result = $this->ipCheckService->getCountryByTimezone('Invalid/Timezone');
        $this->assertNull($result);
    }

    public function testIsVpnIp()
    {
        $ipAddress = '192.168.1.1';
        $ipInfo = [
            'hostname' => 'vpn.example.com',
            'org' => 'VPN Provider',
            'isp' => 'VPN Service'
        ];

        $this->ipApiService
            ->shouldReceive('getIpInfo')
            ->with($ipAddress)
            ->once()
            ->andReturn($ipInfo);

        $result = $this->ipCheckService->isVpnIp($ipAddress);
        $this->assertTrue($result);
    }

    public function testIsVpnIpReturnsFalseForNonVpnIp()
    {
        $ipAddress = '192.168.1.1';
        $ipInfo = [
            'hostname' => 'regular.example.com',
            'org' => 'Regular ISP',
            'isp' => 'Regular Service'
        ];

        $this->ipApiService
            ->shouldReceive('getIpInfo')
            ->with($ipAddress)
            ->once()
            ->andReturn($ipInfo);

        $result = $this->ipCheckService->isVpnIp($ipAddress);
        $this->assertFalse($result);
    }

    public function testValidateIpAddress()
    {
        $this->assertTrue($this->ipCheckService->validateIpAddress('192.168.1.1'));
        $this->assertTrue($this->ipCheckService->validateIpAddress('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
        $this->assertFalse($this->ipCheckService->validateIpAddress('invalid-ip'));
        $this->assertFalse($this->ipCheckService->validateIpAddress('256.256.256.256'));
    }
} 