<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';

interface SettingsFormData {
    username: string;
    email: string;
    subscription_plan: string;
    privacy_settings: {
        profile_visible: boolean;
        show_tributes: boolean;
        allow_tribute_requests: boolean;
        email_notifications: boolean;
    };
    permissions: {
        legacy_messages: boolean;
        family_management: boolean;
        ai_suggestions: boolean;
    };
}

interface Props {
    initialData: SettingsFormData;
}

const props = defineProps<Props>();

const form = useForm<SettingsFormData>(props.initialData);

const submit = () => {
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        },
    });
};

const cancel = () => {
    // Reset form to original values
    form.reset();
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">
        <!-- Basic Settings -->
        <div class="space-y-6">
            <div>
                <Label for="username" class="text-sm font-medium text-gray-700">Username</Label>
                <Input
                    id="username"
                    v-model="form.username"
                    type="text"
                    placeholder="Your username"
                    class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.username }"
                />
                <p v-if="form.errors.username" class="mt-1 text-sm text-red-600">
                    {{ form.errors.username }}
                </p>
            </div>
            
            <div>
                <Label for="email" class="text-sm font-medium text-gray-700">Email:</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                    {{ form.errors.email }}
                </p>
            </div>
        </div>
        
        <!-- Subscription Plan -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Subscription Plan</h2>
            <div>
                <Label for="plan" class="text-sm font-medium text-gray-700">Plan:</Label>
                <Input
                    id="plan"
                    v-model="form.subscription_plan"
                    type="text"
                    class="mt-1 block w-full"
                    readonly
                />
            </div>
        </div>
        
        <!-- Privacy Settings -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Privacy Setting</h2>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="profile_visible"
                        v-model:checked="form.privacy_settings.profile_visible"
                    />
                    <Label for="profile_visible" class="text-sm text-gray-700">
                        Allow my profile to be visible
                    </Label>
                </div>
                
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="show_tributes"
                        v-model:checked="form.privacy_settings.show_tributes"
                    />
                    <Label for="show_tributes" class="text-sm text-gray-700">
                        Show my tributes to others
                    </Label>
                </div>
                
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="allow_tribute_requests"
                        v-model:checked="form.privacy_settings.allow_tribute_requests"
                    />
                    <Label for="allow_tribute_requests" class="text-sm text-gray-700">
                        Allow others to request tributes
                    </Label>
                </div>
                
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="email_notifications"
                        v-model:checked="form.privacy_settings.email_notifications"
                    />
                    <Label for="email_notifications" class="text-sm text-gray-700">
                        Receive email notifications
                    </Label>
                </div>
            </div>
        </div>
        
        <!-- Permissions -->
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Permissions</h2>
            <div class="space-y-3">
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="legacy_messages"
                        v-model:checked="form.permissions.legacy_messages"
                    />
                    <Label for="legacy_messages" class="text-sm text-gray-700">
                        Let others send me legacy messages
                    </Label>
                </div>
                
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="family_management"
                        v-model:checked="form.permissions.family_management"
                    />
                    <Label for="family_management" class="text-sm text-gray-700">
                        Let family members manage my profile after passing
                    </Label>
                </div>
                
                <div class="flex items-center space-x-3">
                    <Checkbox
                        id="ai_suggestions"
                        v-model:checked="form.permissions.ai_suggestions"
                    />
                    <Label for="ai_suggestions" class="text-sm text-gray-700">
                        Allow AI-generated memorial suggestions
                    </Label>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
            <Button
                type="button"
                variant="outline"
                @click="cancel"
                class="flex-1 sm:flex-none"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                :disabled="form.processing"
                class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700"
            >
                {{ form.processing ? 'Saving...' : 'Save' }}
            </Button>
        </div>
    </form>
</template> 