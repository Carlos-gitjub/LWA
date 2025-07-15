<template>
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto px-4 py-10">
      
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
          🧱 Create Your Movie List
        </h1>
        <Link
          href="/movies-streaming/advanced"
          class="text-sm text-blue-600 hover:underline"
        >
          ← Back to advanced options menu
        </Link>
      </div>

      <div class="flex flex-col gap-4 mb-6">
        <!-- Search bar -->
        <div class="flex w-full max-w-full">
          <input
            type="text"
            placeholder="e.g. Inception"
            class="flex-grow border rounded-l px-4 py-2 shadow-sm focus:outline-none focus:ring focus:border-blue-300"
          />
          <button
            class="px-4 py-2 bg-blue-600 text-white rounded-r hover:bg-blue-700 transition"
          >
            🔍 Search
          </button>
        </div>

        <!-- Region + Search Platforms -->
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700">🌍 Region:</span>
            <RegionSelector v-model="region" />
          </div>

          <button
            class="hidden md:inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
          >
            Search subscription platforms
          </button>
        </div>

      <div class="space-y-2 mb-10">
        <div
          class="bg-white border-l-4 border-blue-500 px-4 py-3 shadow-sm rounded"
        >
          🎬 <strong>Inception</strong> (2010)
        </div>
        <div
          class="bg-white border-l-4 border-blue-500 px-4 py-3 shadow-sm rounded"
        >
          🎬 <strong>Braveheart</strong> (1995)
        </div>
        
      </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import RegionSelector from '@/Components/RegionSelector.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const region = ref('ES')

</script>

