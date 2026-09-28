<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sanctum token authentication for the JSON API.
 */
class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_issues_a_token_for_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $login->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $token = $login->json('token');

        $this->assertNotEmpty($token);

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJson(['data' => ['id' => $user->id, 'email' => $user->email]]);
    }

    public function test_api_login_rejects_a_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'nope-nope-nope',
        ])->assertUnauthorized();
    }

    public function test_admin_token_can_read_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'Password123!']);

        $token = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'Password123!',
        ])->assertOk()->json('token');

        $this->withToken($token)
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_member_token_is_forbidden_from_admin_endpoints(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);

        $token = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/admin/dashboard')->assertForbidden();
    }

    public function test_unauthenticated_request_to_a_protected_endpoint_returns_401(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }
}
