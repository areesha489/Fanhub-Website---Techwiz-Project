<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * In-app notifications of the member.
 *
 * GET /notifications · POST /notifications/{notification}/read ·
 * POST /notifications/read-all
 */
class NotificationController extends Controller
{
    /**
     * Paginated notification history.
     */
    public function index(Request $request): View
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->getKey())
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('notifications', [
            'notifications' => $notifications,
            'unreadCount' => Notification::query()
                ->where('user_id', $request->user()->getKey())
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    /**
     * Mark a single notification as read (topbar dropdown / list button).
     */
    public function read(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->getKey(), 403);

        $notification->markAsRead();

        if ($notification->link !== null && $notification->link !== '') {
            return redirect()->to($notification->link);
        }

        return back();
    }

    /**
     * Clear the unread badge.
     */
    public function readAll(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
