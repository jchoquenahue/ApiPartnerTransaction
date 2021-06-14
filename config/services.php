<?php

return [
    'token' => [
        'timeout' => env('TOKEN_EXPIRE_AT'),
    ],
    'permit' => [
        'timeout' => env('PERMITS_EXPIRE_AT'),
    ],
    'pda' => [
        'base_uri' => env('PDA_SERVICE_BASE_URL'),
        'secret' => env('PDA_SERVICE_SECRET'),
        'hash' => env('PDA_SERVICE_HASH'),
        'timeout' => env('PDA_SERVICE_TIMEOUT'),
        'partner_name'=>env('PARTNER_NAME'),
    ],
    'corek' => [
        'base_uri' => env('CK_SERVICE_BASE_URL'),
        'secret' => env('CK_SERVICE_SECRET'),
        'hash' => env('CK_SERVICE_HASH'),
        'timeout' => env('CK_SERVICE_TIMEOUT'),
    ],
    'partner' => [
        'base_uri' => env('PERTNER_SERVICE_BASE_URL'),
        'secret' => env('PERTNER_SERVICE_SECRET'),
        'hash' => env('PERTNER_SERVICE_HASH'),
        'timeout' => env('PERTNER_SERVICE_TIMEOUT'),
        'webhook' => env('PERTNER_WEBHOOK'),

    ],

];
