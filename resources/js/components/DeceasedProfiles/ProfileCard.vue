<template>
  <div 
    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 cursor-pointer group"
    @click="$emit('view', profile)"
  >
    <!-- Profile Photo with Lazy Loading -->
    <div class="relative">
      <img 
        v-if="imageLoaded"
        :src="profile.profile_photo_url" 
        :alt="profile.name"
        class="w-full h-48 object-cover rounded-t-lg"
        @error="handleImageError"
      />
      <div 
        v-else
        class="w-full h-48 bg-gray-200 rounded-t-lg flex items-center justify-center"
      >
        <div class="animate-pulse w-16 h-16 bg-gray-300 rounded-full"></div>
      </div>
      <div class="absolute inset-0 bg-black bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-t-lg"></div>
    </div>

    <!-- Profile Info -->
    <div class="p-4">
      <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ profile.name }}</h3>
      <p class="text-sm text-gray-600 mb-2">{{ profile.years_lived }}</p>
      <p v-if="profile.memorial_message" class="text-sm text-gray-500 italic line-clamp-2">
        "{{ profile.memorial_message }}"
      </p>
      
      <!-- Action Buttons -->
      <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100">
        <button 
          @click.stop="$emit('view', profile)"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
          View Memorial
        </button>
        <div v-if="canEdit" class="flex space-x-2">
          <button 
            @click.stop="$emit('edit', profile)"
            class="text-gray-600 hover:text-gray-800 text-sm"
          >
            Edit
          </button>
          <button 
            @click.stop="$emit('delete', profile)"
            class="text-red-600 hover:text-red-800 text-sm"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  profile: {
    type: Object,
    required: true
  },
  currentUserId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['view', 'edit', 'delete'])

const imageLoaded = ref(false)

const canEdit = computed(() => {
  return props.profile.created_by === props.currentUserId
})

const handleImageError = (event) => {
  event.target.src = '/default-avatar.svg'
  imageLoaded.value = true
}

onMounted(() => {
  // Lazy load image
  const img = new Image()
  img.onload = () => {
    imageLoaded.value = true
  }
  img.onerror = () => {
    imageLoaded.value = true
  }
  img.src = props.profile.profile_photo_url
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
