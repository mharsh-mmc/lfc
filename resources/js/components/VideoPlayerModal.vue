<template>
  <Modal :show="show" @close="$emit('close')" max-width="4xl">
    <div class="relative bg-black rounded-lg overflow-hidden">
      <!-- Video Container -->
      <div class="relative aspect-video bg-black">
        <video
          ref="videoPlayer"
          :src="video.url"
          class="w-full h-full"
          @loadedmetadata="onVideoLoaded"
          @timeupdate="onTimeUpdate"
          @ended="onVideoEnded"
          @play="onPlay"
          @pause="onPause"
        >
          Your browser does not support the video tag.
        </video>

        <!-- Custom Controls Overlay -->
        <div 
          v-if="showControls"
          class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"
          @mousemove="showControls = true"
          @mouseleave="hideControlsDelayed"
        >
          <!-- Top Controls -->
          <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-center">
            <h3 class="text-white font-medium">{{ video.caption || 'Video' }}</h3>
            <button 
              @click="$emit('close')"
              class="text-white hover:text-gray-300 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Center Play Button -->
          <div v-if="!isPlaying" class="absolute inset-0 flex items-center justify-center">
            <button 
              @click="togglePlay"
              class="bg-white bg-opacity-90 text-gray-800 p-4 rounded-full hover:bg-opacity-100 transition-all duration-200"
            >
              <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
              </svg>
            </button>
          </div>

          <!-- Bottom Controls -->
          <div class="absolute bottom-0 left-0 right-0 p-4">
            <!-- Progress Bar -->
            <div class="mb-4">
              <div 
                class="relative h-2 bg-gray-600 rounded-full cursor-pointer"
                @click="seekTo"
                ref="progressBar"
              >
                <div 
                  class="absolute top-0 left-0 h-full bg-blue-500 rounded-full transition-all duration-100"
                  :style="{ width: progress + '%' }"
                ></div>
                <div 
                  class="absolute top-0 h-full w-4 bg-blue-500 rounded-full transform -translate-x-1/2 cursor-pointer"
                  :style="{ left: progress + '%' }"
                ></div>
              </div>
            </div>

            <!-- Control Buttons -->
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <!-- Play/Pause Button -->
                <button 
                  @click="togglePlay"
                  class="text-white hover:text-gray-300 transition-colors"
                >
                  <svg v-if="!isPlaying" class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                  <svg v-else class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                  </svg>
                </button>

                <!-- Rewind/Forward Buttons -->
                <button 
                  @click="rewind"
                  class="text-white hover:text-gray-300 transition-colors"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                  </svg>
                </button>

                <button 
                  @click="forward"
                  class="text-white hover:text-gray-300 transition-colors"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 005 8v8a1 1 0 001.6.8l5.334-4z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.934 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.334-4z"/>
                  </svg>
                </button>

                <!-- Volume Control -->
                <div class="flex items-center space-x-2">
                  <button 
                    @click="toggleMute"
                    class="text-white hover:text-gray-300 transition-colors"
                  >
                    <svg v-if="!isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M6.343 6.343a1 1 0 000 1.414l8.486 8.486a1 1 0 001.414-1.414L7.757 5.343a1 1 0 00-1.414 0z"/>
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                  </button>
                  <input 
                    type="range" 
                    min="0" 
                    max="100" 
                    v-model="volume" 
                    @input="updateVolume"
                    class="w-16 h-1 bg-gray-600 rounded-lg appearance-none cursor-pointer slider"
                  />
                </div>
              </div>

              <!-- Time Display -->
              <div class="text-white text-sm">
                {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
              </div>

              <!-- Fullscreen Button -->
              <button 
                @click="toggleFullscreen"
                class="text-white hover:text-gray-300 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Loading Spinner -->
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import Modal from './Modal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  video: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const videoPlayer = ref(null)
const progressBar = ref(null)
const showControls = ref(true)
const isPlaying = ref(false)
const isLoading = ref(true)
const currentTime = ref(0)
const duration = ref(0)
const progress = ref(0)
const volume = ref(100)
const isMuted = ref(false)
const controlsTimeout = ref(null)

// Video event handlers
const onVideoLoaded = () => {
  isLoading.value = false
  duration.value = videoPlayer.value.duration
}

const onTimeUpdate = () => {
  if (videoPlayer.value) {
    currentTime.value = videoPlayer.value.currentTime
    progress.value = (currentTime.value / duration.value) * 100
  }
}

const onVideoEnded = () => {
  isPlaying.value = false
}

const onPlay = () => {
  isPlaying.value = true
}

const onPause = () => {
  isPlaying.value = false
}

// Control functions
const togglePlay = () => {
  if (videoPlayer.value) {
    if (isPlaying.value) {
      videoPlayer.value.pause()
    } else {
      videoPlayer.value.play()
    }
  }
}

const seekTo = (event) => {
  if (videoPlayer.value && progressBar.value) {
    const rect = progressBar.value.getBoundingClientRect()
    const clickX = event.clientX - rect.left
    const percentage = clickX / rect.width
    const newTime = percentage * duration.value
    videoPlayer.value.currentTime = newTime
  }
}

const rewind = () => {
  if (videoPlayer.value) {
    videoPlayer.value.currentTime = Math.max(0, currentTime.value - 10)
  }
}

const forward = () => {
  if (videoPlayer.value) {
    videoPlayer.value.currentTime = Math.min(duration.value, currentTime.value + 10)
  }
}

const toggleMute = () => {
  if (videoPlayer.value) {
    videoPlayer.value.muted = !isMuted.value
    isMuted.value = !isMuted.value
  }
}

const updateVolume = () => {
  if (videoPlayer.value) {
    videoPlayer.value.volume = volume.value / 100
  }
}

const toggleFullscreen = () => {
  if (videoPlayer.value) {
    if (document.fullscreenElement) {
      document.exitFullscreen()
    } else {
      videoPlayer.value.requestFullscreen()
    }
  }
}

const hideControlsDelayed = () => {
  if (controlsTimeout.value) {
    clearTimeout(controlsTimeout.value)
  }
  controlsTimeout.value = setTimeout(() => {
    if (isPlaying.value) {
      showControls.value = false
    }
  }, 3000)
}

const formatTime = (seconds) => {
  if (!seconds || isNaN(seconds)) return '0:00'
  const mins = Math.floor(seconds / 60)
  const secs = Math.floor(seconds % 60)
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

// Watch for show prop changes
watch(() => props.show, (newValue) => {
  console.log('VideoPlayerModal show changed:', newValue);
  if (newValue) {
    console.log('Video data:', props.video);
    showControls.value = true
    isLoading.value = true
  }
})

onMounted(() => {
  // Add keyboard shortcuts
  const handleKeydown = (event) => {
    if (!props.show) return
    
    switch (event.key) {
      case ' ':
        event.preventDefault()
        togglePlay()
        break
      case 'ArrowLeft':
        event.preventDefault()
        rewind()
        break
      case 'ArrowRight':
        event.preventDefault()
        forward()
        break
      case 'm':
      case 'M':
        event.preventDefault()
        toggleMute()
        break
      case 'f':
      case 'F':
        event.preventDefault()
        toggleFullscreen()
        break
      case 'Escape':
        if (document.fullscreenElement) {
          document.exitFullscreen()
        }
        break
    }
  }

  document.addEventListener('keydown', handleKeydown)

  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown)
    if (controlsTimeout.value) {
      clearTimeout(controlsTimeout.value)
    }
  })
})
</script>

<style scoped>
.slider::-webkit-slider-thumb {
  appearance: none;
  height: 12px;
  width: 12px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
}

.slider::-moz-range-thumb {
  height: 12px;
  width: 12px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
}
</style>
