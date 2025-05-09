<?php

namespace IpCountryDetector\Services\Interfaces;

interface IPCountryServiceInterface
{
    public function getCountryByIp(string $ipAddress): string;
    public function getCountryByTimezone(string $timezone): ?string;
    public function isVpnIp(string $ipAddress): bool;
    public function validateIpAddress(string $ipAddress): bool;
}