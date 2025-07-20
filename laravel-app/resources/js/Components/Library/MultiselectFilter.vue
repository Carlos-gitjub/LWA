<template>
  <div class="relative w-full">
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>

    <div class="border rounded p-2 bg-white flex flex-wrap gap-2 min-h-[42px]" @click="toggleDropdown">
      <span
        v-for="item in modelValue"
        :key="item"
        class="bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded flex items-center gap-1"
      >
        {{ item }}
        <button @click.stop="removeItem(item)" class="text-blue-500 hover:text-blue-700">&times;</button>
      </span>
      <input
        ref="input"
        type="text"
        class="flex-grow outline-none text-sm text-gray-700 placeholder-gray-400"
        :placeholder="modelValue.length === 0 ? placeholder : ''"
        @focus="dropdownOpen = true"
        v-model="search"
      />
    </div>

    <ul
      v-if="dropdownOpen && filteredOptions.length"
      class="absolute z-10 mt-1 w-full bg-white border rounded shadow max-h-60 overflow-y-auto"
    >
      <li
        v-for="option in filteredOptions"
        :key="option"
        @click="selectOption(option)"
        class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
      >
        {{ option }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  options: Array,
  modelValue: Array,
  label: String,
  placeholder: String
})
const emit = defineEmits(['update:modelValue'])

const search = ref('')
const dropdownOpen = ref(false)
const input = ref(null)

const filteredOptions = computed(() => {
  return props.options.filter(
    o => o.toLowerCase().includes(search.value.toLowerCase()) && !props.modelValue.includes(o)
  )
})

const selectOption = (option) => {
  emit('update:modelValue', [...props.modelValue, option])
  search.value = ''
  input.value?.focus()
}

const removeItem = (item) => {
  emit('update:modelValue', props.modelValue.filter(i => i !== item))
}

const closeDropdown = (e) => {
  if (!e.target.closest('.relative')) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', closeDropdown)
})
onBeforeUnmount(() => {
  window.removeEventListener('click', closeDropdown)
})
</script>
