<template>
  <section class="bg-[#F9FAFB] py-16 px-4 md:px-24 text-center">
    <h2 class="text-3xl md:text-4xl font-playfair font-bold text-[#1E3A8A] mb-2">
      Find Your Loved Ones
    </h2>
    <p class="text-lg text-[#1F2937] mb-8">Start discovering your family story</p>
    <div class="flex flex-col md:flex-row items-center justify-center gap-12">
      <img 
        src="/landing/find-loved-ones.jpg" 
        alt="Find Loved Ones" 
        class="rounded-xl shadow-lg w-full md:w-1/2 max-w-lg object-cover" 
      />
      <div class="flex-1 flex flex-col items-center md:items-start">
        <p class="text-base text-[#1F2937] mb-4">
          Search billions of ancestor profiles, photographs, and historical documents.
        </p>
        <form @submit="handleSearch" class="flex flex-col md:flex-row gap-4 w-full max-w-md mx-auto md:mx-0">
          <div class="flex-1 relative">
            <input 
              v-model="searchName"
              type="text" 
              placeholder="Enter name" 
              class="w-full px-4 py-2 rounded-md border border-[#D6D3D1] focus:ring-2 focus:ring-[#1E3A8A]" 
              @input="handleNameInput"
              @focus="debouncedFetchSuggestions"
            />
            <!-- Suggestions for name field -->
            <SearchSuggestions 
              v-if="searchName && searchName.length >= 2"
              :show-suggestions="showSuggestions"
              :suggestions="suggestions"
              @select="selectSuggestion"
              @hide="hideSuggestions"
            />
          </div>
          <div class="flex-1 relative">
            <input 
              v-model="searchCity"
              type="text" 
              placeholder="Enter place" 
              class="w-full px-4 py-2 rounded-md border border-[#D6D3D1] focus:ring-2 focus:ring-[#1E3A8A]" 
              @input="handleCityInput"
              @focus="debouncedFetchSuggestions"
            />
            <!-- Suggestions for city field -->
            <SearchSuggestions 
              v-if="searchCity && searchCity.length >= 2"
              :show-suggestions="showSuggestions"
              :suggestions="suggestions"
              @select="selectSuggestion"
              @hide="hideSuggestions"
            />
          </div>
          <button 
            type="submit"
            :disabled="searchLoading || (!searchName && !searchCity)"
            class="bg-[#1E3A8A] text-white px-6 py-2 rounded-full font-bold font-playfair shadow hover:bg-[#283593] transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ searchLoading ? 'Searching...' : 'Search' }}
          </button>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import SearchSuggestions from '@/components/SearchSuggestions.vue';

const emit = defineEmits(['search']);

// Search functionality
const searchName = ref('');
const searchCity = ref('');
const searchLoading = ref(false);

// Live search suggestions
const suggestions = ref([]);
const showSuggestions = ref(false);
const suggestionsLoading = ref(false);
const suggestionsTimeout = ref(null);

const handleSearch = (event) => {
  event.preventDefault();
  emit('search', { name: searchName.value, city: searchCity.value });
};

const fetchSuggestions = async () => {
  if ((!searchName.value || searchName.value.length < 2) && 
      (!searchCity.value || searchCity.value.length < 2)) {
    suggestions.value = [];
    showSuggestions.value = false;
    return;
  }

  suggestionsLoading.value = true;
  
  try {
    const params = new URLSearchParams();
    if (searchName.value) params.append('name', searchName.value);
    if (searchCity.value) params.append('city', searchCity.value);
    
    const response = await fetch(`/api/search/suggestions?${params.toString()}`);
    if (response.ok) {
      suggestions.value = await response.json();
      showSuggestions.value = suggestions.value.length > 0;
    } else {
      suggestions.value = [];
      showSuggestions.value = false;
    }
  } catch (error) {
    console.error('Suggestions error:', error);
    suggestions.value = [];
    showSuggestions.value = false;
  } finally {
    suggestionsLoading.value = false;
  }
};

const debouncedFetchSuggestions = () => {
  // Clear existing timeout
  if (suggestionsTimeout.value) {
    clearTimeout(suggestionsTimeout.value);
  }
  
  // Set new timeout
  suggestionsTimeout.value = setTimeout(() => {
    fetchSuggestions();
  }, 300); // 300ms delay
};

const handleNameInput = () => {
  debouncedFetchSuggestions();
};

const handleCityInput = () => {
  debouncedFetchSuggestions();
};

const selectSuggestion = (suggestion) => {
  // Extract name and location from suggestion
  const nameParts = suggestion.name.split(' ');
  const locationParts = suggestion.location.split(', ');
  
  // Try to intelligently fill the fields
  if (nameParts.length > 0) {
    searchName.value = suggestion.name;
  }
  
  if (locationParts.length > 0) {
    searchCity.value = locationParts[0]; // Use first part of location (city)
  }
  
  showSuggestions.value = false;
  
  // Automatically perform search
  emit('search', { name: searchName.value, city: searchCity.value });
};

const hideSuggestions = () => {
  showSuggestions.value = false;
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700,italic&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
</style>
