<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\User;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_user_can_register(): void
    {
        $response = $this->tenantRequest('POST', '/api/auth/register', [
            'name'                  => 'Test User',
            'email'                 => 'testuser@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['_id', 'name', 'email', 'role'],
                     'token',
                 ]);
    }

    public function test_first_registered_user_becomes_admin(): void
    {
        // Clear existing users
        User::truncate();

        $response = $this->tenantRequest('POST', '/api/auth/register', [
            'name'                  => 'First User',
            'email'                 => 'first@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('user.role', 'admin');
    }

    public function test_user_can_login(): void
    {
        User::create([
            'name'     => 'Login Test',
            'email'    => 'login@example.com',
            'password' => 'password123',
            'role'     => 'customer',
        ]);

        $response = $this->tenantRequest('POST', '/api/auth/login', [
            'email'    => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'name'     => 'Wrong Pass',
            'email'    => 'wrong@example.com',
            'password' => 'correctpassword',
            'role'     => 'customer',
        ]);

        $response = $this->tenantRequest('POST', '/api/auth/login', [
            'email'    => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user  = User::create([
            'name'     => 'Logout User',
            'email'    => 'logout@example.com',
            'password' => 'password123',
            'role'     => 'customer',
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->tenantRequest('POST', '/api/auth/logout', [], $token);
        $response->assertStatus(200);
    }

    public function test_request_without_tenant_header_fails(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/json'])
                         ->postJson('/api/auth/login', [
                             'email'    => 'test@example.com',
                             'password' => 'password',
                         ]);

        $response->assertStatus(400);
    }
}
