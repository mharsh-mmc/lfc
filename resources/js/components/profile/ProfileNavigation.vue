<template>
  <div class="bg-white border-b">
    <div class="w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 md:w-[90%] md:max-w-[700px] lg:w-full lg:max-w-[1440px]">
      <nav class="flex space-x-8">
        <Link 
          :href="`/profile/${profileUserData.id}`"
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm',
            currentActiveTab === 'about' 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          About Me
        </Link>

        <Link 
          :href="`/profile/${profileUserData.id}/familytree`"
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm',
            currentActiveTab === 'familytree' 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Family Tree
        </Link>

        <Link 
          :href="`/profile/${profileUserData.id}/video`"
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm',
            currentActiveTab === 'video' 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Video
        </Link>
        <Link 
          :href="`/profile/${profileUserData.id}/letters`"
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm',
            currentActiveTab === 'letters' 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Letters
        </Link>
        <Link 
          :href="`/profile/${profileUserData.id}/photos`"
          :class="[
            'py-4 px-1 border-b-2 font-medium text-sm',
            currentActiveTab === 'photos' 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Photos
        </Link>
        
        <!-- More Tab with Dropdown -->
        <div v-if="isOwnProfile" class="relative group">
          <button 
            data-more-button
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm flex items-center space-x-1',
              currentActiveTab === 'more' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
            @click="toggleMoreDropdown"
          >
            <span>More</span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showMoreDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          
          <!-- Dropdown Menu -->
          <div 
            data-more-dropdown
            v-show="showMoreDropdown"
            class="absolute top-full left-0 mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50"
          >
            <div class="py-1">
              <button 
                @click="handleMoreTabClick('create-deceased')"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Deceased Profiles</span>
              </button>
              <Link 
                :href="`/profile/${profileUserData.id}/notifications`"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center space-x-2"
                @click="showMoreDropdown = false"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span>Notifications</span>
              </Link>
            </div>
          </div>
        </div>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  profileUserData: {
    type: Object,
    required: true
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  },
  currentActiveTab: {
    type: String,
    default: 'about'
  }
});

const emit = defineEmits(['more-tab-clicked']);

// More dropdown state
const showMoreDropdown = ref(false);

const toggleMoreDropdown = () => {
  showMoreDropdown.value = !showMoreDropdown.value;
};

const handleMoreTabClick = (tab) => {
  showMoreDropdown.value = false;
  emit('more-tab-clicked', tab);
};

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', (event) => {
    const moreButton = document.querySelector('[data-more-button]');
    const dropdown = document.querySelector('[data-more-dropdown]');
    
    if (moreButton && dropdown) {
      if (!moreButton.contains(event.target) && !dropdown.contains(event.target)) {
        showMoreDropdown.value = false;
      }
    }
  });
});
</script>

<style scoped>
/* Navigation specific styles */
</style>
