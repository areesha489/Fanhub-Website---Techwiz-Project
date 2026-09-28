<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Session authentication: registration, sign in, guest protection, sign out.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_an_account_and_opens_the_dashboard(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New Fan',
            'email' => 'new.fan@example.test',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new.fan@example.test']);
    }

    public function test_registration_rejects_a_password_that_fails_the_rules(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New Fan',
            'email' => 'new.fan@example.test',
            'password' => 'short',
            'password_confirmation' => 'short',
            'terms' => '1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new.fan@example.test']);
    }

    public function test_sign_in_with_the_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $user->email,
            'password' => 'not-the-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_member_can_sign_in_and_open_the_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('bookmarks.index'))->assertRedirect(route('login'));
        $this->get(route('submissions.create'))->assertRedirect(route('login'));
    }

    public function test_logout_signs_the_member_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
