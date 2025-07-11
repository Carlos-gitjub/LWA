<template>
  <div class="relative">
    <label class="text-sm font-medium block mb-1">🌍 Region:</label>

    <button
      class="flex items-center gap-2 border rounded px-3 py-2 w-full sm:w-auto hover:bg-gray-100 transition"
      @click="open = !open"
      type="button"
    >
      <img :src="flagUrl(modelValue)" :alt="modelValue" class="w-6 h-auto" />
      <span>{{ emojiFor(modelValue) }}</span>
    </button>

    <div
      v-if="open"
      class="absolute z-50 mt-2 bg-white border rounded shadow-md w-48"
    >
      <ul>
        <li
          v-for="r in regions"
          :key="r.code"
          @click="selectRegion(r.code)"
          class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 cursor-pointer"
        >
          <img :src="flagUrl(r.code)" :alt="r.code" class="w-5 h-auto" />
          {{ r.emoji }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const regions = [
  { code: 'ES', emoji: '🇪🇸' },
  { code: 'US', emoji: '🇺🇸' },
  { code: 'GB', emoji: '🇬🇧' }
]

const selectRegion = code => {
  emit('update:modelValue', code)
  open.value = false
}

const flagUrl = code =>
  `https://flagcdn.com/h20/${code.toLowerCase()}.png`

const emojiFor = code =>
  regions.find(r => r.code === code)?.emoji || ''
</script>
