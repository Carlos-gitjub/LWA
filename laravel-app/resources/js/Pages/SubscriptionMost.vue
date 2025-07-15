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
            v-model="search"
            placeholder="e.g. Inception"
            class="flex-grow border rounded-l px-4 py-2 shadow-sm focus:outline-none focus:ring focus:border-blue-300"
          />
          <button
            @click="searchMovie"
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
            @click="analyzePlatforms"
            class="hidden md:inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
          >
            Search subscription platforms
          </button>
        </div>

        <!-- Resultado de búsqueda -->
        <div v-if="searchResult" class="mb-4 bg-white p-4 rounded border shadow-sm">
          🎬 <strong>{{ searchResult.title }}</strong> ({{ searchResult.year }})
          <button
            @click="addMovie"
            class="ml-4 px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition"
          >
            ➕ Add to list
          </button>
        </div>

        <!-- Lista de películas seleccionadas -->
        <div class="space-y-2 mb-10" v-if="movieList.length > 0">
          <div
            v-for="(movie, index) in movieList"
            :key="index"
            class="bg-white border-l-4 border-blue-500 px-4 py-3 shadow-sm rounded"
          >
            🎬 <strong>{{ movie.title }}</strong> ({{ movie.year }})
          </div>
        </div>

        <div v-if="Object.keys(platformResults).length" class="mt-8 space-y-6">
          <h2 class="text-xl font-semibold text-gray-800">
            📺 Subscription Platforms ({{ region }})
          </h2>

          <div v-for="(data, platform) in platformResults" :key="platform" class="bg-white rounded border shadow p-4">
            <h3 class="text-blue-700 font-bold mb-2">{{ platform }} ({{ data.count }} titles)</h3>
            <ul class="list-disc pl-5 text-gray-700">
              <li v-for="(movie, index) in data.movies" :key="index">
                {{ movie }}
              </li>
            </ul>
          </div>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>



<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
import RegionSelector from '@/Components/RegionSelector.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const search = ref('')
const searchResult = ref(null)
const movieList = ref([])
const error = ref(null)
const region = ref('ES')
const platformResults = ref({})

// Buscar película por nombre
const searchMovie = async () => {
  if (!search.value.trim()) return
  error.value = null
  searchResult.value = null

  try {
    const response = await axios.post('/api/movies/search-title', {
      title: search.value
    })

    if (response.data) {
      searchResult.value = response.data
    } else {
      error.value = 'Movie not found.'
    }
  } catch (e) {
    console.error(e)
    error.value = 'Error searching movie.'
  }
}

// Añadir a la lista (máximo 30)
const addMovie = () => {
  if (
    searchResult.value &&
    movieList.value.length < 30 &&
    !movieList.value.some(m => m.id === searchResult.value.id)
  ) {
    movieList.value.push(searchResult.value)
    searchResult.value = null
    search.value = ''
  }
}

const analyzePlatforms = async () => {
  try {
    const response = await axios.post('/movies-streaming/advanced/subscription-most/analyze', {
      movies: movieList.value,
      region: region.value
    })

    platformResults.value = response.data
  } catch (e) {
    console.error('Error analyzing platforms:', e)
  }
}

</script>
