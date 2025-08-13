<template>
  <div class="relative group">
    <!-- Vue Flow Handles for Connections -->
    <!-- Top Handle -->
    <Handle
      type="source"
      position="top"
      :id="`${node.id}-top`"
      class="w-4 h-4 bg-blue-500 border-2 border-white rounded-full hover:bg-blue-600 hover:scale-125 transition-all duration-200"
    />
    
    <!-- Right Handle -->
    <Handle
      type="source"
      position="right"
      :id="`${node.id}-right`"
      class="w-4 h-4 bg-green-500 border-2 border-white rounded-full hover:bg-green-600 hover:scale-125 transition-all duration-200"
    />
    
    <!-- Bottom Handle -->
    <Handle
      type="source"
      position="bottom"
      :id="`${node.id}-bottom`"
      class="w-4 h-4 bg-purple-500 border-2 border-white rounded-full hover:bg-purple-600 hover:scale-125 transition-all duration-200"
    />
    
    <!-- Left Handle -->
    <Handle
      type="source"
      position="left"
      :id="`${node.id}-left`"
      class="w-4 h-4 bg-orange-500 border-2 border-white rounded-full hover:bg-orange-600 hover:scale-125 transition-all duration-200"
    />

    <!-- Main Node Container -->
    <div
      class="relative bg-white border-2 rounded-full shadow-lg p-4 min-w-[140px] transition-all duration-200 cursor-pointer"
      :class="[
        isCenter 
          ? 'border-blue-500 shadow-blue-200 scale-110' 
          : 'border-gray-300 hover:border-blue-400 hover:shadow-xl',
        isSelected ? 'ring-2 ring-blue-500' : ''
      ]"
    >
      <!-- Profile Photo -->
      <div class="flex justify-center mb-3">
        <div class="relative">
          <img
            :src="node.data.profile_photo_url || '/default-avatar.svg'"
            :alt="node.data.name"
            class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
          />
          <!-- Center Node Indicator -->
          <div
            v-if="isCenter"
            class="absolute -top-1 -right-1 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center"
          >
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Name -->
      <div class="text-center mb-2">
        <h4 class="font-semibold text-gray-900 text-sm truncate" :title="node.data.name">
          {{ node.data.name }}
        </h4>
      </div>

      <!-- Username -->
      <div v-if="node.data.showDetails !== false" class="text-center mb-2">
        <p class="text-xs text-gray-500 truncate" :title="node.data.username || 'No username'">
          {{ node.data.username || 'No username' }}
        </p>
      </div>

      <!-- Relation Badge -->
      <div v-if="node.data.showDetails !== false" class="text-center">
        <span
          class="inline-block px-3 py-1 text-xs font-medium rounded-full"
          :class="getRelationBadgeClass(node.data.relation)"
        >
          {{ formatRelation(node.data.relation) }}
        </span>
      </div>

      <!-- Performance Mode Indicator -->
      <div v-if="node.data.showDetails === false" class="text-center">
        <span class="text-xs text-gray-400">Click for details</span>
      </div>

      <!-- Node Actions (Builder Mode Only) -->
      <div
        v-if="isBuilderMode && !isCenter"
        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
      >
        <button
          @click.stop="handleEdit"
          class="p-1 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition-colors"
          title="Edit"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </button>
      </div>

      <div
        v-if="isBuilderMode && !isCenter"
        class="absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
      >
        <button
          @click.stop="handleDelete"
          class="p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
          title="Delete"
        >
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Hover Info -->
    <div
      v-if="!isCenter"
      class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-30"
    >
      <div class="bg-gray-900 text-white text-xs rounded-lg px-2 py-1 whitespace-nowrap">
        <p class="font-medium">{{ node.data.name }}</p>
        <p class="text-gray-300">{{ formatRelation(node.data.relation) }}</p>
        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Handle } from '@vue-flow/core';

const props = defineProps({
  node: {
    type: Object,
    required: true
  },
  isCenter: {
    type: Boolean,
    default: false
  },
  isBuilderMode: {
    type: Boolean,
    default: false
  },
  isSelected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click', 'edit', 'delete', 'start-connection']);

// Computed properties
const getRelationBadgeClass = (relation) => {
  const classes = {
    'self': 'bg-blue-100 text-blue-800',
    'parent': 'bg-green-100 text-green-800',
    'child': 'bg-purple-100 text-purple-800',
    'spouse': 'bg-pink-100 text-pink-800',
    'sibling': 'bg-yellow-100 text-yellow-800',
    'grandparent': 'bg-indigo-100 text-indigo-800',
    'grandchild': 'bg-teal-100 text-teal-800',
    'aunt': 'bg-amber-100 text-amber-800',
    'uncle': 'bg-amber-100 text-amber-800',
    'niece': 'bg-cyan-100 text-cyan-800',
    'nephew': 'bg-cyan-100 text-cyan-800',
    'cousin': 'bg-lime-100 text-lime-800',
    'father_in_law': 'bg-red-100 text-red-800',
    'mother_in_law': 'bg-red-100 text-red-800',
    'son_in_law': 'bg-red-100 text-red-800',
    'daughter_in_law': 'bg-red-100 text-red-800',
    'brother_in_law': 'bg-red-100 text-red-800',
    'sister_in_law': 'bg-red-100 text-red-800',
    'step_parent': 'bg-violet-100 text-violet-800',
    'step_child': 'bg-violet-100 text-violet-800',
    'step_sibling': 'bg-violet-100 text-violet-800',
    'friend': 'bg-slate-100 text-slate-800',
    'colleague': 'bg-slate-100 text-slate-800',
    'neighbor': 'bg-slate-100 text-slate-800',
    'mentor': 'bg-slate-100 text-slate-800',
    'student': 'bg-slate-100 text-slate-800',
    'teacher': 'bg-slate-100 text-slate-800',
    'guardian': 'bg-slate-100 text-slate-800',
    'foster_parent': 'bg-slate-100 text-slate-800',
    'foster_child': 'bg-slate-100 text-slate-800'
  };
  return classes[relation] || 'bg-gray-100 text-gray-800';
};

const formatRelation = (relation) => {
  const labels = {
    'self': 'You',
    'parent': 'Parent',
    'child': 'Child',
    'spouse': 'Spouse',
    'sibling': 'Sibling',
    'grandparent': 'Grandparent',
    'grandchild': 'Grandchild',
    'aunt': 'Aunt',
    'uncle': 'Uncle',
    'niece': 'Niece',
    'nephew': 'Nephew',
    'cousin': 'Cousin',
    'father_in_law': 'Father-in-Law',
    'mother_in_law': 'Mother-in-Law',
    'son_in_law': 'Son-in-Law',
    'daughter_in_law': 'Daughter-in-Law',
    'brother_in_law': 'Brother-in-Law',
    'sister_in_law': 'Sister-in-Law',
    'step_parent': 'Step Parent',
    'step_child': 'Step Child',
    'step_sibling': 'Step Sibling',
    'friend': 'Friend',
    'colleague': 'Colleague',
    'neighbor': 'Neighbor',
    'mentor': 'Mentor',
    'student': 'Student',
    'teacher': 'Teacher',
    'guardian': 'Guardian',
    'foster_parent': 'Foster Parent',
    'foster_child': 'Foster Child'
  };
  return labels[relation] || relation;
};

// Methods
const handleNodeClick = () => {
  emit('click', props.node);
};

const handleEdit = () => {
  emit('edit', props.node);
};

const handleDelete = () => {
  if (confirm(`Are you sure you want to remove ${props.node.data.name} from the family tree?`)) {
    emit('delete', props.node.id);
  }
};

const startConnection = (direction) => {
  emit('start-connection', {
    nodeId: props.node.id,
    direction: direction,
    position: {
      x: props.node.x || 0,
      y: props.node.y || 0
    }
  });
};
</script>

<style scoped>
/* Custom styles for the family tree node */
.family-node-enter-active,
.family-node-leave-active {
  transition: all 0.3s ease;
}

.family-node-enter-from,
.family-node-leave-to {
  opacity: 0;
  transform: scale(0.8);
}

/* Oval shape styling */
.rounded-full {
  border-radius: 50%;
}

/* Connection dot animations */
[data-handle] {
  transition: all 0.2s ease;
}

[data-handle]:hover {
  transform: scale(1.25);
  box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
}
</style>
