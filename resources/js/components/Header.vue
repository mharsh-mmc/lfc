<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    showTagline: {
        type: Boolean,
        default: true
    },
    variant: {
        type: String,
        default: 'default', // 'default' or 'dark'
        validator: (value) => ['default', 'dark'].includes(value)
    }
});
</script>

<template>
    <div>
        <!-- Tagline Section -->
        <div class="flex items-center justify-center gap-8 bg-[#ffffff] text-black px-8 py-4">
            <span class="uppercase tracking-widest text-xs font-semibold hidden md:inline">Some stories deserve to be remembered forever</span>
        </div>
        
        <!-- Main Header -->
        <header :class="[
            'shadow-sm border-b',
            variant === 'dark' ? 'bg-[#1E3A8A] text-white' : 'bg-white'
        ]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <!-- Logo -->
                    <div class="flex items-center flex-1">
                        <Link href="/" :class="[
                            'text-2xl font-bold transition',
                            variant === 'dark' ? 'text-white hover:text-blue-200' : 'text-blue-400 hover:text-blue-600'
                        ]">
                            liveforever
                        </Link>
                    </div>
                    
                    <!-- Navigation - Centered -->
                    <nav class="flex items-center space-x-6 flex-1 justify-center">
                        <Link href="/" :class="[
                            'transition',
                            variant === 'dark' ? 'text-white hover:underline font-playfair' : 'text-gray-700 hover:text-blue-600'
                        ]">Home</Link>
                        <Link href="/leave-a-mark" :class="[
                            'transition',
                            variant === 'dark' ? 'text-white hover:underline font-playfair' : 'text-gray-700 hover:text-blue-600'
                        ]">Leave a mark</Link>
                        <Link href="/profile" :class="[
                            'transition',
                            variant === 'dark' ? 'text-white hover:underline font-playfair' : 'text-gray-700 hover:text-blue-600'
                        ]">Member</Link>
                    </nav>
                    
                    <!-- User Actions -->
                    <div class="flex items-center space-x-3 flex-1 justify-end">
                        <div v-if="$page.props.auth.user" class="flex items-center gap-4">
                            <Link href="/profile" :class="[
                                'font-medium transition hover:underline',
                                variant === 'dark' ? 'text-white font-playfair' : 'text-gray-700'
                            ]">Hello, {{ $page.props.auth.user.name }}</Link>
                            <Link :href="route('logout')" method="post" as="button" :class="[
                                'px-4 py-2 rounded-md border font-medium transition',
                                variant === 'dark'
                                    ? 'border-white text-white font-bold font-playfair hover:bg-white hover:text-[#1E3A8A]'
                                    : 'border-gray-300 text-gray-700 hover:bg-gray-50'
                            ]">Logout</Link>
                        </div>
                        <div v-else>
                            <Link :href="route('register')" :class="[
                                'px-4 py-2 rounded-md transition',
                                variant === 'dark'
                                    ? 'bg-white text-[#1E3A8A] font-bold font-playfair shadow hover:bg-[#F5EDE2]'
                                    : 'bg-blue-400 text-white hover:bg-blue-500'
                            ]">
                                Sign up
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>
</template> 