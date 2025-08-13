<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import VideoUploadModal from '@/components/VideoUploadModal.vue';
import PhotoUploadModal from '@/components/PhotoUploadModal.vue';
// Letter components are now handled within LettersTab.vue
import SuccessPopup from '@/components/SuccessPopup.vue';

// Profile Components
import ProfileBanner from '@/components/profile/ProfileBanner.vue';
import ProfileInfo from '@/components/profile/ProfileInfo.vue';
import ProfileSettings from '@/components/profile/ProfileSettings.vue';
import ProfileNavigation from '@/components/profile/ProfileNavigation.vue';

// Tab Components
import AboutTab from '@/components/profile/AboutTab.vue';
import FamilyTreeTab from '@/components/profile/FamilyTreeTab.vue';

import VideoTab from '@/components/profile/VideoTab.vue';
import ImagesTab from '@/components/profile/ImagesTab.vue';
import LettersTab from '@/components/profile/LettersTab.vue';
import NotificationsTab from '@/components/profile/NotificationsTab.vue';
import DeceasedProfilesTab from '@/components/profile/DeceasedProfilesTab.vue';

const props = defineProps({
    profileUser: Object,
    isOwnProfile: Boolean,
    canViewProfile: Boolean,
    isProfilePublic: Boolean,
    activeTab: {
        type: String,
        default: 'about'
    },
    letters: {
        type: Array,
        default: () => []
    },
    videos: {
        type: Array,
        default: () => []
    },
    photos: {
        type: Array,
        default: () => []
    },
});

// Create reactive copy of profile user data
const profileUserData = ref({ ...props.profileUser });

// Watch for changes in props and update reactive data
watch(() => props.profileUser, (newUser) => {
    profileUserData.value = { ...newUser };
}, { deep: true });

// Reactive state for privacy settings
const currentPrivacyState = ref(props.isProfilePublic);
const currentCanViewProfile = ref(props.canViewProfile);

// Use the activeTab from props with better initialization
const activeTab = ref(props.activeTab || 'about');

// Computed property for better reactivity
const currentActiveTab = computed(() => {
    return activeTab.value;
});

// Watch for changes in activeTab prop and update the reactive ref
watch(() => props.activeTab, (newTab) => {
    if (newTab && newTab !== activeTab.value) {
        activeTab.value = newTab;
    }
}, { immediate: true });

// Also watch for route changes
watch(() => window.location.pathname, (newPath) => {
    const pathSegments = newPath.split('/');
    const lastSegment = pathSegments[pathSegments.length - 1];
    
    // Map URL segments to tab names
    const tabMap = {
        'familytree': 'familytree',
        'video': 'video', 
        'letters': 'letters',
        'photos': 'photos',
        'notifications': 'notifications'
    };
    
    if (tabMap[lastSegment] && tabMap[lastSegment] !== activeTab.value) {
        activeTab.value = tabMap[lastSegment];
    } else if (!tabMap[lastSegment] && activeTab.value !== 'about') {
        activeTab.value = 'about';
    }
}, { immediate: true });

// Success popup states
const showBannerSuccessPopup = ref(false);
const showProfilePicSuccessPopup = ref(false);

// Modal states
const showUploadModal = ref(false);
const showPhotoUploadModal = ref(false);

// Event handlers
const handleBannerUpdated = () => {
    showBannerSuccessPopup.value = true;
};

const handleProfilePicUpdated = () => {
    showProfilePicSuccessPopup.value = true;
};

const handlePrivacyToggled = (isPublic) => {
    currentPrivacyState.value = isPublic;
    currentCanViewProfile.value = props.isOwnProfile || isPublic;
};

const handleMoreTabClicked = (tab) => {
    if (tab === 'create-deceased') {
        activeTab.value = 'create-deceased';
    }
};

const handleDeceasedProfileCreated = () => {
    // Profile created successfully
};

const handleDeceasedProfileUpdated = () => {
    // Profile updated successfully
};

const handleDeceasedProfileDeleted = () => {
    // Profile deleted successfully
};

const handleSuccessClose = () => {
    showBannerSuccessPopup.value = false;
    showProfilePicSuccessPopup.value = false;
};

// Navigation function
const navigateToTab = (tab) => {
    const userId = profileUserData.value.id;
    const routes = {
        'about': `/profile/${userId}`,
        'familytree': `/profile/${userId}/familytree`,
        'video': `/profile/${userId}/video`,
        'letters': `/profile/${userId}/letters`,
        'photos': `/profile/${userId}/photos`,
        'notifications': `/profile/${userId}/notifications`,
    };
    
    if (routes[tab]) {
        router.visit(routes[tab], {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }
};

// Utility functions
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const getAge = (dateOfBirth) => {
    if (!dateOfBirth) return '';
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <Header variant="dark" />

        <!-- Full-width flex container -->
        <div class="flex flex-col w-full">
            <!-- Lock Screen (shown when profile is private and user is not the owner) -->
            <div v-if="!currentCanViewProfile" class="min-h-screen bg-amber-50 flex items-center justify-center relative">
                <!-- Blurred background content -->
                <div class="absolute inset-0 bg-amber-50 opacity-30"></div>
                
                <!-- Lock Modal -->
                <div class="relative bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4 text-center z-10">
                    <!-- Lock Icon -->
                    <div class="mb-6">
                        <svg class="w-16 h-16 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    
                    <!-- Lock Message -->
                    <h2 class="text-xl font-semibold text-blue-600 mb-4">This Profile is Locked</h2>
                    <p class="text-blue-500 mb-6">This profile is private. Buy a subscription to unlock and view it.</p>
                    
                    <!-- Unlock Button -->
                    <button class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-medium">
                        Unlock With Subscription
                    </button>
                </div>
            </div>

            <!-- Profile Content (shown when profile is accessible) -->
            <div v-else class="w-full max-w-[1440px] mx-auto md:w-[90%] md:max-w-[700px] lg:w-full lg:max-w-[1440px]">
                <!-- Profile Banner -->
                <ProfileBanner 
                    :profile-user-data="profileUserData"
                    :is-own-profile="isOwnProfile"
                    @banner-updated="handleBannerUpdated"
                />

                <!-- Profile Info -->
                <ProfileInfo 
                    :profile-user-data="profileUserData"
                    :is-own-profile="isOwnProfile"
                    @profile-pic-updated="handleProfilePicUpdated"
                />
                


                <!-- Profile Settings (only for own profile) -->
                <ProfileSettings 
                    v-if="isOwnProfile"
                    :is-own-profile="isOwnProfile"
                    :current-privacy-state="currentPrivacyState"
                    @privacy-toggled="handlePrivacyToggled"
                />
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ProfileNavigation 
            :profile-user-data="profileUserData"
            :is-own-profile="isOwnProfile"
            :current-active-tab="currentActiveTab"
            @more-tab-clicked="handleMoreTabClicked"
        />

        <!-- Tab Content -->
        <div class="w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 md:w-[90%] md:max-w-[700px] lg:w-full lg:max-w-[1440px]" :key="`tab-content-${currentActiveTab}`">
            <!-- About Me Tab -->
            <AboutTab 
                v-if="currentActiveTab === 'about'"
                :profile-user-data="profileUserData"
                :is-own-profile="isOwnProfile"
            />

            <!-- Family Tree Tab -->
            <FamilyTreeTab 
                v-else-if="currentActiveTab === 'familytree'"
                :profile-user-data="profileUserData"
                :current-user="profileUserData"
            />

            <!-- Video Tab -->
            <VideoTab 
                v-else-if="currentActiveTab === 'video'"
                :user-id="profileUserData.id"
                :is-own-profile="isOwnProfile"
            />

            <!-- Images Tab -->
            <ImagesTab 
                v-else-if="currentActiveTab === 'photos'"
                :user-id="profileUserData.id"
                :is-own-profile="isOwnProfile"
            />

            <!-- Letters Tab -->
            <LettersTab 
                v-else-if="currentActiveTab === 'letters'"
                :user-id="profileUserData.id"
                :is-own-profile="isOwnProfile"
            />

            <!-- Notifications Tab -->
            <NotificationsTab 
                v-else-if="currentActiveTab === 'notifications'"
                :is-own-profile="isOwnProfile"
            />

            <!-- Deceased Profiles Tab -->
            <DeceasedProfilesTab 
                v-else-if="currentActiveTab === 'create-deceased'"
                :user-id="profileUserData.id"
                @profile-created="handleDeceasedProfileCreated"
                @profile-updated="handleDeceasedProfileUpdated"
                @profile-deleted="handleDeceasedProfileDeleted"
            />
        </div>
    </div>

    <!-- Footer -->
    <Footer />

    <!-- Video Upload Modal -->
    <VideoUploadModal 
        :show="showUploadModal"
        @close="showUploadModal = false"
    />

    <!-- Photo Upload Modal -->
    <PhotoUploadModal 
        :show="showPhotoUploadModal"
        @close="showPhotoUploadModal = false"
    />

    <!-- Letter functionality is now handled within LettersTab.vue -->

    <!-- Success Popups -->
    <SuccessPopup
        :show="showBannerSuccessPopup"
        title="Banner Updated Successfully!"
        message="Your profile banner has been updated and is now visible on your profile."
        button-text="Great!"
        @close="handleSuccessClose"
    />
    
    <SuccessPopup
        :show="showProfilePicSuccessPopup"
        title="Profile Picture Updated Successfully!"
        message="Your profile picture has been updated and is now visible on your profile."
        button-text="Great!"
        @close="handleSuccessClose"
    />
</template>

<style scoped>
/* DynamicProfile specific styles */
</style> 