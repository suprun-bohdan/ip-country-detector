<?php

namespace IpCountryDetector\Services;

use Predis\Client;

class RedisCacheService
{
    private Client $client;
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct()
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => config('ipcountry.redis.host'),
            'port'   => config('ipcountry.redis.port'),
            'password' => config('ipcountry.redis.password'),
            'database' => config('ipcountry.redis.database'),
        ]);
    }

    public function get(string $key): ?string
    {
        return $this->client->get($key);
    }

    public function set(string $key, string $value, int $ttl = self::CACHE_TTL): void
    {
        $this->client->setex($key, $ttl, $value);
    }

    public function delete(string $key): void
    {
        $this->client->del([$key]);
    }
}
