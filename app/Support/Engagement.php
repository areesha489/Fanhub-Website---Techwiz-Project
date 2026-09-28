<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\ContentView;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Small engagement helpers shared by controllers:
 * view tracking, activity logging and user notifications.
 */
class Engagement
{
    /**
     * Record a detail-page view (viewable row + views_count increment).
     *
     * The stored fingerprint is a 45-character slice of a keyed SHA-256 — long
     * enough to de-duplicate visitors, short enough for the `views.ip` column
     * (45 = the longest possible IP literal, so strict MySQL accepts it).
     *
     * Silently no-ops when the model has no views_count column.
     */
    public static function recordView(Model $model, string $type): void
    {
        try {
            if (! array_key_exists('views_count', $model->getAttributes()) && ! in_array('views_count', $model->getFillable(), true)) {
                return;
            }

            $model->newQuery()->whereKey($model->getKey())->increment('views_count');

            ContentView::query()->create([
                'user_id' => Auth::id(),
                'viewable_type' => $model::class,
                'viewable_id' => $model->getKey(),
                'ip' => substr(hash('sha256', request()->ip().config('app.key')), 0, 45),
            ]);
        } catch (\Throwable $e) {
            Log::warning('View tracking failed: '.$e->getMessage());
        }
    }

    /**
     * Append an activity log entry for the authenticated user (or a guest).
     */
    public static function log(?int $userId, string $action, string $description, ?string $subjectType = null, int|string|null $subjectId = null): void
    {
        try {
            ActivityLog::query()->create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Activity logging failed: '.$e->getMessage());
        }
    }

    /**
     * Create an in-app notification for a user.
     */
    public static function notify(int $userId, string $title, string $body, ?string $link = null): void
    {
        try {
            Notification::query()->create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'link' => $link,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Notification failed: '.$e->getMessage());
        }
    }

    /**
     * Extract a YouTube/Vimeo id from a URL for embedded players.
     */
    public static function embedId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (Str::contains($url, 'youtube.com/watch')) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            return $query['v'] ?? null;
        }

        if (Str::contains($url, 'youtu.be/')) {
            return explode('/', (string) parse_url($url, PHP_URL_PATH))[1] ?? null;
        }

        if (Str::contains($url, 'vimeo.com/')) {
            return explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[1] ?? null;
        }

        return $url;
    }

    /**
     * Build an iframe embed URL (YouTube/Vimeo) from a stored url/embed_url.
     */
    public static function embedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            if (Str::contains($url, 'youtube.com/embed') || Str::contains($url, 'player.vimeo.com')) {
                return $url;
            }

            $id = self::embedId($url);

            if ($id && Str::contains($url, 'youtu')) {
                return "https://www.youtube.com/embed/{$id}";
            }

            if ($id && Str::contains($url, 'vimeo')) {
                return "https://player.vimeo.com/video/{$id}";
            }

            return $url;
        }

        return "https://www.youtube.com/embed/{$url}";
    }
}
