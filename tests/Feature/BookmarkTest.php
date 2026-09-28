<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bookmark AJAX contract: create, idempotent repeat, note editing, removal
 * and the guest behaviour of the same endpoints.
 */
class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Content $content;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->content = Content::factory()->create();
    }

    public function test_json_bookmark_is_created_with_201(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('bookmarks.store'), [
            'type' => 'content',
            'id' => $this->content->id,
            'note' => 'Watch this weekend',
        ]);

        $response->assertCreated()
            ->assertJson(['bookmarked' => true, 'note' => 'Watch this weekend']);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->user->id,
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
            'note' => 'Watch this weekend',
        ]);
    }

    public function test_repeating_a_bookmark_keeps_a_single_row(): void
    {
        $payload = ['type' => 'content', 'id' => $this->content->id];

        $this->actingAs($this->user)
            ->postJson(route('bookmarks.store'), $payload)
            ->assertCreated();

        $this->actingAs($this->user)
            ->postJson(route('bookmarks.store'), $payload)
            ->assertOk()
            ->assertJson(['bookmarked' => true]);

        $this->assertDatabaseCount('bookmarks', 1);
    }

    public function test_bookmark_note_can_be_updated(): void
    {
        $bookmark = $this->user->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
            'note' => 'First note',
        ]);

        $this->actingAs($this->user)
            ->putJson(route('bookmarks.update', $bookmark), ['note' => 'Second note'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('bookmarks', ['id' => $bookmark->id, 'note' => 'Second note']);
        $this->assertDatabaseHas('bookmark_notes', ['bookmark_id' => $bookmark->id, 'note' => 'Second note']);
    }

    public function test_bookmark_can_be_removed(): void
    {
        $bookmark = $this->user->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
        ]);

        $this->actingAs($this->user)
            ->deleteJson(route('bookmarks.destroy', $bookmark))
            ->assertOk()
            ->assertJson(['bookmarked' => false]);

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_another_members_bookmark_cannot_be_edited(): void
    {
        $owner = User::factory()->create();
        $bookmark = $owner->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
        ]);

        $this->actingAs($this->user)
            ->putJson(route('bookmarks.update', $bookmark), ['note' => 'Not mine'])
            ->assertForbidden();
    }

    public function test_guest_bookmark_post_redirects_to_login_and_json_returns_401(): void
    {
        $payload = ['type' => 'content', 'id' => $this->content->id];

        $this->post(route('bookmarks.store'), $payload)->assertRedirect(route('login'));

        $this->postJson(route('bookmarks.store'), $payload)->assertUnauthorized();

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_bookmarks_page_lists_saved_items(): void
    {
        $this->user->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
            'note' => 'For the marathon',
        ]);

        $this->actingAs($this->user)
            ->get(route('bookmarks.index'))
            ->assertOk()
            ->assertSee($this->content->title);
    }

    public function test_dashboard_survives_a_bookmark_whose_title_was_removed(): void
    {
        $this->user->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
        ]);

        $this->content->delete();

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Removed item')
            ->assertDontSee('App\\Models\\Content');
    }
}
