<template>
  <div class="space-y-6">
    <UnifiedMediaTab
      media-type="video"
      :user-id="userId"
      :is-own-profile="isOwnProfile"
      @media-uploaded="handleMediaUploaded"
      @media-updated="handleMediaUpdated"
      @media-deleted="handleMediaDeleted"
    />
  </div>
</template>

<script setup>
import UnifiedMediaTab from './UnifiedMediaTab.vue'

const props = defineProps({
  userId: {
    type: [String, Number],
    required: true
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  }
})

const handleMediaUploaded = () => {
  // Video uploaded successfully
}

const handleMediaUpdated = () => {
  // Video updated successfully
}

const handleMediaDeleted = (mediaId) => {
  // Video deleted successfully
}
</script>

<style scoped>
/* Video tab specific styles */
</style>
