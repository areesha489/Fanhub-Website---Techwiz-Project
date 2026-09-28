<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Bookmark endpoints of the JSON API (auth:sanctum).
 */
class ApiBookmarkTest extends TestCase
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

    public function test_bookmark_is_created_with_201_and_a_data_envelope(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/bookmarks', [
            'type' => 'content',
            'id' => $this->content->id,
            'note' => 'Reading list',
        ])->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'bookmarkable_type', 'bookmarkable_id', 'note', 'title']]);

        $this->assertDatabaseHas('bookmarks', [
            'user_id' => $this->user->id,
            'bookmarkable_id' => $this->content->id,
            'note' => 'Reading list',
        ]);
    }

    public function test_repeating_a_bookmark_stays_idempotent(): void
    {
        Sanctum::actingAs($this->user);

        $payload = ['type' => 'content', 'id' => $this->content->id];

        $this->postJson('/api/bookmarks', $payload)->assertCreated();
        $this->postJson('/api/bookmarks', $payload)->assertCreated();

        $this->assertDatabaseCount('bookmarks', 1);
    }

    public function test_bookmark_note_can_be_updated(): void
    {
        Sanctum::actingAs($this->user);

        $bookmark = $this->user->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
            'note' => 'Old note',
        ]);

        $this->patchJson('/api/bookmarks/'.$bookmark->id, ['note' => 'Rewatched, still great'])
            ->assertOk()
            ->assertJson(['data' => ['note' => 'Rewatched, still great']]);

        $this->assertDatabaseHas('bookmarks', ['id' => $bookmark->id, 'note' => 'Rewatched, still great']);
    }

    public function test_bookmark_can_be_deleted(): void
    {
        Sanctum::actingAs($this->user);

        $bookmark = $this->user->bookmarks()->create([
            'bookmarkable_type' => Content::class,
            'bookmarkable_id' => $this->content->id,
        ]);

        $this->deleteJson('/api/bookmarks/'.$bookmark->id)->assertOk();

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_guest_cannot_list_or_create_bookmarks(): void
    {
        $this->getJson('/api/bookmarks')->assertUnauthorized();
        $this->postJson('/api/bookmarks', ['type' => 'content', 'id' => $this->content->id])
            ->assertUnauthorized();

        $this->assertDatabaseCount('bookmarks', 0);
    }

    public function test_invalid_bookmark_type_is_rejected(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/bookmarks', ['type' => 'planet', 'id' => $this->content->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }
}
