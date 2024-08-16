<?php

namespace IpCountryDetector\Services;

use Exception;
use Illuminate\Http\JsonResponse;

class ErrorHandlerService implements Interfaces\ErrorHandlerInterface
{
    public function handle(Exception $exception): JsonResponse
    {
        return response()->json(['message' => $exception->getMessage()], 401);
    }
}