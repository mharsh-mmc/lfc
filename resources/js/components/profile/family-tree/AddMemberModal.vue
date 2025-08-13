<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">Add New Family Member</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter full name"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
            <input
              v-model="form.username"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter username (optional)"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
            <input
              v-model="form.date_of_birth"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Relation to You *</label>
            <select
              v-model="form.relation"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <!-- Immediate Family -->
              <optgroup label="Immediate Family">
                <option value="parent">Parent</option>
                <option value="child">Child</option>
                <option value="spouse">Spouse</option>
                <option value="sibling">Sibling</option>
              </optgroup>
              
              <!-- Extended Family -->
              <optgroup label="Extended Family">
                <option value="grandparent">Grandparent</option>
                <option value="grandchild">Grandchild</option>
                <option value="aunt">Aunt</option>
                <option value="uncle">Uncle</option>
                <option value="niece">Niece</option>
                <option value="nephew">Nephew</option>
                <option value="cousin">Cousin</option>
              </optgroup>
              
              <!-- In-Laws -->
              <optgroup label="In-Laws">
                <option value="father_in_law">Father-in-Law</option>
                <option value="mother_in_law">Mother-in-Law</option>
                <option value="son_in_law">Son-in-Law</option>
                <option value="daughter_in_law">Daughter-in-Law</option>
                <option value="brother_in_law">Brother-in-Law</option>
                <option value="sister_in_law">Sister-in-Law</option>
              </optgroup>
              
              <!-- Step Family -->
              <optgroup label="Step Family">
                <option value="step_parent">Step Parent</option>
                <option value="step_child">Step Child</option>
                <option value="step_sibling">Step Sibling</option>
              </optgroup>
              
              <!-- Other Relationships -->
              <optgroup label="Other">
                <option value="friend">Friend</option>
                <option value="colleague">Colleague</option>
                <option value="neighbor">Neighbor</option>
                <option value="mentor">Mentor</option>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
                <option value="guardian">Guardian</option>
                <option value="foster_parent">Foster Parent</option>
                <option value="foster_child">Foster Child</option>
              </optgroup>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
          <input
            v-model="form.location"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="City, Country"
          />
        </div>

        <!-- Profile Photo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
          <div class="flex items-center space-x-4">
            <div class="relative">
              <img
                :src="profilePhotoPreview || '/default-avatar.svg'"
                :alt="form.name"
                class="w-20 h-20 rounded-full object-cover border-2 border-gray-300"
              />
              <button
                type="button"
                @click="triggerFileInput"
                class="absolute -bottom-1 -right-1 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center hover:bg-blue-600 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
              </button>
            </div>
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              @change="handlePhotoChange"
              class="hidden"
            />
            <div class="text-sm text-gray-500">
              <p>Click the + button to upload a photo</p>
              <p>Recommended: Square image, 400x400px or larger</p>
            </div>
          </div>
        </div>

        <!-- Bio Section -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Biography</label>
          <textarea
            v-model="form.bio"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Tell us about this person..."
          ></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Profession</label>
            <input
              v-model="form.profession"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="What do they do for work?"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Passion/Interest</label>
            <input
              v-model="form.passion"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="What are they passionate about?"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mission in Life</label>
            <textarea
              v-model="form.mission"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="What is their life's mission?"
            ></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Calling/Purpose</label>
            <textarea
              v-model="form.calling"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="What is their calling or purpose?"
            ></textarea>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            :class="[
              'px-6 py-2 text-white rounded-md transition-colors flex items-center space-x-2',
              isSubmitting 
                ? 'bg-gray-400 cursor-not-allowed' 
                : 'bg-blue-600 hover:bg-blue-700'
            ]"
          >
            <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ isSubmitting ? 'Creating...' : 'Create Profile' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const props = defineProps({
  profileUserData: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'add-person']);

// Form state
const form = reactive({
  name: '',
  username: '',
  relation: 'parent',
  date_of_birth: '',
  location: '',
  bio: '',
  profession: '',
  passion: '',
  mission: '',
  calling: ''
});

// UI state
const isSubmitting = ref(false);
const profilePhotoPreview = ref(null);
const profilePhotoFile = ref(null);
const fileInput = ref(null);

// Methods
const triggerFileInput = () => {
  fileInput.value?.click();
};

const handlePhotoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    profilePhotoFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      profilePhotoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleSubmit = async () => {
  console.log('Form submission started');
  if (!form.name.trim()) return;
  
  console.log('Form data:', form);
  isSubmitting.value = true;
  
  try {
    // Create FormData for file upload
    const formData = new FormData();
    
    // Only include fields that exist in the users table
    formData.append('name', form.name.trim());
    formData.append('username', form.username || form.name.toLowerCase().replace(/\s+/g, '_') + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
    formData.append('relation', form.relation);
    
    // Optional fields - only append if they have values
    if (form.date_of_birth) {
      formData.append('date_of_birth', form.date_of_birth);
    }
    if (form.location) {
      formData.append('location', form.location.trim());
    }
    if (form.bio) {
      formData.append('bio', form.bio.trim());
    }
    if (form.profession) {
      formData.append('profession', form.profession.trim());
    }
    if (form.passion) {
      formData.append('passion', form.passion.trim());
    }
    if (form.mission) {
      formData.append('mission', form.mission.trim());
    }
    if (form.calling) {
      formData.append('calling', form.calling.trim());
    }
    
    // Position for the tree node
    formData.append('x_position', Math.floor(Math.random() * 400 - 200));
    formData.append('y_position', Math.floor(Math.random() * 400 - 200));
    
    // Profile photo if selected
    if (profilePhotoFile.value) {
      formData.append('profile_photo', profilePhotoFile.value);
    }

    console.log('FormData created, sending request to API');
    console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

    // Create profile and add to tree
    const response = await fetch(`/api/profiles/${props.profileUserData.id}/familytree/create-profile`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: formData
    });

    console.log('Response received:', response);
    console.log('Response status:', response.status);

    if (response.ok) {
      const newProfile = await response.json();
      console.log('Profile created successfully:', newProfile);
      
      // Add to tree with random position
      const treeNode = {
        id: newProfile.node.id,
        name: newProfile.node.profile.name,
        username: newProfile.node.profile.username,
        profile_photo_url: newProfile.node.profile.profile_photo_path ? `/storage/${newProfile.node.profile.profile_photo_path}` : null,
        relation: newProfile.node.relation,
        x: Math.random() * 400 - 200,
        y: Math.random() * 400 - 200
      };
      
      emit('add-person', treeNode);
      emit('close');
    } else {
      const errorData = await response.json();
      console.error('Error response:', errorData);
      throw new Error(errorData.error || 'Failed to create profile');
    }
  } catch (error) {
    console.error('Failed to create profile:', error);
    alert('Failed to create profile. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};
</script>
