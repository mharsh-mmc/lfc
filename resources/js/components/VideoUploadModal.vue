<template>
  <Modal :show="show" @close="$emit('close')" max-width="2xl">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Upload A Video</h2>
        <button 
          @click="$emit('close')" 
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Quality Instructions -->
      <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <p class="text-sm text-blue-800">
          For best results, video uploads should be in MP4 format and under 100MB. Supported formats: MP4, AVI, MOV, WMV, FLV, WebM.
        </p>
      </div>

      <!-- Upload Area -->
      <div 
        v-if="!selectedFile"
        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-colors"
        :class="{
          'border-blue-500 bg-blue-50': isDragOver,
          'border-gray-300': !isDragOver
        }"
        @drop.prevent="handleDrop"
        @dragover.prevent="isDragOver = true"
        @dragleave.prevent="isDragOver = false"
        @dragenter.prevent="isDragOver = true"
      >
        <!-- Upload Icon -->
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>

        <!-- Upload Text -->
        <p class="text-lg font-medium text-gray-900 mb-2">Drag and drop video file to upload</p>
        <p class="text-sm text-gray-500 mb-4">Your Video will be private until you publish your profile</p>

        <!-- File Input -->
        <input
          ref="fileInput"
          type="file"
          accept="video/*,.mp4,.avi,.mov,.wmv,.flv,.webm"
          class="hidden"
          @change="handleFileSelect"
        />

        <!-- Select Files Button -->
        <button
          @click="$refs.fileInput.click()"
          class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors"
        >
          Select Files
        </button>
      </div>

      <!-- Selected Video Information -->
      <div v-if="selectedFile" class="border-2 border-gray-300 rounded-lg p-6">
        <div class="flex items-center space-x-4">
          <!-- Video Icon -->
          <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2z"/>
            </svg>
          </div>

          <!-- Video Details -->
          <div class="flex-1">
            <h4 class="text-lg font-medium text-gray-900">{{ selectedFile.name }}</h4>
            <p class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
            <p class="text-sm text-gray-500">{{ selectedFile.type }}</p>
            
            <!-- Validation Status -->
            <div class="mt-2 flex items-center space-x-2">
              <!-- File Size Status -->
              <span v-if="selectedFile.size <= 100 * 1024 * 1024" class="text-xs text-green-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                File size OK
              </span>
              <span v-else class="text-xs text-red-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                File too large
              </span>
              
              <!-- Format Status -->
              <span v-if="isValidVideoFile(selectedFile)" class="text-xs text-green-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Format OK
              </span>
              <span v-else class="text-xs text-red-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                Invalid format
              </span>
            </div>
          </div>

          <!-- Remove Button -->
          <button
            @click="removeSelectedFile"
            class="text-red-500 hover:text-red-700 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
          </button>
        </div>

        <!-- Caption Input -->
        <div class="mt-4">
          <label for="caption" class="block text-sm font-medium text-gray-700 mb-2">
            Caption (optional)
          </label>
          <textarea
            id="caption"
            v-model="caption"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Add a caption to your video..."
            maxlength="500"
          ></textarea>
          <p class="text-xs text-gray-500 mt-1">{{ caption.length }}/500 characters</p>
        </div>

        <!-- Upload Progress Bar -->
        <div v-if="uploading" class="mt-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Uploading...</span>
            <span class="text-sm text-gray-500">{{ Math.round(uploadProgress) || 0 }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: (Math.round(uploadProgress) || 0) + '%' }"
            ></div>
          </div>
        </div>

        <!-- Upload Button -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
            :disabled="uploading"
          >
            Cancel
          </button>
          <button
            @click="uploadVideo"
            :disabled="!isFileValid || uploading"
            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="uploading">Uploading...</span>
            <span v-else>Upload Video</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Success Popup -->
    <SuccessPopup
      :show="showSuccessPopup"
      title="Video Uploaded Successfully!"
      message="Your video has been uploaded and is now available in your video gallery."
      button-text="Great!"
      @close="handleSuccessClose"
    />

    <!-- Error Popup -->
    <ErrorPopup
      :show="showErrorPopup"
      :title="errorTitle"
      :message="errorMessage"
      button-text="OK"
      @close="showErrorPopup = false"
    />
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import Modal from './Modal.vue'
import SuccessPopup from './SuccessPopup.vue'
import ErrorPopup from './ErrorPopup.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'uploaded'])

const selectedFile = ref(null)
const caption = ref('')
const uploading = ref(false)
const uploadProgress = ref(0)
const fileInput = ref(null)
const isDragOver = ref(false)
const showSuccessPopup = ref(false)
const showErrorPopup = ref(false)
const errorMessage = ref('')
const errorTitle = ref('Upload Failed')

const handleDrop = (event) => {
  isDragOver.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    
    // Check file size (100MB = 100 * 1024 * 1024 bytes)
    const maxSize = 100 * 1024 * 1024 // 100MB in bytes
    if (file.size > maxSize) {
      errorTitle.value = 'File Too Large'
      errorMessage.value = 'The video file size must be under 100MB. Please select a smaller video file.'
      showErrorPopup.value = true
      return
    }
    
    // Check if it's a valid video file
    if (!isValidVideoFile(file)) {
      errorTitle.value = 'Unsupported Format'
      errorMessage.value = 'Please select a video file in MP4, AVI, MOV, WMV, FLV, or WebM format.'
      showErrorPopup.value = true
      return
    }
    
    selectedFile.value = file
  }
}

const handleFileSelect = (event) => {
  const files = event.target.files
  if (files.length > 0) {
    const file = files[0]
    
    // Check file size (100MB = 100 * 1024 * 1024 bytes)
    const maxSize = 100 * 1024 * 1024 // 100MB in bytes
    if (file.size > maxSize) {
      errorTitle.value = 'File Too Large'
      errorMessage.value = 'The video file size must be under 100MB. Please select a smaller video file.'
      showErrorPopup.value = true
      event.target.value = ''
      return
    }
    
    // Check if it's a valid video file
    if (!isValidVideoFile(file)) {
      errorTitle.value = 'Unsupported Format'
      errorMessage.value = 'Please select a video file in MP4, AVI, MOV, WMV, FLV, or WebM format.'
      showErrorPopup.value = true
      event.target.value = ''
      return
    }
    
    selectedFile.value = file
  }
}

const removeSelectedFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const isValidVideoFormat = (fileType) => {
  const allowedTypes = [
    'video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 
    'video/x-ms-wmv', 'video/x-flv', 'video/webm'
  ]
  return allowedTypes.includes(fileType)
}

const isValidVideoFile = (file) => {
  // Check file extension
  const fileName = file.name.toLowerCase()
  const allowedExtensions = ['.mp4', '.avi', '.mov', '.wmv', '.flv', '.webm']
  const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext))
  
  // Check MIME type
  const isValidMimeType = isValidVideoFormat(file.type)
  
  // Allow if either extension is valid OR MIME type is valid
  return hasValidExtension || isValidMimeType
}

const isFileValid = computed(() => {
  if (!selectedFile.value) {
    return false
  }
  return selectedFile.value.size <= 100 * 1024 * 1024 && isValidVideoFile(selectedFile.value)
})

const uploadVideo = () => {
  if (!selectedFile.value) {
    errorTitle.value = 'No File Selected'
    errorMessage.value = 'Please select a file first.'
    showErrorPopup.value = true
    return
  }

  uploading.value = true
  uploadProgress.value = 0


  // Use Inertia.js to upload the video
  const form = useForm({
    file: selectedFile.value,
    media_type: 'video',
    title: caption.value.trim() || 'Untitled Video',
    description: caption.value.trim() || '',
    is_private: false,
  })

  form.post('/media/upload', {
    onProgress: (progress) => {
      uploadProgress.value = Math.round(progress)
    },
    onSuccess: () => {
      // Show success popup
      showSuccessPopup.value = true
      
      // Reset form
      selectedFile.value = null
      caption.value = ''
      uploading.value = false
      uploadProgress.value = 0
      
      // Emit uploaded event
      emit('uploaded')
    },
    onError: (errors) => {
  
      
      let message = 'Upload failed. Please check your input and try again.'
      let title = 'Upload Failed'
      
      if (errors.file) {
        if (errors.file.includes('file size')) {
          title = 'File Too Large'
          message = 'The video file size must be under 100MB.'
        } else if (errors.file.includes('file of type')) {
          title = 'Unsupported Format'
          message = 'Please select a video file in MP4, AVI, MOV, WMV, FLV, or WebM format.'
        } else if (errors.file.includes('required')) {
          title = 'Missing File'
          message = 'Please select a video file to upload.'
        }
      } else if (errors.media_type) {
        title = 'Invalid Media Type'
        message = 'Please select a valid video file.'
      } else if (errors.title) {
        title = 'Title Required'
        message = 'Please provide a title for your video.'
      } else if (errors.error && errors.error.includes('maximum limit')) {
        title = 'Upload Limit Reached'
        message = 'You have reached the maximum limit of 20 videos. Please delete some videos before uploading new ones.'
      }
      
      errorTitle.value = title
      errorMessage.value = message
      showErrorPopup.value = true
      uploading.value = false
      uploadProgress.value = 0
    },
    onFinish: () => {
      uploading.value = false
      uploadProgress.value = 0
    }
  })
}

const handleSuccessClose = () => {
  showSuccessPopup.value = false
  emit('close')
}
</script> 