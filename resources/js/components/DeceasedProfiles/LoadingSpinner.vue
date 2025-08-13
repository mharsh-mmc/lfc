<template>
  <div class="flex justify-center items-center py-8">
    <div class="relative">
      <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
      <div class="absolute inset-0 w-8 h-8 border-4 border-transparent border-t-blue-400 rounded-full animate-ping"></div>
    </div>
    <span v-if="message" class="ml-3 text-gray-600">{{ message }}</span>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: 'Loading...'
  }
})
</script>
