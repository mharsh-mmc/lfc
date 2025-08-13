<template>
  <section class="bg-[#F5EDE2] py-16 px-4 md:px-24">
    <h6 class="text-2xl md:text-3xl font-playfair font-bold text-[#1E3A8A] mb-8 text-center">
      44,000+ five-star reviews
    </h6>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
        <img 
          src="/landing/review-1.jpg" 
          alt="Review 1" 
          class="w-20 h-20 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" 
        />
        <div class="text-[#1E3A8A] font-bold mb-2">
          "Now our father passed, I realized how many stories we never captured. Live Forever gave our story a purpose as family's legacy, memories, and love will live on forever, even after he's gone."
        </div>
        <div class="text-[#1F2937] text-sm">— Avery Patel, NJ — Teacher</div>
      </div>
      <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
        <img 
          src="/landing/review-2.jpg" 
          alt="Review 2" 
          class="w-20 h-20 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" 
        />
        <div class="text-[#1E3A8A] font-bold mb-2">
          "We use it to create a timeline where people can answer one question, then link to build a digital legacy that lives on, even when we're gone."
        </div>
        <div class="text-[#1F2937] text-sm">— Amelia Thompson, NY — Granddaughter</div>
      </div>
      <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
        <img 
          src="/landing/review-3.jpg" 
          alt="Review 3" 
          class="w-20 h-20 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" 
        />
        <div class="text-[#1E3A8A] font-bold mb-2">
          "I think about my family's stories, and I know, someday, someone will treasure an ancestor's story that would have been lost."
        </div>
        <div class="text-[#1F2937] text-sm">— Mark Walters, CA — Grandson</div>
      </div>
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
