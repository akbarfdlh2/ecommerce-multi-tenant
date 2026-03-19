<?php

return [
    'default' => 'mongodb_central',

    'connections' => [
        /*
         |---------------------------------------------------------------
         | Central Database — stores tenant registry
         |---------------------------------------------------------------
         */
        'mongodb_central' => [
            'driver'   => 'mongodb',
            'host'     => env('MONGODB_HOST', 'mongodb'),
            'port'     => (int) env('MONGODB_PORT', 27017),
            'database' => env('MONGODB_CENTRAL_DB', 'ecommerce_central'),
            'username' => env('MONGODB_USERNAME', ''),
            'password' => env('MONGODB_PASSWORD', ''),
            'options'  => [
                'authSource' => 'admin',
            ],
        ],

        /*
         |---------------------------------------------------------------
         | Tenant Database — switched dynamically per request
         |---------------------------------------------------------------
         */
        'mongodb_tenant' => [
            'driver'   => 'mongodb',
            'host'     => env('MONGODB_HOST', 'mongodb'),
            'port'     => (int) env('MONGODB_PORT', 27017),
            'database' => 'tenant_placeholder', // overwritten by IdentifyTenant middleware
            'username' => env('MONGODB_USERNAME', ''),
            'password' => env('MONGODB_PASSWORD', ''),
            'options'  => [
                'authSource' => 'admin',
            ],
        ],
    ],

    'migrations' => [
        'table'     => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client'  => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'url'      => env('REDIS_URL'),
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],
];
