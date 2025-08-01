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
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

function googleSignIn() {
    window.location.href = '/auth/google/redirect';
}
</script>

<template>
    <Head title="Login" />
    <div class="min-h-screen flex items-stretch bg-[#f7f0e6] font-lato">
        <!-- Left: Login Form (50%) -->
        <div class="w-1/2 flex flex-col justify-center px-40 py-10 bg-[#f7f0e6]">
            <div class="mb-10">
                <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                <h2 class="text-3xl font-semibold mb-2 font-playfair">Welcome back</h2>
                <p class="text-gray-600 mb-8">Welcome back! Please enter your details.</p>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input id="email" v-model="form.email" type="email" required autofocus placeholder="Enter your email" class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white" />
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <input id="password" v-model="form.password" type="password" required placeholder="Enter your password" class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white" />
                        <!-- Eye icon could be added here for show/hide password -->
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" v-model="form.remember" class="mr-2 rounded border-gray-300" />
                        Remember password
                    </label>
                    <Link :href="route('password.request')" class="text-sm text-[#1a237e] hover:underline font-medium">Forgot Password</Link>
                </div>
                <button type="submit" :disabled="form.processing" class="w-full bg-[#1a237e] text-white py-2 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50">Sign in</button>
                <button type="button" @click="googleSignIn" class="w-full flex items-center justify-center border border-gray-300 bg-white py-2 rounded-md font-semibold text-lg hover:bg-gray-100 transition">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="w-6 h-6 mr-2" />
                    Sign in with Google
                </button>
            </form>
            <div class="mt-8 text-center text-gray-600">
                Don't have an account?
                <Link :href="route('register')" class="text-[#1a237e] font-medium hover:underline ml-1">Sign up</Link>
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
                    alt="Login carousel image"
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
