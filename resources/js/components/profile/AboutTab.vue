<template>
  <div class="space-y-8">
    <!-- Basic Information -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Basic Information:</h3>
        <button v-if="isOwnProfile && !editingBasicInfo" @click="editingBasicInfo = true" class="text-blue-600 hover:text-blue-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </button>
      </div>
      
      <!-- Edit Form for Basic Information -->
      <form v-if="editingBasicInfo && isOwnProfile" @submit.prevent="updateBasicInfo" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input v-model="basicInfoForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="basicInfoForm.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input v-model="basicInfoForm.username" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input v-model="basicInfoForm.title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
            <input v-model="basicInfoForm.date_of_birth" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <input v-model="basicInfoForm.location" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
            <input v-model="basicInfoForm.height_cm" type="number" min="50" max="300" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
            <input v-model="basicInfoForm.weight_kg" type="number" min="20" max="500" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        <div class="flex justify-end space-x-3">
          <button type="button" @click="cancelEdit('basic')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" :disabled="basicInfoForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ basicInfoForm.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
      
      <!-- Display Basic Information -->
      <div v-else class="space-y-2">
        <p><strong>Height:</strong> {{ profileUserData.height_cm || 'Not specified' }}cm</p>
        <p><strong>Weight:</strong> {{ profileUserData.weight_kg || 'Not specified' }}kg</p>
      </div>
    </div>

    <!-- Education -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Education:</h3>
        <button v-if="isOwnProfile && !showAddEducationForm" @click="showAddEducationForm = true" class="text-blue-600 hover:text-blue-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
        </button>
      </div>
      
      <!-- Add Education Form -->
      <form v-if="showAddEducationForm && isOwnProfile" @submit.prevent="addEducation" class="space-y-4 mb-6 p-4 border border-gray-200 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
            <input v-model="educationForm.institution" type="text" placeholder="School, University, etc." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Degree</label>
            <input v-model="educationForm.degree" type="text" placeholder="Bachelor's, Master's, PhD, etc." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Field of Study</label>
            <input v-model="educationForm.field_of_study" type="text" placeholder="Computer Science, Business, etc." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
            <input v-model="educationForm.period" type="text" placeholder="e.g., 2018-2022 or 2018-Present" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea v-model="educationForm.description" rows="3" placeholder="Additional details about your education..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>
        <div class="flex items-center">
          <input v-model="educationForm.is_current" type="checkbox" id="is_current" class="mr-2" @change="handleCurrentEducationChange">
          <label for="is_current" class="text-sm text-gray-700">Currently studying here</label>
        </div>
        <p class="text-xs text-gray-500 mt-1">Note: Only one education entry can be marked as current at a time.</p>
        <div class="flex justify-end space-x-3">
          <button type="button" @click="cancelEducationEdit" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" :disabled="educationForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ educationForm.processing ? 'Adding...' : 'Add Education' }}
          </button>
        </div>
      </form>
      
      <!-- Display Education Entries -->
      <div v-if="educationEntries.length > 0" class="space-y-4">
        <div v-for="education in educationEntries" :key="education.id" class="border border-gray-200 rounded-lg p-4">
          <!-- Edit Form for Individual Education -->
          <form v-if="editingEducationEntry === education.id && isOwnProfile" @submit.prevent="updateEducation(education)" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Institution</label>
                <input v-model="educationForm.institution" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Degree</label>
                <input v-model="educationForm.degree" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Field of Study</label>
                <input v-model="educationForm.field_of_study" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                <input v-model="educationForm.period" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="educationForm.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex items-center">
              <input v-model="educationForm.is_current" type="checkbox" id="is_current_edit" class="mr-2" @change="handleCurrentEducationChange">
              <label for="is_current_edit" class="text-sm text-gray-700">Currently studying here</label>
            </div>
            <p class="text-xs text-gray-500 mt-1">Note: Only one education entry can be marked as current at a time.</p>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="cancelEducationEdit" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="educationForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                {{ educationForm.processing ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
          
          <!-- Display Individual Education -->
          <div v-else class="flex justify-between items-start">
            <div class="flex-1">
              <div class="flex items-center space-x-2 mb-2">
                <h4 class="font-semibold text-gray-900">{{ education.institution }}</h4>
                <span v-if="education.is_current" class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Current</span>
              </div>
              <p v-if="education.degree" class="text-gray-700">{{ education.degree }}</p>
              <p v-if="education.field_of_study" class="text-gray-600">{{ education.field_of_study }}</p>
              <p v-if="education.period" class="text-gray-500 text-sm">{{ education.period }}</p>
              <p v-if="education.description" class="text-gray-600 mt-2">{{ education.description }}</p>
            </div>
            <div v-if="isOwnProfile" class="flex space-x-2">
              <button @click="editEducation(education)" class="text-blue-600 hover:text-blue-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
              </button>
              <button @click="deleteEducation(education)" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- No Education Entries -->
      <div v-else class="text-center py-8 text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 5.477 5.754 5 7.5 5s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.523 18.246 19 16.5 19c-1.746 0-3.332-.477-4.5-1.253"/>
        </svg>
        <p>No education entries yet.</p>
        <p v-if="isOwnProfile" class="text-sm">Click the + button to add your education history.</p>
      </div>
    </div>

    <!-- About Me -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">About Me:</h3>
        <button v-if="isOwnProfile && !editingAboutMe" @click="editingAboutMe = true" class="text-blue-600 hover:text-blue-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </button>
      </div>
      
      <!-- Edit Form for About Me -->
      <form v-if="editingAboutMe && isOwnProfile" @submit.prevent="updateAboutMe" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tell us about yourself:</label>
          <RichTextEditor 
            v-model="aboutMeForm.about_content"
            placeholder="Write about yourself, your interests, experiences, and what makes you unique..."
          />
        </div>
        <div class="flex justify-end space-x-3">
          <button type="button" @click="cancelEdit('about')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
            Cancel
          </button>
          <button type="submit" :disabled="aboutMeForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ aboutMeForm.processing ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
      
      <!-- Display About Me -->
      <div v-else class="prose prose-sm max-w-none">
        <div v-if="profileUserData.about_content" v-html="profileUserData.about_content"></div>
        <div v-else class="text-gray-500 italic">
          No information provided yet.
        </div>
      </div>
    </div>

    <!-- Connections -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Connections:</h3>
        <div class="flex items-center space-x-2">
          <button class="bg-blue-400 text-white px-4 py-2 rounded-md hover:bg-blue-500">
            All Connections
          </button>
          <button v-if="isOwnProfile" class="text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
          </button>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Sample Connection Cards -->
        <div class="border rounded-lg p-4 text-center">
          <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <span class="text-blue-600 font-semibold">LIVE</span>
          </div>
          <h4 class="font-medium text-gray-900">Antonietta, Cianci</h4>
          <p class="text-sm text-gray-600">0 connections in common</p>
          <button class="mt-3 bg-blue-400 text-white px-4 py-1 rounded text-sm hover:bg-blue-500">
            Follow
          </button>
        </div>
        <!-- Repeat for more connections -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import RichTextEditor from '@/components/RichTextEditor.vue';

const props = defineProps({
  profileUserData: {
    type: Object,
    required: true
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['profile-updated']);

// Edit states for different sections
const editingBasicInfo = ref(false);
const editingAboutMe = ref(false);

// Education-related state
const showAddEducationForm = ref(false);
const editingEducationEntry = ref(null);
const educationEntries = ref(props.profileUserData.education || []);

// Form for basic information
const basicInfoForm = useForm({
  name: props.profileUserData.name,
  email: props.profileUserData.email,
  username: props.profileUserData.username || '',
  title: props.profileUserData.title || '',
  date_of_birth: props.profileUserData.date_of_birth || '',
  location: props.profileUserData.location || '',
  height_cm: props.profileUserData.height_cm || '',
  weight_kg: props.profileUserData.weight_kg || '',
});

// Form for education
const educationForm = useForm({
  institution: '',
  degree: '',
  field_of_study: '',
  period: '',
  description: '',
  is_current: false,
});

// Form for about me
const aboutMeForm = useForm({
  name: props.profileUserData.name,
  email: props.profileUserData.email,
  username: props.profileUserData.username || '',
  title: props.profileUserData.title || '',
  date_of_birth: props.profileUserData.date_of_birth || '',
  location: props.profileUserData.location || '',
  bio: props.profileUserData.bio || '',
  height_cm: props.profileUserData.height_cm || '',
  weight_kg: props.profileUserData.weight_kg || '',
  passion: props.profileUserData.passion || '',
  profession: props.profileUserData.profession || '',
  mission: props.profileUserData.mission || '',
  calling: props.profileUserData.calling || '',
  about_content: props.profileUserData.about_content || '',
});

// Computed property to check if any education is currently marked as current
const hasCurrentEducation = computed(() => {
  return educationEntries.value.some(edu => edu.is_current);
});

// Update functions
const updateBasicInfo = () => {
  console.log('updateBasicInfo called');
  
  // Validate form data before submission
  if (!basicInfoForm.name || !basicInfoForm.email || !basicInfoForm.username) {
    alert('Please fill in all required fields (Name, Email, Username)');
    return;
  }
  
  // Convert numeric fields to proper types
  const formData = {
    name: basicInfoForm.name,
    email: basicInfoForm.email,
    username: basicInfoForm.username,
    title: basicInfoForm.title || '',
    date_of_birth: basicInfoForm.date_of_birth || '',
    location: basicInfoForm.location || '',
    height_cm: basicInfoForm.height_cm ? parseInt(basicInfoForm.height_cm) : null,
    weight_kg: basicInfoForm.weight_kg ? parseInt(basicInfoForm.weight_kg) : null,
  };
  
  console.log('Submitting form data:', formData);
  
  // Use Inertia.js form submission with proper error handling
  basicInfoForm.post('/profile', {
    preserveScroll: true,
    onStart: () => {
      console.log('Form submission started');
    },
    onSuccess: (page) => {
      console.log('Basic info updated successfully');
      console.log('Response page:', page);
      editingBasicInfo.value = false;
      
      // Show success message
      alert('Profile updated successfully!');
      
      // Refresh the page to show updated data
      window.location.reload();
    },
    onError: (errors) => {
      console.error('Basic info update errors:', errors);
      console.error('Form errors object:', basicInfoForm.errors);
      
      // Show error message
      let errorMessage = 'Failed to update profile. ';
      if (errors.name) errorMessage += errors.name[0] + ' ';
      if (errors.email) errorMessage += errors.email[0] + ' ';
      if (errors.username) errorMessage += errors.username[0] + ' ';
      if (errors.height_cm) errorMessage += errors.height_cm[0] + ' ';
      if (errors.weight_kg) errorMessage += errors.weight_kg[0] + ' ';
      
      alert(errorMessage);
    },
    onFinish: () => {
      console.log('Form submission finished');
    },
  });
};

const addEducation = () => {
  educationForm.post('/education', {
    preserveScroll: true,
    onSuccess: () => {
      // Reload the page to get fresh data
      window.location.reload();
    },
    onError: (errors) => {
      console.error('Add education errors:', errors);
    },
  });
};

const updateEducation = (education) => {
  console.log('Updating education with ID:', education.id);
  console.log('Form data being sent:', {
    institution: educationForm.institution,
    degree: educationForm.degree,
    field_of_study: educationForm.field_of_study,
    period: educationForm.period,
    description: educationForm.description,
    is_current: educationForm.is_current,
  });
  
  educationForm.put(`/education/${education.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Update successful, reloading page...');
      // Reload the page to get fresh data
      window.location.reload();
    },
    onError: (errors) => {
      console.error('Update education errors:', errors);
    },
  });
};

const deleteEducation = (education) => {
  if (confirm('Are you sure you want to delete this education entry?')) {
    router.delete(`/education/${education.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Reload the page to get fresh data
        window.location.reload();
      },
      onError: (errors) => {
        console.error('Delete education errors:', errors);
      },
    });
  }
};

const editEducation = (education) => {
  console.log('Editing education:', education);
  editingEducationEntry.value = education.id;
  
  // Reset form first to clear any previous data
  educationForm.reset();
  
  // Pre-populate the form with existing data
  educationForm.institution = education.institution || '';
  educationForm.degree = education.degree || '';
  educationForm.field_of_study = education.field_of_study || '';
  educationForm.period = education.period || '';
  educationForm.description = education.description || '';
  educationForm.is_current = education.is_current || false;
  
  console.log('Form data after population:', {
    institution: educationForm.institution,
    degree: educationForm.degree,
    field_of_study: educationForm.field_of_study,
    period: educationForm.period,
    description: educationForm.description,
    is_current: educationForm.is_current,
  });
};

const cancelEducationEdit = () => {
  editingEducationEntry.value = null;
  showAddEducationForm.value = false;
  educationForm.reset();
};

// Function to handle current education checkbox change
const handleCurrentEducationChange = () => {
  // If user is checking the current checkbox, show a confirmation
  if (educationForm.is_current && hasCurrentEducation.value) {
    if (!confirm('This will unmark any other education entry that is currently marked as "current". Continue?')) {
      educationForm.is_current = false;
      return;
    }
  }
};

const updateAboutMe = () => {
  aboutMeForm.post('/profile', {
    preserveScroll: true,
    onSuccess: (response) => {
      editingAboutMe.value = false;
      // Reload the page to show updated content
      window.location.reload();
    },
    onError: (errors) => {
      console.error('About Me update errors:', errors);
    },
  });
};

const cancelEdit = (section) => {
  switch(section) {
    case 'basic':
      editingBasicInfo.value = false;
      basicInfoForm.reset();
      break;
    case 'about':
      editingAboutMe.value = false;
      aboutMeForm.reset();
      break;
  }
};
</script>

<style scoped>
/* About tab specific styles */
</style>
