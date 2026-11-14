<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { Link } from '@inertiajs/vue3'
import RegionSelector from '@/Components/MoviesStreaming/RegionSelector.vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const search = ref('')
const searchResult = ref(null)
const movieList = ref([])
const error = ref(null)
const region = ref('ES')
const platformResults = ref([])
const isSearching = ref(false)
const analysisState = ref('idle')
const openPlatforms = ref({})
const analysisError = ref(null)
let selectionVersion = 0

const isAnalyzed = computed(() => ['success', 'empty'].includes(analysisState.value))
const isAnalyzing = computed(() => analysisState.value === 'analyzing')

watch([movieList, region], () => {
  selectionVersion++
  platformResults.value = []
  analysisState.value = 'idle'
  analysisError.value = null
}, { deep: true, flush: 'sync' })

const searchMovie = async () => {
  if (!search.value.trim()) return
  error.value = null
  searchResult.value = null
  isSearching.value = true

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
    error.value = e.response?.data?.message ?? 'Error searching movie.'
  } finally {
    isSearching.value = false
  }
}

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

const removeMovie = (index) => {
  movieList.value.splice(index, 1)
}

const analyzePlatforms = async () => {
  const version = selectionVersion
  analysisState.value = 'analyzing'
  analysisError.value = null
  platformResults.value = []
  try {
    const response = await axios.post('/movies-streaming/advanced/subscription-most/analyze', {
      movies: movieList.value,
      region: region.value
    })

    if (version === selectionVersion) {
      platformResults.value = response.data
      analysisState.value = response.data.length ? 'success' : 'empty'
    }
  } catch (e) {
    if (version === selectionVersion) {
      analysisError.value = e.response?.data?.message ?? 'Could not complete the analysis. Please try again.'
      analysisState.value = 'error'
    }
  } finally {
    if (version === selectionVersion && analysisState.value === 'analyzing') {
      analysisState.value = 'idle'
    }
  }
}

const togglePlatform = (platform) => {
  openPlatforms.value[platform] = !openPlatforms.value[platform]
}

</script>

<template>
  <AuthenticatedLayout>
    <div class="w-full px-4 py-10">
      <div
        :class="[
          'transition-all duration-500 ease-in-out mx-auto flex flex-col gap-8',
          isAnalyzed ? 'max-w-5xl md:flex-row justify-center items-start' : 'max-w-xl'
        ]"
      >
        <!-- LEFT: Movie creation UI -->
        <div :class="['transition-all duration-500 w-full', isAnalyzed ? 'md:w-1/2' : '']">
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
            <div class="flex w-full">
              <input
                type="text"
                v-model="search"
                @keyup.enter="searchMovie"
                placeholder="e.g. Inception"
                class="flex-grow border rounded-l px-4 py-2 shadow-sm focus:outline-none focus:ring focus:border-blue-300"
              />
              <button
                @click="searchMovie"
                :disabled="isSearching"
                class="px-4 py-2 bg-blue-600 text-white rounded-r hover:bg-blue-700 transition flex items-center gap-2"
              >
                <span v-if="!isSearching">🔍 Search</span>
                <span v-else class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
              </button>

            </div>
            


            <!-- Region + Analyze -->
            <div class="flex justify-between items-center">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">🌍 Region:</span>
                <RegionSelector v-model="region" />
              </div>

              <template v-if="movieList.length > 0">
                <button
                  @click="analyzePlatforms"
                  :disabled="isAnalyzing"
                  class="hidden md:inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition"
                >
                  <span v-if="!isAnalyzing">Search subscription platforms</span>
                  <span v-else class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                </button>
              </template>

            </div>

            <p v-if="error" role="alert" class="text-red-600">{{ error }}</p>
            <p v-if="analysisState === 'error'" role="alert" class="text-red-600">{{ analysisError }}</p>
            <p v-if="analysisState === 'empty'" class="text-gray-600">
              No subscription platforms found for this selection.
            </p>
            <p v-if="movieList.length >= 30" class="text-gray-600">Maximum 30 movies.</p>

            <!-- Search result -->
            <div
              v-if="searchResult"
              class="mb-4 bg-white p-4 rounded border shadow-sm flex justify-between items-center text-sm"
            >
              <div>
                🎬 <strong>{{ searchResult.label }}</strong>
              </div>
              <button
                @click="addMovie"
                class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition"
              >
                ➕ Add to list
              </button>
            </div>

            <!-- Selected movie list -->
            <div class="space-y-1 mb-8" v-if="movieList.length > 0">
              <div
                v-for="(movie, index) in movieList"
                :key="index"
                class="bg-white border-l-4 border-blue-500 px-4 py-1 shadow-sm rounded text-sm flex justify-between items-center"
              >
                <span>🎬 <strong>{{ movie.label }}</strong></span>
                <button
                  @click="removeMovie(index)"
                  class="text-red-500 hover:text-red-700 text-xs px-2"
                  title="Remove"
                  alt="Remove"
                >
                  ❌ Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: Results -->
        <div
          v-if="platformResults.length"
          class="transition-all duration-500 w-full md:w-1/2"
        >
          <h2 class="text-xl font-semibold text-gray-800 mb-4">
            📺 Subscription Platforms ({{ region }})
          </h2>

          <div
            v-for="data in platformResults"
            :key="data.id"
            class="bg-white rounded border shadow mb-2"
          >
            <!-- Header as dropdown toggle -->
            <button
              @click="togglePlatform(data.id)"
              class="w-full flex justify-between items-center px-4 py-3 text-left text-blue-700 font-bold hover:bg-gray-50 transition"
            >
              <span>{{ data.name }} ({{ data.count }} titles)</span>
              <span>
                <svg
                  :class="{ 'rotate-180': openPlatforms[data.id] }"
                  class="h-4 w-4 transform transition-transform duration-300"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
                </svg>
              </span>
            </button>

            <!-- Dropdown content -->
            <transition name="fade">
              <ul
                v-if="openPlatforms[data.id]"
                class="list-disc pl-8 pr-4 pb-3 text-gray-700"
              >
                <li v-for="(movie, index) in data.movies" :key="index">
                  {{ movie }}
                </li>
              </ul>
            </transition>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
