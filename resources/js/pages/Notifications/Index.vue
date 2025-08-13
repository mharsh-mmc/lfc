<template>
  <AppLayout title="Notifications">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Notifications
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <!-- Header -->
          <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
              All Notifications
            </h3>
            <div class="flex items-center space-x-4">
              <span class="text-sm text-gray-500">
                {{ unreadCount }} unread
              </span>
              <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
              >
                Mark all as read
              </button>
            </div>
          </div>

          <!-- Notifications List -->
          <div class="divide-y divide-gray-200">
            <div v-if="notifications.data.length === 0" class="px-6 py-12 text-center">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h4a2 2 0 002-2V9a2 2 0 00-2-2H10a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <p class="text-gray-500">No notifications yet</p>
            </div>

            <div
              v-for="notification in notifications.data"
              :key="notification.id"
              @click="markAsRead(notification.id)"
              class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition-colors"
              :class="{ 'bg-blue-50': !notification.is_read }"
            >
              <div class="flex items-start space-x-4">
                <!-- Notification Icon -->
                <div class="flex-shrink-0">
                  <div
                    class="w-10 h-10 rounded-full flex items-center justify-center"
                    :class="getNotificationIconClass(notification.type)"
                  >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        v-if="notification.type === 'profile_view'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                      />
                      <path
                        v-if="notification.type === 'profile_view'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                      <path
                        v-if="notification.type === 'connection_request'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                      />
                      <path
                        v-if="notification.type === 'connection_accepted'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                      <path
                        v-if="notification.type === 'tribute_added'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                      />
                      <path
                        v-if="notification.type === 'subscription_reminder'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                      <path
                        v-if="notification.type === 'subscription_expired'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                      />
                      <path
                        v-if="notification.type === 'new_message'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                      />
                      <path
                        v-if="notification.type === 'legacy_reminder'"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                      />
                    </svg>
                  </div>
                </div>

                <!-- Notification Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">
                      {{ notification.title }}
                    </p>
                    <div class="flex items-center space-x-2">
                      <span class="text-xs text-gray-400">
                        {{ notification.time_ago }}
                      </span>
                      <div v-if="!notification.is_read" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    </div>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ notification.message }}
                  </p>
                </div>

                <!-- Actions -->
                <div class="flex-shrink-0">
                  <button
                    @click.stop="deleteNotification(notification.id)"
                    class="text-gray-400 hover:text-red-500 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="notifications.links && notifications.links.length > 3" class="px-6 py-4 border-t border-gray-200">
            <nav class="flex justify-center">
              <div class="flex space-x-1">
                <Link
                  v-for="link in notifications.links"
                  :key="link.label"
                  :href="link.url"
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-md',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                  ]"
                  v-html="link.label"
                />
              </div>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Notification {
  id: number;
  type: string;
  title: string;
  message: string;
  is_read: boolean;
  created_at: string;
  time_ago: string;
  data?: any;
}

interface PaginatedNotifications {
  data: Notification[];
  links: any[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

const props = defineProps<{
  notifications: PaginatedNotifications;
  unreadCount: number;
}>();

const markAsRead = (notificationId: number) => {
  const form = useForm({});
  form.post(`/notifications/${notificationId}/mark-read`, {
    onSuccess: () => {
      // Update the notification locally
      const notification = props.notifications.data.find(n => n.id === notificationId);
      if (notification && !notification.is_read) {
        notification.is_read = true;
      }
    },
    onError: (errors) => {
      console.error('Failed to mark notification as read:', errors);
    }
  });
};

const markAllAsRead = () => {
  const form = useForm({});
  form.post('/notifications/mark-all-read', {
    onSuccess: () => {
      // Update all notifications locally
      props.notifications.data.forEach(notification => {
        notification.is_read = true;
      });
    },
    onError: (errors) => {
      console.error('Failed to mark all notifications as read:', errors);
    }
  });
};

const deleteNotification = (notificationId: number) => {
  const form = useForm({});
  form.delete(`/notifications/${notificationId}`, {
    onSuccess: () => {
      // Remove the notification from the list
      const index = props.notifications.data.findIndex(n => n.id === notificationId);
      if (index > -1) {
        props.notifications.data.splice(index, 1);
      }
    },
    onError: (errors) => {
      console.error('Failed to delete notification:', errors);
    }
  });
};

const getNotificationIconClass = (type: string): string => {
  const classes = {
    'profile_view': 'bg-blue-500',
    'connection_request': 'bg-green-500',
    'connection_accepted': 'bg-green-500',
    'tribute_added': 'bg-pink-500',
    'subscription_reminder': 'bg-yellow-500',
    'subscription_expired': 'bg-red-500',
    'new_message': 'bg-purple-500',
    'legacy_reminder': 'bg-indigo-500',
  };
  return classes[type as keyof typeof classes] || 'bg-gray-500';
};
</script> 