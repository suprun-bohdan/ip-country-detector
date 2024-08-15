<?php

namespace wtg\IpCountryDetector\Services\Interfaces;

use Illuminate\Http\JsonResponse;

interface IpCountryServiceInterface
{
    public function getCountry(string $ipAddress): string;
}