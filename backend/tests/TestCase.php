<?php

namespace Tests;

use App\Models\Central\Tenant;
use App\Services\TenantDatabaseManager;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected ?Tenant $currentTenant = null;

    /**
     * Set up a test tenant and switch the database connection to it.
     */
    protected function setUpTenant(array $overrides = []): Tenant
    {
        $dbManager = app(TenantDatabaseManager::class);

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'test-tenant'],
            array_merge([
                'name'          => 'Test Tenant',
                'slug'          => 'test-tenant',
                'domain'        => 'test-tenant.localhost',
                'database_name' => 'tenant_test',
                'owner_email'   => 'test@example.com',
                'plan'          => 'free',
                'is_active'     => true,
            ], $overrides)
        );

        $dbManager->initialize($tenant);
        $this->currentTenant = $tenant;

        return $tenant;
    }

    /**
     * Make an API request with tenant header pre-set.
     */
    protected function tenantRequest(string $method, string $uri, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        $headers = [
            'X-Tenant-Domain' => $this->currentTenant?->domain ?? 'test-tenant.localhost',
            'Accept'          => 'application/json',
        ];

        if ($token) {
            $headers['Authorization'] = "Bearer $token";
        }

        return $this->withHeaders($headers)->json($method, $uri, $data);
    }

    protected function tearDown(): void
    {
        // Clean up test tenant database after tests
        if ($this->currentTenant) {
            try {
                $mongo = \Illuminate\Support\Facades\DB::connection('mongodb_tenant')->getMongoClient();
                $mongo->dropDatabase($this->currentTenant->database_name);
            } catch (\Throwable) {
                // ignore
            }
        }
        parent::tearDown();
    }
}
