<template>
  <div class="w-full">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="text-gray-500 mt-2">Loading videos...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="videos.length === 0" class="text-center py-12">
      <div class="max-w-md mx-auto">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Videos Yet</h3>
        <p class="text-gray-500 mb-6">Share your precious moments with family and friends</p>
        <button 
          v-if="isOwnProfile"
          @click="(event) => { console.log('Add video button clicked'); $emit('add-video'); }" 
          class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors"
        >
          Upload Your First Video
        </button>
      </div>
    </div>

    <!-- Videos Grid -->
    <div v-else class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Video Gallery</h3>
        <button 
          v-if="isOwnProfile"
          @click="(event) => { console.log('Add video header button clicked'); $emit('add-video'); }" 
          class="text-blue-600 hover:text-blue-800 flex items-center"
        >
          <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Add Video
        </button>
      </div>

      <!-- Responsive Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div 
          v-for="video in videos" 
          :key="video.id" 
          class="group relative bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200"
        >
          <!-- Video Thumbnail Container (4:2.5 ratio) -->
          <div class="relative aspect-[4/2.5] bg-gray-100">
            <img 
              :src="video.thumbnail" 
              :alt="video.caption || 'Video thumbnail'"
              class="w-full h-full object-cover"
            />
            
            <!-- Play Button Overlay -->
            <div class="absolute inset-0 pointer-events-none">
              <!-- Background overlay -->
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200"></div>
              
              <!-- Play Button (with pointer-events-auto to make it clickable) -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button 
                  @click="(event) => playVideo(video, event)"
                  class="bg-white bg-opacity-90 text-gray-800 p-3 rounded-full hover:bg-opacity-100 transition-all duration-200 pointer-events-auto cursor-pointer"
                >
                  <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Duration Badge -->
            <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
              {{ formatDuration(video.duration) }}
            </div>
          </div>

          <!-- Video Info -->
          <div class="p-4">
            <h4 class="font-medium text-gray-900 mb-1 line-clamp-2">
              {{ video.caption || 'Untitled Video' }}
            </h4>
            <p class="text-sm text-gray-500">
              {{ formatDate(video.created_at) }}
            </p>
            
            <!-- Action Buttons -->
            <div class="mt-3 flex justify-between items-center">
              <button 
                @click="(event) => playVideo(video, event)"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium cursor-pointer"
              >
                Play
              </button>
              <div class="flex space-x-2">
                                 <button 
                   v-if="isOwnProfile"
                   @click="(event) => { console.log('Edit button clicked', event); editVideo(video); }"
                   class="text-gray-500 hover:text-gray-700 cursor-pointer"
                 >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                                 <button 
                   v-if="isOwnProfile"
                   @click="(event) => { console.log('Delete button clicked', event); deleteVideo(video); }"
                   class="text-red-500 hover:text-red-700 cursor-pointer"
                 >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video Player Modal -->
    <VideoPlayerModal
      :show="showVideoPlayer"
      :video="selectedVideo"
      @close="closeVideoPlayer"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import VideoPlayerModal from './VideoPlayerModal.vue'

const props = defineProps({
  videos: {
    type: Array,
    default: () => []
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['add-video', 'edit-videos', 'play-video', 'edit-video', 'delete-video'])

const showVideoPlayer = ref(false)
const selectedVideo = ref(null)

const playVideo = (video, event) => {
  console.log('Play video clicked:', video);
  console.log('Event target:', event?.target);
  console.log('Current showVideoPlayer value:', showVideoPlayer.value);
  selectedVideo.value = video;
  showVideoPlayer.value = true;
  console.log('Video player modal should be open:', showVideoPlayer.value);
}

const closeVideoPlayer = () => {
  showVideoPlayer.value = false
  selectedVideo.value = null
}

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
    month: 'short',
    day: 'numeric'
  })
}

const editVideo = (video) => {
  console.log('Edit video clicked:', video);
  console.log('Emitting edit-video event');
  emit('edit-video', video)
}

const deleteVideo = (video) => {
  console.log('Delete video clicked:', video);
  emit('delete-video', video);
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>