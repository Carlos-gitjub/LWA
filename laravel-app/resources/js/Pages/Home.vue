<template>
    <Head title="Home" />

    <div class="flex flex-col min-h-screen bg-gray-50">
        
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-semibold text-gray-900">LWA</h1>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            @click="router.visit('/login')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                        >
                            Log in
                        </button>
                        <button
                            @click="router.visit('/register')"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                        >
                            Create Account
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        
        <main class="flex-1 flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8 text-center">
                <div>
                    <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                        Welcome to LWA
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        A modern web application built with Laravel, Vue.js, and Tailwind CSS
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
</script> 
