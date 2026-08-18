<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = UserNotification::where('user_id', $request->user()->user_id)->latest();

        if ($request->boolean('unread')) {
            $query->unread();
        }

        return response()->json([
            'data'         => $query->limit(50)->get(),
            'unread_count' => UserNotification::where('user_id', $request->user()->user_id)->unread()->count(),
        ]);
    }

    public function markAsRead(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->user_id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Marked as read.', 'data' => $notification]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = UserNotification::where('user_id', $request->user()->user_id)
            ->unread()->update(['read_at' => now()]);

        return response()->json(['message' => "Marked {$updated} alerts as read."]);
    }

    public function destroy(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->user_id, 403);

        $notification->delete();

        return response()->json(['message' => 'Alert removed.']);
    }
}