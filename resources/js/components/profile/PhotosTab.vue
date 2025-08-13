<template>
  <div class="w-full">
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Photo Gallery</h3>
        <button v-if="isOwnProfile" @click="showUploadModal = true" class="text-blue-600 hover:text-blue-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
        </button>
      </div>
      
      <!-- Loading State -->
      <div v-if="loadingPhotos" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-500 mt-2">Loading photos...</p>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="photos.length === 0" class="text-center py-8">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 00-2-2H6a2 2 0 002 2z"/>
        </svg>
        <h4 class="text-lg font-medium text-gray-900 mb-2">Photo Gallery</h4>
        <p class="text-gray-500 mb-4">Share and organize your precious photos</p>
        <button v-if="isOwnProfile" @click="showUploadModal = true" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
          Upload Photos
        </button>
      </div>
      
      <!-- Photos Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="photo in photos" :key="photo.id" class="relative group">
          <img :src="photo.url" :alt="photo.caption || 'Photo'" class="w-full h-48 object-cover rounded-lg">
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center">
            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
              <button @click="viewPhoto(photo)" class="bg-white text-gray-800 px-3 py-1 rounded-md text-sm mr-2">
                View
              </button>
              <button v-if="isOwnProfile" @click="deletePhoto(photo)" class="bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Photo Upload Modal -->
    <PhotoUploadModal 
      v-if="showUploadModal"
      :show="showUploadModal"
      @close="showUploadModal = false"
      @uploaded="handlePhotoUploaded"
      @success="handleUploadSuccess"
    />

    <!-- Photo View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click="showViewModal = false">
      <div class="max-w-4xl max-h-full p-4" @click.stop>
        <div class="bg-white rounded-lg overflow-hidden">
          <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">{{ selectedPhoto?.caption || 'Photo' }}</h3>
            <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="p-4">
            <img :src="selectedPhoto?.url" :alt="selectedPhoto?.caption || 'Photo'" class="max-w-full max-h-96 object-contain mx-auto">
          </div>
        </div>
      </div>
    </div>

    <!-- Success Popup -->
    <Transition name="success-popup">
      <div v-if="showSuccessPopup" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4 transform transition-all duration-300 scale-100 animate-success-bounce">
          <!-- Success Icon with Animation -->
          <div class="flex justify-center mb-6">
            <div class="relative">
              <div class="w-16 h-16 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center animate-pulse">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <!-- Animated rings -->
              <div class="absolute inset-0 w-16 h-16 border-4 border-green-200 rounded-full animate-ping"></div>
              <div class="absolute inset-0 w-16 h-16 border-2 border-green-300 rounded-full animate-pulse"></div>
            </div>
          </div>

          <!-- Success Message -->
          <div class="text-center mb-6">
            <h3 class="text-2xl font-bold gradient-text mb-2">Success!</h3>
            <p class="text-gray-600 text-lg leading-relaxed font-medium">{{ successMessage }}</p>
          </div>

          <!-- Action Button -->
          <div class="flex justify-center">
            <button 
              @click="showSuccessPopup = false" 
              class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-3 rounded-lg font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
            >
              Continue
            </button>
          </div>

          <!-- Decorative Elements -->
          <div class="absolute top-4 right-4">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-bounce"></div>
          </div>
          <div class="absolute bottom-4 left-4">
            <div class="w-1 h-1 bg-blue-400 rounded-full animate-pulse"></div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Confirmation Modal -->
    <Transition name="confirmation-popup">
      <div v-if="showConfirmationModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md mx-4 transform transition-all duration-300 scale-100">
          <!-- Warning Icon -->
          <div class="flex justify-center mb-6">
            <div class="relative">
              <div class="w-16 h-16 bg-gradient-to-r from-red-400 to-red-600 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Confirmation Message -->
          <div class="text-center mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Confirm Deletion</h3>
            <p class="text-gray-600 text-lg leading-relaxed">{{ confirmationMessage }}</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-center space-x-4">
            <button 
              @click="confirmDelete" 
              class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white px-6 py-3 rounded-lg font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
            >
              Delete
            </button>
            <button 
              @click="cancelDelete" 
              class="bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white px-6 py-3 rounded-lg font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import PhotoUploadModal from '@/components/PhotoUploadModal.vue';

const props = defineProps({
  photos: {
    type: Array,
    default: () => []
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  },
  profileUserData: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['photos-updated']);

// Photo-related state
const photos = ref(props.photos || []);
const loadingPhotos = ref(false);
const showUploadModal = ref(false);
const showViewModal = ref(false);
const selectedPhoto = ref(null);
const showSuccessPopup = ref(false);
const successMessage = ref('');

// Confirmation modal state
const showConfirmationModal = ref(false);
const confirmationMessage = ref('');
const photoToDelete = ref(null);

// Watch photos prop changes
watch(() => props.photos, (newPhotos) => {
  photos.value = newPhotos || [];
}, { deep: true });

// Photo functions
const loadPhotos = () => {
  if (!props.isOwnProfile) return;
  
  // Use Inertia.js approach to refresh photos data
  router.visit(`/profile/${props.profileUserData?.username || props.profileUserData?.id}/photos`, {
    only: ['photos'],
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
};

const handleAddPhoto = () => {
  if (photos.value.length >= 50) {
    alert('You have reached the maximum limit of 50 photos. Please delete some photos before uploading new ones.');
    return;
  }
  showUploadModal.value = true;
};

const handlePhotoUploaded = () => {
  // Refresh photos data using Inertia.js
  router.visit(`/profile/${props.profileUserData?.username || props.profileUserData?.id}/photos`, {
    only: ['photos'],
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
  
  // Emit event to parent
  emit('photos-updated');
};

const handleUploadSuccess = (message) => {
  successMessage.value = message;
  showSuccessPopup.value = true;
  emit('photos-updated'); // Refresh photos after successful upload
};

const viewPhoto = (photo) => {
  selectedPhoto.value = photo;
  showViewModal.value = true;
};

const deletePhoto = async (photo) => {
  console.log('🗑️ Delete photo requested:', photo);
  photoToDelete.value = photo;
  confirmationMessage.value = `Are you sure you want to delete "${photo.caption || 'Photo'}"? This action cannot be undone.`;
  showConfirmationModal.value = true;
};

const confirmDelete = async () => {
  if (!photoToDelete.value) return;
  
  console.log('✅ Delete confirmed for photo:', photoToDelete.value);

  try {
    // Use Inertia.js router.delete for proper CSRF handling
    router.delete(`/media/${photoToDelete.value.id}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        console.log('✅ Photo deleted successfully:', photoToDelete.value);
        // Show success popup
        successMessage.value = `"${photoToDelete.value.caption || 'Photo'}" deleted successfully!`;
        showSuccessPopup.value = true;
        
        // Remove photo from local state
        const index = photos.value.findIndex(p => p.id === photoToDelete.value.id);
        if (index > -1) {
          photos.value.splice(index, 1);
        }
        
        // Emit event to parent
        emit('photos-updated');
      },
      onError: (errors) => {
        console.error('❌ Failed to delete photo:', errors);
        alert('Failed to delete photo. Please try again.');
      },
      onFinish: () => {
        photoToDelete.value = null;
        showConfirmationModal.value = false;
      }
    });

  } catch (error) {
    console.error('❌ Delete error:', error);
    alert('Failed to delete photo. Please try again.');
    photoToDelete.value = null;
    showConfirmationModal.value = false;
  }
};

const cancelDelete = () => {
  console.log('❌ Delete cancelled for photo:', photoToDelete.value);
  photoToDelete.value = null;
  showConfirmationModal.value = false;
};

// Expose methods for parent component
defineExpose({
  loadPhotos,
  handlePhotoUploaded
});
</script>

<style scoped>
/* Photos tab specific styles */

/* Success popup animations */
.success-popup-enter-active,
.success-popup-leave-active {
  transition: all 0.3s ease;
}

.success-popup-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.success-popup-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Custom animations */
@keyframes success-bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    transform: translate3d(0, -8px, 0);
  }
  70% {
    transform: translate3d(0, -4px, 0);
  }
  90% {
    transform: translate3d(0, -2px, 0);
  }
}

.animate-success-bounce {
  animation: success-bounce 1s ease-in-out;
}

/* Gradient text effect */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Confirmation modal animations */
.confirmation-popup-enter-active,
.confirmation-popup-leave-active {
  transition: all 0.3s ease;
}

.confirmation-popup-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.confirmation-popup-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
