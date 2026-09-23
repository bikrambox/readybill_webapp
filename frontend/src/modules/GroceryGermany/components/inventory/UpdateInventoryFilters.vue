<template>
  <div class="row g-3 mb-3 mb-lg-4">
    <!-- Filter Column Dropdown -->
    <div class="col-12 col-lg-3">
      <select 
        class="form-select" 
        :value="filterColumn" 
        @change="handleFilterColumnChange"
      >
        <option value="item_name">{{ $t('common.Item Name') }}</option>
        <option value="quantity">{{ $t('common.Stock Quantity') }}</option>
        <option value="mrp">{{ $t('common.MRP') }}</option>
        <option value="sale_price">{{ $t('common.Rate') }}</option>
      </select>
    </div>

    <!-- Search Input -->
    <div class="col-12 col-lg-5">
      <div class="input-group">
        <span class="input-group-text bg-white border-end-0">
          <i class="bi bi-search"></i>
        </span>
        <input 
          type="text" 
          class="form-control border-start-0 ps-0" 
          :placeholder="searchPlaceholder"
          :value="searchQuery"
          @input="handleSearchChange"
        />
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="col-12 col-lg-4 d-none d-lg-flex justify-content-end gap-2" v-if="isAdmin">
      <span class="text-muted d-none d-lg-inline align-self-center me-2">
        {{ selectedCount }} {{ $t('common.selected') }}
      </span>
      <button 
        class="btn btn-outline-secondary" 
        @click="$emit('deselect')" 
        :disabled="selectedCount === 0"
      >
        {{ $t('common.Deselect') }}
      </button>
      <button 
        class="btn btn-danger" 
        @click="$emit('delete')" 
        :disabled="selectedCount === 0"
      >
        <i class="bi bi-trash"></i>
        <span class="d-none d-lg-inline ms-1">{{ $t('common.Delete') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  itemNames: {
    type: Array,
    required: true
  },
  searchQuery: {
    type: String,
    default: ''
  },
  filterColumn: {
    type: String,
    default: 'item_name'
  },
  selectedCount: {
    type: Number,
    default: 0
  },
  isAdmin: { type: Boolean, default: false }
});

const emit = defineEmits([
  'update:searchQuery',
  'update:filterColumn',
  'deselect', 
  'delete'
]);

const searchPlaceholder = computed(() => {
  const placeholders = {
    'item_name': 'Search by item name...',
    'quantity': 'Search by stock quantity...',
    'mrp': 'Search by MRP...',
    'sale_price': 'Search by rate...'
  };
  return placeholders[props.filterColumn] || 'Search...';
});

const handleFilterColumnChange = (event) => {
  emit('update:filterColumn', event.target.value);
};

const handleSearchChange = (event) => {
  emit('update:searchQuery', event.target.value);
};
</script>

<style scoped>
.form-select:focus,
.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group-text {
  border-right: 0;
}

.form-control.border-start-0 {
  border-left: 0;
}

.form-control.border-start-0:focus {
  border-left: 0;
  box-shadow: none;
}

.input-group:focus-within .input-group-text {
  border-color: #86b7fe;
}

.btn-outline-secondary:disabled,
.btn-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 991px) {
  .text-muted {
    font-size: 14px;
  }
}
</style>
