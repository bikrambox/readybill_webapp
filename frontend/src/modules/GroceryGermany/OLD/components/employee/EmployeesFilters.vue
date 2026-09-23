<template>
  <div class="filters-section mb-3 mb-lg-4">
    <div class="row g-3 align-items-center">
      <!-- Filter Type Dropdown -->
      <div class="col-12 col-md-4 col-lg-3">
        <select
          class="form-select"
          :value="selectedFilterType"
          @change="handleFilterTypeChange"
        >
          <option value="name">Employee Name</option>
          <option value="email">Email</option>
          <option value="contact">Contact Number</option>
        </select>
      </div>

      <!-- Search Input -->
      <div class="col-12 col-md-4 col-lg-6">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search"></i>
          </span>
          <input
            type="text"
            class="form-control border-start-0 ps-0"
            :placeholder="getSearchPlaceholder()"
            :value="searchQuery"
            @input="handleSearchChange"
          />
        </div>
      </div>

      <!-- Add Employee Button -->
      <div class="col-12 col-md-4 col-lg-3 d-flex justify-content-md-end">
        <a
          class="btn btn-primary w-100 w-md-auto"
          href="add-employee"
          rel="noopener"
        >
          <i class="bi bi-plus-circle me-2"></i>
          Add Employee
        </a>
      </div>
    </div>

    <!-- Active Filters Display -->
    <!-- <div v-if="hasActiveFilters" class="active-filters mt-3">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small fw-medium">Active Filter:</span>

        <span class="badge bg-primary rounded-pill">
          {{ getFilterLabel() }}: {{ searchQuery }}
          <button
            type="button"
            class="btn-close btn-close-white ms-2"
            @click="clearSearch"
            style="font-size: 0.65rem"
            aria-label="Clear filter"
          ></button>
        </span>

        <button
          class="btn btn-sm btn-outline-secondary"
          @click="clearAllFilters"
        >
          Clear Filter
        </button>
      </div>
    </div> -->


  </div>
</template>

<script setup>
import { computed, onMounted } from "vue"

const props = defineProps({
  filterOptions: {
    type: Object,
    required: true,
  },
  selectedFilterType: {
    type: String,
    default: "name",
  },
  selectedFilterValue: {
    type: String,
    default: "",
  },
  searchQuery: {
    type: String,
    default: "",
  },
  selectedCount: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits([
  "update:filter-type",
  "update:filter-value",
  "update:search-query",
  "add-employee",
])

// Set default filter type to 'name' on mount if not already set
onMounted(() => {
  if (!props.selectedFilterType) {
    emit("update:filter-type", "name")
  }
})

const hasActiveFilters = computed(() => {
  return props.searchQuery && props.searchQuery.trim() !== ""
})

const getFilterLabel = () => {
  const labels = {
    name: "Name",
    email: "Email",
    contact: "Contact",
  }
  return labels[props.selectedFilterType] || "Name"
}

const getSearchPlaceholder = () => {
  const placeholders = {
    name: "Search by employee name...",
    email: "Search by email address...",
    contact: "Search by contact number...",
  }
  return placeholders[props.selectedFilterType] || "Search employees..."
}

const handleFilterTypeChange = (event) => {
  const newType = event.target.value
  emit("update:filter-type", newType)
  emit("update:filter-value", "")
  emit("update:search-query", "")
}

const handleSearchChange = (event) => {
  emit("update:search-query", event.target.value)
}

const clearSearch = () => {
  emit("update:search-query", "")
}

const clearAllFilters = () => {
  emit("update:search-query", "")
}
</script>

<style scoped>
.filters-section {
  background: white;
  padding: 1.25rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-select,
.form-control {
  border-radius: 8px;
  border: 1px solid #dee2e6;
  font-size: 14px;
  transition: all 0.15s ease-in-out;
}

.form-select:focus,
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.form-select {
  font-weight: 500;
  color: #495057;
}

.input-group-text {
  border-radius: 8px 0 0 8px;
  background-color: white;
  color: #6c757d;
}

.input-group .form-control {
  border-radius: 0 8px 8px 0;
}

.input-group .form-control::placeholder {
  color: #adb5bd;
  font-size: 14px;
}

.btn-primary {
  border-radius: 8px;
  padding: 0.5rem 1.25rem;
  font-weight: 500;
  font-size: 14px;
  white-space: nowrap;
  transition: all 0.15s ease-in-out;
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(13, 110, 253, 0.25);
}

.btn-primary i {
  font-size: 16px;
}

.active-filters {
  padding-top: 0.75rem;
  border-top: 1px solid #e9ecef;
}

.badge {
  padding: 0.5rem 0.75rem;
  font-weight: 500;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
}

.btn-close-white {
  opacity: 0.8;
  width: 0.75em;
  height: 0.75em;
}

.btn-close-white:hover {
  opacity: 1;
}

.btn-sm {
  font-size: 13px;
  padding: 0.375rem 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .filters-section {
    padding: 1rem;
  }

  .form-select,
  .form-control,
  .btn-primary {
    font-size: 13px;
  }

  .btn-primary {
    padding: 0.5rem 1rem;
    justify-content: center;
  }
}

@media (max-width: 576px) {
  .form-select {
    font-size: 14px;
  }
  
  .input-group .form-control::placeholder {
    font-size: 13px;
  }
}
</style>
