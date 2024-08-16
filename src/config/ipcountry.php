<?php

use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;

return [
    'auth_key' => env('IPCOUNTRY_AUTH_KEY', 'client_auth_key_123'),
    'auth_enabled' => env('IPCOUNTRY_AUTH_ENABLED', true),
    'route' => '/ip-country',
    'middleware' => [IpAuthorization::class],
    'redis_prefix' => env('IPCOUNTRY_REDIS_PREFIX', 'ip_country'),
];
