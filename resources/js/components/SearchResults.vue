<template>
  <div v-if="showResults" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-xl">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-playfair font-bold text-[#1E3A8A]">Search Results</h2>
            <p class="text-sm text-gray-600 mt-1">
              Found {{ totalResults }} profile{{ totalResults !== 1 ? 's' : '' }} for "{{ searchTerm }}"
            </p>
          </div>
          <button 
            @click="closeResults"
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
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#1E3A8A]"></div>
          <span class="ml-3 text-[#1E3A8A]">Searching profiles...</span>
        </div>

        <!-- Results -->
        <div v-else-if="profiles.length > 0" class="space-y-6">
          <!-- Search Results Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="profile in profiles" 
              :key="profile.id"
              class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer"
              @click="viewProfile(profile)"
            >
              <!-- Profile Photo -->
              <div class="flex items-center space-x-4 mb-4">
                <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-[#F5EDE2]">
                  <img 
                    :src="profile.profile_photo_url || '/default-avatar.svg'" 
                    :alt="profile.name"
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                  />
                </div>
                <div class="flex-1">
                  <h3 class="font-playfair font-bold text-lg text-[#1E3A8A]">{{ profile.name }}</h3>
                  <p v-if="profile.profession" class="text-sm text-gray-600">{{ profile.profession }}</p>
                </div>
              </div>

              <!-- Location -->
              <div v-if="profile.city || profile.state || profile.country" class="mb-3">
                <div class="flex items-center text-sm text-gray-600">
                  <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                  </svg>
                  <span>{{ formatLocation(profile) }}</span>
                </div>
              </div>

              <!-- Bio Preview -->
              <div v-if="profile.bio" class="text-sm text-gray-700 line-clamp-3">
                {{ truncateBio(profile.bio) }}
              </div>

              <!-- View Profile Button -->
              <button class="w-full mt-4 bg-[#1E3A8A] text-white px-4 py-2 rounded-md hover:bg-[#283593] transition-colors text-sm font-semibold">
                View Profile
              </button>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex justify-center items-center space-x-2 pt-6 border-t border-gray-200">
            <button 
              @click="changePage(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            
            <span class="px-3 py-2 text-sm text-gray-600">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            
            <button 
              @click="changePage(currentPage + 1)"
              :disabled="currentPage >= totalPages"
              class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>

        <!-- No Results -->
        <div v-else class="text-center py-12">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">No profiles found</h3>
          <p class="text-gray-600 mb-4">Try adjusting your search terms or browse our memorial profiles instead.</p>
          <button 
            @click="browseMemorials"
            class="bg-[#1E3A8A] text-white px-6 py-2 rounded-md hover:bg-[#283593] transition-colors"
          >
            Browse Memorials
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  showResults: {
    type: Boolean,
    default: false
  },
  searchTerm: {
    type: String,
    default: ''
  },
  searchResults: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);

const closeResults = () => {
  emit('close');
};

const profiles = computed(() => props.searchResults.data || []);
const totalResults = computed(() => props.searchResults.total || 0);
const currentPage = computed(() => props.searchResults.current_page || 1);
const totalPages = computed(() => props.searchResults.last_page || 1);

const formatLocation = (profile) => {
  const parts = [profile.city, profile.state, profile.country].filter(Boolean);
  return parts.join(', ');
};

const truncateBio = (bio) => {
  if (!bio) return '';
  return bio.length > 120 ? bio.substring(0, 120) + '...' : bio;
};

const handleImageError = (event) => {
  event.target.src = '/default-avatar.svg';
};

const viewProfile = (profile) => {
  router.visit(`/profile/${profile.id}`);
};

const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    emit('page-change', page);
  }
};

const browseMemorials = () => {
  closeResults();
  // Scroll to tribute grid section
  const tributeSection = document.querySelector('.bg-[#1E3A8A]');
  if (tributeSection) {
    tributeSection.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700,italic&display=swap');

.font-playfair {
  font-family: 'Playfair Display', serif;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
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
