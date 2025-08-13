<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const email = page.props.email || '';
const form = useForm({
    email: email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'));
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
    <Head title="Reset Password" />
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
            <!-- Left: Reset Password Form (2/3) -->
            <div class="w-1/2 flex flex-col justify-center px-8 lg:px-16 xl:px-40 py-10 bg-[#f7f0e6] relative z-10">
                <div class="mb-10">
                    <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                    <h2 class="text-4xl font-semibold mb-2 font-playfair">Reset Password</h2>
                    <p class="text-gray-600 mb-8 text-lg">Enter your new password below.</p>
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium mb-1">Email</label>
                        <input id="email" v-model="form.email" type="email" required autofocus placeholder="Enter your email" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium mb-1">New Password</label>
                        <input id="password" v-model="form.password" type="password" required placeholder="Enter new password" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input id="password_confirmation" v-model="form.password_confirmation" type="password" required placeholder="Confirm new password" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50 mt-2">
                        Reset Password
                    </button>
                </form>
            </div>
            
            <!-- Right: Static Image (1/3) -->
            <div class="w-1/2 flex items-center justify-center bg-[#f7f0e6] relative overflow-hidden">
                <transition-group name="carousel-fade" tag="div" class="absolute inset-0 w-full h-full">
                    <img v-for="(img, idx) in images" :key="img" v-show="current === idx" :src="img" class="object-cover w-full h-full transition-opacity duration-700" style="min-height: 100vh;" alt="Reset carousel image" />
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
                    <h2 class="text-3xl font-semibold mb-2 font-playfair text-center">Reset Password</h2>
                    <p class="text-gray-600 mb-6 text-center">Enter your new password below.</p>
                </div>
                <form @submit.prevent="submit" class="space-y-6 max-w-md mx-auto">
                    <div>
                        <label for="email-tablet" class="block text-sm font-medium mb-1">Email</label>
                        <input id="email-tablet" v-model="form.email" type="email" required autofocus placeholder="Enter your email" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="password-tablet" class="block text-sm font-medium mb-1">New Password</label>
                        <input id="password-tablet" v-model="form.password" type="password" required placeholder="Enter new password" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>
                    <div>
                        <label for="password_confirmation-tablet" class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input id="password_confirmation-tablet" v-model="form.password_confirmation" type="password" required placeholder="Confirm new password" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50 mt-2">
                        Reset Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile Layout (300px-600px) -->
        <div class="sm:hidden min-h-screen flex items-center justify-center p-4 relative z-10">
            <div class="w-full max-w-md bg-white/90 backdrop-blur-sm rounded-xl shadow-2xl p-8">
                <div class="mb-8">
                    <div class="text-2xl font-bold mb-6 tracking-tight font-playfair text-center">liveforever</div>
                    <h2 class="text-2xl font-semibold mb-2 font-playfair text-center">Reset Password</h2>
                    <p class="text-gray-600 mb-6 text-center">Enter your new password below.</p>
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email-mobile" class="block text-sm font-medium mb-1">Email</label>
                        <input id="email-mobile" v-model="form.email" type="email" required autofocus placeholder="Enter your email" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="password-mobile" class="block text-sm font-medium mb-1">New Password</label>
                        <input id="password-mobile" v-model="form.password" type="password" required placeholder="Enter new password" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>
                    <div>
                        <label for="password_confirmation-mobile" class="block text-sm font-medium mb-1">Confirm Password</label>
                        <input id="password_confirmation-mobile" v-model="form.password_confirmation" type="password" required placeholder="Confirm new password" class="w-full rounded-md border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white text-base" />
                        <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full bg-[#1a237e] text-white py-3 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50 mt-2">
                        Reset Password
                    </button>
                </form>
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
