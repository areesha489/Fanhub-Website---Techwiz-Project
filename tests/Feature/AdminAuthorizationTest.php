<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The /admin panel is closed to guests and regular members (403) and open to
 * active administrators.
 */
class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Representative admin pages spanning the dashboard, analytics, resource
     * lists, moderation queues and settings.
     *
     * @var list<string>
     */
    private const ADMIN_ROUTES = [
        'admin.dashboard',
        'admin.analytics',
        'admin.categories.index',
        'admin.content.index',
        'admin.users.index',
        'admin.submissions.index',
        'admin.feedback.index',
        'admin.chatbot.index',
        'admin.chatbot.queries',
        'admin.settings.show',
    ];

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    }

    public function test_member_without_admin_role_gets_403(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_admin_can_open_every_representative_admin_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        foreach (self::ADMIN_ROUTES as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
