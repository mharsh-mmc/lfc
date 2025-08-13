<template>
  <section class="bg-[#1E3A8A] text-white py-16 px-4 md:px-24 flex flex-col md:flex-row items-center gap-12">
    <div class="flex-1 flex flex-col items-center md:items-start">
      <h5 class="text-2xl md:text-3xl font-playfair font-bold mb-4">
        Capture a Lifetime. Share It Forever.
      </h5>
      <p class="text-base mb-6">
        Preserve stories, memories, and legacies—Live Forever lets you create a digital heirloom for your family, so your story lives on.
      </p>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
        <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">
          Grow Your Family Tree
        </div>
        <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">
          Your Memory Vault
        </div>
        <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">
          Letters for Tomorrow
        </div>
        <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">
          Timeline of a Life Well Lived
        </div>
      </div>
    </div>
    <div class="flex-1 flex justify-center">
      <img 
        src="/landing/capture-polaroid.jpg" 
        alt="Polaroid" 
        class="rounded-2xl shadow-xl w-72 h-80 object-cover" 
      />
    </div>
  </section>
</template>

<script setup>
// No props or emits needed for this simple section
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700,italic&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
</style>
