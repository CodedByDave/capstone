<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserNotificationController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $notifications = $user->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'data'       => $n->data,
                'read_at'    => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        // Mark all as read when the page is opened
        $user->unreadNotifications()->update(['read_at' => now()]);

        return Inertia::render('user/Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function poll(): JsonResponse
    {
        $user  = auth()->user();
        $unread = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($n) => [
                'id'   => $n->id,
                'type' => $n->data['type'] ?? null,
                'title' => $n->data['title'] ?? '',
                'body'  => $n->data['body'] ?? '',
            ]);

        return response()->json([
            'count'   => $unread->count(),
            'unread'  => $unread->values(),
        ]);
    }

    public function markRead(string $id): JsonResponse
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
