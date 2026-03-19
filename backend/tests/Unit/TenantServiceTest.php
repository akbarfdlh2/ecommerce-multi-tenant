<?php

namespace Tests\Unit;

use App\Models\Central\Tenant;
use App\Services\TenantDatabaseManager;
use App\Services\TenantService;
use Tests\TestCase;

class TenantServiceTest extends TestCase
{
    protected TenantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TenantService::class);
    }

    public function test_can_create_tenant(): void
    {
        $tenant = $this->service->create([
            'name'        => 'Unit Test Store',
            'owner_email' => 'unit@test.com',
            'plan'        => 'free',
        ]);

        $this->assertInstanceOf(Tenant::class, $tenant);
        $this->assertEquals('unit-test-store', $tenant->slug);
        $this->assertStringStartsWith(config('tenancy.db_prefix'), $tenant->database_name);
    }

    public function test_slug_is_unique(): void
    {
        $this->service->create(['name' => 'Same Name', 'owner_email' => 'a@test.com']);
        $second = $this->service->create(['name' => 'Same Name', 'owner_email' => 'b@test.com']);

        $this->assertEquals('same-name-1', $second->slug);
    }

    public function test_can_find_tenant_by_domain(): void
    {
        $tenant = $this->service->create([
            'name'        => 'Domain Test',
            'owner_email' => 'domain@test.com',
            'domain'      => 'myshop.localhost',
        ]);

        $found = $this->service->findByDomain('myshop.localhost');

        $this->assertNotNull($found);
        $this->assertEquals($tenant->slug, $found->slug);
    }

    public function test_returns_null_for_unknown_domain(): void
    {
        $result = $this->service->findByDomain('unknown.example.com');
        $this->assertNull($result);
    }

    public function test_database_switch_updates_connection(): void
    {
        $tenant = Tenant::make([
            'slug'          => 'switch-test',
            'database_name' => 'tenant_switch_test',
        ]);

        $manager = app(TenantDatabaseManager::class);
        $manager->switchTo($tenant->database_name);

        $this->assertEquals(
            'tenant_switch_test',
            config('database.connections.mongodb_tenant.database')
        );
    }
}
