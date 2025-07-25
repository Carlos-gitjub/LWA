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
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Guardar</button>
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

const submitForm = () => {
  const formData = new FormData()
  formData.append('title', form.value.title)
  formData.append('author', form.value.author)
  if (form.value.file) formData.append('file', form.value.file)
  if (form.value.selected_thumbnail) {
    formData.append('cover_base64', form.value.selected_thumbnail)
  }

  if (props.book?.id) {
    // Modo edición
    axios.post(`/library/update/${props.book.id}`, formData)
      .then((res) => {
        emit('updated', res.data.book)
        emit('close')
      })
      .catch((err) => console.error('Error al actualizar libro:', err))
  } else {
    // Modo creación
    axios.post('/library/store', formData)
      .then((res) => {
        emit('added', res.data.book)
        emit('close')
      })
      .catch((err) => console.error('Error al crear libro:', err))
  }
}
</script>


