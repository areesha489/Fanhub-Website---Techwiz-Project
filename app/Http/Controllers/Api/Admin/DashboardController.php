<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Bookmark;
use App\Models\Category;
use App\Models\Character;
use App\Models\ChatbotQuery;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\Event;
use App\Models\FanSubmission;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Merchandise;
use App\Models\Rating;
use App\Models\Release;
use App\Models\User;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;

class DashboardController extends AdminApiController
{
    /**
     * GET /api/admin/dashboard — platform stats plus top lists.
     */
    public function index(): JsonResponse
    {
        $stats = [
            'users' => User::query()->count(),
            'admins' => User::query()->where('role', User::ROLE_ADMIN)->count(),
            'new_users_week' => User::query()->where('created_at', '>=', now()->subWeek())->count(),
            'categories' => Category::query()->count(),
            'contents' => Content::query()->count(),
            'published_contents' => Content::query()->published()->count(),
            'draft_contents' => Content::query()->where('status', Content::STATUS_DRAFT)->count(),
            'characters' => Character::query()->count(),
            'articles' => Article::query()->count(),
            'media' => Media::query()->count(),
            'events' => Event::query()->count(),
            'merchandise' => Merchandise::query()->count(),
            'releases' => Release::query()->count(),
            'bookmarks' => Bookmark::query()->count(),
            'ratings' => Rating::query()->count(),
            'views' => ContentView::query()->count(),
            'pending_submissions' => FanSubmission::query()->where('status', FanSubmission::STATUS_PENDING)->count(),
            'open_feedback' => Feedback::query()->whereIn('status', [Feedback::STATUS_OPEN, Feedback::STATUS_IN_REVIEW])->count(),
            'chat_queries' => ChatbotQuery::query()->count(),
            'activities' => ActivityLog::query()->count(),
        ];

        $topContents = Content::query()->with('category')->orderByDesc('views_count')->take(5)->get();
        $topCharacters = Character::query()->with('category')->orderByDesc('views_count')->take(5)->get();
        $topArticles = Article::query()->with(['category', 'author'])->orderByDesc('views_count')->take(5)->get();

        $recentSubmissions = FanSubmission::query()->with(['user', 'category'])->latest('id')->take(5)->get();
        $recentFeedback = Feedback::query()->with('user')->latest('id')->take(5)->get();

        $payload = [
            'stats' => $stats,
            'top_contents' => $topContents
                ->map(fn (Content $content): array => [
                    'id' => $content->id,
                    'title' => $content->title,
                    'slug' => $content->slug,
                    'category' => ApiSerializer::categoryRef($content->category),
                    'views_count' => (int) $content->views_count,
                    'rating' => (float) $content->rating,
                    'popularity' => (int) $content->popularity,
                    'status' => $content->status,
                ])
                ->values()
                ->all(),
            'top_characters' => $topCharacters
                ->map(fn (Character $character): array => [
                    'id' => $character->id,
                    'name' => $character->name,
                    'slug' => $character->slug,
                    'fandom' => $character->fandom,
                    'views_count' => (int) $character->views_count,
                    'popularity' => (int) $character->popularity,
                ])
                ->values()
                ->all(),
            'top_articles' => $topArticles
                ->map(fn (Article $article): array => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                    'author' => $article->author?->name,
                    'views_count' => (int) $article->views_count,
                    'status' => $article->status,
                ])
                ->values()
                ->all(),
            'recent_submissions' => $recentSubmissions
                ->map(fn (FanSubmission $submission): array => ApiSerializer::submission($submission))
                ->values()
                ->all(),
            'recent_feedback' => $recentFeedback
                ->map(fn (Feedback $feedback): array => ApiSerializer::feedback($feedback))
                ->values()
                ->all(),
        ];

        return response()->json($this->data($payload));
    }
}
