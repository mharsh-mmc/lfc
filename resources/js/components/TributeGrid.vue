<template>
  <section class="bg-[#1E3A8A] py-16 px-4 md:px-24">
    <h4 class="text-2xl md:text-3xl font-playfair font-bold text-white mb-10 text-center">In Loving Memory</h4>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
      <div 
        v-for="(tribute, index) in tributes" 
        :key="tribute.id" 
        class="tribute-card bg-white rounded-xl shadow-lg p-6 flex flex-col items-center transform transition-all duration-500 hover:scale-105 hover:shadow-2xl cursor-pointer group"
        @mouseenter="handleHover(index)"
        @mouseleave="handleHoverLeave(index)"
        @click="openProfilePopup(tribute)"
      >
        <!-- Memorial Background -->
        <div class="relative w-28 h-28 mb-4 overflow-hidden rounded-full border-4 border-[#F5EDE2] group-hover:border-[#1E3A8A] transition-colors duration-300">
          <div class="absolute inset-0 bg-gradient-to-br from-amber-200 to-amber-400 opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
          <img 
            v-if="tribute.profile_photo_url && tribute.profile_photo_url !== '/default-avatar.svg'" 
            :src="tribute.profile_photo_url" 
            :alt="tribute.name" 
            class="w-full h-full object-cover relative z-10 group-hover:scale-110 transition-transform duration-500"
            @error="handleImageError"
          />
          <div 
            v-else 
            class="w-full h-full bg-gray-200 flex items-center justify-center relative z-10 group-hover:scale-110 transition-transform duration-500"
          >
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <!-- Shimmer Effect -->
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
        </div>
        
        <!-- Name with Elegant Typography -->
        <div class="font-playfair text-lg font-bold text-[#1E3A8A] mb-1 text-center group-hover:text-[#283593] transition-colors duration-300">
          {{ tribute.name }}
        </div>
        
        <!-- Years with Fade Effect -->
        <div class="text-[#1F2937] text-sm mb-1 opacity-80 group-hover:opacity-100 transition-opacity duration-300">
          {{ tribute.years_lived }}
        </div>
        
        <!-- Quote with Elegant Styling -->
        <div class="italic text-[#1F2937] text-xs text-center leading-relaxed group-hover:text-[#374151] transition-colors duration-300">
          "{{ tribute.memorial_message }}"
        </div>
        
        <!-- Memorial Icons -->
        <div class="flex items-center justify-center gap-3 mt-3 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
          <div class="w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/>
            </svg>
          </div>
          <div class="w-6 h-6 bg-gray-600 rounded-full flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
      <p class="text-white mt-2">Loading memorial profiles...</p>
    </div>
    
    <!-- Empty State -->
    <div v-if="!loading && tributes.length === 0" class="text-center py-8">
      <svg class="w-16 h-16 mx-auto mb-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
      </svg>
      <p class="text-white/70">No memorial profiles available yet</p>
    </div>

    <!-- Deceased Profile Popup -->
    <DeceasedProfilePopup 
      :is-open="showPopup" 
      :profile-id="selectedProfileId" 
      @close="closePopup" 
    />
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DeceasedProfilePopup from '@/components/DeceasedProfilePopup.vue';

const tributes = ref([]);
const loading = ref(true);
const showPopup = ref(false);
const selectedProfileId = ref(null);

const fetchTributes = async () => {
  console.log('🔄 Starting to fetch tributes...');
  try {
    const response = await fetch('/api/deceased-profiles/public');
    console.log('📡 API Response status:', response.status);
    
    if (response.ok) {
      const result = await response.json();
      console.log('📦 Raw API response:', result);
      
      // The API returns {data: [...], meta: {...}} structure
      tributes.value = result.data || [];
      console.log('✅ Fetched tributes:', tributes.value);
      console.log('📊 Number of tributes:', tributes.value.length);
    } else {
      console.error('❌ Failed to fetch tributes - Status:', response.status);
      // Fallback to static data if API fails
      tributes.value = getFallbackTributes();
      console.log('🔄 Using fallback tributes');
    }
  } catch (error) {
    console.error('💥 Error fetching tributes:', error);
    // Fallback to static data
    tributes.value = getFallbackTributes();
    console.log('🔄 Using fallback tributes due to error');
  } finally {
    loading.value = false;
    console.log('🏁 Finished loading tributes');
  }
};

const getFallbackTributes = () => [
  {
    id: 1,
    name: 'Liam Carter',
    years_lived: '1945–2023',
    memorial_message: 'A lifetime of love and laughter',
    profile_photo_url: '/landing/memory-1.jpg'
  },
  {
    id: 2,
    name: 'Sophia Ross',
    years_lived: '1952–2024',
    memorial_message: 'Her kindness touched every life',
    profile_photo_url: '/landing/memory-2.jpg'
  },
  {
    id: 3,
    name: 'Zara El-Sayed',
    years_lived: '1991–2024',
    memorial_message: 'A lifetime of service to others',
    profile_photo_url: '/landing/memory-3.jpg'
  },
  {
    id: 4,
    name: 'Mateo Fernandez',
    years_lived: '1938–2023',
    memorial_message: 'Strength and wisdom in every word',
    profile_photo_url: '/landing/memory-4.jpg'
  },
  {
    id: 5,
    name: 'Kenji Tanaka',
    years_lived: '1947–2024',
    memorial_message: 'Honor and tradition lived daily',
    profile_photo_url: '/landing/memory-5.jpg'
  },
  {
    id: 6,
    name: 'Amelia Thompson',
    years_lived: '1955–2023',
    memorial_message: 'Grace and beauty in every moment',
    profile_photo_url: '/landing/memory-6.jpg'
  },
  {
    id: 7,
    name: 'Mark Walters',
    years_lived: '1940–2024',
    memorial_message: 'Adventure and courage defined him',
    profile_photo_url: '/landing/memory-7.jpg'
  },
  {
    id: 8,
    name: 'Anika Patel',
    years_lived: '1962–2023',
    memorial_message: 'Compassion flowed from her heart',
    profile_photo_url: '/landing/memory-8.jpg'
  }
];

const handleHover = (index) => {
  console.log(`Hovering over tribute ${index}`);
};

const handleHoverLeave = (index) => {
  console.log(`Left tribute ${index}`);
};

const openProfilePopup = (tribute) => {
  selectedProfileId.value = tribute.id;
  showPopup.value = true;
};

const closePopup = () => {
  showPopup.value = false;
  selectedProfileId.value = null;
};

const handleImageError = (event) => {
  console.log('Image failed to load:', event.target.src);
  event.target.style.display = 'none';
  // Show fallback icon
  const fallback = event.target.nextElementSibling;
  if (fallback) {
    fallback.style.display = 'flex';
  }
};

onMounted(() => {
  fetchTributes();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700,italic&display=swap');

.font-playfair {
  font-family: 'Playfair Display', serif;
}

.tribute-card {
  position: relative;
  overflow: hidden;
}

.tribute-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.tribute-card:hover::before {
  left: 100%;
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #1E3A8A;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #283593;
}
</style> 