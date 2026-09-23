<template>
  <div class="alert alert-danger" role="alert">
    <h6 class="alert-heading mb-2">Validation Errors</h6>
    <ul class="mb-0 ps-3">
      <li v-for="(err, index) in errorList" :key="index">
        {{ err }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  error: {
    type: [String, Array, Object],
    default: null
  }
})

const errorList = computed(() => {
  if (!props.error) return []
  if (Array.isArray(props.error)) return props.error
  if (typeof props.error === 'string') return [props.error]
  if (typeof props.error === 'object') {
    return Object.values(props.error).flat()
  }
  return []
})
</script>
