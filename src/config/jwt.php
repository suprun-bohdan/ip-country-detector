<?php

return [
    'secret' => env('JWT_SECRET', 'jwtsecret'),
    'keys' => [
        'public' => env('IPCOUNTRY_JWT_PUBLIC_KEY_PATH', storage_path('oauth-public.key')),
        'private' => env('IPCOUNTRY_JWT_PRIVATE_KEY_PATH', storage_path('oauth-private.key')),
    ],
    'ttl' => 60,
    'algo' => 'RS256',
];