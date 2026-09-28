<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Support\ApiSerializer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    /**
     * GET /api/notifications — the member's notifications, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $request->user()
            ->notifications()
            ->latest('id')
            ->paginate($this->perPage($request, 15))
            ->withQueryString();

        return response()->json(
            ApiSerializer::page($paginator, fn (Notification $notification): array => ApiSerializer::notification($notification))
        );
    }

    /**
     * PATCH /api/notifications/{notification} — mark as read.
     */
    public function read(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403, 'You may only update your own notifications.');
        }

        $notification->markAsRead();
        $notification->refresh();

        return response()->json($this->data(ApiSerializer::notification($notification)));
    }
}
