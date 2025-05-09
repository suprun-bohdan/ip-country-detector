<?php

namespace IpCountryDetector\Tests\Unit\Services;

use IpCountryDetector\Services\IPCacheService;
use IpCountryDetector\Tests\TestCase;
use Illuminate\Support\Facades\Log;

class IPCacheServiceTest extends TestCase
{
    private IPCacheService $ipCacheService;
    private $redisService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->redisService = Mockery::mock(\IpCountryDetector\Services\Drivers\RedisCacheService::class);
        $this->ipCacheService = new IPCacheService($this->redisService);
    }

    public function testGetReturnsCachedValue()
    {
        $key = 'test-key';
        $expectedValue = 'test-value';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('get')
            ->with($formattedKey)
            ->once()
            ->andReturn($expectedValue);

        $result = $this->ipCacheService->get($key);
        $this->assertEquals($expectedValue, $result);
    }

    public function testGetReturnsNullOnError()
    {
        $key = 'test-key';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('get')
            ->with($formattedKey)
            ->once()
            ->andThrow(new \Exception('Redis error'));

        Log::shouldReceive('warning')
            ->once()
            ->with('Cache Get Error', [
                'key' => $key,
                'error' => 'Redis error'
            ]);

        $result = $this->ipCacheService->get($key);
        $this->assertNull($result);
    }

    public function testSetStoresValueWithDefaultTtl()
    {
        $key = 'test-key';
        $value = 'test-value';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('set')
            ->with($formattedKey, $value, 86400)
            ->once();

        $this->ipCacheService->set($key, $value);
    }

    public function testSetStoresValueWithCustomTtl()
    {
        $key = 'test-key';
        $value = 'test-value';
        $ttl = 3600;
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('set')
            ->with($formattedKey, $value, $ttl)
            ->once();

        $this->ipCacheService->set($key, $value, $ttl);
    }

    public function testSetHandlesError()
    {
        $key = 'test-key';
        $value = 'test-value';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('set')
            ->with($formattedKey, $value, 86400)
            ->once()
            ->andThrow(new \Exception('Redis error'));

        Log::shouldReceive('warning')
            ->once()
            ->with('Cache Set Error', [
                'key' => $key,
                'error' => 'Redis error'
            ]);

        $this->ipCacheService->set($key, $value);
    }

    public function testDeleteRemovesValue()
    {
        $key = 'test-key';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('delete')
            ->with($formattedKey)
            ->once();

        $this->ipCacheService->delete($key);
    }

    public function testDeleteHandlesError()
    {
        $key = 'test-key';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('delete')
            ->with($formattedKey)
            ->once()
            ->andThrow(new \Exception('Redis error'));

        Log::shouldReceive('warning')
            ->once()
            ->with('Cache Delete Error', [
                'key' => $key,
                'error' => 'Redis error'
            ]);

        $this->ipCacheService->delete($key);
    }

    public function testHasChecksExistence()
    {
        $key = 'test-key';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('has')
            ->with($formattedKey)
            ->once()
            ->andReturn(true);

        $result = $this->ipCacheService->has($key);
        $this->assertTrue($result);
    }

    public function testHasHandlesError()
    {
        $key = 'test-key';
        $formattedKey = 'test_ip_country::' . $key;

        $this->redisService
            ->shouldReceive('has')
            ->with($formattedKey)
            ->once()
            ->andThrow(new \Exception('Redis error'));

        Log::shouldReceive('warning')
            ->once()
            ->with('Cache Has Error', [
                'key' => $key,
                'error' => 'Redis error'
            ]);

        $result = $this->ipCacheService->has($key);
        $this->assertFalse($result);
    }
} 