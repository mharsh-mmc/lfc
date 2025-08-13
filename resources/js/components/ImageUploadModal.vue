<template>
  <Modal :show="show" @close="$emit('close')" max-width="2xl">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Upload An Image</h2>
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
          For best results, image uploads should be in JPEG, PNG, WebP, or GIF format and under 50MB. Supported formats: JPEG, JPG, PNG, WebP, GIF.
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>

        <!-- Upload Text -->
        <p class="text-lg font-medium text-gray-900 mb-2">Drag and drop image file to upload</p>
        <p class="text-sm text-gray-500 mb-4">Your Image will be private until you publish your profile</p>

        <!-- File Input -->
        <input
          ref="fileInput"
          type="file"
          accept="image/*,.jpg,.jpeg,.png,.webp,.gif"
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

      <!-- Selected Image Information -->
      <div v-if="selectedFile" class="border-2 border-gray-300 rounded-lg p-6">
        <div class="flex items-center space-x-4">
          <!-- Image Preview -->
          <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
            <img 
              v-if="imagePreview" 
              :src="imagePreview" 
              :alt="selectedFile.name"
              class="w-full h-full object-cover"
            />
            <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>

          <!-- Image Details -->
          <div class="flex-1">
            <h4 class="text-lg font-medium text-gray-900">{{ selectedFile.name }}</h4>
            <p class="text-sm text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
            <p class="text-sm text-gray-500">{{ selectedFile.type }}</p>
            
            <!-- Validation Status -->
            <div class="mt-2 flex items-center space-x-2">
              <!-- File Size Status -->
              <span v-if="selectedFile.size <= 50 * 1024 * 1024" class="text-xs text-green-600 flex items-center">
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
              <span v-if="isValidImageFile(selectedFile)" class="text-xs text-green-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                Format supported
              </span>
              <span v-else class="text-xs text-red-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                Format not supported
              </span>
            </div>
          </div>

          <!-- Remove Button -->
          <button
            @click="removeSelectedFile"
            class="text-red-600 hover:text-red-800 transition-colors"
            title="Remove file"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Caption Input (Optional) -->
      <div class="mt-6">
        <label for="caption" class="block text-sm font-medium text-gray-700 mb-2">
          Caption (Optional)
        </label>
        <input
          id="caption"
          v-model="caption"
          type="text"
          placeholder="Add a caption to your image..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <p class="text-xs text-gray-500 mt-1">You can add a caption now or edit it later</p>
      </div>

      <!-- Upload Progress Bar -->
      <div v-if="uploading" class="mt-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Uploading...</span>
          <span class="text-sm text-gray-500">{{ uploadProgress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div 
            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
            :style="{ width: uploadProgress + '%' }"
          ></div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3 mt-8">
        <button
          @click="$emit('close')"
          class="px-6 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition-colors"
        >
          Cancel
        </button>
        <button
          @click="uploadImage"
          :disabled="!selectedFile || uploading || !isFileValid"
          class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ uploading ? 'Uploading...' : 'Upload' }}
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, computed } from 'vue'
import Modal from './Modal.vue'
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
const isDragOver = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const imagePreview = ref(null)
const fileInput = ref(null)

const handleDrop = (event) => {
  isDragOver.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    
    // Check file size (50MB = 50 * 1024 * 1024 bytes)
    const maxSize = 50 * 1024 * 1024 // 50MB in bytes
    if (file.size > maxSize) {
      alert('❌ File too large: The image file size must be under 50MB. Please select a smaller image file.')
      return
    }
    
    // Check if it's a valid image file
    if (!isValidImageFile(file)) {
      alert('❌ Unsupported format: Please select an image file in JPEG, PNG, WebP, or GIF format.')
      return
    }
    
    selectedFile.value = file
    createImagePreview(file)
  }
}

const handleFileSelect = (event) => {
  const files = event.target.files
  if (files.length > 0) {
    const file = files[0]
    
    // Check file size (50MB = 50 * 1024 * 1024 bytes)
    const maxSize = 50 * 1024 * 1024 // 50MB in bytes
    if (file.size > maxSize) {
      alert('❌ File too large: The image file size must be under 50MB. Please select a smaller image file.')
      event.target.value = ''
      return
    }
    
    // Check if it's a valid image file
    if (!isValidImageFile(file)) {
      alert('❌ Unsupported format: Please select an image file in JPEG, PNG, WebP, or GIF format.')
      event.target.value = ''
      return
    }
    
    selectedFile.value = file
    createImagePreview(file)
  }
}

const createImagePreview = (file) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeSelectedFile = () => {
  selectedFile.value = null
  imagePreview.value = null
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

const isValidImageFormat = (fileType) => {
  const allowedTypes = [
    'image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'
  ]
  return allowedTypes.includes(fileType)
}

const isValidImageFile = (file) => {
  // Check file extension
  const fileName = file.name.toLowerCase()
  const allowedExtensions = ['.jpg', '.jpeg', '.png', '.webp', '.gif']
  const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext))
  
  // Check MIME type
  const isValidMimeType = isValidImageFormat(file.type)
  
  // Allow if either extension is valid OR MIME type is valid
  return hasValidExtension || isValidMimeType
}

const isFileValid = computed(() => {
  if (!selectedFile.value) {
    return false
  }
  return selectedFile.value.size <= 50 * 1024 * 1024 && isValidImageFile(selectedFile.value)
})

const uploadImage = () => {
  if (!selectedFile.value) {
    alert('❌ No file selected: Please select a file first.')
    return
  }

  uploading.value = true
  uploadProgress.value = 0
  console.log('Starting image upload...', selectedFile.value)

  // Use Inertia.js to upload the image
  const form = useForm({
    image: selectedFile.value,
    caption: caption.value.trim(),
  })

  form.post('/images', {
    onProgress: (progress) => {
      uploadProgress.value = Math.round(progress)
    },
    onSuccess: () => {
      alert('✅ Image uploaded successfully!')
      emit('uploaded')
      
      // Reset form
      selectedFile.value = null
      imagePreview.value = null
      caption.value = ''
      emit('close')
    },
    onError: (errors) => {
      console.error('Upload failed:', errors)
      
      let errorMessage = 'Upload failed'
      
      if (errors.image) {
        if (errors.image.includes('file size')) {
          errorMessage = '❌ File too large: The image file size must be under 50MB.'
        } else if (errors.image.includes('file of type')) {
          errorMessage = '❌ Unsupported format: Please select an image file in JPEG, PNG, WebP, or GIF format.'
        } else if (errors.image.includes('required')) {
          errorMessage = '❌ Missing file: Please select an image file to upload.'
        }
      } else if (errors.message && errors.message.includes('maximum limit')) {
        errorMessage = '❌ Upload limit reached: You have reached the maximum limit of 50 images. Please delete some images before uploading new ones.'
      }
      
      alert(errorMessage)
    },
    onFinish: () => {
      uploading.value = false
      uploadProgress.value = 0
    }
  })
}
</script> 