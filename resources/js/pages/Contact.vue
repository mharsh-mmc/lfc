<template>
    <Head title="Contact Us - LiveForever" />
    <div class="min-h-screen bg-gray-50">
        
        
        <!-- Header -->
        <Header variant="dark" />
        
        <!-- Main Content -->
        <main>
            <!-- Contact Form Section -->
            <section class="bg-white py-16 px-4 md:px-24">
                <div class="max-w-4xl mx-auto">
                    <!-- Section Header -->
                    <div class="mb-12">
                        <h2 class="text-sm font-semibold text-gray-600 mb-4">Contact Us</h2>
                        <h1 class="text-4xl md:text-5xl font-playfair font-bold text-gray-800 mb-4">Get in touch with us.</h1>
                        <p class="text-2xl font-playfair text-gray-800">We're here to assist you.</p>
                    </div>
                    
                    <!-- Contact Form -->
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- First Name -->
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input 
                                    type="text" 
                                    id="firstName" 
                                    placeholder="Enter First Name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                />
                            </div>
                            
                            <!-- Last Name -->
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input 
                                    type="text" 
                                    id="lastName" 
                                    placeholder="Enter Last Name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                />
                            </div>
                        </div>
                        
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input 
                                type="email" 
                                id="email" 
                                placeholder="Enter Your Email Address"
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            />
                        </div>
                        
                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea 
                                id="message" 
                                rows="6"
                                placeholder="Enter your message here..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                            ></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <div>
                            <button 
                                type="submit"
                                class="bg-[#1E3A8A] text-white px-8 py-3 rounded-md font-semibold hover:bg-[#283593] transition-colors"
                            >
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </section>
            
            <!-- Contact Information Section -->
            <section class="bg-gray-100 py-16 px-4 md:px-24 relative">
                <div class="max-w-6xl mx-auto relative">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                        <!-- Left Section -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-600 mb-4">Contact Info</h3>
                            <h2 class="text-3xl md:text-4xl font-playfair font-bold text-gray-800 mb-8">We are always happy to assist you</h2>
                        </div>
                        
                        <!-- Right Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Email Address -->
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Email Address</h4>
                                <p class="text-lg text-gray-600 mb-2">help@info.com</p>
                                <p class="text-sm text-gray-500">Monday - Friday 6 am to 8 pm EST</p>
                            </div>
                            
                            <!-- Phone Number -->
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Number</h4>
                                <p class="text-lg text-gray-600 mb-2">(808) 998-34256</p>
                                <p class="text-sm text-gray-500">Monday - Friday 6 am to 8 pm EST</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        
        <!-- Footer -->
        <Footer />
    </div>
</template>

<script setup>
import Header from '@/components/Header.vue';
import Footer from '@/components/Footer.vue';
import { Head } from '@inertiajs/vue3';
</script>
