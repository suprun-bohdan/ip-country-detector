<?php

namespace IpCountryDetector\Services;

use IpCountryDetector\Services\Interfaces\CacheServiceInterface;
use Illuminate\Support\Facades\Log;

class IPCacheService implements CacheServiceInterface
{
    private const DEFAULT_TTL = 86400; // 24 hours

    public function __construct(
        private readonly RedisCacheService $redisService
    ) {}

    public function get(string $key): mixed
    {
        try {
            return $this->redisService->get($this->formatKey($key));
        } catch (\Exception $e) {
            Log::warning('Cache Get Error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        try {
            $this->redisService->set(
                $this->formatKey($key),
                $value,
                $ttl ?? self::DEFAULT_TTL
            );
        } catch (\Exception $e) {
            Log::warning('Cache Set Error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function delete(string $key): void
    {
        try {
            $this->redisService->delete($this->formatKey($key));
        } catch (\Exception $e) {
            Log::warning('Cache Delete Error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function has(string $key): bool
    {
        try {
            return $this->redisService->has($this->formatKey($key));
        } catch (\Exception $e) {
            Log::warning('Cache Has Error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function formatKey(string $key): string
    {
        return config('ipcountry.redis.prefix') . '::' . $key;
    }
}
