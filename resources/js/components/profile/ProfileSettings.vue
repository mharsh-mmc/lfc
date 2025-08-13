<template>
  <div class="absolute top-4 right-4 z-10 flex space-x-2">
    <!-- Notification Bell Button -->
    <div class="bg-white bg-opacity-80 hover:bg-opacity-100 rounded-md p-1 transition-all duration-200">
      <NotificationBell />
    </div>

    <!-- Settings Button -->
    <div class="relative" @click.stop>
      <button 
        @click="showSettingsDropdown = !showSettingsDropdown"
        class="bg-white bg-opacity-80 hover:bg-opacity-100 text-gray-700 p-2 rounded-md text-sm font-medium transition-all duration-200"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </button>
      
      <!-- Settings Dropdown Menu -->
      <div v-if="showSettingsDropdown" class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg py-1 z-50">
        <div class="px-4 py-3 border-b border-gray-200">
          <h3 class="text-sm font-medium text-gray-900">Profile Settings</h3>
        </div>
        
        <!-- Settings Link -->
        <div class="px-4 py-2">
          <Link 
            href="/settings"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Settings</span>
          </Link>
        </div>
        
        <!-- Public Profile Toggle -->
        <div class="px-4 py-3">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700">Public Profile</span>
            <button 
              @click="togglePrivacy"
              :class="[
                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                currentPrivacyState ? 'bg-blue-600' : 'bg-gray-200'
              ]"
            >
              <span 
                :class="[
                  'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                  currentPrivacyState ? 'translate-x-6' : 'translate-x-1'
                ]"
              ></span>
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-1">
            {{ currentPrivacyState ? 'Your profile is visible to everyone' : 'Your profile is private and only visible to you' }}
          </p>
        </div>
        
        <!-- Reset Password Option -->
        <div class="px-4 py-2">
          <button 
            @click="showSettingsDropdown = false"
            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md flex items-center space-x-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <span>Reset Password</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import NotificationBell from '@/components/NotificationBell.vue';

const props = defineProps({
  isOwnProfile: {
    type: Boolean,
    default: false
  },
  currentPrivacyState: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['privacy-toggled']);

// Settings dropdown state
const showSettingsDropdown = ref(false);

// Privacy toggle functionality
const togglePrivacy = () => {
  router.post('/profile/toggle-privacy', {}, {
    preserveScroll: true,
    onSuccess: (response) => {
      // Update the reactive state
      emit('privacy-toggled', response.is_public);
    },
    onError: (errors) => {
      console.error('Privacy toggle errors:', errors);
    },
  });
};

// Close dropdown when clicking outside
const closeDropdown = () => {
  showSettingsDropdown.value = false;
};

// Expose closeDropdown for parent component
defineExpose({
  closeDropdown
});
</script>

<style scoped>
/* Settings specific styles */
</style>
