<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => {
            window.location = route('password.code', { email: form.email });
        }
    });
};

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
// Use the same image as in the screenshot
</script>

<template>
    <Head title="Forgot Password" />
    <div class="min-h-screen flex items-stretch bg-[#f7f0e6] font-lato">
        <!-- Left: Forgot Password Form (50%) -->
        <div class="w-1/2 flex flex-col justify-center px-40 py-10 bg-[#f7f0e6]">
            <div class="mb-10">
                <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                <h2 class="text-4xl font-semibold mb-2 font-playfair">Forgot Password</h2>
                <p class="text-gray-600 mb-8 text-lg">Enter your email address and we’ll give you reset instruction.</p>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium mb-1">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Enter your email"
                        class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base"
                    />
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                </div>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50 mt-2"
                >
                    Forget password
                </button>
            </form>
            <div class="mt-8 text-center text-gray-600">
                Already have an account?
                <Link :href="route('login')" class="text-[#1a237e] font-medium hover:underline ml-1">Sign In</Link>
            </div>
        </div>
        <!-- Right: Static Image (50%) -->
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
</style>
