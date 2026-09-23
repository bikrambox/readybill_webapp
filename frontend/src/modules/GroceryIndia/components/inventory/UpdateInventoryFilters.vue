<template>
  <div class="row g-3 mb-3 mb-lg-4">
    <!-- Filter Column Dropdown -->
    <div class="col-12 col-lg-3">
      <select
        class="form-select"
        :value="filterColumn"
        @change="handleFilterColumnChange"
      >
        <option value="item_name">{{ $t("common.Item Name") }}</option>
        <option value="quantity">{{ $t("common.Stock Quantity") }}</option>
        <option value="mrp">{{ $t("common.MRP") }}</option>
        <option value="sale_price">{{ $t("common.Rate") }}</option>
        <option value="sku">{{ $t("common.SKU") }}</option>
        <option value="category">{{ $t("common.Category") }}</option>
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

    <!-- Count Info + Action Buttons -->
    <div
      class="col-12 col-lg-4 d-flex align-items-center justify-content-between justify-content-lg-end gap-2 flex-wrap"
    >
      <!-- Item Count Badges (always visible) -->
      <div class="d-flex align-items-center gap-2 item-count-info">
        <span class="badge-count">
          <i class="bi bi-box-seam me-1"></i>
          {{ totalCount }} {{ $t("common.items") }}
        </span>
        <span
          class="badge-count"
          :class="selectedCount > 0 ? 'badge-count--selected' : ''"
        >
          <i class="bi bi-check2-square me-1"></i>
          {{ selectedCount }} {{ $t("common.selected") }}
        </span>
      </div>

      <!-- Action Buttons (admin only, always visible, disabled when none selected) -->
      <div v-if="isAdmin" class="d-flex align-items-center gap-2">
        <button
          class="btn btn-outline-secondary btn-sm"
          @click="$emit('deselect')"
          :disabled="selectedCount === 0"
        >
          {{ $t("common.Deselect") }}
        </button>
        <button
          class="btn btn-danger btn-sm"
          @click="$emit('delete')"
          :disabled="selectedCount === 0"
        >
          <i class="bi bi-trash"></i>
          <span class="d-none d-sm-inline ms-1">{{ $t("common.Delete") }}</span>
          <span class="ms-1">({{ selectedCount }})</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  itemNames: {
    type: Array,
    required: true,
  },
  totalCount: {
    type: Number,
    default: 0,
  },
  searchQuery: {
    type: String,
    default: "",
  },
  filterColumn: {
    type: String,
    default: "item_name",
  },
  selectedCount: {
    type: Number,
    default: 0,
  },
  isAdmin: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "update:searchQuery",
  "update:filterColumn",
  "deselect",
  "delete",
]);

const searchPlaceholder = computed(() => {
  const placeholders = {
    item_name: "Search by item name...",
    quantity: "Search by stock quantity...",
    mrp: "Search by MRP...",
    sale_price: "Search by rate...",
  };
  return placeholders[props.filterColumn] || "Search...";
});

const handleFilterColumnChange = (event) => {
  emit("update:filterColumn", event.target.value);
};

const handleSearchChange = (event) => {
  emit("update:searchQuery", event.target.value);
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

/* Item count badges */
.badge-count {
  display: inline-flex;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 500;
  color: #6c757d;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 0.2rem 0.55rem;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.badge-count--selected {
  color: #0d6efd;
  background-color: #cfe2ff;
  border-color: #9ec5fe;
}

.btn-outline-secondary:disabled,
.btn-danger:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  pointer-events: auto;
}

@media (max-width: 575px) {
  .item-count-info {
    font-size: 13px;
  }
}
</style>
