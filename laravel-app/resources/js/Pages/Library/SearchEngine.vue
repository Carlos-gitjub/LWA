<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Multiselect from 'vue-multiselect'

const query = ref('')
const selectedAuthors = ref([])
const selectedBooks = ref([])
const results = ref([])
const totalMatches = ref(0)
const totalPages = ref(0)

const authors = ref([])
const books = ref([])

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
  try {
    const res = await axios.post('/library/search-engine', {
      q: query.value,
      authors: selectedAuthors.value.map(a => a.id),
      books: selectedBooks.value.map(b => b.id)
    })

    results.value = res.data.results
    totalMatches.value = res.data.total_matches
    totalPages.value = res.data.total_pages
  } catch (e) {
    console.error('Error al buscar:', e)
  }
}

onMounted(() => {
  fetchFilters()
})
</script>

<template>
  <div class="container mx-auto p-6">
    <!-- Filtros -->
    <div class="mb-6 flex flex-col md:flex-row md:items-end md:gap-4">
      <div class="flex-1">
        <label class="block mb-1 font-semibold text-gray-700">Buscar texto</label>
        <input
          v-model="query"
          type="text"
          placeholder="Buscar contenido dentro de libros"
          class="w-full border rounded p-2"
        />
      </div>

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

      <button
        @click="search"
        class="mt-4 md:mt-0 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        Buscar
      </button>
    </div>

    <!-- Resumen -->
    <div v-if="results.length" class="mb-4 text-gray-600 text-sm">
      <strong>Resultados encontrados:</strong> {{ totalMatches }} en {{ totalPages }} páginas.
    </div>

    <!-- Tarjetas -->
    <div v-if="results.length" class="space-y-4">
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
          Página {{ result.page }} — {{ result.matches }} coincidencia<span v-if="result.matches > 1">s</span> en esta página
        </p>
      </div>
    </div>

    <div v-else class="text-center text-gray-400 mt-8">
      No hay resultados.
    </div>
  </div>
</template>
