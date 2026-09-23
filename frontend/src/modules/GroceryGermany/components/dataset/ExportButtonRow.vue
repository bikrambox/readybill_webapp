<script setup>
const emit = defineEmits(['export'])
</script>

<template>
  <div class="d-md-flex justify-content-between mt-3">
    <button
      class="btn btn-success fw-semibold w-100 w-md-auto py-2"
      @click="emit('export')"
    >
      {{ $t('common.Export Data') }}
    </button>
  </div>
</template>

<style scoped>
@media (min-width: 768px) {
  .w-md-auto { width: auto !important; }
}
</style>
