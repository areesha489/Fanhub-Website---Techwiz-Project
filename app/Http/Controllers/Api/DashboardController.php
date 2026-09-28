<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    /**
     * GET /api/dashboard — personal stats plus the member's recent activity.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $bookmarks = $user->bookmarks()->latest('id')->take(8)->get();
        $notifications = $user->notifications()->latest('id')->take(5)->get();
        $submissions = $user->submissions()->with('category')->latest('id')->take(5)->get();
        $activity = $user->activities()->latest('id')->take(8)->get();

        $payload = [
            'stats' => [
                'bookmarks' => $user->bookmarks()->count(),
                'unread_notifications' => $user->unreadNotificationsCount(),
                'submissions' => $user->submissions()->count(),
                'pending_submissions' => $user->submissions()->where('status', 'pending')->count(),
                'ratings' => $this->ratingCount($user->id),
                'activity_week' => $user->activities()->where('created_at', '>=', now()->subWeek())->count(),
                'articles' => $user->articles()->published()->count(),
            ],
            'bookmarks' => $this->mapBookmarks($bookmarks),
            'notifications' => $notifications
                ->map(fn ($notification): array => ApiSerializer::notification($notification))
                ->values()
                ->all(),
            'submissions' => $submissions
                ->map(fn ($submission): array => ApiSerializer::submission($submission))
                ->values()
                ->all(),
            'activity' => $activity
                ->map(fn ($entry): array => ApiSerializer::activity($entry))
                ->values()
                ->all(),
            'preferences' => $this->preferencePayload($user->resolvePreferences()),
        ];

        return response()->json($this->data($payload));
    }
}
