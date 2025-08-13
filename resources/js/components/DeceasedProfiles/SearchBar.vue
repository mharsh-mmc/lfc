<template>
  <div class="mb-6">
    <div class="relative">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search memorial profiles..."
        class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        @input="handleSearch"
      />
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>
      <button
        v-if="searchQuery"
        @click="clearSearch"
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>
    
    <!-- Search Results Count -->
    <div v-if="searchQuery && resultsCount !== null" class="mt-2 text-sm text-gray-600">
      Found {{ resultsCount }} result{{ resultsCount !== 1 ? 's' : '' }}
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'

const props = defineProps({
  resultsCount: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['search', 'clear'])

const searchQuery = ref('')
const searchTimeout = ref(null)

const handleSearch = () => {
  // Clear existing timeout
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  // Debounce search
  searchTimeout.value = setTimeout(() => {
    emit('search', searchQuery.value)
  }, 300)
}

const clearSearch = () => {
  searchQuery.value = ''
  emit('clear')
}

// Clear timeout on unmount
onUnmounted(() => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
})
</script>
