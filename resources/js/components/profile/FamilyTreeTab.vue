<template>
  <div class="bg-white rounded-lg shadow-sm">
    <!-- Mode Toggle and Controls -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200">
      <div class="flex items-center space-x-4">
        <h3 class="text-xl font-semibold text-gray-900">Family Tree</h3>
        <div class="flex items-center space-x-2">
          <button
            @click="currentMode = 'viewer'"
            :class="[
              'px-3 py-2 text-sm rounded-md transition-colors',
              currentMode === 'viewer'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Viewer Mode
          </button>
          <button
            v-if="canEdit"
            @click="currentMode = 'builder'"
            :class="[
              'px-3 py-2 text-sm rounded-md transition-colors',
              currentMode === 'builder'
                ? 'bg-green-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
          >
            Builder Mode
          </button>
        </div>
      </div>
      

    </div>

    <!-- Content -->
    <div class="p-6">
      <!-- Family Tree Component -->
      <FamilyTree
        :profile-user-data="profileUserData"
        :is-editable="currentMode === 'builder' && canEdit"
        @tree-updated="handleTreeUpdated"
      />
    </div>


  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import FamilyTree from './family-tree/FamilyTree.vue';

const props = defineProps({
  profileUserData: {
    type: Object,
    required: true
  },
  currentUser: {
    type: Object,
    default: null
  }
});

// Reactive state
const currentMode = ref('viewer');

// Computed properties
const canEdit = computed(() => {
  if (!props.currentUser) return false;
  return props.currentUser.id === props.profileUserData.id;
});



// Methods
const handleTreeUpdated = () => {
  // Emit event to parent if needed
  // This could trigger a refresh of the tree data
};



// Set initial mode based on permissions
onMounted(() => {
  console.log('FamilyTreeTab mounted');
  console.log('Profile user data:', props.profileUserData);
  console.log('Current user:', props.currentUser);
  console.log('Can edit:', canEdit.value);
  
  if (canEdit.value) {
    currentMode.value = 'builder';
    console.log('Set mode to builder');
  } else {
    currentMode.value = 'viewer';
    console.log('Set mode to viewer');
  }
  
  console.log('Final mode:', currentMode.value);
});
</script>

<style scoped>
/* Family Tree Tab specific styles */
</style>
