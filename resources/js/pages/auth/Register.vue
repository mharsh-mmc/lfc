<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

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

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

function googleSignIn() {
    window.location.href = '/auth/google/redirect';
}
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen flex items-stretch bg-[#f7f0e6] font-lato">
        <!-- Left: Register Form (50%) -->
        <div class="w-1/2 flex flex-col justify-center px-40 py-10 bg-[#f7f0e6]">
            <div class="mb-10">
                <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                <h2 class="text-3xl font-semibold mb-2 font-playfair">Welcome to liveforever</h2>
                <p class="text-gray-600 mb-8">Build something timeless. Start your journey today.</p>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium mb-1">Full Name</label>
                    <input 
                        id="name" 
                        v-model="form.name" 
                        type="text" 
                        required 
                        autofocus 
                        autocomplete="name" 
                        placeholder="Enter your full Name" 
                        class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white" 
                    />
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email Address</label>
                    <input 
                        id="email" 
                        v-model="form.email" 
                        type="email" 
                        required 
                        autocomplete="username" 
                        placeholder="Enter Your Email Address" 
                        class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white" 
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <input 
                            id="password" 
                            v-model="form.password" 
                            type="password" 
                            required 
                            autocomplete="new-password" 
                            placeholder="Enter Your Password" 
                            class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white pr-10" 
                        />
                        <!-- Eye icon for show/hide password -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                    <div class="relative">
                        <input 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            type="password" 
                            required 
                            autocomplete="new-password" 
                            placeholder="Confirm your password" 
                            class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white pr-10" 
                        />
                        <!-- Eye icon for show/hide password -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                </div>
                <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="flex items-center">
                    <input 
                        id="terms" 
                        v-model="form.terms" 
                        type="checkbox" 
                        required 
                        class="mr-2 rounded border-gray-300" 
                    />
                    <label for="terms" class="text-sm text-gray-600">
                        I agree to the 
                        <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900">Terms of Service</a> 
                        and 
                        <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900">Privacy Policy</a>
                    </label>
                    <div v-if="form.errors.terms" class="mt-1 text-sm text-red-600">{{ form.errors.terms }}</div>
                </div>
                <button 
                    type="submit" 
                    :disabled="form.processing" 
                    class="w-full bg-[#1a237e] text-white py-2 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                >
                    Sign Up
                </button>
                <button 
                    type="button" 
                    @click="googleSignIn" 
                    class="w-full flex items-center justify-center border border-gray-300 bg-white py-2 rounded-md font-semibold text-lg hover:bg-gray-100 transition"
                >
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="w-6 h-6 mr-2" />
                    Sign in with Google
                </button>
            </form>
            <div class="mt-8 text-center text-gray-600">
                Already have an account?
                <Link :href="route('login')" class="text-[#1a237e] font-medium hover:underline ml-1">Sign In</Link>
            </div>
        </div>
        <!-- Right: Carousel (50%) -->
        <div class="w-1/2 flex items-center justify-center bg-[#f7f0e6] relative overflow-hidden">
            <transition-group name="carousel-fade" tag="div" class="absolute inset-0 w-full h-full">
                <img
                    v-for="(img, idx) in images"
                    :key="img"
                    v-show="current === idx"
                    :src="img"
                    class="object-cover w-full h-full transition-opacity duration-700 "
                    style="min-height: 100vh;"
                    alt="Register carousel image"
                />
            </transition-group>
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
