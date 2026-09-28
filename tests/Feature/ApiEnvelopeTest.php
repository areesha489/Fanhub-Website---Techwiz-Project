<?php

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * JSON API envelope contract: {"data": ...} for single resources and
 * {"data": [...], "meta": {...}} for lists, plus guest restrictions.
 */
class ApiEnvelopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_content_list_returns_data_and_pagination_meta(): void
    {
        $this->getJson('/api/content?per_page=5')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'title',
                    'slug',
                    'category',
                    'content_type',
                    'cover_image',
                    'bookmarked',
                ]],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            ])
            ->assertJson(['meta' => ['current_page' => 1, 'per_page' => 5]]);
    }

    public function test_categories_list_returns_data_and_meta(): void
    {
        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'icon', 'accent', 'content_count']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            ]);
    }

    public function test_events_list_returns_data_and_meta(): void
    {
        $this->getJson('/api/events')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'slug', 'event_type', 'start_date', 'status']],
                'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            ]);
    }

    public function test_content_detail_returns_a_single_resource_envelope(): void
    {
        $content = Content::query()->published()->firstOrFail();

        $this->getJson('/api/content/'.$content->slug)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'slug',
                    'category',
                    'synopsis',
                    'tags',
                    'characters',
                    'related',
                    'gallery',
                ],
            ])
            ->assertJson(['data' => ['id' => $content->id, 'title' => $content->title]]);
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/content/not-a-real-title')->assertNotFound();
    }

    public function test_draft_titles_are_not_exposed_on_the_public_api(): void
    {
        $draft = Content::factory()->draft()->create();

        $this->getJson('/api/content?per_page=50')
            ->assertOk()
            ->assertJsonMissing(['data' => [['id' => $draft->id]]]);
    }

    public function test_guest_cannot_read_the_dashboard(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }
}
