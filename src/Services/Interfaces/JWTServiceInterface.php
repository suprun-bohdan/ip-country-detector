<?php

namespace wtg\IpCountryDetector\Services\Interfaces;

interface JWTServiceInterface
{
    public function parseToken(array $jwt): array;
}