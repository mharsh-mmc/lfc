<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import SettingsForm from '@/components/SettingsForm.vue';

const page = usePage();
const user = page.props.auth.user;

// Get existing settings or use defaults
const existingSettings = user?.settings || {
    subscription_plan: 'Free',
    privacy_settings: {
        profile_visible: user?.is_public || false,
        show_tributes: false,
        allow_tribute_requests: false,
        email_notifications: false,
    },
    permissions: {
        legacy_messages: true,
        family_management: false,
        ai_suggestions: false,
    },
};

const formData = {
    username: user?.username || '',
    email: user?.email || 'mauro.pompelli@gmail.com',
    subscription_plan: existingSettings.subscription_plan,
    privacy_settings: existingSettings.privacy_settings,
    permissions: existingSettings.permissions,
};
</script>

<template>
    <Head title="Settings" />
    
    <div class="min-h-screen bg-white">
        <!-- Header -->
        <Header variant="dark" />
        
        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Settings Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
                </div>
                
                <!-- Success Message -->
                <div v-if="$page.props.flash?.success" class="px-6 py-4 bg-green-50 border-b border-green-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ $page.props.flash.success }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Settings Form -->
                <div class="p-6">
                    <SettingsForm :initial-data="formData" />
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
/* Custom styles for better responsive design */
@media (max-width: 640px) {
    .space-y-8 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 1.5rem;
    }
}

@media (max-width: 768px) {
    .space-y-6 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 1.25rem;
    }
}
</style> 