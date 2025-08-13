<template>
  <Modal :show="show" @close="$emit('close')" max-width="4xl">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ letter?.title || 'Letter' }}</h2>
        <button 
          @click="$emit('close')" 
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Letter Content -->
      <div class="space-y-6">
        <!-- Image Letter -->
        <div v-if="letter?.type === 'uploaded' && letter?.url" class="text-center">
          <img 
            :src="letter.url" 
            :alt="letter.title"
            class="max-w-full max-h-96 object-contain rounded-lg shadow-lg mx-auto"
          />
        </div>

        <!-- Text Letter -->
        <div v-else-if="letter?.content" class="bg-yellow-50 p-6 rounded-lg">
          <div class="font-handwriting text-gray-800 leading-relaxed whitespace-pre-wrap">
            <!-- Letter Title -->
            <div v-if="letter.title" class="font-bold text-2xl mb-4 text-gray-900 text-center">
              {{ letter.title }}
            </div>
            <!-- Letter Description -->
            <div v-if="letter.description" class="text-gray-600 mb-4 italic text-center">
              {{ letter.description }}
            </div>
            <!-- Letter Content -->
            <div class="text-lg leading-relaxed">
              {{ letter.content }}
            </div>
          </div>
        </div>

        <!-- Letter Details -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h4 class="font-semibold text-gray-900 mb-2">Letter Details</h4>
              <div class="space-y-2 text-sm">
                <div>
                  <span class="font-medium text-gray-700">Title:</span>
                  <span class="ml-2 text-gray-600">{{ letter?.title || 'Untitled' }}</span>
                </div>
                <div v-if="letter?.description">
                  <span class="font-medium text-gray-700">Description:</span>
                  <span class="ml-2 text-gray-600">{{ letter.description }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-700">Type:</span>
                  <span class="ml-2 text-gray-600 capitalize">{{ letter?.type || 'Unknown' }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-700">Created:</span>
                  <span class="ml-2 text-gray-600">{{ formatDate(letter?.created_at) }}</span>
                </div>
              </div>
            </div>
            
            <div v-if="letter?.file_size">
              <h4 class="font-semibold text-gray-900 mb-2">File Information</h4>
              <div class="space-y-2 text-sm">
                <div>
                  <span class="font-medium text-gray-700">File Size:</span>
                  <span class="ml-2 text-gray-600">{{ formatFileSize(letter.file_size) }}</span>
                </div>
                <div v-if="letter?.mime_type">
                  <span class="font-medium text-gray-700">File Type:</span>
                  <span class="ml-2 text-gray-600">{{ letter.mime_type }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end space-x-3 mt-8">
        <button
          @click="$emit('close')"
          class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
        >
          Close
        </button>
        <button
          v-if="isOwnProfile"
          @click="deleteLetter"
          class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
        >
          Delete Letter
        </button>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref } from 'vue'
import Modal from './Modal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  letter: {
    type: Object,
    default: null
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'delete'])

const deleteLetter = () => {
  if (confirm('Are you sure you want to delete this letter? This action cannot be undone.')) {
    emit('delete', props.letter)
  }
}

const formatDate = (date) => {
  if (!date) return 'Unknown'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.font-handwriting {
  font-family: 'Cursive', 'Brush Script MT', 'Segoe Script', cursive;
}
</style> 