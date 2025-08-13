<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <ProfileCard
      v-for="profile in visibleProfiles"
      :key="profile.id"
      :profile="profile"
      :current-user-id="currentUserId"
      @view="handleView"
      @edit="handleEdit"
      @delete="handleDelete"
    />
  </div>
  
  <!-- Load More Button -->
  <div v-if="hasMore" class="text-center mt-8">
    <button
      @click="loadMore"
      :disabled="loading"
      class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
    >
      <span v-if="loading">Loading...</span>
      <span v-else>Load More</span>
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import ProfileCard from './ProfileCard.vue'

const props = defineProps({
  profiles: {
    type: Array,
    default: () => []
  },
  currentUserId: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 12
  }
})

const emit = defineEmits(['view', 'edit', 'delete', 'load-more'])

const loading = ref(false)
const currentPage = ref(1)

const visibleProfiles = computed(() => {
  const start = 0
  const end = currentPage.value * props.perPage
  return props.profiles.slice(start, end)
})

const hasMore = computed(() => {
  return visibleProfiles.value.length < props.profiles.length
})

const handleView = (profile) => {
  emit('view', profile)
}

const handleEdit = (profile) => {
  emit('edit', profile)
}

const handleDelete = (profile) => {
  emit('delete', profile)
}

const loadMore = async () => {
  if (loading.value || !hasMore.value) return
  
  loading.value = true
  currentPage.value++
  
  // Emit event to parent to load more data if needed
  emit('load-more', currentPage.value)
  
  // Simulate loading delay
  await new Promise(resolve => setTimeout(resolve, 500))
  loading.value = false
}

// Reset pagination when profiles change
watch(() => props.profiles, () => {
  currentPage.value = 1
}, { deep: true })
</script>
