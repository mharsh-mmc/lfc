<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/components/ActionMessage.vue';
import FormSection from '@/components/FormSection.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import QuillEditor from '@/components/QuillEditor.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
    banner: null,
    title: props.user.title || '',
    date_of_birth: props.user.date_of_birth || '',
    location: props.user.location || '',
    bio: props.user.bio || '',
    height_cm: props.user.height_cm || '',
    weight_kg: props.user.weight_kg || '',
    university: props.user.university || '',
    field_of_study: props.user.field_of_study || '',
    education_period: props.user.education_period || '',
    passion: props.user.passion || '',
    profession: props.user.profession || '',
    mission: props.user.mission || '',
    calling: props.user.calling || '',
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);
const bannerPreview = ref(null);
const bannerInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('profile.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const selectNewBanner = () => {
    bannerInput.value.click();
};

const updateBannerPreview = () => {
    const banner = bannerInput.value.files[0];

    if (! banner) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        bannerPreview.value = e.target.result;
    };

    reader.readAsDataURL(banner);
};

const clearBannerFileInput = () => {
    if (bannerInput.value?.value) {
        bannerInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #title>
            Profile Information
        </template>

        <template #description>
            Update your account's profile information and email address.
        </template>

        <template #form>
            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input
                    id="photo"
                    ref="photoInput"
                    type="file"
                    class="hidden"
                    @change="updatePhotoPreview"
                >

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="! photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.profile_photo_path"
                    type="button"
                    class="mt-2"
                    @click.prevent="deletePhoto"
                >
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Profile Banner -->
            <div class="col-span-6 sm:col-span-4">
                <!-- Banner File Input -->
                <input
                    id="banner"
                    ref="bannerInput"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="updateBannerPreview"
                >

                <InputLabel for="banner" value="Profile Banner" />

                <!-- Current Banner -->
                <div v-show="! bannerPreview" class="mt-2">
                    <div v-if="user.banner_url" class="relative">
                        <img :src="user.banner_url" :alt="user.name + ' banner'" class="w-full h-32 object-cover rounded-lg">
                    </div>
                    <div v-else class="w-full h-32 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- New Banner Preview -->
                <div v-show="bannerPreview" class="mt-2">
                    <img :src="bannerPreview" :alt="user.name + ' banner preview'" class="w-full h-32 object-cover rounded-lg">
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewBanner">
                    Select New Banner
                </SecondaryButton>

                <SecondaryButton
                    v-if="user.banner_url"
                    type="button"
                    class="mt-2"
                    @click.prevent="clearBannerFileInput"
                >
                    Remove Banner
                </SecondaryButton>

                <InputError :message="form.errors.banner" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-2" />

                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                    <p class="text-sm mt-2">
                        Your email address is unverified.

                        <Link
                            :href="route('verification.send')"
                            method="post"
                            as="button"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            @click.prevent="sendEmailVerification"
                        >
                            Click here to re-send the verification email.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                </div>
            </div>

            <!-- Title -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="title" value="Title/Profession" />
                <TextInput
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="e.g., Teacher, Inventor, CEO"
                />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>

            <!-- Date of Birth -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="date_of_birth" value="Date of Birth" />
                <TextInput
                    id="date_of_birth"
                    v-model="form.date_of_birth"
                    type="date"
                    class="mt-1 block w-full"
                />
                <InputError :message="form.errors.date_of_birth" class="mt-2" />
            </div>

            <!-- Location -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="location" value="Location" />
                <TextInput
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="e.g., Italy, Foggia"
                />
                <InputError :message="form.errors.location" class="mt-2" />
            </div>

            <!-- Bio -->
            <div class="col-span-6">
                <InputLabel for="bio" value="Bio" />
                <QuillEditor
                    v-model="form.bio"
                    placeholder="Tell us about yourself..."
                    :error="form.errors.bio"
                    height="200px"
                />
            </div>

            <!-- Height -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="height_cm" value="Height (cm)" />
                <TextInput
                    id="height_cm"
                    v-model="form.height_cm"
                    type="number"
                    class="mt-1 block w-full"
                    placeholder="152"
                />
                <InputError :message="form.errors.height_cm" class="mt-2" />
            </div>

            <!-- Weight -->
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="weight_kg" value="Weight (kg)" />
                <TextInput
                    id="weight_kg"
                    v-model="form.weight_kg"
                    type="number"
                    class="mt-1 block w-full"
                    placeholder="56"
                />
                <InputError :message="form.errors.weight_kg" class="mt-2" />
            </div>

            <!-- University -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="university" value="University" />
                <TextInput
                    id="university"
                    v-model="form.university"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="e.g., Northbridge University"
                />
                <InputError :message="form.errors.university" class="mt-2" />
            </div>

            <!-- Field of Study -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="field_of_study" value="Field of Study" />
                <TextInput
                    id="field_of_study"
                    v-model="form.field_of_study"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="e.g., Nuclear engineering"
                />
                <InputError :message="form.errors.field_of_study" class="mt-2" />
            </div>

            <!-- Education Period -->
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="education_period" value="Education Period" />
                <TextInput
                    id="education_period"
                    v-model="form.education_period"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="e.g., 1852 - 1854"
                />
                <InputError :message="form.errors.education_period" class="mt-2" />
            </div>

            <!-- Passion -->
            <div class="col-span-6">
                <InputLabel for="passion" value="What do you love to do? (Your passion)" />
                <QuillEditor
                    v-model="form.passion"
                    placeholder="Describe what you love to do..."
                    :error="form.errors.passion"
                    height="150px"
                />
            </div>

            <!-- Profession -->
            <div class="col-span-6">
                <InputLabel for="profession" value="What are you good at? (Your profession)" />
                <QuillEditor
                    v-model="form.profession"
                    placeholder="Describe what you are good at..."
                    :error="form.errors.profession"
                    height="150px"
                />
            </div>

            <!-- Mission -->
            <div class="col-span-6">
                <InputLabel for="mission" value="What does the world need that you can give? (Your mission)" />
                <QuillEditor
                    v-model="form.mission"
                    placeholder="Describe your mission..."
                    :error="form.errors.mission"
                    height="150px"
                />
            </div>

            <!-- Calling -->
            <div class="col-span-6">
                <InputLabel for="calling" value="What do you have a natural inclination towards? (Your calling)" />
                <QuillEditor
                    v-model="form.calling"
                    placeholder="Describe your natural calling..."
                    :error="form.errors.calling"
                    height="150px"
                />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Saved.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Save
            </PrimaryButton>
        </template>
    </FormSection>
</template>
