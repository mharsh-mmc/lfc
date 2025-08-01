<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import SignupPopup from '@/components/signup-popup.vue';
import Footer from '@/components/Footer.vue'

const showSignupPopup = ref(false);

onMounted(() => {
  if (!localStorage.getItem('signupPopupShown')) {
    const onScroll = () => {
      showSignupPopup.value = true;
      localStorage.setItem('signupPopupShown', '1');
      window.removeEventListener('scroll', onScroll);
    };
    window.addEventListener('scroll', onScroll, { once: true });
  }
});

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
  <Head title="Welcome" />
  <div class="font-lato bg-[#F5EDE2]">
    <!-- Top Bar -->
    <div class="flex items-center justify-center gap-8 bg-[#ffffff] text-black px-8 py-4">
        <span class="uppercase tracking-widest text-xs font-semibold hidden md:inline">Some stories deserve to be remembered forever</span>
      </div>
    <nav class="bg-[#1E3A8A] text-white px-20 py-4 flex items-center justify-between"> 
      <div class="flex items-center gap-8">
        <span class="text-2xl font-bold font-playfair tracking-tight">liveforever</span>
      </div>
              <div class="  hidden md:flex gap-12 text-white font-semibold">
          <Link href="/" class="hover:underline font-playfair">Home</Link>
          <Link href="/leave-a-mark" class="hover:underline font-playfair">Leave a mark</Link>
          <Link href="#" class="hover:underline font-playfair">Member</Link>
          </div>
      <div class="  hidden md:flex gap-12 text-white font-semibold">
        <Link :href="route('login')" class="ml-4 px-6 py-2 rounded-full bg-white text-[#1E3A8A] font-bold font-playfair shadow hover:bg-[#F5EDE2] transition">Sign up</Link>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="flex flex-col md:flex-row items-stretch bg-[#F5EDE2]">
      <div class="md:w-1/2 w-full flex items-center justify-center bg-[#F5EDE2] p-0 h-[600px] ">
        <video src="/landing/hero-main.mp4" autoplay loop muted playsinline class=" shadow-xl w-full h-[600px] md:h-[600px] object-cover"></video>
      </div>
      <div class="md:w-1/2 w-full flex flex-col justify-center items-end px-8 md:px-16 py-12">
        <h1 class="text-4xl md:text-5xl font-playfair font-bold text-right text-[#1E3A8A] mb-6 leading-tight">Preserve Memories.<br/>Connect Generations.<br/><span class="italic">Live Forever.</span></h1>
        <p class="text-lg text-[#1F2937] text-right mb-6">Preserve stories, memories, and legacies. Live Forever helps you create a digital heirloom for your family, so your story lives on.</p>
        <button class="bg-[#1E3A8A] text-white px-8 py-3 rounded-sm font-bold font-playfair text-lg shadow hover:bg-[#283593] transition mb-4 w-max">CREATE YOUR MEMORIES</button>
        <div class="text-[#1F2937] italic text-right text-base mt-2">“I finally told my story the way I always wanted to”<br/><span class="not-italic font-bold">— Claire Matthews, 84 — Retired Schoolteacher</span></div>
      </div>
    </section>

    <!-- Find Your Loved Ones -->
    <section class="bg-[#F9FAFB] py-16 px-4 md:px-24 text-center">
      <h2 class="text-3xl md:text-4xl font-playfair font-bold text-[#1E3A8A] mb-2">Find Your Loved Ones</h2>
      <p class="text-lg text-[#1F2937] mb-8">Start discovering your family story</p>
      <div class="flex flex-col md:flex-row items-center justify-center gap-12">
        <img src="/landing/find-loved-ones.jpg" alt="Find Loved Ones" class="rounded-xl shadow-lg w-full md:w-1/2 max-w-lg object-cover" />
        <div class="flex-1 flex flex-col items-center md:items-start">
          <p class="text-base text-[#1F2937] mb-4">Search billions of ancestor profiles, photographs, and historical documents.</p>
          <form class="flex flex-col md:flex-row gap-4 w-full max-w-md mx-auto md:mx-0">
            <input type="text" placeholder="Enter name" class="flex-1 px-4 py-2 rounded-md border border-[#D6D3D1] focus:ring-2 focus:ring-[#1E3A8A]" />
            <input type="text" placeholder="Enter place" class="flex-1 px-4 py-2 rounded-md border border-[#D6D3D1] focus:ring-2 focus:ring-[#1E3A8A]" />
            <button class="bg-[#1E3A8A] text-white px-6 py-2 rounded-full font-bold font-playfair shadow hover:bg-[#283593] transition">Search</button>
          </form>
        </div>
      </div>
    </section>

    <!-- Digital Legacy Platform -->
    <section class="py-12 px-4 md:px-24 text-center bg-[#F5EDE2]">
      <h3 class="text-2xl md:text-3xl font-playfair font-bold text-[#1E3A8A] mb-4">A Digital Legacy Platform To Celebrate Life, Love, and Memory</h3>
      <p class="text-base text-[#1F2937] max-w-3xl mx-auto">We all carry stories within us—of laughter, wisdom, heartache, and hope. At Live Forever, we help you preserve those cherished moments through digital profiles, photographs, written stories, and videos. Your future generations, all just a few clicks away. Like a snapshot of a soul, Live Forever brings tomorrow’s history into the hands of those who cherish you most.</p>
    </section>

    <!-- In Loving Memory Grid -->
    <section class="bg-[#1E3A8A] py-16 px-4 md:px-24">
      <h4 class="text-2xl md:text-3xl font-playfair font-bold text-white mb-10 text-center">In Loving Memory</h4>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
        <div v-for="i in 8" :key="i" class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center">
          <img :src="`/landing/memory-${i}.jpg`" :alt="`Memory ${i}`" class="w-28 h-28 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" />
          <div class="font-playfair text-lg font-bold text-[#1E3A8A] mb-1">{{ ['Liam Carter','Sophia Ross','Zara El-Sayed','Mateo Fernandez','Kenji Tanaka','Amelia Thompson','Mark Walters','Anika Patel'][i-1] }}</div>
          <div class="text-[#1F2937] text-sm mb-1">19XX–20XX</div>
          <div class="italic text-[#1F2937] text-xs">“A lifetime of love and laughter”</div>
        </div>
      </div>
    </section>

    <!-- Capture & Share It Forever -->
    <section class="bg-[#1E3A8A] text-white py-16 px-4 md:px-24 flex flex-col md:flex-row items-center gap-12">
      <div class="flex-1 flex flex-col items-center md:items-start">
        <h5 class="text-2xl md:text-3xl font-playfair font-bold mb-4">Capture a Lifetime. Share It Forever.</h5>
        <p class="text-base mb-6">Preserve stories, memories, and legacies—Live Forever lets you create a digital heirloom for your family, so your story lives on.</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 w-full">
          <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">Grow Your Family Tree</div>
          <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">Your Memory Vault</div>
          <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">Letters for Tomorrow</div>
          <div class="bg-[#F5EDE2] text-[#1E3A8A] rounded-lg p-4 font-bold text-center text-sm">Timeline of a Life Well Lived</div>
        </div>
      </div>
      <div class="flex-1 flex justify-center">
        <img src="/landing/capture-polaroid.jpg" alt="Polaroid" class="rounded-2xl shadow-xl w-72 h-80 object-cover" />
      </div>
    </section>

    <!-- Step by Step Section -->
    <section class="bg-[#F5EDE2] py-16 px-4 md:px-24">
      <h6 class="text-2xl md:text-3xl font-playfair font-bold text-[#1E3A8A] mb-8 text-center">Create Something That Lasts, Step by Step</h6>
      <div class="flex flex-col md:flex-row items-center justify-center gap-12">
        <div class="flex flex-col items-center text-center flex-1">
          <div class="bg-[#1E3A8A] text-white rounded-full w-14 h-14 flex items-center justify-center text-2xl font-bold mb-4">1</div>
          <div class="font-playfair text-lg font-bold text-[#1E3A8A] mb-2">Sign Up & Create Your Profile</div>
          <p class="text-[#1F2937] text-base">Start by creating your profile. Then add your story, photos, and memories to begin your digital legacy.</p>
        </div>
        <div class="flex flex-col items-center text-center flex-1">
          <div class="bg-[#1E3A8A] text-white rounded-full w-14 h-14 flex items-center justify-center text-2xl font-bold mb-4">2</div>
          <div class="font-playfair text-lg font-bold text-[#1E3A8A] mb-2">Add Memories & Connections</div>
          <p class="text-[#1F2937] text-base">Create new memories, upload photos, and connect with loved ones to build your family’s story together.</p>
        </div>
        <div class="flex flex-col items-center text-center flex-1">
          <div class="bg-[#1E3A8A] text-white rounded-full w-14 h-14 flex items-center justify-center text-2xl font-bold mb-4">3</div>
          <div class="font-playfair text-lg font-bold text-[#1E3A8A] mb-2">Invite Loved Ones</div>
          <p class="text-[#1F2937] text-base">Send an invite to family and friends so they can add their own stories, photos, and messages.</p>
        </div>
      </div>
    </section>

    <!-- Reviews Section -->
    <section class="bg-[#F5EDE2] py-16 px-4 md:px-24">
      <h6 class="text-2xl md:text-3xl font-playfair font-bold text-[#1E3A8A] mb-8 text-center">44,000+ five-star reviews</h6>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
          <img src="/landing/review-1.jpg" alt="Review 1" class="w-20 h-20 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" />
          <div class="text-[#1E3A8A] font-bold mb-2">“Now our father passed, I realized how many stories we never captured. Live Forever gave our story a purpose as family’s legacy, memories, and love will live on forever, even after he’s gone.”</div>
          <div class="text-[#1F2937] text-sm">— Avery Patel, NJ — Teacher</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
          <img src="/landing/review-2.jpg" alt="Review 2" class="w-20 h-20 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" />
          <div class="text-[#1E3A8A] font-bold mb-2">“We use it to create a timeline where people can answer one question, then link to build a digital legacy that lives on, even when we’re gone.”</div>
          <div class="text-[#1F2937] text-sm">— Amelia Thompson, NY — Granddaughter</div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center">
          <img src="/landing/review-3.jpg" alt="Review 3" class="w-20 h-20 object-cover rounded-full mb-4 border-4 border-[#F5EDE2]" />
          <div class="text-[#1E3A8A] font-bold mb-2">“I think about my family’s stories, and I know, someday, someone will treasure an ancestor’s story that would have been lost.”</div>
          <div class="text-[#1F2937] text-sm">— Mark Walters, CA — Grandson</div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <Footer />
    <SignupPopup v-model="showSignupPopup" />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@700,italic&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
.font-lato {
  font-family: 'Lato', sans-serif;
}
</style>
