<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Footer from '@/components/Footer.vue';
</script>

<template>
    <Head title="Leave A Mark" />
    <div class="font-lato">
        <!-- Top Banner -->
        <div class="bg-white text-black text-center py-2">
            <span class="text-sm font-semibold">SOME STORIES DESERVE TO BE REMEMBERED FOREVER.</span>
        </div>

        <!-- Navigation -->
        <nav class="bg-[#1E3A8A] text-white px-8 md:px-20 py-4 flex items-center justify-between">
            <div class="flex items-center">
                <span class="text-2xl font-bold font-playfair tracking-tight">liveforever</span>
            </div>
            <div class="hidden md:flex gap-8 text-white font-semibold">
                <Link href="/" class="hover:underline font-playfair">Home</Link>
                <Link href="/leave-a-mark" class="hover:underline font-playfair">Leave a mark</Link>
                <Link href="/members" class="hover:underline font-playfair">Member</Link>
            </div>
            <div class="hidden md:flex">
                <Link :href="route('register')" class="px-6 py-2 rounded-full bg-white text-[#1E3A8A] font-bold font-playfair shadow hover:bg-[#F5EDE2] transition">Sign up</Link>
            </div>
        </nav>

        <!-- Redesigned Hero/Intro Section -->
        <section class="w-full bg-white px-8 md:px-20 py-12">
          <!-- Row 1: Two-Column Text Block -->
          <div class="grid md:grid-cols-2 gap-12 items-start">
            <!-- Left Column: Eyebrow + Heading -->
            <div>
              <div class="text-sm font-semibold italic text-[#1E3A8A] mb-2 font-lato">
                Leave Your Digital Mark On The World!
              </div>
              <h1 class="font-playfair font-bold text-4xl md:text-5xl text-[#1E3A8A] leading-tight mb-4">
                Leave your digital<br />
                mark on the world!
              </h1>
            </div>
            <!-- Right Column: Paragraph -->
            <div>
              <p class="text-base md:text-lg text-[#333] leading-relaxed font-lato">
                Dear Friend, Are You Ready To Make A Difference In The Vast Ocean Of The Internet? Are You Ready To Leave Your Mark, A Bright Trail That Will Continue To Shine Through The Passage Of Time? Well, It Is Time To Consider The Opportunity To Leave Your Mark On A Web Platform.<br /><br />
                The online world is like a big open book, constantly evolving and full of stories to share. <span class="font-bold text-[#1E3A8A]">One single story, told with heart, can inspire and connect generations.</span> Your voice matters, and your mark can last forever.
              </p>
            </div>
          </div>
          <!-- Row 2: Full-Width Image -->
          <div class="mt-10 w-full">
            <img
              src="/leaveamark/family.jpg"
              alt="Elderly man with child on shoulders, smiling"
              class="w-full rounded-2xl shadow-2xl object-cover"
              style="max-height: 480px;"
            />
          </div>
        </section>

        <!-- Second Section -->
        <section class="bg-white px-4 md:px-24 py-12">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="flex flex-col gap-4">
                    <div class="text-xs mb-2 font-semibold text-[#1F2937]">Leave Your Digital Mark On The World!</div>
                    <h2 class="text-4xl font-playfair font-bold text-[#1F2937] mb-4">Leave your digital mark on the world!</h2>
                    <div class="text-[#1F2937] text-base md:text-sm mb-2">
                        Dear Friend, Are You Ready To Make A Difference In The Vast Ocean Of The Internet? Are You Ready To Leave Your Mark, A Bright Trail That Will Continue To Shine Through The Passage Of Time? Well, It Is Time To Consider The Opportunity To Leave Your Mark On A Web Platform.
                    </div>
                    <div class="text-[#1F2937] text-base md:text-sm mb-2">
                        The Online World Is Like A Big Open Book, Constantly Evolving And Full Of Stories To Share. And You Have A Story To Tell, A Vision To Express, A Talent To Show The World. Whether It's Through A Blog, A Social Media Page, An Art Portfolio Or Any Other Form Of Online Expression, Leaving A Trace Of Yourself On A Web Platform Can Bring Incredible Benefits.
                    </div>
                    <div class="text-[#1F2937] text-base md:text-sm">
                        Imagine Being Able To Inspire Someone On The Other Side Of The World With Your Words, Make Someone Smile With Your Art, Help Someone With Your Knowledge. The Power Of The Internet Is Incredible, And Every Single Voice Matters. No Matter How Small Or Big You Think Your Contribution Is, There Is Always Someone Out There Who Could Be Touched By Your Online Presence.
                    </div>
                </div>
                <div class="min-h-[340px] md:min-h-[420px] w-full rounded-2xl shadow-2xl bg-cover bg-center" style="background-image: url('/leaveamark/family-2.jpg');"></div>
            </div>
        </section>

        <!-- Digital Legacy Platform Section -->
        <section class="bg-[#F5EDE2] py-12 px-4 md:px-24 text-center">
            <div class="text-3xl font-playfair font-bold text-[#1F2937] mb-4 italic">
                A Digital Legacy Platform to Celebrate Life, Love, and Memory
            </div>
            <div class="text-base text-[#1F2937] max-w-4xl mx-auto mb-4 leading-relaxed">
                We All Carry Stories Worth Telling — Of Laughter, Wisdom, Resilience, And Love. But Memories Fade, And Voices Quiet With Time. LiveForever Helps You Preserve Those Cherished Moments Through Heartfelt Digital Reflections. Whether It's A Voice Message To Your Future Grandchildren, A Letter To Someone You Love, Or A Snapshot Of A Special Day, Your Legacy Deserves A Home That Will Outlast Time.
            </div>
            <div class="text-xl font-playfair font-bold text-[#1F2937]">
                Create. Share. Be Remembered.
            </div>
        </section>

        <!-- Reviews Section -->
        <section class="bg-white py-16 px-4 md:px-24">
            <h3 class="text-3xl font-playfair font-bold text-[#1F2937] mb-12 text-center">
                44,000+ five-star reviews
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Review Card 1 -->
                <div class="bg-[#F9FAFB] rounded-xl shadow-lg p-8 flex flex-col items-center text-center">
                    <div class="flex text-yellow-400 mb-4 text-xl">
                        <span v-for="i in 5" :key="'r1'+i">★</span>
                    </div>
                    <div class="text-[#1F2937] font-bold mb-4 leading-relaxed">
                        When my father passed, I realized how many stories we never captured. Live Forever gave me a way to preserve my family's legacy, memories, and voice for my children. It's become our digital heirloom.
                    </div>
                    <div class="text-[#1F2937] text-sm font-semibold">
                        — Aanya Patel, 34 — Teacher
                    </div>
                </div>
                <!-- Review Card 2 -->
                <div class="bg-[#FEF3C7] rounded-xl shadow-lg p-8 flex flex-col items-center text-center">
                    <div class="flex text-yellow-400 mb-4 text-xl">
                        <span v-for="i in 5" :key="'r2'+i">★</span>
                    </div>
                    <div class="text-[#1F2937] font-bold mb-4 leading-relaxed">
                        I always wanted to share my roots. I had photos and had an app, but Live Forever gave me a platform to truly share my voice and connect with my family's past.
                    </div>
                    <div class="text-[#1F2937] text-sm font-semibold">
                        — Amelia Thompson, NY — Granddaughter
                    </div>
                </div>
                <!-- Review Card 3 -->
                <div class="bg-[#F9FAFB] rounded-xl shadow-lg p-8 flex flex-col items-center text-center">
                    <div class="flex text-yellow-400 mb-4 text-xl">
                        <span v-for="i in 5" :key="'r3'+i">★</span>
                    </div>
                    <div class="text-[#1F2937] font-bold mb-4 leading-relaxed">
                        I think about my family's stories, and I know, someday, someone will treasure an ancestor's story that would have been lost.
                    </div>
                    <div class="text-[#1F2937] text-sm font-semibold">
                        — Mark Walters, CA — Grandson
                    </div>
                </div>
            </div>
            <!-- Pagination Dots -->
            <div class="flex justify-center mt-8 space-x-2">
                <div class="w-2 h-2 bg-[#1E3A8A] rounded-full"></div>
                <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
            </div>
        </section>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Playfair+Display:wght@400;700;900&display=swap');
.font-playfair {
  font-family: 'Playfair Display', serif;
}
.font-lato {
  font-family: 'Lato', sans-serif;
}
</style>
