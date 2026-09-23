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
          <option value="name">{{ $t('common.Employee Name') }}</option>
          <option value="email">{{ $t('common.Email') }}</option>
          <option value="mobile">{{ $t('common.Contact Number') }}</option>
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
          {{ $t('employee_page.Add Employee') }}
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue"
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

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

let searchDebounceTimer = null

// Set default filter type to 'name' on mount
onMounted(() => {
  if (!props.selectedFilterType) {
    emit("update:filter-type", "name")
  }
})

const getSearchPlaceholder = () => {
  const placeholders = {
    // name: "Search by employee name...",
    // email: "Search by email address...",
    // contact: "Search by contact number...",

    name: `${t('common.Search by employee name')}...`,
    email: `${t('common.Search by email address')}...`,
    contact: `${t('common.Search by contact number')}...`,

  }
  return placeholders[props.selectedFilterType] || `${t('common.Search employees')}...`
}

const handleFilterTypeChange = (event) => {
  const newType = event.target.value
  console.log('🔄 Filter type changed to:', newType)
  
  emit("update:filter-type", newType)
  emit("update:filter-value", "")
  emit("update:search-query", "")
}

const handleSearchChange = (event) => {
  const searchValue = event.target.value
  console.log('🔍 Search input changed:', searchValue)
  
  emit("update:search-query", searchValue)
  
  // Clear existing timer
  if (searchDebounceTimer) {
    clearTimeout(searchDebounceTimer)
  }
  
  // Set new timer for debounced search (500ms delay)
  searchDebounceTimer = setTimeout(() => {
    console.log('✅ Triggering search after debounce')
    // The parent component will handle the actual search via the watch or immediate emit
  }, 500)
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
