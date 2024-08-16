<?php

namespace IpCountryDetector\Services\Interfaces;

interface JWTServiceInterface
{
    public function parseToken(array $jwt): array;
}