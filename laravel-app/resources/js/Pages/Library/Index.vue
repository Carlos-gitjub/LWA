<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <input v-model="search" @input="searchBooks"
              type="text" placeholder="Buscar por título o autor"
              class="border p-2 rounded w-1/2" />

        <button @click="showModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Añadir libro
        </button>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="book in books" :key="book.id" class="bg-white border rounded-lg shadow p-3 w-full max-w-[200px] mx-auto relative group hover:shadow-md transition">
          <!-- Imagen de portada -->
          <div class="w-full h-32 bg-gray-100 rounded flex items-center justify-center overflow-hidden mb-2">
            <img
              v-if="book.cover_url"
              :src="book.cover_url"
              alt="Portada del libro"
              class="object-cover h-full w-full"
            />
            <div v-else class="text-gray-400 text-xs">Sin portada</div>
          </div>

          <!-- Título y autor -->
          <div class="text-sm mb-1">
            <h3 class="font-semibold text-gray-800 truncate leading-tight">{{ book.title }}</h3>
            <p class="text-gray-500 text-xs truncate">{{ book.author || 'Autor desconocido' }}</p>
          </div>

          <!-- Menú ⋯ contextual -->
          <div class="absolute top-2 right-2">
            <button @click="toggleMenu(book.id)" class="text-gray-500 hover:text-gray-800 text-lg">⋯</button>
            <div
              v-if="openMenu === book.id"
              class="absolute right-0 mt-2 w-28 bg-white border shadow rounded z-10"
            >
              <button
                @click="editBook(book)"
                class="block px-3 py-2 hover:bg-gray-100 w-full text-left text-sm"
              >
                ✏️ Editar
              </button>
              <button
                @click="deleteBook(book.id)"
                class="block px-3 py-2 text-red-600 hover:bg-red-100 w-full text-left text-sm"
              >
                🗑 Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BookForm v-if="showModal" @close="showModal = false" />

  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BookForm from '@/Components/Library/BookForm.vue'
const showModal = ref(false)

const props = defineProps({
  books: Array
})

const books = ref(props.books)
const search = ref('')
const openMenu = ref(null)

const searchBooks = async () => {
  if (search.value.length === 0) {
    books.value = history.state.props.books
    return
  }

  const res = await axios.get('/library/search', {
    params: { q: search.value }
  })
  books.value = res.data
}

const goToAddBook = () => {
  router.visit('/library/create')
}

const editBook = (book) => {
  router.visit(`/library/edit/${book.id}`)
}

const deleteBook = async (id) => {
  if (confirm('¿Estás seguro de eliminar este libro?')) {
    await axios.delete(`/library/delete/${id}`)
    books.value = books.value.filter(b => b.id !== id)
  }
}

const toggleMenu = (id) => {
  openMenu.value = openMenu.value === id ? null : id
}
</script>
