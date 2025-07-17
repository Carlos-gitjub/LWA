<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <div class="flex justify-between items-center mb-6">
        <input v-model="search" @input="searchBooks"
              type="text" placeholder="Buscar por título o autor"
              class="border p-2 rounded w-1/2" />

        <button @click="goToAddBook"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Añadir libro
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="book in books" :key="book.id" class="border rounded p-4 shadow">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-lg font-bold">{{ book.title }}</h3>
              <p class="text-sm text-gray-600">{{ book.author }}</p>
            </div>

            <div class="relative">
              <button @click="toggleMenu(book.id)" class="text-gray-500 hover:text-gray-800">
                ⋯
              </button>

              <div v-if="openMenu === book.id" class="absolute right-0 mt-2 w-32 bg-white border shadow rounded z-10">
                <button @click="editBook(book)" class="block px-4 py-2 hover:bg-gray-100 w-full text-left">Editar</button>
                <button @click="deleteBook(book.id)" class="block px-4 py-2 text-red-600 hover:bg-red-100 w-full text-left">Eliminar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const books = ref([])
const search = ref('')
const openMenu = ref(null)

onMounted(() => {
  books.value = history.state.props.books || []
})

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
