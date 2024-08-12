<?php

namespace wtg\IpCountryDetector\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IpAuthorization
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $authKey = config('ipcountry.auth_key');

        if ($request->header('Authorization') !== $authKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
