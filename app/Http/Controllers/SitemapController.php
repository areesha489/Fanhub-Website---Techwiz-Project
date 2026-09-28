<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\FanSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Human readable sitemap of the platform.
 *
 * GET /sitemap
 */
class SitemapController extends Controller
{
    /**
     * Newest entries listed per entity (skipped when the table is empty).
     */
    private const LATEST_LIMIT = 50;

    /**
     * Grouped navigation + database driven links.
     */
    public function show(Request $request): View
    {
        return view('sitemap', [
            'groups' => $this->staticGroups(),
            'latest' => $this->latestGroups(),
        ]);
    }

    /**
     * Hand written navigation groups.
     *
     * @return array<string, array<int, array{label: string, to: string}>>
     */
    private function staticGroups(): array
    {
        return [
            'Platform' => [
                ['label' => 'Home', 'to' => route('home')],
                ['label' => 'Explore', 'to' => route('explore')],
                ['label' => 'Search', 'to' => route('search')],
            ],
            'Discover' => [
                ['label' => 'Characters', 'to' => route('characters.index')],
                ['label' => 'Articles', 'to' => route('articles.index')],
                ['label' => 'Community', 'to' => route('community.index')],
                ['label' => 'Multimedia', 'to' => route('media.index')],
                ['label' => 'Events', 'to' => route('events.index')],
                ['label' => 'Merchandise', 'to' => route('merchandise.index')],
                ['label' => 'Upcoming releases', 'to' => route('releases.index')],
            ],
            'Account' => [
                ['label' => 'Login', 'to' => route('login')],
                ['label' => 'Register', 'to' => route('register')],
                ['label' => 'Feedback', 'to' => route('feedback.create')],
                ['label' => 'Contact', 'to' => route('contact')],
            ],
            'Legal' => [
                ['label' => 'Privacy', 'to' => route('privacy')],
                ['label' => 'Terms', 'to' => route('terms')],
            ],
        ];
    }

    /**
     * Category and database driven link groups.
     *
     * @return array<string, array<int, array{label: string, to: string}>>
     */
    private function latestGroups(): array
    {
        $groups = [];

        try {
            $groups['Categories'] = Category::query()
                ->orderBy('position')
                ->orderBy('name')
                ->get(['name', 'slug'])
                ->map(fn (Category $category): array => [
                    'label' => $category->name,
                    'to' => route('category.show', $category->slug),
                ])
                ->all();
        } catch (\Throwable $exception) {
            $groups['Categories'] = [];
        }

        $groups['Latest content'] = $this->linksFor(
            fn () => Content::query()->where('status', 'published')->latest('id')->limit(self::LATEST_LIMIT)->get(['title', 'slug']),
            'content.show'
        );

        $groups['Community'] = $this->linksFor(
            fn () => FanSubmission::query()->approved()->latest('id')->limit(self::LATEST_LIMIT)->get(['title', 'slug']),
            'community.show'
        );

        $groups['Latest articles'] = $this->linksFor(
            fn () => Article::query()->where('status', 'published')->latest('id')->limit(self::LATEST_LIMIT)->get(['title', 'slug']),
            'articles.show'
        );

        $groups['Latest characters'] = $this->linksFor(
            fn () => Character::query()->latest('id')->limit(self::LATEST_LIMIT)->get(['name', 'slug']),
            'characters.show',
            'name'
        );

        $groups['Latest events'] = $this->linksFor(
            fn () => Event::query()->latest('id')->limit(self::LATEST_LIMIT)->get(['name', 'slug']),
            'events.show',
            'name'
        );

        return array_filter($groups, fn (array $links): bool => $links !== []);
    }

    /**
     * Map a freshly queried model collection onto route links.
     *
     * @param  callable(): Collection<int, object>  $query
     * @return array<int, array{label: string, to: string}>
     */
    private function linksFor(callable $query, string $route, string $titleColumn = 'title'): array
    {
        try {
            return $query()
                ->map(fn (object $item): array => [
                    'label' => (string) $item->{$titleColumn},
                    'to' => route($route, $item->slug),
                ])
                ->values()
                ->all();
        } catch (\Throwable $exception) {
            return [];
        }
    }
}
