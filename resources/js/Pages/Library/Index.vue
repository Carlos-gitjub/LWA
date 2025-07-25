<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import debounce from 'lodash/debounce'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BookForm from '@/Components/Library/BookForm.vue'

const showModal = ref(false)
const books = ref([])
const search = ref('')
const openMenu = ref(null)
const selectedImage = ref(null)
const notification = ref({ message: '', type: '' })
const viewMode = ref(localStorage.getItem('viewMode') || 'cards')
const editingBook = ref(null)

// SEARCH
const searchBooks = async () => {
  try {
    const query = search.value.trim()

    if (query === '') {
      const res = await axios.get('/library', {
          headers: { Accept: 'application/json' }
      })
      books.value = res.data
    } else {
      const res = await axios.get('/library/search', {
        params: { q: query }
      })
      books.value = res.data
    }
  } catch (err) {
    console.error('Error al buscar libros:', err)
  }
}
watch(search, debounce(searchBooks, 300))

// ACTIONS
const editBook = (book) => {
  editingBook.value = book
  showModal.value = true
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

const addBook = (book) => {
  books.value.unshift(book)
}

const updateBook = (book) => {
  const idx = books.value.findIndex(b => b.id === book.id)
  if (idx !== -1) books.value[idx] = book
}

// MODAL & MENU
const toggleMenu = (id) => openMenu.value = openMenu.value === id ? null : id
const openImageModal = (imgUrl) => selectedImage.value = imgUrl
const handleClickOutside = (event) => {
  const clickedInsideMenu = event.target.closest('[data-menu]')
  if (!clickedInsideMenu) openMenu.value = null
}
onMounted(() => {
  window.addEventListener('click', handleClickOutside)
  searchBooks()
})
onBeforeUnmount(() => window.removeEventListener('click', handleClickOutside))

// VIEW & NOTIFY
const toggleView = (mode) => {
  viewMode.value = mode
  localStorage.setItem('viewMode', mode)
}
const showNotification = (message, type = 'success') => {
  notification.value = { message, type }
  setTimeout(() => (notification.value.message = ''), 3000)
}
</script>

<template>
  <AuthenticatedLayout>
    <main class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por título o autor"
          class="border p-2 rounded w-1/2"
        />

        <div class="flex gap-2 mb-4">
          <button @click="toggleView('cards')" :class="viewMode === 'cards' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="p-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h4v4H4V6zm6 0h4v4h-4V6zm6 0h4v4h-4V6zM4 14h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z" />
            </svg>
          </button>
          <button @click="toggleView('table')" :class="viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'" class="p-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

        <div class="flex gap-3">
          <button
            @click="showModal = true"
            class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-3 rounded-lg shadow transition duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Añadir libro
          </button>

          <button
            @click="router.visit('/library/search-engine')"
            class="flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-5 py-3 rounded-lg shadow-lg transition duration-200"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 103.75 3.75a7.5 7.5 0 0012.9 12.9z" />
            </svg>
            Search engine
          </button>
        </div>
      </div>

      <!-- CARD VIEW -->
      <div v-if="viewMode === 'cards'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="book in books" :key="book.id" class="bg-white border rounded-lg shadow p-3 w-full max-w-[200px] mx-auto relative group hover:shadow-md transition">
          <div
            class="relative w-full aspect-[3/4] bg-gray-100 rounded overflow-hidden mb-2 cursor-pointer"
            @click="openImageModal(book.cover_url || `/storage/${book.cover_path}`)"
          >
            <img v-if="book.cover_url || book.cover_path" :src="book.cover_url || `/storage/${book.cover_path}`" alt="Portada del libro" class="w-full h-full object-cover" />
            <div v-else class="flex items-center justify-center h-full text-gray-400 text-xs">Sin portada</div>
          </div>
          <div class="text-sm mb-1">
            <h3 class="font-semibold text-gray-800 truncate leading-tight" :title="book.title">{{ book.title }}</h3>
            <p class="text-gray-500 text-xs truncate">{{ book.author || 'Autor desconocido' }}</p>
          </div>
          <div class="absolute top-2 right-2" data-menu>
            <button @click.stop="toggleMenu(book.id)" class="text-white hover:bg-gray-800 w-8 h-8 rounded-full border flex items-center justify-center bg-gray-500 shadow">⋯</button>
            <div v-if="openMenu === book.id" class="absolute right-0 mt-2 w-32 bg-white border shadow rounded z-10">
              <button @click="editBook(book)" class="block px-3 py-2 hover:bg-gray-100 w-full text-left text-sm">✏️ Editar</button>
              <button @click="deleteBook(book.id)" class="block px-3 py-2 text-red-600 hover:bg-red-100 w-full text-left text-sm">🗑 Eliminar</button>
              <a v-if="book.file_path" :href="`/storage/${book.file_path}`" download class="block px-3 py-2 hover:bg-gray-100 w-full text-left text-sm">📥 Descargar</a>
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
            <tr v-for="book in books" :key="book.id" class="border-t hover:bg-gray-50">
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
    </main>

    <BookForm
      v-if="showModal"
      :book="editingBook"
      @close="() => { showModal = false; editingBook = null }"
      @added="addBook"
      @updated="updateBook"
    />

  </AuthenticatedLayout>

  <div v-if="notification.message" class="fixed bottom-4 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded shadow text-white z-50" :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
    {{ notification.message }}
  </div>

  <div v-if="selectedImage" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" @click.self="selectedImage = null">
    <div class="relative">
      <button @click="selectedImage = null" class="absolute top-2 right-2 bg-white rounded-full shadow p-1 hover:bg-gray-100 transition" title="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <img :src="selectedImage" class="object-contain max-h-[90vh] max-w-[90vw] min-h-[400px] min-w-[300px] bg-white rounded shadow-lg p-2" />
    </div>
  </div>
</template>