<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
import RegionSelector from '@/Components/RegionSelector.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'


const search = ref('')
const region = ref('ES') // Valor por defecto
const results = ref([])
const loading = ref(false)
const error = ref(null)

const label = (type) => {
  switch (type) {
    case 'sub': return 'Subscription'
    case 'rent': return 'Rental'
    case 'buy': return 'Purchase'
    default: return type
  }
}


const searchMovie = async () => {
  if (!search.value.trim()) return

  loading.value = true
  error.value = null
  results.value = []

  try {
    const response = await axios.post('/movies-streaming/search', {
      title: search.value,
      region: region.value
    })
    results.value = response.data
  } catch (err) {
    error.value = 'Error fetching movie data.'
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>


<template>
  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Where to watch?</h1>
        <Link
          href="/movies-streaming/advanced"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-gray-100 border border-gray-300 rounded hover:bg-gray-200 transition"
        >
          <!-- Replace this icon block with either option -->
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <rect x="3" y="5" width="18" height="2.5" rx="1" />
            <rect x="3" y="10.75" width="18" height="2.5" rx="1" />
            <rect x="3" y="16.5" width="18" height="2.5" rx="1" />
          </svg>
          Advanced options
        </Link>
      </div>

      <form @submit.prevent="searchMovie" class="w-full mb-6">
        <div class="flex w-full max-w-full">
          <input
            type="text"
            placeholder="e.g. Inception"
            v-model="search"
            class="flex-grow border rounded-l px-4 py-2 shadow-sm focus:outline-none focus:ring focus:border-blue-300"
          />
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-r hover:bg-blue-700 transition"
          >
            🔍 Search
          </button>
        </div>

        <div class="flex items-center gap-2 mt-4">
          <span class="text-sm font-medium text-gray-700">🌍 Region:</span>
          <RegionSelector v-model="region" />
        </div>
      </form>
   
      <div>
        <div v-if="loading" class="text-gray-600 italic">Searching...</div>
        <div v-else-if="error" class="text-red-600">{{ error }}</div>
        <div v-else-if="results.length === 0" class="text-gray-500 italic">No results found yet.</div>

        <ul v-else class="space-y-4 mt-4">
          <li
            v-for="(platform, index) in results"
            :key="index"
            class="border p-4 rounded shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4"
          >
            <div class="flex-1 font-semibold">{{ platform.platform }}</div>

            <div class="flex-1 text-sm text-gray-700">
              {{ label(platform.type) }} • {{ platform.format }}
            </div>

            <div class="flex-1 text-sm">
              <span class="font-medium">Price:</span> {{ platform.price }}
            </div>

            <div class="flex-1 text-right">
              <a :href="platform.url" target="_blank" class="text-blue-600 hover:underline text-sm">
                Go to platform →
              </a>
            </div>
          </li>
        </ul>

      </div>

    </div>
  </AuthenticatedLayout>
</template>