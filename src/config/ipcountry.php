<?php

use IpCountryDetector\Http\Middleware\IpAuthorization;

return [
    'auth_key' => env('IPCOUNTRY_AUTH_KEY', 'client_auth_key_123'),
    'auth_enabled' => env('IPCOUNTRY_AUTH_ENABLED', true),
    'route' => '/ip-country',
    'middleware' => [IpAuthorization::class],
    'redis' => [
        'host' => env('IPCOUNTRY_REDIS_HOST', '127.0.0.1'),
        'port' => env('IPCOUNTRY_REDIS_PORT', 6379),
        'database' => env('IPCOUNTRY_REDIS_DATABASE', 0),
        'password' => env('IPCOUNTRY_REDIS_PASSWORD', 0),
        'prefix' => env('IPCOUNTRY_REDIS_PREFIX', 'ip_country'),
    ],
    'secret' => env('JWT_SECRET', 'jwtsecret'),
    'keys' => [
        'public' => env('IPCOUNTRY_JWT_PUBLIC_KEY_PATH', storage_path('oauth-public.key')),
        'private' => env('IPCOUNTRY_JWT_PRIVATE_KEY_PATH', storage_path('oauth-private.key')),
    ],
    'ttl' => 60,
    'algo' => 'RS256',
];
