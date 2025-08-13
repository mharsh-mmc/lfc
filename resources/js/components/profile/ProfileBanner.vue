<template>
  <div class="relative h-64 w-full overflow-hidden">
    <!-- Banner Background -->
    <div v-if="profileUserData.banner_url" class="absolute inset-0">
      <img 
        :src="profileUserData.banner_url" 
        :alt="profileUserData.name + ' banner'"
        class="w-full h-full object-cover"
      >
      <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    </div>
    
    <!-- Default Banner Pattern -->
    <div v-else class="absolute inset-0 bg-gradient-to-r from-blue-100 via-blue-200 to-blue-300">
      <div class="absolute inset-0 opacity-40">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <pattern id="banner-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
              <!-- Flowing curved lines -->
              <path d="M0 20 Q10 10 20 20 T40 20" stroke="rgba(255,255,255,0.4)" fill="none" stroke-width="2"/>
              <path d="M0 30 Q10 20 20 30 T40 30" stroke="rgba(255,255,255,0.3)" fill="none" stroke-width="1.5"/>
              <path d="M0 10 Q10 5 20 10 T40 10" stroke="rgba(255,255,255,0.2)" fill="none" stroke-width="1"/>
            </pattern>
          </defs>
          <rect width="100" height="100" fill="url(#banner-pattern)"/>
        </svg>
      </div>
    </div>

    <!-- Edit Banner Button (for own profile) -->
    <div v-if="isOwnProfile" class="absolute top-4 right-4 z-10 flex space-x-2">
      <!-- Edit Banner Button -->
      <button 
        @click="editingBanner = true"
        class="bg-white bg-opacity-80 hover:bg-opacity-100 text-gray-700 p-2 rounded-md text-sm font-medium transition-all duration-200"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 00-2-2H6a2 2 0 002 2z"/>
        </svg>
      </button>
    </div>

    <!-- Banner Upload Modal -->
    <div v-if="editingBanner && isOwnProfile" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Update Banner</h3>
        
        <!-- Banner Preview -->
        <div class="mb-4">
          <div v-if="bannerPreview" class="relative">
            <img :src="bannerPreview" alt="Banner preview" class="w-full h-32 object-cover rounded-lg">
            <button @click="bannerPreview = null" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div v-else class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center">
            <span class="text-gray-500">No banner selected</span>
          </div>
        </div>

        <!-- File Input -->
        <input
          ref="bannerInput"
          type="file"
          accept="image/*"
          @change="updateBannerPreview"
          class="hidden"
        >

        <div class="flex space-x-3">
          <button 
            @click="selectBanner"
            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            Select Banner
          </button>
          <button 
            @click="updateBanner"
            :disabled="mediaForm.processing || !mediaForm.banner"
            class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
          >
            {{ mediaForm.processing ? 'Uploading...' : 'Upload' }}
          </button>
          <button 
            @click="cancelMediaEdit"
            class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({
  profileUserData: {
    type: Object,
    required: true
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['banner-updated']);

// Banner-related state
const editingBanner = ref(false);
const bannerInput = ref(null);
const bannerPreview = ref(null);

// Form for banner
const mediaForm = useForm({
  banner: null,
});

// Banner functions
const selectBanner = () => {
  bannerInput.value.click();
};

const updateBannerPreview = () => {
  const file = bannerInput.value.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    bannerPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
  mediaForm.banner = file;
};

const updateBanner = () => {
  if (!mediaForm.banner) {
    return;
  }

  // Create a FormData object for file upload
  const formData = new FormData();
  formData.append('banner', mediaForm.banner);

  // Use Inertia.js router.post for proper CSRF handling
  router.post('/profile/banner', formData, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      editingBanner.value = false;
      bannerPreview.value = null;
      clearFileInput(bannerInput);
      emit('banner-updated');
      
      // Refresh the page to show the new banner
      window.location.reload();
    },
    onError: (errors) => {
      console.error('Banner update error:', errors);
      alert('Failed to update banner. Please try again.');
    }
  });
};

const clearFileInput = (inputRef) => {
  if (inputRef.value?.value) {
    inputRef.value.value = null;
  }
};

const cancelMediaEdit = () => {
  editingBanner.value = false;
  bannerPreview.value = null;
  clearFileInput(bannerInput);
  mediaForm.reset();
};
</script>

<style scoped>
/* Banner-specific styles */
</style>
