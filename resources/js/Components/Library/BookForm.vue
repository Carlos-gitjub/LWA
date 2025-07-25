<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-xl p-6 relative">
      <button @click="$emit('close')" class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl">&times;</button>

      <h2 class="text-2xl font-bold mb-4">
        {{ props.book ? 'Editar libro' : 'Añadir nuevo libro' }}
      </h2>


      <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block font-semibold mb-1">Título *</label>
          <input v-model="form.title" type="text" class="border p-2 rounded w-full" />
          <p v-if="errors.title" class="text-red-500 text-sm">{{ errors.title }}</p>
        </div>

        <div>
          <label class="block font-semibold mb-1">Autor</label>
          <input v-model="form.author" type="text" class="border p-2 rounded w-full" />
          <p v-if="errors.author" class="text-red-500 text-sm">{{ errors.author }}</p>
        </div>

        <div>
          <label class="block font-semibold mb-1">Archivo PDF (opcional)</label>
          <input type="file" @change="handleFile" class="w-full" accept="application/pdf" />
          <p v-if="errors.file" class="text-red-500 text-sm">{{ errors.file }}</p>
        </div>

        <div class="flex justify-end gap-4">
          <button @click.prevent="$emit('close')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Cancelar</button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center justify-center gap-2 min-w-[100px]"
          >
            <svg
              v-if="isSubmitting"
              class="animate-spin h-4 w-4 text-white"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
              ></path>
            </svg>
            <span>{{ isSubmitting ? 'Guardando...' : 'Guardar' }}</span>
          </button>

        </div>

        <div v-if="form.file">
          <label class="block font-semibold mb-1">Selecciona una portada desde el PDF:</label>
          <PdfPreview :file="form.file" @select="(thumb) => form.selected_thumbnail = thumb" />
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'
import PdfPreview from '@/Components/Library/PdfPreview.vue'

const emit = defineEmits(['close', 'added', 'updated'])
const props = defineProps({ book: Object })
const isSubmitting = ref(false)

const form = ref({
  title: '',
  author: '',
  file: null,
  selected_thumbnail: null
})

watch(() => props.book, (book) => {
  if (book) {
    form.value.title = book.title
    form.value.author = book.author
  }
}, { immediate: true })

const errors = usePage().props.errors || {}

const handleFile = (e) => {
  form.value.file = e.target.files[0]
}

const submitForm = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true

  const formData = new FormData()
  formData.append('title', form.value.title)
  formData.append('author', form.value.author)
  if (form.value.file) formData.append('file', form.value.file)
  if (form.value.selected_thumbnail) {
    formData.append('cover_base64', form.value.selected_thumbnail)
  }

  const endpoint = props.book?.id
    ? `/library/update/${props.book.id}`
    : '/library/store'

  try {
    const res = await axios.post(endpoint, formData)
    props.book?.id ? emit('updated', res.data.book) : emit('added', res.data.book)
    emit('close')
  } catch (err) {
    console.error('Error al guardar el libro:', err)
  } finally {
    isSubmitting.value = false
  }
}

</script>


