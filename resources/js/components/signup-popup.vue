<script setup>
import { Link } from '@inertiajs/vue3';
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  modelValue: Boolean
});
const emit = defineEmits(['update:modelValue']);

function close() {
  emit('update:modelValue', false);
}
</script>

<template>
  <transition name="fade">
    <div v-if="props.modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
      <div class="bg-white rounded-2xl shadow-2xl flex flex-col md:flex-row w-[90vw] max-w-3xl overflow-hidden relative">
        <!-- Left: Image -->
        <div class="md:w-1/2 w-full h-64 md:h-auto flex-shrink-0">
          <img src="/landing/memory-1.jpg" alt="Popup" class="w-full h-full object-cover" />
        </div>
        <!-- Right: Content -->
        <div class="md:w-1/2 w-full p-8 flex flex-col justify-between relative">
          <!-- Close Button -->
          <button @click="close" class="absolute top-4 right-4 text-2xl text-[#1E3A8A] hover:text-[#1F2937] focus:outline-none">&times;</button>
          <div>
            <h2 class="text-3xl font-playfair font-bold text-[#1E3A8A] mb-4 leading-tight">Enter the World of<br />Eternal Beauty</h2>
            <p class="text-[#1F2937] mb-4">Where timeless design meets modern sophistication. Begin your journey with us.</p>
            <div class="mb-4">
              <div class="font-playfair text-lg mb-2">Join Our Inner Circle:</div>
              <ul class="list-none space-y-1 text-[#1F2937]">
                <li>✓ Early access to curated pieces</li>
                <li>✓ VIP-only launches and previews</li>
                <li>✓ Personalized shopping experiences</li>
                <li>✓ Invitations to exclusive online events and styling sessions</li>
              </ul>
            </div>
            <div class="mb-6 text-sm">
              <span class="text-[#1E3A8A]">Already a member?</span>
              <Link :href="route('login')" class="text-[#1E3A8A] underline ml-1">Log in now to unlock your timeless experience.</Link>
            </div>
          </div>
          <div class="flex flex-col gap-3 mt-2">
            <Link :href="route('register')" class="w-full border border-[#1E3A8A] text-[#1E3A8A] font-playfair font-semibold py-2 rounded-md text-lg text-center hover:bg-[#F5EDE2] transition">Sign Up</Link>
            <Link :href="route('login')" class="w-full bg-[#1E3A8A] text-white font-playfair font-semibold py-2 rounded-md text-lg text-center hover:bg-[#283593] transition">Sign In</Link>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700,italic&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
.font-lato {
  font-family: 'Lato', sans-serif;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.fade-enter-to, .fade-leave-from {
  opacity: 1;
}
</style> 