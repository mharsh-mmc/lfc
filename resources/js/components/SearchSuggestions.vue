<template>
  <div v-if="showSuggestions && suggestions.length > 0" class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto">
    <div class="py-2">
      <div 
        v-for="suggestion in suggestions" 
        :key="suggestion.id"
        class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors"
        @click="selectSuggestion(suggestion)"
      >
        <div class="flex items-center space-x-3">
          <!-- Profile Photo -->
          <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 flex-shrink-0">
            <img 
              :src="suggestion.profile_photo_url || '/default-avatar.svg'" 
              :alt="suggestion.name"
              class="w-full h-full object-cover"
              @error="handleImageError"
            />
          </div>
          
          <!-- Suggestion Info -->
          <div class="flex-1 min-w-0">
            <div class="font-medium text-gray-900 truncate">{{ suggestion.name }}</div>
            <div class="text-sm text-gray-600 truncate">{{ suggestion.location }}</div>
            <div v-if="suggestion.profession" class="text-xs text-gray-500 truncate">{{ suggestion.profession }}</div>
          </div>
          
          <!-- Arrow Icon -->
          <div class="text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  showSuggestions: {
    type: Boolean,
    default: false
  },
  suggestions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['select']);

const handleImageError = (event) => {
  event.target.src = '/default-avatar.svg';
};

const selectSuggestion = (suggestion) => {
  emit('select', suggestion);
};

// Auto-hide suggestions when clicking outside
const hideSuggestions = () => {
  emit('hide');
};

// Add click outside listener
watch(() => props.showSuggestions, (show) => {
  if (show) {
    setTimeout(() => {
      document.addEventListener('click', hideSuggestions);
    }, 100);
  } else {
    document.removeEventListener('click', hideSuggestions);
  }
});
</script>

<style scoped>
/* Custom scrollbar for suggestions */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #1E3A8A;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #283593;
}
</style>
