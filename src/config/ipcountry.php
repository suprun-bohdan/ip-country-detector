<?php

use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;

return [
    'auth_key' => env('IPCOUNTRY_AUTH_KEY', 'client_auth_key_123'),
    'auth_enabled' => env('IPCOUNTRY_AUTH_ENABLED', false),
    'route' => '/ip-country',
    'middleware' => [IpAuthorization::class],
    'secret' => env('JWT_SECRET', 'jwtsecret'),
    'keys' => [
        'public' => env('IPCOUNTRY_JWT_PUBLIC_KEY_PATH', storage_path('oauth-public.key')),
        'private' => env('IPCOUNTRY_JWT_PRIVATE_KEY_PATH', storage_path('oauth-private.key')),
    ],
    'redis' => [
        'host' => env('IPCOUNTRY_REDIS_HOST', '127.0.0.1'),
        'port' => env('IPCOUNTRY_REDIS_PORT', 6379),
        'database' => 0,
        'password' => null,
        'prefix' => env('IPCOUNTRY_REDIS_PREFIX', 'ip_country'),
    ],
    'ttl' => 60,
    'algo' => 'RS256',
];
