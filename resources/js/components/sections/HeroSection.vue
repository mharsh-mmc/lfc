<template>
  <section class="flex flex-col md:flex-row items-stretch bg-[#F5EDE2]">
    <div class="md:w-1/2 w-full flex items-center justify-center bg-[#F5EDE2] p-0 h-[600px]">
      <video 
        src="/landing/hero-main.mp4" 
        autoplay 
        loop 
        muted 
        playsinline 
        class="shadow-xl w-full h-[600px] md:h-[600px] object-cover"
      ></video>
    </div>
    <div class="md:w-1/2 w-full flex flex-col justify-center items-end px-8 md:px-16 py-12">
      <h1 class="text-4xl md:text-5xl font-playfair font-bold text-right text-[#1E3A8A] mb-6 leading-tight">
        Preserve Memories.<br/>Connect Generations.<br/>
        <span class="italic">Live Forever.</span>
      </h1>
      <p class="text-lg text-[#1F2937] text-right mb-6">
        Preserve stories, memories, and legacies. Live Forever helps you create a digital heirloom for your family, so your story lives on.
      </p>
      <Link 
        href="/profile" 
        class="bg-[#1E3A8A] text-white px-8 py-3 rounded-sm font-bold font-playfair text-lg shadow hover:bg-[#283593] transition mb-4 w-max inline-block"
      >
        CREATE YOUR MEMORIES
      </Link>
      <div class="text-[#1F2937] italic text-right text-base mt-2">
        "I finally told my story the way I always wanted to"<br/>
        <span class="not-italic font-bold">— Claire Matthews, 84 — Retired Schoolteacher</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700,italic&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
</style>
