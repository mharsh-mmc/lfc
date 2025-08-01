<template>
  <footer class="relative w-full">
    <!-- Background Image with Overlay -->
    <div class="relative w-full h-auto">
      <img 
        src="/images/footer/footer-bg.jpg" 
        alt="Footer Background" 
        class="w-full h-auto object-cover"
      />
      <div class="absolute inset-0 bg-blue-900/80"></div>
      
      <!-- Content Overlay -->
      <div class="absolute inset-0 flex flex-col">
        <!-- Left/Center Section (2/3 width) -->
        <div class="w-2/3 px-8 py-12 text-white flex flex-row ">
          <!-- Mission Statement -->
          <div class="mb-8">
            <p class="text-xl italic font-serif leading-relaxed max-w-2xl">
              In Memory Of Those We Loved, In Honor Of Those We Remember – Preserving Their Stories, Voices, And Legacy For Generations To Come.
            </p>
          </div>
          
          <!-- Contact and Navigation Links -->
          <div class="grid grid-rows-3 gap-8 mb-8 ">
            <!-- Visit Us -->
            <div>
              <h3 class="text-sm font-bold uppercase tracking-wider mb-4">Visit Us</h3>
              <div class="space-y-2 text-sm">
                <p>ATAKULE, ANKARA</p>
                <p>MON-SUN: 9 AM - 1 AM</p>
                <p>+90 123 456 7890</p>
              </div>
            </div>
            
            <!-- Quick Links -->
            <div>
              <h3 class="text-sm font-bold uppercase tracking-wider mb-4">Quick Links</h3>
              <div class="space-y-2 text-sm">
                <a href="/" class="block hover:underline">HOME</a>
                <a href="/login" class="block hover:underline">LOGIN</a>
                <a href="/register" class="block hover:underline">SIGNUP</a>
                <a href="/leave-a-mark" class="block hover:underline">LEAVE A MARK</a>
                <a href="/contact" class="block hover:underline">CONTACT</a>
              </div>
            </div>
            
            <!-- Connect With Us -->
            <div>
              <h3 class="text-sm font-bold uppercase tracking-wider mb-4">Connect With Us</h3>
              <div class="space-y-2 text-sm">
                <a href="#" class="block hover:underline">INSTAGRAM</a>
                <a href="#" class="block hover:underline">LINKEDIN</a>
                <a href="#" class="block hover:underline">YOUTUBE</a>
              </div>
            </div>
          </div>
          
          
        
        <!-- Right Section (1/3 width) -->
        <div class="w-1/3">
          <img 
            src="/images/footer/hands-generational-bw.jpg" 
            alt="Intertwined hands - generational connection" 
            class="w-full h-full object-cover"
          />
        </div>
      </div>
      <!-- Bottom Section -->
      <div class="space-y-2">
            <h2 class="text-2xl font-serif">liveforever</h2>
            <p class="text-xs text-gray-300">© 2025 LIVEFOREVER. ALL RIGHTS RESERVED.</p>
          </div>
        </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
// Footer component
</script>

<style scoped>
/* Additional custom styles if needed */
</style> 