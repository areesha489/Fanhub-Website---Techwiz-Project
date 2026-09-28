<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Category;
use App\Models\Character;
use App\Models\Content;
use App\Models\Event;
use App\Models\FanSubmission;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Release;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class MetaController extends ApiController
{
    /**
     * GET /api/meta — distinct filter values plus platform counts.
     */
    public function index(): JsonResponse
    {
        $years = Content::query()
            ->published()
            ->whereNotNull('release_date')
            ->pluck('release_date')
            ->map(fn ($date): int => (int) $date->format('Y'))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $payload = [
            'genres' => $this->facet(Content::class, 'genre', publishedOnly: true),
            'years' => $years,
            'content_types' => $this->facet(Content::class, 'content_type', publishedOnly: true),
            'media_types' => $this->facet(Media::class, 'media_type', publishedOnly: true),
            'event_types' => $this->facet(Event::class, 'event_type'),
            'release_types' => $this->facet(Release::class, 'release_type'),
            'event_statuses' => $this->facet(Event::class, 'status'),
            'release_statuses' => $this->facet(Release::class, 'status'),
            'submission_types' => $this->facet(FanSubmission::class, 'type'),
            'cities' => $this->facet(Event::class, 'city'),
            'counts' => [
                'categories' => Category::query()->count(),
                'contents' => Content::query()->published()->count(),
                'characters' => Character::query()->count(),
                'articles' => Article::query()->published()->count(),
                'media' => Media::query()->published()->count(),
                'events' => Event::query()->count(),
                'merchandise' => Merchandise::query()->published()->count(),
                'releases' => Release::query()->count(),
            ],
            'posts_per_page' => (int) Setting::value('posts_per_page', '12'),
        ];

        return response()->json(['data' => $payload]);
    }

    /**
     * Distinct values of a column with row counts.
     *
     * @param  class-string<Model>  $model
     * @return list<array{value: string, count: int}>
     */
    private function facet(string $model, string $column, bool $publishedOnly = false): array
    {
        $query = $model::query()->select($column)->whereNotNull($column);

        if ($publishedOnly && method_exists($model, 'scopePublished')) {
            $query->published();
        }

        return $query
            ->selectRaw("{$column} as value, COUNT(*) as count")
            ->groupBy($column)
            ->orderBy($column)
            ->get()
            ->map(fn (Model $row): array => [
                'value' => (string) $row->getAttribute('value'),
                'count' => (int) $row->getAttribute('count'),
            ])
            ->values()
            ->all();
    }
}
