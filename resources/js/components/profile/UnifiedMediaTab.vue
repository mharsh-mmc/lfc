<template>
  <div class="space-y-6">
    <!-- Header with Upload Button -->
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">{{ getTabTitle() }}</h3>
      <button
        v-if="isOwnProfile && canUploadMore()"
        @click="showUploadModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Upload {{ getMediaTypeLabel() }}</span>
      </button>
    </div>

    <!-- Media Grid -->
    <div v-if="mediaItems.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="item in mediaItems"
        :key="item.id"
        class="relative group bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-200"
      >
        <!-- Media Preview -->
        <div class="relative">
          <!-- Video Thumbnail -->
          <div v-if="item.media_type === 'video' || item.collection_name === 'videos'" class="relative">
            <img
              :src="item.thumbnail_url || item.url"
              :alt="item.title"
              class="w-full h-48 object-cover cursor-pointer"
              @click="playVideo(item)"
            />
            
            <!-- Play Button Overlay -->
            <div class="absolute inset-0 pointer-events-none">
              <!-- Background overlay -->
              <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200"></div>
              
              <!-- Play Button (with pointer-events-auto to make it clickable) -->
              <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button 
                  @click="playVideo(item)"
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
              {{ formatDuration(item.duration) }}
            </div>
          </div>
          
          <!-- Image/Letter Preview -->
          <img
            v-else
            :src="item.url"
            :alt="item.title"
            class="w-full h-48 object-cover cursor-pointer"
            @click="viewMedia(item)"
          />
          
          <!-- Delete Button (top-right corner, owner only) -->
          <button
            v-if="isOwnProfile"
            @click="deleteMedia(item.id)"
            class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100 z-10"
            title="Delete"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <!-- Media Info -->
        <div class="p-4">
          <h4 class="font-medium text-gray-900 text-center line-clamp-2 mb-2">{{ item.title }}</h4>
          <p v-if="item.description" class="text-sm text-gray-600 text-center line-clamp-2 mb-3">{{ item.description }}</p>
          
          <!-- Action Buttons for Videos -->
          <div v-if="item.media_type === 'video' || item.collection_name === 'videos'" class="mt-3 flex justify-between items-center">
            <button 
              @click="playVideo(item)"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium cursor-pointer"
            >
              Play
            </button>
            <div class="flex space-x-2">
              <button 
                v-if="isOwnProfile"
                @click="editMedia(item)"
                class="text-gray-500 hover:text-gray-700 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button 
                v-if="isOwnProfile"
                @click="deleteMedia(item.id)"
                class="text-red-500 hover:text-red-700 cursor-pointer"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Regular info for non-videos -->
          <div v-else class="flex justify-between items-center text-xs text-gray-500">
            <span>{{ formatDate(item.created_at) }}</span>
            <span>{{ item.file_size }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="mediaItems.length === 0" class="text-center py-12">
      <div class="w-16 h-16 mx-auto mb-4 text-gray-300">
        <svg v-if="mediaType === 'video'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        <svg v-else-if="mediaType === 'image'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <svg v-else fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2z"/>
        </svg>
      </div>
      <h4 class="text-lg font-medium text-gray-900 mb-2">No {{ getMediaTypeLabel() }}s Yet</h4>
      <p class="text-gray-500 mb-6">Start building your {{ getMediaTypeLabel() }} collection</p>
      
      <button
        v-if="isOwnProfile"
        @click="showUploadModal = true"
        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors"
      >
        Upload Your First {{ getMediaTypeLabel() }}
      </button>
    </div>

    <!-- Limit Warning -->
    <div v-if="isOwnProfile && !canUploadMore()" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <p class="text-sm text-yellow-800">
          You've reached the maximum limit of {{ getMediaLimit() }} {{ getMediaTypeLabel() }}s. Delete some to add new ones.
        </p>
      </div>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Upload {{ getMediaTypeLabel() }}</h3>
          <button @click="closeUploadModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="uploadMedia" class="space-y-4">
          <!-- Title Input -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              id="title"
              v-model="uploadForm.title"
              type="text"
              required
              maxlength="255"
              :placeholder="`Enter a title for your ${getMediaTypeLabel()}`"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Description Input -->
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              id="description"
              v-model="uploadForm.description"
              rows="3"
              maxlength="1000"
              :placeholder="`Brief description of your ${getMediaTypeLabel()}`"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>



          <!-- Privacy Toggle -->
          <div class="flex items-center">
            <input
              id="is_private"
              v-model="uploadForm.is_private"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="is_private" class="ml-2 block text-sm text-gray-900">
              Make this {{ getMediaTypeLabel() }} private
            </label>
          </div>

          <!-- File Upload -->
          <div>
            <label for="file" class="block text-sm font-medium text-gray-700 mb-1">{{ getMediaTypeLabel() }} File *</label>
            <input
              id="file"
              ref="fileInput"
              type="file"
              :accept="getAcceptedFileTypes()"
              required
              @change="handleFileSelect"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <p class="text-xs text-gray-500 mt-1">{{ getFileTypeDescription() }}</p>
          </div>

          <!-- Preview -->
          <div v-if="filePreview" class="text-center">
            <img v-if="isImageFile" :src="filePreview" alt="Preview" class="w-full h-32 object-cover rounded-md" />
            <div v-else class="w-full h-32 bg-gray-100 rounded-md flex items-center justify-center">
              <span class="text-gray-500">{{ selectedFile?.name }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closeUploadModal"
              class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="uploading"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ uploading ? 'Uploading...' : `Upload ${getMediaTypeLabel()}` }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Media Preview Modal -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
      <!-- Debug info -->
      <div class="absolute top-4 left-4 bg-white p-2 rounded text-xs z-[10000]">
        Modal: {{ showPreviewModal }} | Media: {{ currentMedia?.title }} | Type: {{ currentMedia?.media_type }}
      </div>
      <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-gray-900">{{ currentMedia?.title }}</h3>
            <div class="flex space-x-2">
              <!-- Edit Button (only for owner) -->
              <button
                v-if="isOwnProfile"
                @click="showEditModal = true"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Edit</span>
              </button>
              <!-- Delete Button (only for owner) -->
              <button
                v-if="isOwnProfile"
                @click="deleteMedia(currentMedia.id)"
                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center space-x-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Delete</span>
              </button>
              <!-- Close Button -->
              <button 
                @click="closePreviewModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
          
          <div v-if="currentMedia" class="text-center">
            <!-- For Videos: Show a preview with play button -->
            <div v-if="currentMedia.media_type === 'video' || currentMedia.collection_name === 'videos'" class="relative">
              <div class="relative max-h-[60vh] rounded-lg overflow-hidden">
                <img
                  :src="currentMedia.thumbnail_url || currentMedia.url"
                  :alt="currentMedia.title"
                  class="w-full h-auto object-cover"
                />
                
                <!-- Play Button Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-20 flex items-center justify-center">
                  <button
                    @click="playVideo(currentMedia)"
                    class="bg-white bg-opacity-90 text-gray-800 p-4 rounded-full hover:bg-opacity-100 transition-all duration-200 cursor-pointer"
                  >
                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z"/>
                    </svg>
                  </button>
                </div>
                
                <!-- Duration Badge -->
                <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                  {{ formatDuration(currentMedia.duration) }}
                </div>
              </div>
              
              <div class="mt-4">
                <button
                  @click="playVideo(currentMedia)"
                  class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors"
                >
                  Play Full Video
                </button>
              </div>
            </div>
            
            <!-- Image Display -->
            <img 
              v-else
              :src="currentMedia.url"
              :alt="currentMedia.title"
              class="w-full h-auto max-h-[60vh] object-contain rounded-lg"
            />
            
            <!-- Media Info -->
            <div class="mt-6 text-left max-w-2xl mx-auto">
              <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ currentMedia.title }}</h4>
              <p v-if="currentMedia.description" class="text-gray-600 mb-4">{{ currentMedia.description }}</p>
              <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                <div>
                  <span class="font-medium">File Size:</span> {{ currentMedia.file_size }}
                </div>
                <div>
                  <span class="font-medium">File Type:</span> {{ currentMedia.file_type }}
                </div>
                <div>
                  <span class="font-medium">Uploaded:</span> {{ formatDate(currentMedia.created_at) }}
                </div>
                <div v-if="currentMedia.media_type === 'video' && currentMedia.duration">
                  <span class="font-medium">Duration:</span> {{ currentMedia.duration }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Edit {{ getMediaTypeLabel() }}</h3>
          <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="updateMedia" class="space-y-4">
          <!-- Title Input -->
          <div>
            <label for="edit-title" class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input
              id="edit-title"
              v-model="editForm.title"
              type="text"
              required
              maxlength="255"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Description Input -->
          <div>
            <label for="edit-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              id="edit-description"
              v-model="editForm.description"
              rows="3"
              maxlength="1000"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>



          <!-- Privacy Toggle -->
          <div class="flex items-center">
            <input
              id="edit-is_private"
              v-model="editForm.is_private"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="edit-is_private" class="ml-2 block text-sm text-gray-900">
              Make this {{ getMediaTypeLabel() }} private
            </label>
          </div>

          <!-- Submit Button -->
          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closeEditModal"
              class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="updating"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ updating ? 'Updating...' : 'Update' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Video Player Modal with vue-plyr -->
    <div v-if="showVideoPlayer" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-2 sm:p-4">
      <div class="bg-white rounded-lg shadow-xl w-[95%] sm:w-[90%] md:w-[80%] lg:w-[70%] xl:w-[60%] relative">
        <!-- Close Button - Top Right Corner -->
        <button 
          @click="closeVideoPlayer"
          class="absolute -top-2 -right-2 sm:-top-3 sm:-right-3 bg-red-500 text-white rounded-full w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center hover:bg-red-600 transition-colors z-10 shadow-lg"
          title="Close"
        >
          <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
        
        <div class="p-3 sm:p-4 md:p-6">
          <!-- Header -->
          <div class="mb-3 sm:mb-4">
            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 truncate pr-4">{{ selectedVideo?.title || 'Video Player' }}</h3>
          </div>
          
          <!-- Video Player Container - Fixed 16:9 Aspect Ratio -->
          <div v-if="selectedVideo" class="relative w-full">
            <!-- 16:9 Aspect Ratio Container -->
            <div class="relative w-full aspect-video">
              <vue-plyr 
                ref="plyrPlayer"
                :options="playerOptions"
                class="absolute inset-0 w-full h-full"
              >
                <video controls crossorigin playsinline class="w-full h-full">
                  <source :src="selectedVideo.url" type="video/mp4" />
                  Your browser does not support the video tag.
                </video>
              </vue-plyr>
            </div>
            
            <!-- Video Info -->
            <div class="mt-3 sm:mt-4 text-left">
              <h4 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">{{ selectedVideo.title }}</h4>
              <p v-if="selectedVideo.description" class="text-sm sm:text-base text-gray-600 mb-2 sm:mb-3">{{ selectedVideo.description }}</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 text-xs sm:text-sm text-gray-500">
                <div>
                  <span class="font-medium">File Size:</span> {{ selectedVideo.file_size }}
                </div>
                <div>
                  <span class="font-medium">Duration:</span> {{ formatDuration(selectedVideo.duration) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  mediaType: {
    type: String,
    required: true,
    validator: (value) => ['video', 'image', 'letter'].includes(value)
  },
  userId: {
    type: [String, Number],
    required: true
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['media-uploaded', 'media-updated', 'media-deleted'])

// Modal state
const showUploadModal = ref(false)
const showPreviewModal = ref(false)
const showEditModal = ref(false)
const showVideoPlayer = ref(false)
const fileInput = ref(null)
const filePreview = ref(null)
const uploading = ref(false)
const updating = ref(false)
const currentMedia = ref(null)
const selectedVideo = ref(null)

// Video player state
const selectedFile = ref(null)
const plyrPlayer = ref(null)

// Plyr player options
const playerOptions = {
  controls: [
    'play-large',  // Big play button
    'play',        // Play/pause toggle
    'rewind',      // Rewind 10s
    'fast-forward',// Forward 10s
    'progress',    // Progress bar
    'current-time',
    'duration',
    'mute',        // Mute toggle
    'volume',
    'fullscreen'   // Fullscreen toggle
  ],
  seekTime: 10,     // Skip time for forward/rewind
  keyboard: { focused: true, global: true }, // Keyboard shortcuts
  autoplay: false,
  hideControls: true, // Auto-hide when inactive
  resetOnEnd: true,
  clickToPlay: true,
  disableContextMenu: true
}

// Form data
const uploadForm = ref({
  title: '',
  description: '',
  is_private: false,
  file: null
})

const editForm = ref({
  title: '',
  description: '',
  is_private: false
})

// Media items
const mediaItems = ref([])

// Computed properties
const isImageFile = computed(() => {
  if (!selectedFile.value) return false
  return selectedFile.value.type.startsWith('image/')
})

// Flash message handling
const page = usePage()




// Methods
const getTabTitle = () => {
  return `${getMediaTypeLabel()}s`
}

const getMediaTypeLabel = () => {
  return props.mediaType.charAt(0).toUpperCase() + props.mediaType.slice(1)
}

const getAcceptedFileTypes = () => {
  const types = {
    video: 'video/*',
    image: 'image/*',
    letter: 'image/*'
  }
  return types[props.mediaType] || '*/*'
}

const getFileTypeDescription = () => {
  const descriptions = {
    video: 'MP4, MOV, AVI, WMV, FLV, WebM up to 100MB',
    image: 'JPG, PNG, WebP, GIF up to 10MB',
    letter: 'JPG, PNG, WebP up to 10MB'
  }
  return descriptions[props.mediaType] || 'All file types'
}

const getMediaLimit = () => {
  const limits = {
    video: 20,
    image: 100,
    letter: 50
  }
  return limits[props.mediaType] || 50
}

const canUploadMore = () => {
  return mediaItems.value.length < getMediaLimit()
}

// File handling
const handleFileSelect = (event) => {
  const file = event.target.files[0]
  console.log('File selected:', file)
  
  if (file) {
    // Validate file size
    const maxSizes = {
      video: 100 * 1024 * 1024, // 100MB
      image: 10 * 1024 * 1024,  // 10MB
      letter: 10 * 1024 * 1024, // 10MB
      document: 50 * 1024 * 1024 // 50MB
    }
    
    const maxSize = maxSizes[props.mediaType] || 10 * 1024 * 1024
    
    if (file.size > maxSize) {
      alert(`File size exceeds the maximum limit for ${props.mediaType}s (${formatBytes(maxSize)})`)
      event.target.value = ''
      return
    }
    
    // Validate file type
    const acceptedTypes = {
      video: ['video/mp4', 'video/mov', 'video/avi', 'video/wmv', 'video/flv', 'video/webm'],
      image: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'],
      letter: ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
      document: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    }
    
    const allowedTypes = acceptedTypes[props.mediaType] || ['*/*']
    
    if (!allowedTypes.includes('*/*') && !allowedTypes.includes(file.type)) {
      alert(`File type not supported for ${props.mediaType}s. Please select a valid file.`)
      event.target.value = ''
      return
    }
    
    selectedFile.value = file
    uploadForm.value.file = file
    
    console.log('File validated successfully:', {
      name: file.name,
      size: file.size,
      type: file.type,
      mediaType: props.mediaType
    })
    
    // Create preview for images
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        filePreview.value = e.target.result
      }
      reader.readAsDataURL(file)
    } else {
      filePreview.value = null
    }
  } else {
    console.log('No file selected')
    selectedFile.value = null
    uploadForm.value.file = null
    filePreview.value = null
  }
}

// Helper function to format bytes
const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

// Upload media
const uploadMedia = async () => {
  if (!uploadForm.value.title.trim() || !uploadForm.value.file) {
    return
  }

  uploading.value = true

  try {
    const formData = new FormData()
    formData.append('media_type', props.mediaType)
    formData.append('file', uploadForm.value.file)
    formData.append('title', uploadForm.value.title)
    formData.append('description', uploadForm.value.description || '')
    formData.append('is_private', uploadForm.value.is_private ? '1' : '0')

    console.log('Uploading media with data:', {
      media_type: props.mediaType,
      title: uploadForm.value.title,
      file: uploadForm.value.file.name,
      size: uploadForm.value.file.size
    })

    // Use fetch for API calls to avoid Inertia response issues
    const response = await fetch('/media/upload', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: formData,
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const result = await response.json()
      if (result.success) {
        console.log('Upload successful:', result)
        closeUploadModal()
        emit('media-uploaded')
        loadMedia()
      } else {
        console.error('Upload failed:', result.error)
        alert(result.error || 'Upload failed. Please try again.')
      }
    } else {
      const errorResult = await response.json()
      console.error('Upload failed:', errorResult)
      if (errorResult.errors) {
        // Handle validation errors
        const errorMessages = Object.values(errorResult.errors).flat().join(', ')
        alert('Upload failed: ' + errorMessages)
      } else {
        alert(errorResult.error || 'Upload failed. Please try again.')
      }
    }

  } catch (error) {
    console.error('Upload error:', error)
    alert(error.message || 'Upload failed. Please try again.')
  } finally {
    uploading.value = false
  }
}

// Load media
const loadMedia = async () => {
  try {
    console.log('Loading media for user:', props.userId, 'type:', props.mediaType)
    
    // Use fetch for API calls to avoid Inertia response issues
    const response = await fetch(`/media/${props.userId}?type=${props.mediaType}`, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const result = await response.json()
      console.log('Media API response:', result)
      
      if (result.success && result.data) {
        mediaItems.value = result.data
        console.log('Media items loaded:', mediaItems.value)
        
        // Debug: Check first item structure
        if (mediaItems.value.length > 0) {
          console.log('First media item:', mediaItems.value[0])
        }
      }
    } else {
      console.error('Failed to load media:', response.statusText)
    }
  } catch (error) {
    console.error('Failed to load media:', error)
  }
}

// View media
const viewMedia = (media) => {
  console.log('viewMedia called with:', media)
  console.log('Media type:', media.media_type)
  console.log('Collection name:', media.collection_name)
  
  // For videos, open the video player modal instead of preview modal
  if (media.media_type === 'video' || media.collection_name === 'videos') {
    playVideo(media)
  } else {
    // For images and other media, use the preview modal
    currentMedia.value = media
    showPreviewModal.value = true
    
    console.log('Modal state after setting:', { 
      showPreviewModal: showPreviewModal.value, 
      currentMedia: currentMedia.value 
    })
  }
}



// Close video player modal
const closeVideoPlayer = () => {
  // Pause the video before closing
  if (plyrPlayer.value?.player) {
    plyrPlayer.value.player.pause()
  }
  showVideoPlayer.value = false;
  selectedVideo.value = null;
}

// Player control methods
const playVideo = (video) => {
  console.log('Play video clicked:', video);
  selectedVideo.value = video;
  showVideoPlayer.value = true;
  console.log('Video player modal should be open:', showVideoPlayer.value);
  
  // Wait for next tick to ensure the player is mounted
  nextTick(() => {
    if (plyrPlayer.value?.player) {
      console.log('Plyr player ready');
    }
  });
}

// Update media
const updateMedia = async () => {
  if (!editForm.value.title.trim()) {
    return
  }

  updating.value = true

  try {
    // Use fetch for API calls to avoid Inertia response issues
    const response = await fetch(`/media/${currentMedia.value.id}`, {
      method: 'PUT',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(editForm.value),
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const result = await response.json()
      if (result.success) {
        currentMedia.value = { ...currentMedia.value, ...editForm.value }
        closeEditModal()
        emit('media-updated')
        loadMedia()
      } else {
        console.error('Update failed:', result.error)
        alert(result.error || 'Update failed. Please try again.')
      }
    } else {
      const errorResult = await response.json()
      console.error('Update failed:', errorResult)
      if (errorResult.errors) {
        // Handle validation errors
        const errorMessages = Object.values(errorResult.errors).flat().join(', ')
        alert('Update failed: ' + errorMessages)
      } else {
        alert(errorResult.error || 'Update failed. Please try again.')
      }
    }

  } catch (error) {
    console.error('Update error:', error)
    alert(error.message || 'Update failed. Please try again.')
  } finally {
    updating.value = false
  }
}

// Delete media
const deleteMedia = async (mediaId) => {
  if (!confirm('Are you sure you want to delete this item?')) {
    return
  }

  try {
    // Use fetch for API calls to avoid Inertia response issues
    const response = await fetch(`/media/${mediaId}`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      credentials: 'same-origin'
    })
    
    if (response.ok) {
      const result = await response.json()
      if (result.success) {
        emit('media-deleted', mediaId)
        loadMedia()
        if (currentMedia.value?.id === mediaId) {
          closePreviewModal()
        }
      } else {
        console.error('Delete failed:', result.error)
        alert(result.error || 'Failed to delete item. Please try again.')
      }
    } else {
      const errorResult = await response.json()
      console.error('Delete failed:', errorResult)
      alert(errorResult.error || 'Failed to delete item. Please try again.')
    }

  } catch (error) {
    console.error('Delete error:', error)
    alert('Failed to delete item. Please try again.')
  }
}

// Modal management
const closeUploadModal = () => {
  showUploadModal.value = false
  uploadForm.value = {
    title: '',
    description: '',
    is_private: false,
    file: null
  }
  filePreview.value = null
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const closePreviewModal = () => {
  showPreviewModal.value = false
  currentMedia.value = null
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = {
    title: '',
    description: '',
    is_private: false
  }
}

// Edit media
const editMedia = (media) => {
  editForm.value = {
    title: media.title,
    description: media.description || '',
    is_private: media.is_private
  }
  showEditModal.value = true
}

// Format date
const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Format duration
const formatDuration = (seconds) => {
  if (!seconds) return '0:00'
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

// Lifecycle
onMounted(() => {
  loadMedia()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom Plyr styling */
:deep(.plyr) {
  border-radius: 0.5rem;
  overflow: hidden;
  width: 100% !important;
  height: 100% !important;
}

:deep(.plyr__video-wrapper) {
  border-radius: 0.5rem;
  width: 100% !important;
  height: 100% !important;
}

:deep(.plyr__video) {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover;
}

:deep(.plyr__control--overlaid) {
  background: rgba(59, 130, 246, 0.9);
  border: 2px solid white;
}

:deep(.plyr__control--overlaid:hover) {
  background: rgba(59, 130, 246, 1);
}

:deep(.plyr__progress__played) {
  background: #3b82f6;
}

:deep(.plyr__control--overlaid) {
  width: 60px;
  height: 60px;
}

/* Ensure video fills the container properly */
:deep(video) {
  width: 100% !important;
  height: 100% !important;
  object-fit: cover;
}
</style>
