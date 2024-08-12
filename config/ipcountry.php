<?php

use wtg\IpCountryDetector\Http\Middleware\IpAuthorization;

return [
    'api_endpoint' => env('IP_COUNTRY_API_ENDPOINT', 'https://api.example.com/get-country'),
    'middleware' => [
        'auth' => IpAuthorization::class,
    ],
    'auth_key' => env('IP_COUNTRY_AUTH_KEY', 'your-auth-key'),
];
