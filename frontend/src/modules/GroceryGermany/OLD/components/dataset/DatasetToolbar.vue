<script setup>
import { defineProps, defineEmits } from 'vue'
const props = defineProps({
  searchQuery: String,
  selectedCount: Number,
  hasSelection: Boolean
})
const emit = defineEmits([
  'update:search-query',
  'deselect',
  'delete-selected',
  'reset-dataset',
  'add-row'
])
</script>

<template>
  <div>
    <!-- Main toolbar row -->
    <div class="bg-white rounded shadow-sm px-3 py-3 mb-2">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
          <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0">
              <i class="bi bi-search text-muted"></i>
            </span>
            <input
              type="text"
              class="form-control border-start-0 ps-0"
              placeholder="Search..."
              :value="searchQuery"
              @input="emit('update:search-query', $event.target.value)"
            >
          </div>
        </div>
        <div class="col-12 col-md-9 mt-2 mt-md-0">
          <div class="d-flex flex-wrap justify-content-md-end gap-2 align-items-center">
            <button class="btn btn-sm btn-primary" @click="emit('add-row')">
              <i class="bi bi-plus-lg me-1"></i>Add Row
            </button>
            <button class="btn btn-sm btn-primary" @click="emit('reset-dataset')">
              <i class="bi bi-arrow-clockwise me-1"></i>Reset Dataset
            </button>
            <button class="btn btn-sm btn-primary" @click="emit('delete-selected')" :disabled="!hasSelection">
              <i class="bi bi-trash me-1"></i>Delete Selected
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Mobile: selected count and deselect between delete/reset -->
    <div v-if="hasSelection" class="d-block d-md-none mt-n2 mb-2">
      <div class="bg-white rounded shadow-sm px-3 py-2 d-flex align-items-center justify-content-between gap-2">
        <span class="small text-muted">
          {{ selectedCount }} selected
        </span>
        <button class="btn btn-sm btn-outline-secondary" @click="emit('deselect')">
          Deselect
        </button>
      </div>
    </div>
  </div>
</template>
