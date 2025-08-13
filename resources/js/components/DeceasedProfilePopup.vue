<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-playfair font-bold text-[#1E3A8A]">In Loving Memory</h2>
          <button 
            @click="closePopup"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <div v-if="profile" class="space-y-6">
          <!-- Hero Section -->
          <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Profile Photo -->
            <div class="relative">
              <div class="w-32 h-32 md:w-40 md:h-40 overflow-hidden rounded-full border-4 border-[#F5EDE2]">
                <img 
                  :src="profile.profile_photo_url || '/default-avatar.svg'" 
                  :alt="profile.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
              </div>
              <!-- Memorial Badge -->
              <div class="absolute -bottom-2 -right-2 bg-[#1E3A8A] text-white rounded-full w-8 h-8 flex items-center justify-center">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>

            <!-- Basic Info -->
            <div class="flex-1 text-center md:text-left">
              <h1 class="text-3xl md:text-4xl font-playfair font-bold text-[#1E3A8A] mb-2">
                {{ profile.name }}
              </h1>
              <div class="text-lg text-[#1F2937] mb-3">
                {{ profile.years_lived }}
              </div>
              <div v-if="profile.relationship" class="text-sm text-[#6B7280] mb-4">
                {{ profile.relationship }}
              </div>
              <div v-if="profile.birth_place || profile.death_place" class="text-sm text-[#6B7280]">
                <span v-if="profile.birth_place">{{ profile.birth_place }}</span>
                <span v-if="profile.birth_place && profile.death_place"> • </span>
                <span v-if="profile.death_place">{{ profile.death_place }}</span>
              </div>
            </div>
          </div>

          <!-- Memorial Message -->
          <div v-if="profile.memorial_message" class="bg-[#F5EDE2] rounded-lg p-6">
            <div class="text-center">
              <div class="text-2xl text-[#1E3A8A] mb-2">"</div>
              <p class="text-lg text-[#1F2937] italic leading-relaxed">
                {{ profile.memorial_message }}
              </p>
              <div class="text-2xl text-[#1E3A8A] mt-2">"</div>
            </div>
          </div>

          <!-- Biography -->
          <div v-if="profile.biography" class="space-y-4">
            <h3 class="text-xl font-playfair font-bold text-[#1E3A8A]">Life Story</h3>
            <div class="prose prose-lg max-w-none text-[#1F2937] leading-relaxed">
              <div v-html="formatBiography(profile.biography)"></div>
            </div>
          </div>

          <!-- Life Timeline -->
          <div class="space-y-4">
            <h3 class="text-xl font-playfair font-bold text-[#1E3A8A]">Life Timeline</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-8 h-8 bg-[#1E3A8A] rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="font-semibold text-[#1E3A8A]">Birth</span>
                </div>
                <div class="text-[#1F2937]">
                  {{ formatDate(profile.birth_date) }}
                  <span v-if="profile.birth_place" class="text-[#6B7280]"> • {{ profile.birth_place }}</span>
                </div>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center gap-3 mb-2">
                  <div class="w-8 h-8 bg-[#DC2626] rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <span class="font-semibold text-[#DC2626]">Passing</span>
                </div>
                <div class="text-[#1F2937]">
                  {{ formatDate(profile.death_date) }}
                  <span v-if="profile.death_place" class="text-[#6B7280]"> • {{ profile.death_place }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Age at Death -->
          <div v-if="profile.age_at_death" class="bg-[#1E3A8A] text-white rounded-lg p-6 text-center">
            <div class="text-3xl font-playfair font-bold mb-2">{{ profile.age_at_death }} years</div>
            <div class="text-lg opacity-90">of a life well lived</div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
            <button 
              @click="viewFullProfile"
              class="flex-1 bg-[#1E3A8A] text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#283593] transition-colors"
            >
              View Full Memorial
            </button>
            <button 
              @click="createTribute"
              class="flex-1 bg-[#F5EDE2] text-[#1E3A8A] px-6 py-3 rounded-lg font-semibold hover:bg-[#E5D5C2] transition-colors border border-[#1E3A8A]"
            >
              Create Your Own Memorial
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-else-if="loading" class="flex items-center justify-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#1E3A8A]"></div>
          <span class="ml-3 text-[#1E3A8A]">Loading memorial profile...</span>
        </div>

        <!-- Error State -->
        <div v-else class="text-center py-12">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
          </svg>
          <p class="text-gray-600">Unable to load memorial profile</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  profileId: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['close']);

const profile = ref(null);
const loading = ref(false);

const closePopup = () => {
  emit('close');
  profile.value = null;
};

const fetchProfile = async () => {
  if (!props.profileId) {
    console.log('⚠️ No profileId provided');
    return;
  }
  
  console.log('🔄 Starting to fetch profile:', props.profileId);
  loading.value = true;
  
  try {
    const response = await fetch(`/api/deceased-profiles/${props.profileId}`);
    console.log('📡 Profile API Response status:', response.status);
    
    if (response.ok) {
      const result = await response.json();
      console.log('📦 Raw profile API response:', result);
      
      // The API returns {data: {...}} structure
      profile.value = result.data || null;
      console.log('✅ Fetched profile:', profile.value);
    } else {
      console.error('❌ Failed to fetch profile - Status:', response.status);
      profile.value = null;
    }
  } catch (error) {
    console.error('💥 Error fetching profile:', error);
    profile.value = null;
  } finally {
    loading.value = false;
    console.log('🏁 Finished loading profile');
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const formatBiography = (text) => {
  if (!text) return '';
  // Convert line breaks to HTML paragraphs
  return text.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
};

const handleImageError = (event) => {
  event.target.src = '/default-avatar.svg';
};

const viewFullProfile = () => {
  if (profile.value) {
    router.visit(`/deceased-profiles/${profile.value.id}`);
  }
};

const createTribute = () => {
  router.visit('/profile');
};

// Watch for profileId changes
watch(() => props.profileId, (newId) => {
  if (newId && props.isOpen) {
    fetchProfile();
  }
});

// Watch for isOpen changes
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.profileId) {
    fetchProfile();
  }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700,italic&display=swap');

.font-playfair {
  font-family: 'Playfair Display', serif;
}

/* Custom scrollbar for the modal */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #1E3A8A;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #283593;
}
</style>
