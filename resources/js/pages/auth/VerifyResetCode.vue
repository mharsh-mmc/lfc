<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const email = page.props.email || '';
const form = useForm({
    email: email,
    code: '',
});

const submit = () => {
    form.post(route('password.code.verify'));
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
</script>

<template>
    <Head title="Verify Reset Code" />
    <div class="min-h-screen flex items-stretch bg-[#f7f0e6] font-lato">
        <!-- Left: Code Verification Form (50%) -->
        <div class="w-1/2 flex flex-col justify-center px-40 py-10 bg-[#f7f0e6]">
            <div class="mb-10">
                <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                <h2 class="text-4xl font-semibold mb-2 font-playfair">Enter Code</h2>
                <p class="text-gray-600 mb-8 text-lg">Enter the 6-digit code sent to your email.</p>
            </div>
            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <div class="w-full rounded-md border border-gray-300 px-4 py-3 bg-gray-100 text-base cursor-not-allowed select-text">{{ form.email }}</div>
                    <input type="hidden" v-model="form.email" />
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium mb-1">Code</label>
                    <input id="code" v-model="form.code" type="text" maxlength="6" required placeholder="Enter 6-digit code" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                    <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</div>
                </div>
                <button type="submit" :disabled="form.processing" class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50 mt-2">
                    Verify Code
                </button>
            </form>
        </div>
        <!-- Right: Carousel (50%) -->
        <div class="w-1/2 relative overflow-hidden">
            <transition-group name="carousel-fade" tag="div" class="absolute inset-0 w-full h-full">
                <img v-for="(img, idx) in images" :key="img" v-show="current === idx" :src="img" class="object-cover w-full h-full transition-opacity duration-700" style="min-height: 100vh;" alt="Verify code carousel image" />
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