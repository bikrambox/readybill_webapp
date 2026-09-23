<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  searchQuery: {
    type: String,
    default: "",
  },
  filterColumn: {
    type: String,
    default: "invoice_number",
  },
  pageSize: {
    type: Number,
    default: 100,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits([
  "update:searchQuery",
  "update:pageSize",
  "update:filterColumn",
  "search",
]);

// Local input ref — prevents double-trigger on every keystroke
const inputValue = ref(props.searchQuery);

// Keep input in sync if parent clears it externally
watch(
  () => props.searchQuery,
  (val) => {
    inputValue.value = val;
  }
);

// Dynamic placeholder based on selected filter column
const searchPlaceholder = computed(() => {
  const placeholders = {
    invoice_number: t("common.Search by Invoice Number"),
    date: "DD/MM/YYYY",
    user: t("common.Search by User"),
    total: t("common.Search by Total"),
  };
  return placeholders[props.filterColumn] || t("common.Search...");
});

// Debounced search — only fires after user stops typing
let searchTimeout;
const updateSearch = (event) => {
  inputValue.value = event.target.value;
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit("update:searchQuery", inputValue.value);
    emit("search", inputValue.value, props.filterColumn);
  }, 500);
};

// Handle filter column change — clear input and re-fetch
const handleFilterColumnChange = (event) => {
  const column = event.target.value;
  inputValue.value = "";
  emit("update:filterColumn", column);
  emit("update:searchQuery", "");
  emit("search", "", column);
};

const updatePageSize = (event) => {
  emit("update:pageSize", parseInt(event.target.value));
};
</script>

<template>
  <div class="transaction-filters mb-3">
    <div class="row g-3 align-items-center">
      <!-- Search Input + Filter Dropdown -->
      <div class="col-12 col-md-9 col-lg-7 col-xl-6 ms-md-auto">
        <div class="search-input-group">
          <span class="search-icon">
            <i class="bi bi-search"></i>
          </span>
          <input
            type="text"
            class="search-input"
            :placeholder="searchPlaceholder"
            :value="inputValue"
            @input="updateSearch"
          />
          <div class="filter-divider"></div>
          <select
            class="filter-select"
            :value="filterColumn"
            @change="handleFilterColumnChange"
          >
            <option value="invoice_number">{{ $t("common.Invoice Number") }}</option>
            <option value="date">{{ $t("common.Date") }}</option>
            <option value="user">{{ $t("common.User") }}</option>
            <option value="total">{{ $t("common.Total") }}</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.transaction-filters {
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 0.5rem;
}

/* Unified search group container */
.search-input-group {
  display: flex;
  align-items: center;
  border: 1.5px solid #dee2e6;
  border-radius: 0.5rem;
  background-color: #fff;
  overflow: hidden;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.search-input-group:focus-within {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
}

/* Search icon */
.search-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.65rem 0 0.85rem;
  color: #6c757d;
  flex-shrink: 0;
  font-size: 0.9rem;
}

/* Text input */
.search-input {
  flex: 1 1 auto;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  padding: 0.45rem 0.5rem 0.45rem 0;
  font-size: 0.875rem;
  color: #212529;
  width: 100%;
}

.search-input::placeholder {
  color: #adb5bd;
}

.search-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  background: transparent;
}

/* Divider between input and dropdown */
.filter-divider {
  width: 1px;
  height: 22px;
  background-color: #dee2e6;
  flex-shrink: 0;
}

/* Filter dropdown */
.filter-select {
  border: none;
  outline: none;
  background-color: #f8f9fa;
  padding: 0.45rem 2rem 0.45rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  flex-shrink: 0;
  min-width: 120px;
  max-width: 150px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 11px 11px;
}

.filter-select:focus {
  outline: none;
  box-shadow: none;
}

.filter-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.filter-select option {
  background-color: #fff;
  font-size: 0.875rem;
}

/* Small screens — full width, smaller dropdown */
@media (max-width: 576px) {
  .transaction-filters {
    padding: 0.65rem 0.5rem;
  }
  .filter-select {
    min-width: 95px;
    max-width: 115px;
    font-size: 0.75rem;
    padding-left: 0.5rem;
    padding-right: 1.4rem;
  }

  .search-icon {
    padding: 0 0.5rem 0 0.65rem;
  }

  .search-input {
    font-size: 0.8rem;
  }
}

/* Very small screens */
@media (max-width: 360px) {
  .filter-select {
    min-width: 80px;
    font-size: 0.7rem;
    padding-left: 0.4rem;
    padding-right: 1.2rem;
  }
}
</style>
