<!-- src/components/UploadData/PageTitle.vue -->
<template>
  <div class="pagetitle mb-4">
    <h1 class="mb-2">{{ title }}</h1>
    <p v-if="info" class="text-muted mb-1">{{ info }}</p>
    <p v-if="note" class="text-danger mb-0">{{ note }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  info: String,
  note: String
})
</script>
