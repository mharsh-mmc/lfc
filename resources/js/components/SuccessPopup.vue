<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="transform opacity-0 scale-95"
    enter-to-class="transform opacity-100 scale-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="transform opacity-100 scale-100"
    leave-to-class="transform opacity-0 scale-95"
  >
    <div
      v-if="show"
      class="fixed inset-0 z-50 flex items-center justify-center"
      @click="handleBackdropClick"
    >
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black bg-opacity-50"></div>
      
      <!-- Popup -->
      <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full mx-4 p-6">
        <!-- Success Icon -->
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        
        <!-- Title -->
        <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
          {{ title }}
        </h3>
        
        <!-- Message -->
        <p class="text-sm text-gray-500 text-center mb-6">
          {{ message }}
        </p>
        
        <!-- Action Button -->
        <div class="flex justify-center">
          <button
            @click="handleClose"
            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium"
          >
            {{ buttonText }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Success!'
  },
  message: {
    type: String,
    default: 'Operation completed successfully.'
  },
  buttonText: {
    type: String,
    default: 'OK'
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['close'])

const handleClose = () => {
  emit('close')
}

const handleBackdropClick = (event) => {
  if (props.closeOnBackdrop && event.target === event.currentTarget) {
    handleClose()
  }
}
</script>
