<!-- resources/js/Components/Library/PdfPreview.vue -->
<template>
  <div class="flex gap-2 flex-wrap">
    <div
      v-for="(thumb, index) in thumbnails"
      :key="index"
      class="w-24 h-32 border rounded overflow-hidden cursor-pointer relative"
      :class="{ 'ring-4 ring-blue-500': selectedIndex === index }"
      @click="$emit('select', thumb)"
    >
      <img :src="thumb" class="w-full h-full object-cover" />
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import * as pdfjsLib from 'pdfjs-dist'
import pdfjsWorker from 'pdfjs-dist/legacy/build/pdf.worker?worker'

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker

const props = defineProps({
  file: File
})
const emit = defineEmits(['select'])

const thumbnails = ref([])
const selectedIndex = ref(null)

watch(() => props.file, async (newFile) => {
  thumbnails.value = []
  selectedIndex.value = null
  if (!newFile) return;

  const reader = new FileReader()
  reader.onload = async (e) => {
    const typedarray = new Uint8Array(e.target.result)

    try {
      const pdf = await pdfjsLib.getDocument({ data: typedarray }).promise
      const numPages = Math.min(pdf.numPages, 5)

      for (let i = 1; i <= numPages; i++) {
        const page = await pdf.getPage(i)
        const viewport = page.getViewport({ scale: 1 })
        const canvas = document.createElement('canvas')
        const context = canvas.getContext('2d')
        canvas.width = viewport.width
        canvas.height = viewport.height

        await page.render({ canvasContext: context, viewport }).promise
        const thumb = canvas.toDataURL('image/jpeg')
        thumbnails.value.push(thumb)
      }
    } catch (err) {
      console.error('❌ Error al procesar el PDF:', err)
    }
  }

  reader.readAsArrayBuffer(newFile)
}, { immediate: true })


</script>
