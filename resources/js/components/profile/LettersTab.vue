<template>
  <div class="space-y-6">
    <UnifiedMediaTab
      media-type="letter"
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
  console.log('Letter uploaded successfully')
}

const handleMediaUpdated = () => {
  console.log('Letter updated successfully')
}

const handleMediaDeleted = (mediaId) => {
  console.log('Letter deleted:', mediaId)
}
</script>
