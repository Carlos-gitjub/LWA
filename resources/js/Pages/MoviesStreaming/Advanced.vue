<template>
  <AuthenticatedLayout>
    <div class="pt-10">
      <div class="max-w-xl mx-auto px-4 text-center">
        
        <!-- Título + enlace de regreso -->
        <div class="flex items-center justify-between mb-10">
          <h1 class="text-3xl font-bold text-gray-900">Advanced Options</h1>
          <Link
            href="/movies-streaming"
            class="text-sm text-blue-600 hover:underline"
          >
            ← Back to basic search 
          </Link>
        </div>

        <!-- Opción avanzada -->
        <div class="flex justify-center">
          <Link
            href="/movies-streaming/advanced/subscription-most"
            class="group block max-w-md w-full p-6 bg-white border border-blue-200 rounded-xl shadow-sm hover:shadow-lg hover:border-blue-400 transition duration-300 ease-in-out transform hover:scale-[1.02]"
          >
            <div class="flex items-center justify-center gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-blue-500 group-hover:text-blue-600 transition" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <span class="text-base font-medium text-gray-700 group-hover:text-blue-600">
                Create a list of movies and see which subscription platform offers the most.
              </span>
            </div>
          </Link>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>


<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>
