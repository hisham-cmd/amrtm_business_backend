<?php

namespace App\Http\Controllers\UpdateService;

use App\Http\Controllers\Controller;
use App\Models\BusinessNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function recipientQuery()
    {
        $user = auth('business')->user();
        $q    = BusinessNotification::query();

        if (in_array($user->role ?? '', ['admin', 'supervisor'])) {
            $q->where('recipient_type', 'admin');
        } else {
            $q->where('recipient_type', 'user')->where('user_id', $user->id);
        }

        return $q;
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->recipientQuery();

        $items = $request->boolean('all')
            ? $query->orderByDesc('created_at')->get()
            : $query->orderByDesc('created_at')->limit(30)->get();

        $unread = $this->recipientQuery()->where('is_read', false)->count();

        return response()->json(['data' => $items, 'unread' => $unread]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = $this->recipientQuery()->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    public function markRead(int $id): JsonResponse
    {
        $this->recipientQuery()->where('id', $id)->update(['is_read' => true]);
        return response()->json(['message' => 'تم التعليم كمقروء']);
    }

    public function markAllRead(): JsonResponse
    {
        $this->recipientQuery()->where('is_read', false)->update(['is_read' => true]);
        return response()->json(['message' => 'تم تعليم الكل كمقروء']);
    }
}