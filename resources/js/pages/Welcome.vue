<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import SignupPopup from '@/components/signup-popup.vue';
import Footer from '@/components/Footer.vue';
import Header from '@/components/Header.vue';
import TributeGrid from '@/components/TributeGrid.vue';
import SearchResults from '@/components/SearchResults.vue';

// Section Components
import HeroSection from '@/components/sections/HeroSection.vue';
import FindLovedOnesSection from '@/components/sections/FindLovedOnesSection.vue';
import DigitalLegacySection from '@/components/sections/DigitalLegacySection.vue';
import CaptureShareSection from '@/components/sections/CaptureShareSection.vue';
import StepByStepSection from '@/components/sections/StepByStepSection.vue';
import ReviewsSection from '@/components/sections/ReviewsSection.vue';

const showSignupPopup = ref(false);

// Search functionality
const searchResults = ref({});
const showSearchResults = ref(false);
const searchLoading = ref(false);
const currentPage = ref(1);

const performSearch = async (searchData, page = 1) => {
  if (!searchData.name && !searchData.city) return;
  
  searchLoading.value = true;
  currentPage.value = page;
  
  try {
    const params = new URLSearchParams();
    if (searchData.name) params.append('name', searchData.name);
    if (searchData.city) params.append('city', searchData.city);
    params.append('page', page);
    
    const response = await fetch(`/api/search/profiles?${params.toString()}`);
    if (response.ok) {
      searchResults.value = await response.json();
      // Add search term to results for pagination
      searchResults.value.searchTerm = `${searchData.name} ${searchData.city}`.trim();
      showSearchResults.value = true;
    } else {
      console.error('Search failed');
    }
  } catch (error) {
    console.error('Search error:', error);
  } finally {
    searchLoading.value = false;
  }
};

const handleSearchFromSection = (searchData) => {
  performSearch(searchData);
};

const closeSearchResults = () => {
  showSearchResults.value = false;
  searchResults.value = {};
};

const handlePageChange = (page) => {
  // Get current search data from results
  const searchTerm = searchResults.value.searchTerm || '';
  const searchParts = searchTerm.split(' ');
  const currentSearchData = {
    name: searchParts[0] || '',
    city: searchParts[1] || ''
  };
  performSearch(currentSearchData, page);
};

onMounted(() => {
  if (!localStorage.getItem('signupPopupShown')) {
    const onScroll = () => {
      showSignupPopup.value = true;
      localStorage.setItem('signupPopupShown', '1');
      window.removeEventListener('scroll', onScroll);
    };
    window.addEventListener('scroll', onScroll, { once: true });
  }
});

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
  <Head title="Welcome" />
  <div class="font-lato bg-[#F5EDE2]">
    <!-- Header -->
    <Header variant="dark" />

    <!-- Hero Section -->
    <HeroSection />

    <!-- Find Your Loved Ones -->
    <FindLovedOnesSection 
      @search="handleSearchFromSection"
    />

    <!-- Digital Legacy Platform -->
    <DigitalLegacySection />

    <!-- Tribute Grid -->
    <TributeGrid />

    <!-- Capture & Share It Forever -->
    <CaptureShareSection />

    <!-- Step by Step Section -->
    <StepByStepSection />

    <!-- Reviews Section -->
    <ReviewsSection />

    <!-- Footer -->
    <Footer />
    <SignupPopup v-model="showSignupPopup" />
    
    <!-- Search Results Modal -->
    <SearchResults 
      :show-results="showSearchResults"
      :search-term="searchResults.searchTerm || ''"
      :search-results="searchResults"
      :loading="searchLoading"
      @close="closeSearchResults"
      @page-change="handlePageChange"
    />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700,italic&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
.font-lato {
  font-family: 'Lato', sans-serif;
}
</style>
