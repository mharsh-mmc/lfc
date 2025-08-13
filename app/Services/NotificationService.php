<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a profile view notification.
     */
    public function createProfileViewNotification(User $viewedUser, User $viewer): void
    {
        $this->createNotification(
            $viewedUser,
            'profile_view',
            'Profile Viewed',
            "Someone just viewed your timeline. You're keeping memories alive — beautifully.",
            ['viewer_id' => $viewer->id, 'viewer_name' => $viewer->name]
        );
    }

    /**
     * Create a connection request notification.
     */
    public function createConnectionRequestNotification(User $recipient, User $requester): void
    {
        $this->createNotification(
            $recipient,
            'connection_request',
            'New Connection Request',
            "{$requester->name} wants to connect with you.",
            ['requester_id' => $requester->id, 'requester_name' => $requester->name]
        );
    }

    /**
     * Create a connection accepted notification.
     */
    public function createConnectionAcceptedNotification(User $user, User $acceptedBy): void
    {
        $this->createNotification(
            $user,
            'connection_accepted',
            'Connection Accepted',
            "{$acceptedBy->name} accepted your connection request.",
            ['accepted_by_id' => $acceptedBy->id, 'accepted_by_name' => $acceptedBy->name]
        );
    }

    /**
     * Create a tribute added notification.
     */
    public function createTributeAddedNotification(User $recipient, User $tributeFrom, string $tributeType = 'tribute'): void
    {
        $this->createNotification(
            $recipient,
            'tribute_added',
            'New Tribute',
            "A heartfelt tribute has been added to {$recipient->name}'s memory space.",
            [
                'tribute_from_id' => $tributeFrom->id,
                'tribute_from_name' => $tributeFrom->name,
                'tribute_type' => $tributeType
            ]
        );
    }

    /**
     * Create a subscription reminder notification.
     */
    public function createSubscriptionReminderNotification(User $user, int $daysLeft): void
    {
        $this->createNotification(
            $user,
            'subscription_reminder',
            'Subscription Reminder',
            "Your plan expires in {$daysLeft} days. Renew now to keep your family legacy accessible.",
            ['days_left' => $daysLeft]
        );
    }

    /**
     * Create a subscription expired notification.
     */
    public function createSubscriptionExpiredNotification(User $user): void
    {
        $this->createNotification(
            $user,
            'subscription_expired',
            'Subscription Expired',
            'Your subscription has expired. Renew now to continue preserving your family legacy.',
            []
        );
    }

    /**
     * Create a new message notification.
     */
    public function createNewMessageNotification(User $recipient, User $sender): void
    {
        $this->createNotification(
            $recipient,
            'new_message',
            'New Message',
            "You have a new message from {$sender->name}.",
            ['sender_id' => $sender->id, 'sender_name' => $sender->name]
        );
    }

    /**
     * Create a legacy reminder notification.
     */
    public function createLegacyReminderNotification(User $user): void
    {
        $this->createNotification(
            $user,
            'legacy_reminder',
            'Legacy Reminder',
            "Don't forget to share your story. Your legacy is important to preserve.",
            []
        );
    }

    /**
     * Create a generic notification.
     */
    public function createNotification(User $user, string $type, string $title, string $message, array $data = []): void
    {
        try {
            Notification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'is_read' => false,
            ]);

            Log::info('Notification created', [
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Delete old notifications (older than specified days).
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }
} 