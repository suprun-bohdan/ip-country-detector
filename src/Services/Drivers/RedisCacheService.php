<?php

namespace IpCountryDetector\Services\Drivers;

use Predis\Client;

class RedisCacheService
{
    private Client $client;
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct()
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => config('ipcountry.redis.host', '127.0.0.1'),
            'port'   => config('ipcountry.redis.port', 6379),
            'password' => config('ipcountry.redis.password', null),
            'database' => config('ipcountry.redis.database', 0),
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
