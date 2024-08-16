<?php

namespace wtg\IpCountryDetector\Services;

use Illuminate\Support\Facades\Cache;

class IPCacheService
{
    private const CACHE_TTL = 86400; //24h

    public function getCountryFromCache(string $ipAddress): ?string
    {
        return Cache::get($this->getCacheKey($ipAddress));
    }

    public function setCountryToCache(string $ipAddress, string $country): void
    {
        Cache::put($this->getCacheKey($ipAddress), $country, self::CACHE_TTL);
    }

    private function getCacheKey(string $ipAddress): string
    {
        return config('ipcountry.redis_prefix') . '::' . $ipAddress;
    }
}
