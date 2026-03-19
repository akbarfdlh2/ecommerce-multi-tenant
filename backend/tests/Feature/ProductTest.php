<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\User;
use App\Models\Tenant\Product;

class ProductTest extends TestCase
{
    protected string $adminToken;
    protected string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => 'password123', 'role' => 'admin',
        ]);
        $this->adminToken = $admin->createToken('test')->plainTextToken;

        $customer = User::create([
            'name' => 'Customer', 'email' => 'customer@test.com',
            'password' => 'password123', 'role' => 'customer',
        ]);
        $this->customerToken = $customer->createToken('test')->plainTextToken;
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->tenantRequest('POST', '/api/products', [
            'name'        => 'Produk Test',
            'price'       => 100000,
            'stock'       => 10,
            'category'    => 'Elektronik',
            'description' => 'Deskripsi produk test',
        ], $this->adminToken);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Produk Test');
    }

    public function test_customer_cannot_create_product(): void
    {
        $response = $this->tenantRequest('POST', '/api/products', [
            'name'  => 'Produk Haram',
            'price' => 50000,
            'stock' => 5,
            'category' => 'Test',
        ], $this->customerToken);

        $response->assertStatus(403);
    }

    public function test_can_list_products(): void
    {
        Product::create([
            'name' => 'Produk List', 'price' => 50000,
            'stock' => 10, 'category' => 'Test',
            'slug' => 'produk-list', 'is_active' => true,
        ]);

        $response = $this->tenantRequest('GET', '/api/products', [], $this->customerToken);

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'per_page']);
    }

    public function test_can_get_single_product(): void
    {
        $product = Product::create([
            'name' => 'Produk Detail', 'price' => 75000,
            'stock' => 5, 'category' => 'Test',
            'slug' => 'produk-detail', 'is_active' => true,
        ]);

        $response = $this->tenantRequest('GET', "/api/products/{$product->_id}", [], $this->customerToken);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Produk Detail');
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::create([
            'name' => 'Old Name', 'price' => 100000,
            'stock' => 10, 'category' => 'Test',
            'slug' => 'old-name', 'is_active' => true,
        ]);

        $response = $this->tenantRequest('PUT', "/api/products/{$product->_id}", [
            'name' => 'New Name', 'price' => 120000,
        ], $this->adminToken);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'New Name');
    }

    public function test_admin_can_delete_product(): void
    {
        $product = Product::create([
            'name' => 'Delete Me', 'price' => 50000,
            'stock' => 5, 'category' => 'Test',
            'slug' => 'delete-me', 'is_active' => true,
        ]);

        $response = $this->tenantRequest('DELETE', "/api/products/{$product->_id}", [], $this->adminToken);
        $response->assertStatus(200);

        $this->assertFalse(Product::active()->find($product->_id) !== null);
    }

    public function test_products_are_isolated_between_tenants(): void
    {
        // Create a product in tenant A
        Product::create([
            'name' => 'Tenant A Product', 'price' => 10000,
            'stock' => 5, 'category' => 'Test',
            'slug' => 'tenant-a-product', 'is_active' => true,
        ]);

        // Switch to a different tenant
        $tenantB = $this->setUpTenant([
            'name'          => 'Tenant B',
            'slug'          => 'tenant-b-test',
            'domain'        => 'tenant-b.localhost',
            'database_name' => 'tenant_b_test',
            'owner_email'   => 'b@test.com',
        ]);

        // Query products — should be empty (different database)
        $this->assertEquals(0, Product::count());
    }
}
