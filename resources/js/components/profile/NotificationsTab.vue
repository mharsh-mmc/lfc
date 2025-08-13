<template>
  <div class="space-y-8">
    <div class="bg-white rounded-lg shadow">
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
        <div v-if="notifications.length === 0" class="px-6 py-12 text-center">
          <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.83 4.83a4 4 0 015.66 0H18a2 2 0 012 2v7.34l1.17 1.17a2 2 0 01-2.83 2.83L4.83 4.83z"/>
          </svg>
          <p class="text-gray-500">No notifications yet</p>
        </div>

        <div
          v-for="notification in notifications"
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
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  isOwnProfile: {
    type: Boolean,
    default: false
  }
});

// Notification-related state
const notifications = ref([]);
const unreadCount = ref(0);
const loadingNotifications = ref(false);

// Notification functions
const fetchNotifications = () => {
  loadingNotifications.value = true;
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
    console.error('Failed to fetch notifications:', error);
  })
  .finally(() => {
    loadingNotifications.value = false;
  });
};

const markAsRead = (notificationId) => {
  const form = useForm({});
  form.post(`/notifications/${notificationId}/mark-read`, {
    onSuccess: () => {
      // Update the notification locally
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
      // Update all notifications locally
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

const getNotificationIconClass = (type) => {
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
  return classes[type] || 'bg-gray-500';
};

// Load notifications when component mounts
onMounted(() => {
  if (props.isOwnProfile) {
    fetchNotifications();
  }
});
</script>

<style scoped>
/* Notifications tab specific styles */
</style>
