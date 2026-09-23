<template>
  <div class="row g-3 mb-3 align-items-end">
    <!-- Page Size Selector -->
    <div class="col-12 col-md-4">
      <div class="d-flex align-items-center gap-2">
        <label class="form-label mb-0 text-nowrap">Show</label>
        <select 
          class="form-select form-select-sm" 
          style="width: auto;"
          v-model="localPageSize"
          @change="handlePageSizeChange"
        >
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <label class="form-label mb-0">Entries</label>
      </div>
    </div>

    <!-- Spacer -->
    <div class="col-md-4 d-none d-md-block"></div>

    <!-- Search Input -->
    <div class="col-12 col-md-4">
      <input 
        type="text" 
        class="form-control form-control-sm" 
        placeholder="Search..."
        v-model="localSearchQuery"
        @input="handleSearchChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  pageSize: {
    type: Number,
    default: 100
  },
  searchQuery: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:pageSize', 'update:searchQuery']);

const localPageSize = ref(props.pageSize);
const localSearchQuery = ref(props.searchQuery);

const handlePageSizeChange = () => {
  emit('update:pageSize', Number(localPageSize.value));
};

const handleSearchChange = () => {
  emit('update:searchQuery', localSearchQuery.value);
};

watch(() => props.pageSize, (newVal) => {
  localPageSize.value = newVal;
});

watch(() => props.searchQuery, (newVal) => {
  localSearchQuery.value = newVal;
});
</script>
