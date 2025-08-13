<template>
  <div class="space-y-6">
    <!-- Header with Create Button -->
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Deceased Profiles</h3>
      <button 
        @click="showCreateModal = true"
        class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
      <p class="text-gray-600 mt-2">Loading profiles...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="deceasedProfiles.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Memorial Profiles Yet</h3>
      <p class="text-gray-500 mb-6">Create your first memorial profile to honor and remember your loved ones</p>
      <button 
        @click="showCreateModal = true"
        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors"
      >
        Create Your First Memorial
      </button>
    </div>

    <!-- Profiles Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="profile in deceasedProfiles" 
        :key="profile.id"
        class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
      >
        <!-- Profile Header -->
        <div class="p-4 border-b border-gray-200">
          <div class="flex justify-between items-start">
            <div class="flex items-center space-x-3">
                             <!-- Profile Photo -->
               <div class="flex-shrink-0">
                 <img 
                   v-if="profile.profile_photo_url" 
                   :src="profile.profile_photo_url" 
                   :alt="profile.name"
                   class="w-12 h-12 rounded-full object-cover border-2 border-gray-200"
                   @error="handleImageError"
                 />
                 <div 
                   v-else 
                   class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-200"
                 >
                   <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                   </svg>
                 </div>
                 <!-- Fallback for failed image loads -->
                 <div 
                   v-if="profile.profile_photo_url" 
                   class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-200 hidden"
                 >
                   <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                   </svg>
                 </div>
               </div>
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-900">{{ profile.name }}</h4>
                <p class="text-sm text-gray-600">{{ profile.relationship || 'Loved One' }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-1">
              <span 
                :class="[
                  'px-2 py-1 text-xs rounded-full',
                  profile.is_public 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-gray-100 text-gray-800'
                ]"
              >
                {{ profile.is_public ? 'Public' : 'Private' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Profile Content -->
        <div class="p-4">
          <div class="space-y-2">
            <p class="text-sm text-gray-600">
              <span class="font-medium">Birth:</span> {{ profile.birth_date }}
            </p>
            <p class="text-sm text-gray-600">
              <span class="font-medium">Death:</span> {{ profile.death_date }}
            </p>
            <p v-if="profile.age_at_death" class="text-sm text-gray-600">
              <span class="font-medium">Age:</span> {{ profile.age_at_death }} years
            </p>
            <p v-if="profile.birth_place" class="text-sm text-gray-600">
              <span class="font-medium">Birth Place:</span> {{ profile.birth_place }}
            </p>
            <p v-if="profile.death_place" class="text-sm text-gray-600">
              <span class="font-medium">Death Place:</span> {{ profile.death_place }}
            </p>
          </div>

          <!-- Memorial Message Preview -->
          <div v-if="profile.memorial_message" class="mt-3 pt-3 border-t border-gray-200">
            <p class="text-sm text-gray-700 line-clamp-2">{{ profile.memorial_message }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <button 
                @click="editProfile(profile)"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                Edit
              </button>
              <button 
                @click="toggleVisibility(profile)"
                :class="[
                  'text-sm font-medium',
                  profile.is_public 
                    ? 'text-orange-600 hover:text-orange-800' 
                    : 'text-green-600 hover:text-green-800'
                ]"
              >
                {{ profile.is_public ? 'Make Private' : 'Make Public' }}
              </button>
            </div>
            <button 
              @click="deleteProfile(profile)"
              class="text-red-600 hover:text-red-800 text-sm font-medium"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
              {{ showEditModal ? 'Edit Memorial Profile' : 'Create Memorial Profile' }}
            </h3>
            <button 
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <form @submit.prevent="showEditModal ? updateProfile() : createProfile()" class="space-y-6">
            <!-- Basic Information -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Basic Information</h4>
              
              <!-- Profile Photo Upload -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Profile Photo
                </label>
                <div class="flex items-center space-x-4">
                  <div class="flex-shrink-0">
                    <img 
                      v-if="photoPreview" 
                      :src="photoPreview" 
                      alt="Preview"
                      class="w-16 h-16 rounded-full object-cover border-2 border-gray-200"
                    />
                                         <img 
                       v-else-if="profileToEdit?.profile_photo_url" 
                       :src="profileToEdit.profile_photo_url"
                       alt="Current photo"
                       class="w-16 h-16 rounded-full object-cover border-2 border-gray-200"
                     />
                    <div 
                      v-else 
                      class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center border-2 border-gray-200"
                    >
                      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1">
                    <input 
                      ref="photoInput"
                      type="file"
                      accept="image/*"
                      @change="updatePhotoPreview"
                      class="hidden"
                    />
                    <button 
                      type="button"
                      @click="$refs.photoInput.click()"
                      class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                      {{ photoPreview || profileToEdit?.profile_photo_url ? 'Change Photo' : 'Upload Photo' }}
                    </button>
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                  </div>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Full Name *
                  </label>
                  <input 
                    v-model="profileForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter full name"
                  />
                  <div v-if="profileForm.errors.name" class="text-red-600 text-sm mt-1">
                    {{ profileForm.errors.name }}
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Relationship
                  </label>
                  <select 
                    v-model="profileForm.relationship"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="">Select relationship</option>
                    <option value="Father">Father</option>
                    <option value="Mother">Mother</option>
                    <option value="Grandfather">Grandfather</option>
                    <option value="Grandmother">Grandmother</option>
                    <option value="Brother">Brother</option>
                    <option value="Sister">Sister</option>
                    <option value="Son">Son</option>
                    <option value="Daughter">Daughter</option>
                    <option value="Uncle">Uncle</option>
                    <option value="Aunt">Aunt</option>
                    <option value="Cousin">Cousin</option>
                    <option value="Friend">Friend</option>
                    <option value="Other">Other</option>
                  </select>
                </div>

                                 <div>
                   <label class="block text-sm font-medium text-gray-700 mb-2">
                     Birth Date *
                   </label>
                   <input 
                     v-model="profileForm.birth_date"
                     type="date"
                     required
                     :max="getCurrentDate()"
                     :min="'1900-01-01'"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   />
                   <div v-if="profileForm.errors.birth_date" class="text-red-600 text-sm mt-1">
                     {{ profileForm.errors.birth_date }}
                   </div>
                   <p class="text-xs text-gray-500 mt-1">Must be between 1900 and today</p>
                 </div>

                 <div>
                   <label class="block text-sm font-medium text-gray-700 mb-2">
                     Death Date *
                   </label>
                   <input 
                     v-model="profileForm.death_date"
                     type="date"
                     required
                     :max="getCurrentDate()"
                     :min="profileForm.birth_date || '1900-01-01'"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                   />
                   <div v-if="profileForm.errors.death_date" class="text-red-600 text-sm mt-1">
                     {{ profileForm.errors.death_date }}
                   </div>
                   <p class="text-xs text-gray-500 mt-1">Must be after birth date and not in the future</p>
                 </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Birth Place
                  </label>
                  <input 
                    v-model="profileForm.birth_place"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., New York, USA"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Death Place
                  </label>
                  <input 
                    v-model="profileForm.death_place"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., Los Angeles, USA"
                  />
                </div>
              </div>
            </div>

            <!-- Biography -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Biography</h4>
              <textarea
                v-model="profileForm.biography"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Share their life story, achievements, and memories..."
              ></textarea>
            </div>

            <!-- Memorial Message -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Memorial Message</h4>
              <textarea
                v-model="profileForm.memorial_message"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Write a personal message in their memory..."
              ></textarea>
            </div>

            <!-- Privacy Settings -->
            <div>
              <h4 class="text-md font-medium text-gray-900 mb-4">Privacy Settings</h4>
              <div class="flex items-center">
                <input 
                  v-model="profileForm.is_public"
                  type="checkbox"
                  id="is-public"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is-public" class="ml-2 text-sm text-gray-700">
                  Make this memorial profile public (visible to everyone)
                </label>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
              <button 
                type="button"
                @click="closeModal"
                class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button 
                type="submit"
                :disabled="profileForm.processing"
                :class="[
                  'px-6 py-2 rounded-md text-white font-medium transition-colors',
                  profileForm.processing 
                    ? 'bg-gray-400 cursor-not-allowed' 
                    : 'bg-blue-600 hover:bg-blue-700'
                ]"
              >
                <span v-if="profileForm.processing">
                  {{ showEditModal ? 'Updating...' : 'Creating...' }}
                </span>
                <span v-else>
                  {{ showEditModal ? 'Update Memorial Profile' : 'Create Memorial Profile' }}
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Delete Memorial Profile</h3>
          <p class="text-gray-600 mb-6">
            Are you sure you want to delete the memorial profile for "{{ profileToDelete?.name }}"? 
            This action cannot be undone.
          </p>
          <div class="flex justify-end space-x-4">
            <button 
              @click="showDeleteModal = false"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              @click="confirmDelete"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const emit = defineEmits(['profile-created', 'profile-updated', 'profile-deleted'])

// State
const loading = ref(false)
const deceasedProfiles = ref([])
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const profileToDelete = ref(null)
const profileToEdit = ref(null)
const photoPreview = ref(null)
const photoInput = ref(null)

// Form
const profileForm = useForm({
  name: '',
  birth_date: '',
  death_date: '',
  birth_place: '',
  death_place: '',
  biography: '',
  memorial_message: '',
  relationship: '',
  is_public: true,
  profile_photo: null
})

// Load user's deceased profiles
const loadProfiles = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/deceased-profiles/user', {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      deceasedProfiles.value = data.data || []
    } else {
      console.error('API Error:', response.status, response.statusText)
    }
  } catch (error) {
    console.error('Error loading profiles:', error)
  } finally {
    loading.value = false
  }
}

// Create new profile using Inertia.js
const createProfile = () => {
  // Handle file upload
  if (photoInput.value?.files[0]) {
    profileForm.profile_photo = photoInput.value.files[0]
  }

  profileForm.post('/api/deceased-profiles', {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      loadProfiles()
      emit('profile-created')
    },
    onError: (errors) => {
      console.error('Create profile errors:', errors)
    },
  })
}

// Edit profile
const editProfile = (profile) => {
  profileToEdit.value = profile
  profileForm.reset()
  profileForm.fill({
    name: profile.name,
    birth_date: profile.birth_date,
    death_date: profile.death_date,
    birth_place: profile.birth_place || '',
    death_place: profile.death_place || '',
    biography: profile.biography || '',
    memorial_message: profile.memorial_message || '',
    relationship: profile.relationship || '',
    is_public: profile.is_public
  })
  showEditModal.value = true
}

// Update profile using Inertia.js
const updateProfile = () => {
  if (!profileToEdit.value) return
  
  // Handle file upload
  if (photoInput.value?.files[0]) {
    profileForm.profile_photo = photoInput.value.files[0]
  }

  profileForm.put(`/api/deceased-profiles/${profileToEdit.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      loadProfiles()
      emit('profile-updated')
    },
    onError: (errors) => {
      console.error('Update profile errors:', errors)
    },
  })
}

// Delete profile using Inertia.js
const deleteProfile = (profile) => {
  profileToDelete.value = profile
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (!profileToDelete.value) return
  
  router.delete(`/api/deceased-profiles/${profileToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      profileToDelete.value = null
      loadProfiles()
      emit('profile-deleted')
    },
    onError: (errors) => {
      console.error('Delete profile errors:', errors)
    },
  })
}

// Toggle visibility using Inertia.js
const toggleVisibility = (profile) => {
  router.patch(`/api/deceased-profiles/${profile.id}/toggle-visibility`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      loadProfiles()
    },
    onError: (errors) => {
      console.error('Toggle visibility errors:', errors)
    },
  })
}

// Photo preview function
const updatePhotoPreview = () => {
  const file = photoInput.value?.files[0]
  if (!file) return

  // Set the file to the form
  profileForm.profile_photo = file

  const reader = new FileReader()
  reader.onload = (e) => {
    photoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

// Handle image error
const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.nextElementSibling.style.display = 'flex'
}

// Get current date for date input max attribute
const getCurrentDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Close modal
const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  profileToEdit.value = null
  photoPreview.value = null
  profileForm.reset()
}

// Load profiles on mount
onMounted(() => {
  loadProfiles()
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
