<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user's notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->wantsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $user->unread_notifications_count,
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unread_count' => $user->unread_notifications_count,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, int $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Notification not found'], 404);
            }
            return back()->withErrors(['error' => 'Notification not found']);
        }

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->fresh()->unread_notifications_count,
            ]);
        }

        return back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $this->notificationService->markAllAsRead($user);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        return response()->json([
            'unread_count' => $user->unread_notifications_count,
        ]);
    }

    /**
     * Get recent notifications for dropdown.
     */
    public function getRecent()
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $user->unread_notifications_count,
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unread_count' => $user->unread_notifications_count,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'unread_count' => $user->fresh()->unread_notifications_count,
        ]);
    }
}
