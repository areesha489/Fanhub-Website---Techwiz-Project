<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\Media;
use App\Models\Merchandise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Public discovery surface: home, explore filters, category pages,
 * detail pages, catalogue indexes, search and unknown-slug handling.
 */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_page_renders_seeded_fandom_content(): void
    {
        $category = Category::query()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($category->name)
            ->assertSee('Popular Fandoms')
            ->assertSee('Subscribe to Our Newsletter')
            ->assertSee('Quick Links');
    }

    public function test_navigation_links_resolve_to_the_website_not_the_admin_api(): void
    {
        foreach ([
            route('home'),
            route('explore'),
            route('characters.index'),
            route('articles.index'),
            route('media.index'),
            route('events.index'),
            route('merchandise.index'),
            route('releases.index'),
        ] as $url) {
            $this->assertStringStartsWith(url('/'), $url);
            $this->assertStringNotContainsString('/api/admin/', $url);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('/api/admin/', false);
    }

    public function test_explore_filters_the_catalogue_server_side(): void
    {
        $content = Content::query()->published()->firstOrFail();

        $response = $this->get(route('explore', [
            'category' => $content->category->slug,
            'type' => $content->content_type,
            'sort' => 'popular',
        ]));

        $response->assertOk()
            ->assertDontSee('We could not load the catalogue right now')
            ->assertSee(self::needle($content->title));
    }

    public function test_explore_renders_an_empty_state_for_an_unknown_category(): void
    {
        $response = $this->get(route('explore', ['category' => 'no-such-category']));

        $response->assertOk()
            ->assertDontSee('We could not load the catalogue right now');
    }

    public function test_category_page_renders_its_catalogue(): void
    {
        $category = Category::query()->firstOrFail();

        $this->get(route('category.show', $category->slug))
            ->assertOk()
            ->assertDontSee('This category could not be loaded right now')
            ->assertSee($category->name);
    }

    public function test_unknown_category_slug_returns_404(): void
    {
        $this->get('/category/definitely-not-a-category')->assertNotFound();
    }

    public function test_content_detail_page_renders(): void
    {
        $content = Content::query()->published()->firstOrFail();

        $this->get(route('content.show', $content->slug))
            ->assertOk()
            ->assertSee($content->title);
    }

    public function test_unknown_content_slug_returns_404(): void
    {
        $this->get('/content/definitely-not-a-title')->assertNotFound();
    }

    public function test_catalogue_indexes_render(): void
    {
        foreach ([
            'characters.index',
            'articles.index',
            'media.index',
            'events.index',
            'merchandise.index',
            'releases.index',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_catalogue_detail_pages_render(): void
    {
        foreach ([
            ['characters.show', Character::query()->firstOrFail()->slug],
            ['articles.show', Article::query()->firstOrFail()->slug],
            ['media.show', Media::query()->firstOrFail()->slug],
            ['events.show', Event::query()->firstOrFail()->slug],
            ['merchandise.show', Merchandise::query()->firstOrFail()->slug],
        ] as [$route, $slug]) {
            $this->get(route($route, $slug))->assertOk();
        }
    }

    public function test_unknown_character_slug_returns_404(): void
    {
        $this->get('/characters/definitely-not-a-character')->assertNotFound();
    }

    public function test_search_finds_a_seeded_title(): void
    {
        $content = Content::query()->published()->firstOrFail();

        $response = $this->get(route('search', ['q' => $content->title]));

        $response->assertOk()
            ->assertDontSee('Search is unavailable right now')
            ->assertSee(self::needle($content->title));
    }

    public function test_search_without_a_query_renders_the_landing_state(): void
    {
        $this->get(route('search'))->assertOk();
    }

    /**
     * Titles are clamped in some cards, so match on a leading fragment.
     */
    private static function needle(string $text, int $length = 12): string
    {
        return mb_substr($text, 0, $length);
    }
}
