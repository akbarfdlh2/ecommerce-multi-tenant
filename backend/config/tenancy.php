<?php

return [
    /*
     |---------------------------------------------------------------
     | Tenant identification strategy
     | Options: 'header', 'subdomain', 'domain'
     |---------------------------------------------------------------
     */
    'identification' => env('TENANT_IDENTIFICATION', 'header'),

    /*
     | HTTP header name used when identification = 'header'
     */
    'header' => env('TENANT_HEADER', 'X-Tenant-Domain'),

    /*
     | Central (non-tenant) connection name
     */
    'central_connection' => 'mongodb_central',

    /*
     | Tenant connection name (switched per request)
     */
    'tenant_connection' => 'mongodb_tenant',

    /*
     | Database name prefix for tenant databases
     | e.g. tenant_ → tenant_mystore
     */
    'db_prefix' => env('TENANT_DB_PREFIX', 'tenant_'),

    /*
     | Cache TTL in seconds for tenant lookups
     */
    'cache_ttl' => env('TENANT_CACHE_TTL', 300),
];
