<template>
  <div class="relative">
    <!-- Notification Bell -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors"
      :class="{ 'text-blue-600': showDropdown }"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h4a2 2 0 002-2V9a2 2 0 00-2-2H10a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
      
      <!-- Notification Badge -->
      <div
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </div>
    </button>

    <!-- Notification Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-xs text-blue-600 hover:text-blue-800"
        >
          Mark all as read
        </button>
      </div>

      <!-- Notifications List -->
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-gray-500">
          <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h4a2 2 0 002-2V9a2 2 0 00-2-2H10a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          <p class="text-sm">No notifications yet</p>
        </div>

        <div v-else>
          <div
            v-for="notification in notifications"
            :key="notification.id"
            @click="markAsRead(notification.id)"
            class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
            :class="{ 'bg-blue-50': !notification.is_read }"
          >
            <!-- Notification Icon -->
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <div
                  class="w-8 h-8 rounded-full flex items-center justify-center"
                  :class="getNotificationIconClass(notification.type)"
                >
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <p class="text-sm font-medium text-gray-900">
                  {{ notification.title }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                  {{ notification.message }}
                </p>
                <p class="text-xs text-gray-400 mt-2">
                  {{ notification.time_ago }}
                </p>
              </div>

              <!-- Unread Indicator -->
              <div v-if="!notification.is_read" class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-4 py-3 border-t border-gray-200">
        <Link
          href="/notifications"
          class="text-sm text-blue-600 hover:text-blue-800 font-medium"
        >
          View all notifications
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';

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

const showDropdown = ref(false);
const notifications = ref<Notification[]>([]);
const unreadCount = ref(0);

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
  if (showDropdown.value) {
    loadNotifications();
  }
};

const loadNotifications = () => {
  // Use fetch API to get notifications data
  fetch('/notifications/recent', {
    method: 'GET',
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    notifications.value = data.notifications || [];
    unreadCount.value = data.unread_count || 0;
  })
  .catch(error => {
    console.error('Failed to load notifications:', error);
  });
};

const markAsRead = (notificationId: number) => {
  const form = useForm({});
  form.post(`/notifications/${notificationId}/mark-read`, {
    onSuccess: () => {
      const notification = notifications.value.find(n => n.id === notificationId);
      if (notification && !notification.is_read) {
        notification.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
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
      notifications.value.forEach(notification => {
        notification.is_read = true;
      });
      unreadCount.value = 0;
    },
    onError: (errors) => {
      console.error('Failed to mark all notifications as read:', errors);
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

const handleClickOutside = (event: Event) => {
  const target = event.target as Element;
  if (!target.closest('.relative')) {
    showDropdown.value = false;
  }
};

onMounted(() => {
  loadNotifications();
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script> 