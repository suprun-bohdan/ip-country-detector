<?php

namespace IpCountryDetector\Services\Interfaces;

interface CacheServiceInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, ?int $ttl = null): void;
    public function delete(string $key): void;
    public function has(string $key): bool;
} 