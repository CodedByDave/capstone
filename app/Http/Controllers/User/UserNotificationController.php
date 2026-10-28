<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserNotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('user/Notifications', [
            'notifications' => $this->notifications->listFor($request->user()),
        ]);
    }

    public function poll(): JsonResponse
    {
        $user = auth()->user();
        $unread = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? null,
                'title' => $n->data['title'] ?? '',
                'body' => $n->data['body'] ?? '',
            ]);

        return response()->json([
            'count' => $unread->count(),
            'unread' => $unread->values(),
        ]);
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $this->notifications->markRead($request->user(), $id);

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllRead($request->user());

        return back();
    }
}
