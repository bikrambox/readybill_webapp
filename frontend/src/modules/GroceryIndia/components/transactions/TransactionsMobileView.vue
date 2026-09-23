<script setup>
import { defineProps, defineEmits, computed, ref } from "vue";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  searchQuery: {
    type: String,
    required: true,
  },
  selectedMonth: {
    type: String,
    required: true,
  },
  totalSales: {
    type: Number,
    required: true,
  },
  months: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  isAdmin: { type: Boolean, default: false },
});

const emit = defineEmits([
  "update:searchQuery",
  "update:selectedMonth",
  "generate-report",
  "search", // NEW
]);

// Local filter column state
const filterColumn = ref("invoice_number");

// Dynamic placeholder based on selected filter column
const searchPlaceholder = computed(() => {
  const placeholders = {
    invoice_number: t("common.Search by Invoice Number"),
    date: "DD/MM/YYYY",
    user: t("common.Search by User"),
    total: t("common.Search by Total"),
  };
  return placeholders[filterColumn.value] || t("common.Search...");
});

// Debounced search
let searchTimeout;
const updateSearch = (event) => {
  clearTimeout(searchTimeout);
  const value = event.target.value;
  emit("update:searchQuery", value); // keep parent ref in sync
  searchTimeout = setTimeout(() => {
    emit("search", value, filterColumn.value); // trigger actual store fetch
  }, 500);
};

// Handle filter column change — clear search query
const handleFilterColumnChange = (event) => {
  filterColumn.value = event.target.value;
  emit("update:searchQuery", "");
  emit("search", "", filterColumn.value); // immediately re-fetch with empty query
};

const updateMonth = async (event) => {
  emit("update:selectedMonth", event.target.value);
};

const handleGenerateReport = () => {
  emit("generate-report");
};

// Computed property for formatted total sales with negative handling
const formattedTotalSales = computed(() => {
  const total = parseFloat(props.totalSales || 0);
  return {
    isNegative: total < 0,
    absoluteValue: Math.abs(total),
  };
});
</script>

<template>
  <div class="mb-4">
    <div class="row g-3">
      <!-- Attached Search Input + Filter Column Dropdown -->
      <!-- Attached Search Input + Filter Column Dropdown -->
      <div class="col-12">
        <div class="search-input-group">
          <span class="search-icon">
            <i class="bi bi-search"></i>
          </span>
          <input
            type="text"
            class="search-input"
            :placeholder="searchPlaceholder"
            :value="searchQuery"
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

      <!-- Generate Report Button -->
      <div v-if="isAdmin" class="col-12">
        <a
          class="btn btn-primary w-100 btn-match-input"
          href="generate-report"
          rel="noopener"
          @click.prevent="handleGenerateReport"
          :class="{ disabled: loading }"
        >
          <i class="bi bi-file-earmark-text me-2"></i>
          {{ $t("common.Generate Report") }}
        </a>
      </div>

      <!-- Month and Total Sales Row -->
      <div class="col-12">
        <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
          <!-- Total Sales Display -->
          <div class="d-flex align-items-center">
            <span class="text-muted small">{{ $t("common.Total Sales") }}:</span>
            <span
              class="fw-bold ms-2"
              :class="formattedTotalSales.isNegative ? 'text-danger' : 'text-success'"
            >
              {{ formattedTotalSales.isNegative ? "-" : ""
              }}{{ $formatCurrency(formattedTotalSales.absoluteValue) }}
            </span>
          </div>

          <!-- Month Dropdown -->
          <div class="d-flex align-items-center">
            <label class="small fw-semibold mb-0 me-2">{{ $t("common.Month") }}:</label>
            <select
              :value="selectedMonth"
              @change="updateMonth"
              :disabled="loading"
              class="form-select form-select-sm borderless-select-mobile"
            >
              <option v-for="month in months" :key="month.value" :value="month.value">
                {{ month.label }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading Indicator -->
      <div v-if="loading" class="col-12 mt-3">
        <div class="data-loader-overlay text-center py-4">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
          </div>
          <span class="text-muted">{{ $t("common.Loading transactions") }}...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Attached search + filter dropdown */
.input-group-filter-select {
  border: 1px solid #dee2e6;
  border-left: 1px solid #dee2e6;
  border-radius: 0 0.375rem 0.375rem 0;
  background-color: #f8f9fa;
  padding: 0.375rem 2rem 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 12px 12px;
  min-width: 130px;
}

.input-group-filter-select:focus {
  outline: none;
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  z-index: 3;
}

.input-group-filter-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.input-group .form-control:focus {
  z-index: 3;
}

/* Button to match input height */
.btn-match-input {
  height: calc(1.5em + 0.75rem + 2px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 0.375rem;
  padding-bottom: 0.375rem;
}

.btn-match-input.disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
}

/* Mobile Borderless Select */
.borderless-select-mobile {
  border: none;
  background-color: transparent;
  box-shadow: none;
  padding-left: 0;
  padding-right: 1.1rem;
  cursor: pointer;
  font-weight: 500;
  min-width: auto;
  background-position: right 0.1rem center;
  background-size: 10px 10px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

.borderless-select-mobile:focus {
  border: none;
  box-shadow: none;
  background-color: transparent;
  outline: none;
}

.borderless-select-mobile:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.borderless-select-mobile option {
  background-color: white;
  padding: 0.5rem;
}

/* Body loader overlay */
.data-loader-overlay {
  background: #f8f9fa;
  border: 1px dashed #dee2e6;
  border-radius: 0.5rem;
  min-height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Unified rounded search group */
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

.search-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.65rem 0 0.85rem;
  color: #6c757d;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.search-input {
  flex: 1 1 auto;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  padding: 0.5rem 0.5rem 0.5rem 0;
  font-size: 0.875rem;
  color: #212529;
  width: 100%;
}

.search-input::placeholder {
  color: #adb5bd;
}

.filter-divider {
  width: 1px;
  height: 22px;
  background-color: #dee2e6;
  flex-shrink: 0;
}

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
  min-width: 110px;
  max-width: 140px;
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

.filter-select option {
  background-color: #fff;
  font-size: 0.875rem;
}

/* Very small screens */
@media (max-width: 360px) {
  .filter-select {
    min-width: 85px;
    font-size: 0.75rem;
    padding-left: 0.4rem;
    padding-right: 1.3rem;
  }

  .search-icon {
    padding: 0 0.5rem 0 0.65rem;
  }

  .search-input {
    font-size: 0.8rem;
  }
}
</style>
