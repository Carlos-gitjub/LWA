<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

const query = ref('')
const selectedAuthors = ref([])
const selectedBooks = ref([])
const results = ref([])
const totalMatches = ref(0)
const totalPages = ref(0)

const authors = ref([])
const books = ref([])

const loading = ref(false)

const highlight = (text, term) => {
  if (!term) return text
  const regex = new RegExp(`(${term})`, 'gi')
  return text.replace(regex, '<span class="bg-yellow-200 font-medium">$1</span>')
}

const fetchFilters = async () => {
  try {
    const res = await axios.get('/library')
    const uniqueAuthors = [...new Set(res.data.map(book => book.author).filter(Boolean))]
    authors.value = uniqueAuthors.map((author) => ({ id: author, name: author }))
    books.value = res.data.map(book => ({ id: book.id, title: book.title }))
  } catch (e) {
    console.error('Error al cargar filtros:', e)
  }
}

const search = async () => {
  loading.value = true
  try {
    const res = await axios.post('/library/search-engine', {
      q: query.value,
      authors: selectedAuthors.value.map(a => a.id),
      books: selectedBooks.value.map(b => b.id),
    })

    results.value = res.data.results
    totalMatches.value = res.data.total_matches
    totalPages.value = res.data.total_pages
  } catch (e) {
    console.error('Error al buscar:', e)
  } finally {
    loading.value = false
  }
}


onMounted(() => {
  fetchFilters()
})
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <!-- Filtros -->
      <div class="mb-6 flex flex-col md:flex-row md:items-end md:gap-4">

        <div class="flex flex-col justify-end">
          <label class="block mb-1 font-semibold text-gray-700 invisible">Volver</label>
          <Link
            href="/library"
            class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition"
          >
            ← Volver
          </Link>
        </div>

        <!-- Campo texto -->
        <div class="flex-1">
          <label class="block mb-1 font-semibold text-gray-700">Buscar texto</label>
          <input
            v-model="query"
            type="text"
            placeholder="Buscar contenido dentro de libros"
            class="w-full border rounded p-2"
            @keyup.enter="search"
          />
        </div>

        <!-- Autores -->
        <div class="flex-1">
          <label class="block mb-1 font-semibold text-gray-700">Filtrar por autores</label>
          <Multiselect
            v-model="selectedAuthors"
            :options="authors"
            :multiple="true"
            :searchable="true"
            placeholder="Seleccionar autores"
            label="name"
            track-by="id"
          />
        </div>

        <!-- Libros -->
        <div class="flex-1">
          <label class="block mb-1 font-semibold text-gray-700">Filtrar por libros</label>
          <Multiselect
            v-model="selectedBooks"
            :options="books"
            :multiple="true"
            :searchable="true"
            placeholder="Seleccionar libros"
            label="title"
            track-by="id"
          />
        </div>

        <!-- Botón Buscar -->
<!-- Botón Buscar -->
<div class="flex flex-col justify-end">
  <label class="block mb-1 font-semibold text-gray-700 invisible">Buscar</label>
  <button
    @click="search"
    :disabled="loading"
    :class="[
      'px-4 py-2 rounded transition',
      loading
        ? 'bg-blue-300 text-white cursor-not-allowed'
        : 'bg-blue-600 text-white hover:bg-blue-700'
    ]"
  >
    Buscar
  </button>
</div>

      </div>

      <!-- Resumen -->
      <div v-if="results.length" class="mb-4 text-gray-600 text-sm">
        <strong>Resultados encontrados:</strong> {{ totalMatches }} en {{ totalPages }} páginas.
      </div>

      <!-- LOADER -->
      <div v-if="loading" class="flex justify-center mt-6">
        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
          </path>
        </svg>
      </div>

      <!-- RESULTADOS -->
      <div v-else-if="results.length" class="space-y-4">
        <div
          v-for="result in results"
          :key="result.page_id"
          class="bg-white shadow rounded-lg p-4 border-l-4 border-yellow-400 cursor-pointer hover:bg-yellow-50 transition"
        >
          <h3 class="text-lg font-semibold text-gray-800">
            {{ result.book }} — {{ result.author }}
          </h3>
          <p class="text-gray-600 mt-2" v-html="highlight(result.preview, query)" />
          <p class="text-sm text-gray-500 mt-1">
            Página {{ result.page }} — {{ result.count }} coincidenc{{ result.count === 1 ? 'ia' : 'ias' }} en esta página
          </p>
        </div>
      </div>

      <!-- NADA ENCONTRADO -->
      <div v-else class="text-center text-gray-400 mt-8">
        No hay resultados.
      </div>
    </div>
  </AuthenticatedLayout>
</template>
