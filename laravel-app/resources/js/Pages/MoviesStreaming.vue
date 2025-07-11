<template>
  <div class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold">Where to watch?</h1>
      <Link href="/where-to-watch/advanced" class="text-blue-600 hover:underline">
        Advanced options
      </Link>
    </div>

    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 mb-6">
      <input
        type="text"
        placeholder="e.g. Inception"
        v-model="search"
        class="flex-1 border rounded px-4 py-2 shadow-sm focus:outline-none focus:ring focus:border-blue-300"
      />

      <RegionSelector v-model="region" />
    </div>

    <!-- Aquí se mostrarán los resultados (en fase posterior) -->
    <div>
      <!-- Placeholder -->
      <p class="text-gray-500 italic">Results will appear here...</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import RegionSelector from '@/Components/RegionSelector.vue'

const search = ref('')
const region = ref('ES') // Valor por defecto
</script>
