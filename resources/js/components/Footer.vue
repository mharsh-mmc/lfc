<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <!-- Footer -->
    <footer class="relative bg-blue-900 text-white">
        <!-- Background image and overlay for entire footer -->
        <div class="absolute inset-0">
            <img 
                src="/images/footer/footer-bg.jpg" 
                alt="Family memories" 
                class="w-full h-full object-cover opacity-30"
            />
            <div class="absolute inset-0 bg-blue-900 bg-opacity-80"></div>
        </div>
        
        <div class="relative  p-[60px]">
            <div class="flex flex-col sm:flex-row justify-between">
                <!-- Left Section - w-1/2 with tagline and logo -->
                <div class="w-full sm:w-1/2 p-6 flex flex-col justify-between h-full">
                    <div class="mb-0">
                        <p class="text-2xl font-satisfy italic text-white leading-relaxed">
                            In Memory Of Those We Loved, In Honor Of Those We<br>
                            Remember – Preserving Their Stories, Voices, And Legacy<br>
                            For Generations To Come.
                        </p>
                    </div>
                    <div class="mt-0">
                        <h1 class="text-6xl font-playfair font-bold text-white">liveforever</h1>
                    </div>
                </div>

                <!-- Right Section - w-1/2 containing middle and right sections -->
                <div class="w-full sm:w-1/2 flex flex-col sm:flex-row gap-0">
                    <!-- Middle Section - Contact Info and Links -->
                    <div class="w-full md:w-1/2 space-y-8">
                        <!-- Visit Us -->
                        <div>
                            <h4 class="font-bold text-white mb-4 uppercase tracking-wide">Visit Us</h4>
                            <div class="space-y-2 text-sm">
                                <p>ATAKULE, ANKARA</p>
                                <p>MON-SUN: 9 AM - 1 AM</p>
                                <p>+90 123 456 7890</p>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div>
                            <h4 class="font-bold text-white mb-4 uppercase tracking-wide">Quick Links</h4>
                            <div class="space-y-2 text-sm">
                                <Link href="/" class="block hover:text-blue-300 transition-colors uppercase">Home</Link>
                                <Link href="/login" class="block hover:text-blue-300 transition-colors uppercase">Login</Link>
                                <Link href="/register" class="block hover:text-blue-300 transition-colors uppercase">Signup</Link>
                                <Link href="/leave-a-mark" class="block hover:text-blue-300 transition-colors uppercase">Leave A Mark</Link>
                                <Link href="/contact" class="block hover:text-blue-300 transition-colors uppercase">Contact</Link>
                            </div>
                        </div>

                        <!-- Connect With Us -->
                        <div>
                            <h4 class="font-bold text-white mb-4 uppercase tracking-wide">Connect With Us</h4>
                            <div class="space-y-2 text-sm">
                                <a href="#" class="block hover:text-blue-300 transition-colors uppercase">Instagram</a>
                                <a href="#" class="block hover:text-blue-300 transition-colors uppercase">LinkedIn</a>
                                <a href="#" class="block hover:text-blue-300 transition-colors uppercase">YouTube</a>
                            </div>
                        </div>

                        <!-- Copyright -->
                        <div class="pt-8 border-t border-blue-800">
                            <p class="text-sm text-white">© 2025 LIVEFOREVER. ALL RIGHTS RESERVED.</p>
                        </div>
                    </div>

                    <!-- Right Section - Black and White Hands Image -->
                    <div class="w-full md:w-1/2 relative overflow-hidden rounded-lg">
                        <img 
                            src="/images/footer/hands-generational-bw.jpg" 
                            alt="Intergenerational hands" 
                            class="w-full h-full object-cover rounded-lg"
                        />
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template> 