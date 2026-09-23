<template>
  <div class="d-flex align-items-center gap-3 flex-wrap">
    <div class="d-flex align-items-center gap-2">
      <label class="small mb-0">Rows per page:</label>
      <select 
        :value="pageSize"
        class="form-select form-select-sm"
        style="width: auto;"
        @change="emit('page-size-change', Number($event.target.value))"
      >
        <option :value="10">10</option>
        <option :value="25">25</option>
        <option :value="50">50</option>
        <option :value="100">100</option>
      </select>
    </div>

    <nav aria-label="Table pagination">
      <ul class="pagination pagination-sm mb-0">
        <li class="page-item" :class="{ disabled: currentPage === 1 }">
          <button 
            class="page-link"
            @click="emit('page-change', currentPage - 1)"
            :disabled="currentPage === 1"
          >
            Previous
          </button>
        </li>
        
        <li 
          v-for="page in visiblePages" 
          :key="page"
          class="page-item"
          :class="{ active: page === currentPage }"
        >
          <button 
            class="page-link"
            @click="emit('page-change', page)"
          >
            {{ page }}
          </button>
        </li>
        
        <li class="page-item" :class="{ disabled: currentPage === totalPages }">
          <button 
            class="page-link"
            @click="emit('page-change', currentPage + 1)"
            :disabled="currentPage === totalPages"
          >
            Next
          </button>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  totalPages: {
    type: Number,
    required: true
  },
  pageSize: {
    type: Number,
    default: 10
  }
})

const emit = defineEmits(['page-change', 'page-size-change'])

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, props.currentPage - Math.floor(maxVisible / 2))
  let end = Math.min(props.totalPages, start + maxVisible - 1)

  if (end - start < maxVisible - 1) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})
</script>
