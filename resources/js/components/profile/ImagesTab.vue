<template>
  <div class="space-y-6">
    <UnifiedMediaTab
      media-type="image"
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
  console.log('Image uploaded successfully')
}

const handleMediaUpdated = () => {
  console.log('Image updated successfully')
}

const handleMediaDeleted = (mediaId) => {
  console.log('Image deleted:', mediaId)
}
</script>
