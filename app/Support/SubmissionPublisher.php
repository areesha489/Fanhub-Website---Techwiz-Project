<?php

namespace App\Support;

use App\Models\Article;
use App\Models\FanSubmission;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns an approved fan submission into a public article or media entry,
 * and pulls that entry back off the site when the decision changes.
 */
class SubmissionPublisher
{
    /**
     * Visual submissions land in the multimedia library. Write-ups become articles.
     *
     * @var list<string>
     */
    private const MEDIA_TYPES = ['artwork', 'video', 'cosplay'];

    private bool $syncing = false;

    /**
     * Publish or retract the public copy so it matches the moderation status.
     */
    public function sync(FanSubmission $submission): void
    {
        if ($this->syncing) {
            return;
        }

        $this->syncing = true;

        try {
            $this->ensureSlug($submission);

            if ($submission->status !== FanSubmission::STATUS_APPROVED) {
                $this->retract($submission);

                return;
            }

            if ($submission->category_id === null || $submission->user_id === null) {
                $this->retract($submission);

                return;
            }

            if (in_array($submission->type, self::MEDIA_TYPES, true)) {
                $this->publishMedia($submission);
                $this->archiveArticle($submission);

                return;
            }

            $this->publishArticle($submission);
            $this->archiveMedia($submission);
        } finally {
            $this->syncing = false;
        }
    }

    /**
     * Take any published catalogue copy off the public site.
     */
    public function retract(FanSubmission $submission): void
    {
        $this->archiveArticle($submission);
        $this->archiveMedia($submission);
    }

    /**
     * Slug that is unique among fan submissions.
     */
    public function makeSlug(string $title, ?int $ignoreId = null): string
    {
        return $this->uniqueSlug('fan_submissions', $title, $ignoreId, 'submission');
    }

    /**
     * Give a row a public slug before it can be linked.
     */
    private function ensureSlug(FanSubmission $submission): void
    {
        if (is_string($submission->slug) && $submission->slug !== '') {
            return;
        }

        $submission->slug = $this->makeSlug($submission->title, $submission->id);
        $this->saveQuietly($submission);
    }

    /**
     * Create or refresh the published article for a write-up.
     */
    private function publishArticle(FanSubmission $submission): void
    {
        $article = $submission->published_article_id
            ? Article::withTrashed()->find($submission->published_article_id)
            : null;

        if ($article?->trashed()) {
            $article->restore();
        }

        $body = $this->articleBody($submission);
        $wordCount = str_word_count(strip_tags($body));
        $fields = [
            'title' => $submission->title,
            'category_id' => $submission->category_id,
            'author_id' => $submission->user_id,
            'excerpt' => Str::limit(trim(strip_tags((string) $submission->description)), 180),
            'body' => $body,
            'cover_image' => $submission->displayImage(),
            'status' => Article::STATUS_PUBLISHED,
            'reading_time' => (int) max(1, (int) ceil($wordCount / 200)),
            'featured' => false,
            'published_at' => $submission->reviewed_at ?? now(),
        ];

        if ($article === null) {
            $fields['slug'] = $this->uniqueSlug('articles', $submission->title, null, 'article');
            $article = Article::query()->create($fields);
        } else {
            $article->fill($fields);
            $article->save();
        }

        if ((int) $submission->published_article_id !== (int) $article->id) {
            $submission->published_article_id = $article->id;
            $this->saveQuietly($submission);
        }
    }

    /**
     * Create or refresh the published multimedia entry for visual fan work.
     */
    private function publishMedia(FanSubmission $submission): void
    {
        $media = $submission->published_media_id
            ? Media::withTrashed()->find($submission->published_media_id)
            : null;

        if ($media?->trashed()) {
            $media->restore();
        }

        $source = $this->safeHttpUrl($submission->url);
        $embed = $source !== null ? Engagement::embedUrl($source) : null;
        $isEmbed = is_string($embed) && (
            str_contains($embed, 'youtube.com/embed/')
            || str_contains($embed, 'player.vimeo.com/video/')
        );
        $image = $submission->displayImage();

        $fields = [
            'title' => $submission->title,
            'category_id' => $submission->category_id,
            'media_type' => $submission->type === 'video' ? 'video' : 'gallery',
            'description' => $submission->description,
            'thumbnail' => $image,
            'url' => $source,
            'embed_url' => $isEmbed ? $embed : null,
            'gallery' => $image !== null ? [$image] : null,
            'featured' => false,
            'status' => Media::STATUS_PUBLISHED,
            'created_by' => $submission->user_id,
        ];

        if ($media === null) {
            $fields['slug'] = $this->uniqueSlug('media', $submission->title, null, 'media');
            $media = Media::query()->create($fields);
        } else {
            $media->fill($fields);
            $media->save();
        }

        if ((int) $submission->published_media_id !== (int) $media->id) {
            $submission->published_media_id = $media->id;
            $this->saveQuietly($submission);
        }
    }

    /**
     * Hide a previously published article without deleting member bookmarks.
     */
    private function archiveArticle(FanSubmission $submission): void
    {
        if ($submission->published_article_id === null) {
            return;
        }

        Article::withTrashed()
            ->whereKey($submission->published_article_id)
            ->update(['status' => Article::STATUS_ARCHIVED]);
    }

    /**
     * Hide a previously published media entry without deleting member bookmarks.
     */
    private function archiveMedia(FanSubmission $submission): void
    {
        if ($submission->published_media_id === null) {
            return;
        }

        Media::withTrashed()
            ->whereKey($submission->published_media_id)
            ->update(['status' => Media::STATUS_ARCHIVED]);
    }

    /**
     * Escape the member's text into paragraphs. Article pages render body as HTML.
     */
    private function articleBody(FanSubmission $submission): string
    {
        $paragraphs = preg_split("/\r\n|\n|\r/", (string) $submission->description) ?: [];
        $html = collect($paragraphs)
            ->map(fn (string $paragraph): string => trim($paragraph))
            ->filter(fn (string $paragraph): bool => $paragraph !== '')
            ->map(fn (string $paragraph): string => '<p>'.e($paragraph).'</p>')
            ->implode('');

        $source = $this->safeHttpUrl($submission->url);

        if ($source !== null) {
            $html .= '<p><a href="'.e($source).'" rel="noopener noreferrer">Source</a></p>';
        }

        return $html !== '' ? $html : '<p>'.e($submission->title).'</p>';
    }

    /**
     * Accept only http(s) links so a stored URL can be rendered as a link.
     */
    private function safeHttpUrl(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    /**
     * Persist publisher bookkeeping without re-entering the model observer.
     */
    private function saveQuietly(FanSubmission $submission): void
    {
        FanSubmission::withoutEvents(function () use ($submission): void {
            $submission->save();
        });
    }

    /**
     * Slug derived from a title, unique inside one table.
     */
    private function uniqueSlug(string $table, string $title, ?int $ignoreId, string $fallback): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : $fallback.'-'.Str::lower(Str::random(6));
        $slug = $base;
        $suffix = 2;

        while (
            DB::table($table)
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
