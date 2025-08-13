<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const images = [
    '/login-1.jpg',
    '/login-2.jpg',
    '/login-3.jpg',
    '/login-4.jpg',
];
const current = ref(0);
let interval = null;

onMounted(() => {
    interval = setInterval(() => {
        current.value = (current.value + 1) % images.length;
    }, 5000);
});
onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <Head title="Email Verification" />
    <div class="min-h-screen bg-[#f7f0e6] font-lato relative">
        <!-- Mobile Background Image (blurred) -->
        <div class="sm:hidden absolute inset-0">
            <img 
                :src="images[current]" 
                class="w-full h-full object-cover blur-sm opacity-30"
                alt="Background"
            />
        </div>

        <!-- Desktop Layout (1025px+) -->
        <div class="hidden lg:flex min-h-screen items-stretch">
            <!-- Left: Email Verification Form (2/3) -->
            <div class="w-1/2 flex flex-col justify-center px-8 lg:px-16 xl:px-40 py-10 bg-[#f7f0e6] relative z-10">
                <div class="mb-10">
                    <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                    <h2 class="text-4xl font-semibold mb-2 font-playfair">Email Verification</h2>
                    <p class="text-gray-600 mb-8 text-lg">Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.</p>
                </div>

                <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800 text-sm">A new verification link has been sent to the email address you provided in your profile settings.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                    >
                        Resend Verification Email
                    </button>
                </form>

                <div class="mt-8 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <Link
                        :href="route('profile.show')"
                        class="text-[#1a237e] hover:underline font-medium"
                    >
                        Edit Profile
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-[#1a237e] hover:underline font-medium"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
            
            <!-- Right: Static Image (1/3) -->
            <div class="w-1/2 flex items-center justify-center bg-[#f7f0e6] relative overflow-hidden">
                <transition-group name="carousel-fade" tag="div" class="absolute inset-0 w-full h-full">
                    <img
                        v-for="(img, idx) in images"
                        :key="img"
                        v-show="current === idx"
                        :src="img"
                        class="object-cover w-full h-full transition-opacity duration-700"
                        style="min-height: 100vh;"
                        alt="Email verification carousel image"
                    />
                </transition-group>
            </div>
        </div>

        <!-- Tablet Layout (600px-1024px) -->
        <div class="hidden sm:block lg:hidden min-h-screen">
            <!-- Top: Two Images Side by Side -->
            <div class="h-1/2 flex">
                <div class="w-1/2 relative overflow-hidden">
                    <img
                        :src="images[0]"
                        class="object-cover w-full h-full"
                        alt="Family image 1"
                    />
                </div>
                <div class="w-1/2 relative overflow-hidden">
                    <img
                        :src="images[1]"
                        class="object-cover w-full h-full"
                        alt="Family image 2"
                    />
                </div>
            </div>
            
            <!-- Bottom: Form -->
            <div class="h-1/2 flex flex-col justify-center px-8 lg:px-16 py-10 bg-[#f7f0e6]">
                <div class="mb-8">
                    <div class="text-2xl font-bold mb-6 tracking-tight font-playfair text-center">liveforever</div>
                    <h2 class="text-3xl font-semibold mb-2 font-playfair text-center">Email Verification</h2>
                    <p class="text-gray-600 mb-6 text-center">Before continuing, could you verify your email address by clicking on the link we just emailed to you?</p>
                </div>

                <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800 text-sm text-center">A new verification link has been sent to the email address you provided in your profile settings.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6 max-w-md mx-auto">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                    >
                        Resend Verification Email
                    </button>
                </form>

                <div class="mt-6 flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <Link
                        :href="route('profile.show')"
                        class="text-[#1a237e] hover:underline font-medium text-center"
                    >
                        Edit Profile
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-[#1a237e] hover:underline font-medium text-center"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </div>

        <!-- Mobile Layout (300px-600px) -->
        <div class="sm:hidden min-h-screen flex items-center justify-center p-4 relative z-10">
            <div class="w-full max-w-md bg-white/90 backdrop-blur-sm rounded-xl shadow-2xl p-8">
                <div class="mb-8">
                    <div class="text-2xl font-bold mb-6 tracking-tight font-playfair text-center">liveforever</div>
                    <h2 class="text-2xl font-semibold mb-2 font-playfair text-center">Email Verification</h2>
                    <p class="text-gray-600 mb-6 text-center">Before continuing, could you verify your email address by clicking on the link we just emailed to you?</p>
                </div>

                <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800 text-sm text-center">A new verification link has been sent to the email address you provided in your profile settings.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                    >
                        Resend Verification Email
                    </button>
                </form>

                <div class="mt-6 flex flex-col space-y-2">
                    <Link
                        :href="route('profile.show')"
                        class="text-[#1a237e] hover:underline font-medium text-center"
                    >
                        Edit Profile
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-[#1a237e] hover:underline font-medium text-center"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700&display=swap');

.font-playfair {
  font-family: 'Playfair Display', serif;
}

.font-lato {
  font-family: 'Lato', sans-serif;
}

.carousel-fade-enter-active, .carousel-fade-leave-active {
  transition: opacity 0.7s;
}

.carousel-fade-enter-from, .carousel-fade-leave-to {
  opacity: 0;
}

.carousel-fade-enter-to, .carousel-fade-leave-from {
  opacity: 1;
}
</style>
