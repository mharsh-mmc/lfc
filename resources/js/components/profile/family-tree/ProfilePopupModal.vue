<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">Profile Details</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Profile Content -->
      <div class="p-6">
        <!-- Profile Header -->
        <div class="flex items-center space-x-4 mb-6">
          <img
            :src="profileData.profile_photo_url"
            :alt="profileData.name"
            class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
          />
          <div>
            <h4 class="text-2xl font-bold text-gray-900">{{ profileData.name }}</h4>
            <p class="text-gray-500">{{ profileData.username }}</p>
            <span
              class="inline-block px-3 py-1 text-sm font-medium rounded-full mt-2"
              :class="getRelationBadgeClass(profileData.relation)"
            >
              {{ formatRelation(profileData.relation) }}
            </span>
          </div>
        </div>

        <!-- Profile Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Basic Information -->
          <div class="space-y-4">
            <h5 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Basic Information</h5>
            
            <div v-if="profileData.date_of_birth" class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <div>
                <p class="text-sm text-gray-500">Date of Birth</p>
                <p class="text-gray-900">{{ formatDate(profileData.date_of_birth) }}</p>
              </div>
            </div>

            <div v-if="profileData.location" class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <div>
                <p class="text-sm text-gray-500">Location</p>
                <p class="text-gray-900">{{ profileData.location }}</p>
              </div>
            </div>

            <div v-if="profileData.profession" class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.815-9-2.145M21 13.255v-3.255A23.931 23.931 0 0012 15c-3.183 0-6.22-.815-9-2.145v3.255"/>
              </svg>
              <div>
                <p class="text-sm text-gray-500">Profession</p>
                <p class="text-gray-900">{{ profileData.profession }}</p>
              </div>
            </div>
          </div>

          <!-- Personal Details -->
          <div class="space-y-4">
            <h5 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Personal Details</h5>
            
            <div v-if="profileData.passion" class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              <div>
                <p class="text-sm text-gray-500">Passion</p>
                <p class="text-gray-900">{{ profileData.passion }}</p>
              </div>
            </div>

            <div v-if="profileData.mission" class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
              <div>
                <p class="text-sm text-gray-500">Mission</p>
                <p class="text-gray-900">{{ profileData.mission }}</p>
              </div>
            </div>

            <div v-if="profileData.calling" class="flex items-center space-x-3">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              <div>
                <p class="text-sm text-gray-500">Calling/Purpose</p>
                <p class="text-gray-900">{{ profileData.calling }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bio Section -->
        <div v-if="profileData.bio" class="mt-6">
          <h5 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2 mb-3">Biography</h5>
          <p class="text-gray-700 leading-relaxed">{{ profileData.bio }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
          >
            Close
          </button>
          <button
            v-if="profileData.id && profileData.id !== currentUserId"
            @click="viewFullProfile"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            View Full Profile
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  profile: {
    type: Object,
    required: true
  },
  currentUserId: {
    type: [String, Number],
    default: null
  }
});

// Add reactive profile data with fallbacks
const profileData = computed(() => {
  return {
    id: props.profile.id,
    name: props.profile.name || 'Unknown',
    username: props.profile.username || 'No username',
    profile_photo_url: props.profile.profile_photo_url || '/default-avatar.svg',
    relation: props.profile.relation || 'family',
    date_of_birth: props.profile.date_of_birth,
    location: props.profile.location,
    bio: props.profile.bio,
    profession: props.profile.profession,
    passion: props.profile.passion,
    mission: props.profile.mission,
    calling: props.profile.calling
  };
});

const emit = defineEmits(['close']);

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

const formatDate = (dateString) => {
  if (!dateString) return '';
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};

const viewFullProfile = () => {
  if (profileData.value.id) {
    window.open(`/profile/${profileData.value.id}`, '_blank');
  }
};
</script>

<style scoped>
/* Custom styles for the profile popup */
.profile-popup-enter-active,
.profile-popup-leave-active {
  transition: all 0.3s ease;
}

.profile-popup-enter-from,
.profile-popup-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
