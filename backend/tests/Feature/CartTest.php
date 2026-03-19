<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\User;
use App\Models\Tenant\Product;

class CartTest extends TestCase
{
    protected string $token;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();

        $user = User::create([
            'name' => 'Cart User', 'email' => 'cart@test.com',
            'password' => 'password123', 'role' => 'customer',
        ]);
        $this->token = $user->createToken('test')->plainTextToken;

        $this->product = Product::create([
            'name' => 'Cart Product', 'price' => 50000,
            'stock' => 20, 'category' => 'Test',
            'slug' => 'cart-product', 'is_active' => true,
        ]);
    }

    public function test_user_can_view_empty_cart(): void
    {
        $response = $this->tenantRequest('GET', '/api/cart', [], $this->token);

        $response->assertStatus(200)
                 ->assertJsonPath('data.total', 0);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $response = $this->tenantRequest('POST', '/api/cart/items', [
            'product_id' => (string) $this->product->_id,
            'quantity'   => 2,
        ], $this->token);

        $response->assertStatus(200)
                 ->assertJsonPath('data.total', 100000.0);
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $response = $this->tenantRequest('POST', '/api/cart/items', [
            'product_id' => (string) $this->product->_id,
            'quantity'   => 999,
        ], $this->token);

        $response->assertStatus(422);
    }

    public function test_user_can_clear_cart(): void
    {
        // Add item first
        $this->tenantRequest('POST', '/api/cart/items', [
            'product_id' => (string) $this->product->_id,
            'quantity'   => 1,
        ], $this->token);

        $response = $this->tenantRequest('DELETE', '/api/cart', [], $this->token);
        $response->assertStatus(200);

        $cartResponse = $this->tenantRequest('GET', '/api/cart', [], $this->token);
        $cartResponse->assertJsonPath('data.total', 0);
    }
}
