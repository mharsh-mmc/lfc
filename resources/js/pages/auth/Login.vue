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

// Password visibility state
const showPassword = ref(false);

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
        onError: (errors) => {
            // Handle specific error cases
            if (errors.email && errors.email.includes('auth.failed')) {
                form.setError('email', 'Invalid email or password. Please try again.');
            }
        },
    });
};

function googleSignIn() {
    window.location.href = '/auth/google/redirect';
}

const togglePassword = () => {
    showPassword.value = !showPassword.value;
    const passwordInputs = document.querySelectorAll('input[type="password"], input[type="text"]');
    passwordInputs.forEach(input => {
        if (input.id.includes('password')) {
            input.type = showPassword.value ? 'text' : 'password';
        }
    });
};
</script>

<template>
    <Head title="Login" />
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
            <!-- Left: Login Form -->
            <div class="w-1/2 flex flex-col justify-center px-8 lg:px-16 xl:px-40 py-10 bg-[#f7f0e6] relative z-10">
                <div class="mb-10">
                    <div class="text-3xl font-bold mb-8 tracking-tight font-playfair">liveforever</div>
                    <h2 class="text-3xl font-semibold mb-2 font-playfair">Welcome back</h2>
                    <p class="text-gray-600 mb-8">Welcome back! Please enter your details.</p>
                </div>
                
                <!-- General Error Message -->
                <div v-if="form.errors.email && !form.errors.email.includes('required')" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ form.errors.email }}
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
                            placeholder="Enter your email" 
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
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                placeholder="Enter your password" 
                                class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white pr-10" 
                            />
                            <!-- Eye icon for password visibility -->
                            <button 
                                type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                @click="togglePassword"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" v-model="form.remember" class="mr-2 rounded border-gray-300" />
                            Remember password
                        </label>
                        <Link :href="route('password.request')" class="text-sm text-[#1a237e] hover:underline font-medium">Forgot Password</Link>
                    </div>
                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="w-full bg-[#1a237e] text-white py-2 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                    >
                        Sign in
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
                    Don't have an account?
                    <Link :href="route('register')" class="text-[#1a237e] font-medium hover:underline ml-1">Sign up</Link>
                </div>
            </div>
            
            <!-- Right: Image Carousel -->
            <div class="w-1/2 flex items-center justify-center bg-[#f7f0e6] relative overflow-hidden">
                <transition-group name="carousel-fade" tag="div" class="absolute inset-0 w-full h-full">
                    <img
                        v-for="(img, idx) in images"
                        :key="img"
                        v-show="current === idx"
                        :src="img"
                        class="object-cover w-full h-full transition-opacity duration-700"
                        style="min-height: 100vh;"
                        alt="Login carousel image"
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
                    <h2 class="text-3xl font-semibold mb-2 font-playfair text-center">Welcome back</h2>
                    <p class="text-gray-600 mb-6 text-center">Welcome back! Please enter your details.</p>
                </div>
                
                <!-- General Error Message -->
                <div v-if="form.errors.email && !form.errors.email.includes('required')" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md text-center">
                    {{ form.errors.email }}
                </div>
                
                <form @submit.prevent="submit" class="space-y-6 max-w-md mx-auto">
                    <div>
                        <label for="email-tablet" class="block text-sm font-medium mb-1">Email</label>
                        <input 
                            id="email-tablet" 
                            v-model="form.email" 
                            type="email" 
                            required 
                            autofocus 
                            placeholder="Enter your email" 
                            class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white" 
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="password-tablet" class="block text-sm font-medium mb-1">Password</label>
                        <div class="relative">
                            <input 
                                id="password-tablet" 
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                placeholder="Enter your password" 
                                class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white pr-10" 
                            />
                            <!-- Eye icon for password visibility -->
                            <button 
                                type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                @click="togglePassword"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" v-model="form.remember" class="mr-2 rounded border-gray-300" />
                            Remember password
                        </label>
                        <Link :href="route('password.request')" class="text-sm text-[#1a237e] hover:underline font-medium">Forgot Password</Link>
                    </div>
                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="w-full bg-[#1a237e] text-white py-2 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                    >
                        Sign in
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
                <div class="mt-6 text-center text-gray-600">
                    Don't have an account?
                    <Link :href="route('register')" class="text-[#1a237e] font-medium hover:underline ml-1">Sign up</Link>
                </div>
            </div>
        </div>

        <!-- Mobile Layout (300px-600px) -->
        <div class="sm:hidden min-h-screen flex items-center justify-center p-4 relative z-10">
            <div class="w-full max-w-md bg-white/90 backdrop-blur-sm rounded-xl shadow-2xl p-8">
                <div class="mb-8">
                    <div class="text-2xl font-bold mb-6 tracking-tight font-playfair text-center">liveforever</div>
                    <h2 class="text-2xl font-semibold mb-2 font-playfair text-center">Welcome back</h2>
                    <p class="text-gray-600 mb-6 text-center">Welcome back! Please enter your details.</p>
                </div>
                
                <!-- General Error Message -->
                <div v-if="form.errors.email && !form.errors.email.includes('required')" class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md text-center">
                    {{ form.errors.email }}
                </div>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email-mobile" class="block text-sm font-medium mb-1">Email</label>
                        <input 
                            id="email-mobile" 
                            v-model="form.email" 
                            type="email" 
                            required 
                            autofocus 
                            placeholder="Enter your email" 
                            class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white" 
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="password-mobile" class="block text-sm font-medium mb-1">Password</label>
                        <div class="relative">
                            <input 
                                id="password-mobile" 
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'" 
                                required 
                                placeholder="Enter your password" 
                                class="w-full rounded-md border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#1a237e] focus:outline-none bg-white pr-10" 
                            />
                            <!-- Eye icon for password visibility -->
                            <button 
                                type="button" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                @click="togglePassword"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm">
                            <input type="checkbox" v-model="form.remember" class="mr-2 rounded border-gray-300" />
                            Remember password
                        </label>
                        <Link :href="route('password.request')" class="text-sm text-[#1a237e] hover:underline font-medium">Forgot Password</Link>
                    </div>
                    <button 
                        type="submit" 
                        :disabled="form.processing" 
                        class="w-full bg-[#1a237e] text-white py-2 rounded-md font-semibold text-lg hover:bg-[#283593] transition disabled:opacity-50"
                    >
                        Sign in
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
                    Don't have an account?
                    <Link :href="route('register')" class="text-[#1a237e] font-medium hover:underline ml-1">Sign up</Link>
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

/* Responsive adjustments */
@media (min-width: 1024px) and (max-width: 1279px) {
  /* Tablet adjustments */
  .lg\:px-16 {
    padding-left: 2rem;
    padding-right: 2rem;
  }
}

@media (min-width: 1280px) {
  /* Desktop adjustments */
  .xl\:px-40 {
    padding-left: 10rem;
    padding-right: 10rem;
  }
}
</style>
