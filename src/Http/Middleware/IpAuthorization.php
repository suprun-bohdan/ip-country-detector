<?php

namespace wtg\IpCountryDetector\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use wtg\IpCountryDetector\Services\ErrorHandlerService;
use wtg\IpCountryDetector\Services\JWTService;

class IpAuthorization
{
    protected JWTService $jwtService;
    protected ErrorHandlerService $errorHandler;

    public function __construct(JWTService $jwtService, ErrorHandlerService $errorHandler)
    {
        $this->jwtService = $jwtService;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $authEnabled = config('ipcountry.auth_enabled');
        $authKey = config('ipcountry.auth_key');

        if ($authEnabled) {
            if ($request->header('Authorization') !== $authKey) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            try {
                $jwt = $this->extractTokenFromHeader($request);
                $this->jwtService->parseToken($jwt);
            } catch (Exception $e) {
                return $this->errorHandler->handle($e);
            }
        }

        return $next($request);
    }

    /**
     * @throws Exception
     */
    protected function extractTokenFromHeader(Request $request): array|string|null
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new Exception('Token not provided');
        }

        return str_replace('Bearer ', '', $authHeader);
    }
}
