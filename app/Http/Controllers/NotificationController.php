<?php

namespace App\Http\Controllers;

use App\Support\Notifications\NotificationPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20)
            ->through(fn (DatabaseNotification $notification) => NotificationPresenter::fromDatabaseNotification($notification));

        return Inertia::render('notifications/index', [
            'inbox' => $notifications,
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'items' => NotificationPresenter::collection($notifications),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse|JsonResponse
    {
        $record = $request->user()->notifications()->where('id', $notification)->firstOrFail();
        $record->markAsRead();

        if ($request->expectsJson()) {
            return response()->json([
                'unreadCount' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse|JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['unreadCount' => 0]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('All notifications marked as read.')]);

        return back();
    }
}
