<template>
  <div class="pagetitle mb-4">
    <h1 class="mb-2">{{ title }}</h1>
    <p v-if="description" class="text-muted mb-1">{{ description }}</p>
    <p v-if="note" class="text-danger mb-0">{{ note }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  note: {
    type: String,
    default: ''
  }
})
</script>
