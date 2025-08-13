<template>
  <Modal :show="show" @close="$emit('close')" max-width="4xl">
    <div class="relative bg-black rounded-lg overflow-hidden">
      <!-- Video Container -->
      <div class="relative aspect-video bg-black">
        <vue-plyr
          ref="videoPlayer"
          :src="video.url"
          :options="plyrOptions"
          class="w-full h-full"
          @ready="onPlayerReady"
          @timeupdate="onTimeUpdate"
          @ended="onVideoEnded"
          @play="onPlay"
          @pause="onPause"
        />

        <!-- Top Controls Overlay -->
        <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-center z-10">
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

        <!-- Loading Spinner -->
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center z-20">
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
const player = ref(null)
const isLoading = ref(true)
const currentTime = ref(0)
const duration = ref(0)
const isPlaying = ref(false)

const plyrOptions = {
  controls: [
    'play-large',
    'play',
    'rewind',
    'fast-forward',
    'progress',
    'current-time',
    'duration',
    'mute',
    'volume',
    'fullscreen'
  ],
  seekTime: 10,
  keyboard: { focused: true, global: true },
  autoplay: false,
  hideControls: false
}

// Video event handlers
const onPlayerReady = (plyrInstance) => {
  player.value = plyrInstance
  isLoading.value = false
  duration.value = plyrInstance.duration || 0
  console.log('Plyr player ready:', plyrInstance)
}

const onTimeUpdate = () => {
  if (player.value) {
    currentTime.value = player.value.currentTime
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

// Watch for show prop changes
watch(() => props.show, (newValue) => {
  console.log('VideoPlayerModal show changed:', newValue);
  if (newValue) {
    console.log('Video data:', props.video);
    isLoading.value = true
  }
})

onMounted(() => {
  // Add keyboard shortcuts
  const handleKeydown = (event) => {
    if (!props.show) return
    
    switch (event.key) {
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
    if (player.value) {
      player.value.destroy()
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
