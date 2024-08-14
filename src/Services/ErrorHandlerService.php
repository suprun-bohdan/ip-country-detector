<?php

namespace wtg\IpCountryDetector\Services;

use Exception;
use Illuminate\Http\JsonResponse;

class ErrorHandlerService
{
    public function handle(Exception $exception): JsonResponse
    {
        return response()->json(['message' => $exception->getMessage()], 401);
    }
}