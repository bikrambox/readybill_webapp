<template>
  <select class="form-select" v-model="selectedCode" style="max-width: 120px;">
    <option v-for="country in countries" :key="country.code" :value="country.dial_code">
      {{ country.flag }} {{ country.dial_code }}
    </option>
  </select>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '+91'
  }
})

const emit = defineEmits(['update:modelValue'])

const selectedCode = ref(props.modelValue)

const countries = [
  { code: 'IN', dial_code: '+91', flag: '🇮🇳' },
  { code: 'US', dial_code: '+1', flag: '🇺🇸' },
  { code: 'GB', dial_code: '+44', flag: '🇬🇧' },
  { code: 'AU', dial_code: '+61', flag: '🇦🇺' },
  // Add more countries as needed
]

watch(selectedCode, (newVal) => {
  emit('update:modelValue', newVal)
})

watch(() => props.modelValue, (newVal) => {
  selectedCode.value = newVal
})
</script>
