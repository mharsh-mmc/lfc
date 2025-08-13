<template>
  <div class="flex flex-row w-full bg-amber-50 px-24 py-8 justify-between">
    <!-- Left Column -->
    <div class="flex flex-col flex-1 pr-8">
      <!-- Row 1: Profile image, name, profession, connections -->
      <div class="flex items-start space-x-6 mb-6">
        <!-- Profile image (circle, fully rounded) -->
        <div class="relative flex-shrink-0">
          <img 
            :src="profilePicPreview || profileUserData.profile_photo_url || '/default-avatar.png'" 
            :alt="profileUserData.name"
            class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg"
            style="margin-top: -80px;"
          >
          

          
          <!-- Profile Picture Upload Button (for own profile) -->
          <button 
            v-if="isOwnProfile"
            @click="editingProfilePic = true"
            class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-colors z-20"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </button>
        </div>
        
        <!-- Name, profession, connections -->
        <div class="flex flex-col justify-center">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ profileUserData.name }}</h1>
          <p class="text-lg text-gray-600 mb-1">{{ profileUserData.title || 'Member' }}</p>
          <p class="text-gray-500">{{ profileUserData.connections_count || 0 }} connections</p>
        </div>
      </div>

      <!-- Row 2: Date of Birth and Email -->
      <div class="flex items-center gap-8">
        <div class="flex items-center text-gray-600">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Date of Birth: {{ formatDate(profileUserData.date_of_birth) }}
        </div>
        <div class="flex items-center text-gray-600">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Email: {{ profileUserData.email }}
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div class="flex flex-col items-end">
      <div class="space-y-4">
        <!-- Location -->
        <div class="flex items-center text-gray-600">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          {{ profileUserData.location || 'Location not set' }}
        </div>

        <!-- Last updated -->
        <div class="flex items-center text-gray-600">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Last updated {{ formatDate(profileUserData.updated_at) }}
        </div>

        <!-- Candles and Flowers -->
        <div class="flex items-center space-x-6">
          <div class="flex items-center text-gray-600">
            <svg class="w-5 h-5 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
            </svg>
            {{ profileUserData.tributes_count || 0 }}
          </div>
          <div class="flex items-center text-gray-600">
            <svg class="w-5 h-5 mr-2 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
            </svg>
            {{ profileUserData.flowers_count || 0 }}
          </div>
        </div>

        <!-- Give Tribute button -->
        <div class="pt-2">
          <button class="bg-blue-400 text-white px-6 py-2 rounded-md hover:bg-blue-500 transition-colors">
            Give Tribute
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Profile Picture Upload Modal -->
  <div v-if="editingProfilePic && isOwnProfile" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
      <h3 class="text-lg font-semibold mb-4">Update Profile Picture</h3>
      
      <!-- Profile Picture Preview -->
      <div class="mb-4 flex justify-center">
        <div class="relative">
          <img 
            :src="profilePicPreview || profileUserData.profile_photo_url || '/default-avatar.png'" 
            :alt="profileUserData.name"
            class="w-32 h-32 rounded-full object-cover border-4 border-gray-200"
          >
        </div>
      </div>

      <!-- File Input -->
      <input
        ref="profilePicInput"
        type="file"
        accept="image/*"
        @change="updateProfilePicPreview"
        class="hidden"
      >

      <div class="flex space-x-3">
        <button 
          @click="selectProfilePic"
          class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
        >
          Select Picture
        </button>
        <button 
          @click="updateProfilePicture"
          :disabled="mediaForm.processing || !mediaForm.photo"
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
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

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



const emit = defineEmits(['profile-pic-updated']);

// Profile picture-related state
const editingProfilePic = ref(false);
const profilePicInput = ref(null);
const profilePicPreview = ref(null);

// Form for profile picture
const mediaForm = useForm({
  photo: null,
});

// Utility functions
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
};

// Profile picture functions
const selectProfilePic = () => {
  profilePicInput.value.click();
};

const updateProfilePicPreview = () => {
  const file = profilePicInput.value.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    profilePicPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
  mediaForm.photo = file;
};

const updateProfilePicture = () => {
  mediaForm.post('/profile/picture', {
    preserveScroll: true,
    onSuccess: () => {
      editingProfilePic.value = false;
      profilePicPreview.value = null;
      clearFileInput(profilePicInput);
      emit('profile-pic-updated');
    },
    onError: (errors) => {
      console.error('Profile picture update errors:', errors);
    },
  });
};

const clearFileInput = (inputRef) => {
  if (inputRef.value?.value) {
    inputRef.value.value = null;
  }
};

const cancelMediaEdit = () => {
  editingProfilePic.value = false;
  profilePicPreview.value = null;
  clearFileInput(profilePicInput);
  mediaForm.reset();
};
</script>

<style scoped>
/* Profile info specific styles */
</style>
