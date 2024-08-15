<?php

namespace wtg\IpCountryDetector\Services;

use Exception;
use Illuminate\Http\JsonResponse;
use wtg\IpCountryDetector\Services\Interfaces;

class ErrorHandlerService implements Interfaces\ErrorHandlerInterface
{
    public function handle(Exception $exception): JsonResponse
    {
        return response()->json(['message' => $exception->getMessage()], 401);
    }
}