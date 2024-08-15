<?php

namespace wtg\IpCountryDetector\Services\Interfaces;

use Illuminate\Http\JsonResponse;

interface ErrorHandlerInterface
{
    public function handle(\Exception $exception): JsonResponse;
}