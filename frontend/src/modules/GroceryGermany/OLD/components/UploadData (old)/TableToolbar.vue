<template>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <button 
        type="button" 
        class="btn btn-danger btn-sm"
        :disabled="selectedCount === 0"
        @click="emit('delete-selected')"
      >
        <i class="bi bi-trash"></i> 
        {{ $t('common.Delete Selected') }}
        <span v-if="selectedCount > 0" class="badge bg-white text-danger ms-1">
          {{ selectedCount }}
        </span>
      </button>
    </div>

    <div class="text-center">
      <h6 class="mb-0">
        Total Items: <span class="badge bg-primary">{{ totalItems }}</span>
      </h6>
    </div>

    <div>
      <button 
        type="button" 
        class="btn btn-success btn-sm"
        @click="emit('add-row')"
      >
        <i class="bi bi-plus-circle"></i> 
        {{ $t('common.Add Row') }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  selectedCount: {
    type: Number,
    default: 0
  },
  totalItems: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['add-row', 'delete-selected'])
</script>
