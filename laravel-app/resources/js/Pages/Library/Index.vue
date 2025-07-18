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

const editBook = (book) => {
  router.visit(`/library/edit/${book.id}`)
}

const deleteBook = async (id) => {
  if (!confirm('¿Estás seguro de eliminar este libro?')) return

  try {
    await axios.delete(`/library/delete/${id}`)
    books.value = books.value.filter(b => b.id !== id)
    showNotification('📘 Libro eliminado correctamente', 'success')
  } catch (err) {
    console.error(err)
    showNotification('❌ Error al eliminar el libro', 'error')
  }
}

const toggleMenu = (id) => {
  openMenu.value = openMenu.value === id ? null : id
}

const addBook = (book) => {
  books.value.unshift(book)
  showModal.value = false
}

const notification = ref({ message: '', type: '' })

const showNotification = (message, type = 'success') => {
  notification.value = { message, type }
  setTimeout(() => (notification.value.message = ''), 3000)
}

const viewMode = ref(localStorage.getItem('viewMode') || 'cards')

const toggleView = (mode) => {
  viewMode.value = mode
  localStorage.setItem('viewMode', mode)
}
</script>


<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <input v-model="search" @input="searchBooks"
              type="text" placeholder="Buscar por título o autor"
              class="border p-2 rounded w-1/2" />

        <div class="flex gap-2 mb-4">
          <button @click="toggleView('cards')" :class="viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="p-2 rounded">
            <!-- Grid icon (cards) -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h4v4H4V6zm6 0h4v4h-4V6zm6 0h4v4h-4V6zM4 14h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z" />
            </svg>
          </button>

          <button @click="toggleView('table')" :class="viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="p-2 rounded">
            <!-- List icon (table) -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <button @click="showModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Añadir libro
        </button>
      </div>

      <!-- CARD VIEW -->
      <div v-if="viewMode === 'cards'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div
          v-for="book in books"
          :key="book.id"
          class="bg-white border rounded-lg shadow p-3 w-full max-w-[200px] mx-auto relative group hover:shadow-md transition"
        >
          <div class="w-full aspect-[3/4] bg-gray-100 rounded flex items-center justify-center overflow-hidden mb-2">
            <img
              v-if="book.cover_url || book.cover_path"
              :src="book.cover_url || `/storage/${book.cover_path}`"
              alt="Portada del libro"
              class="object-contain h-full w-full"
            />
            <div v-else class="text-gray-400 text-xs">Sin portada</div>
          </div>

          <div class="text-sm mb-1">
            <h3
              class="font-semibold text-gray-800 truncate leading-tight"
              :title="book.title"
            >
              {{ book.title }}
            </h3>
            <p class="text-gray-500 text-xs truncate">{{ book.author || 'Autor desconocido' }}</p>
          </div>

          <div class="absolute top-2 right-2">
            <button @click="toggleMenu(book.id)" class="text-gray-500 hover:text-gray-800 text-lg">⋯</button>
            <div v-if="openMenu === book.id" class="absolute right-0 mt-2 w-28 bg-white border shadow rounded z-10">
              <button @click="editBook(book)" class="block px-3 py-2 hover:bg-gray-100 w-full text-left text-sm">✏️ Editar</button>
              <button @click="deleteBook(book.id)" class="block px-3 py-2 text-red-600 hover:bg-red-100 w-full text-left text-sm">🗑 Eliminar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- TABLE VIEW -->
      <div v-else>
        <table class="table-auto w-full border text-sm bg-white shadow rounded">
          <thead class="bg-gray-100 text-left">
            <tr>
              <th class="p-3">Título</th>
              <th class="p-3">Autor</th>
              <th class="p-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="book in books"
              :key="book.id"
              class="border-t hover:bg-gray-50"
            >
              <td class="p-3" :title="book.title">
                <span class="truncate block max-w-xs">{{ book.title }}</span>
              </td>
              <td class="p-3" :title="book.author || 'Autor desconocido'">
                <span class="truncate block max-w-xs">{{ book.author || 'Autor desconocido' }}</span>
              </td>
              <td class="p-3 flex gap-2">
                <button @click="editBook(book)" class="text-blue-600 hover:underline">Editar</button>
                <button @click="deleteBook(book.id)" class="text-red-600 hover:underline">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <BookForm v-if="showModal" @close="showModal = false" @added="addBook" />
  </AuthenticatedLayout>
  <div
  v-if="notification.message"
  class="fixed bottom-4 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow text-white z-50"
  :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
>
  {{ notification.message }}
</div>
</template>
