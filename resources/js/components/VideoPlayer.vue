<template>
  <div class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="flex justify-between items-center p-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">{{ video?.caption || 'Video Player' }}</h3>
        <button 
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Video Container -->
      <div class="relative">
        <video
          ref="videoElement"
          :src="video?.url"
          class="w-full h-auto max-h-[70vh]"
          controls
          preload="metadata"
          @loadedmetadata="onVideoLoaded"
          @timeupdate="onTimeUpdate"
          @ended="onVideoEnded"
        >
          Your browser does not support the video tag.
        </video>
      </div>

      <!-- Video Info -->
      <div class="p-4">
        <div class="flex items-center justify-between mb-2">
          <h4 class="text-lg font-medium text-gray-900">{{ video?.caption }}</h4>
          <span class="text-sm text-gray-500">{{ formatDuration(video?.duration || 0) }}</span>
        </div>
        <p class="text-sm text-gray-600">{{ formatDate(video?.created_at) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  video: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const videoElement = ref(null)

const formatDuration = (seconds) => {
  if (!seconds) return '0:00'
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const onVideoLoaded = () => {
  if (videoElement.value) {
    // Video metadata loaded
    console.log('Video loaded:', videoElement.value.duration)
  }
}

const onTimeUpdate = () => {
  // Handle time updates if needed
}

const onVideoEnded = () => {
  // Handle video end if needed
}

// Close on escape key
const handleEscape = (event) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
})
</script> 