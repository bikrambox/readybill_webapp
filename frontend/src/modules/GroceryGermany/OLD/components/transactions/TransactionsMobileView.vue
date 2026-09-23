<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  searchQuery: {
    type: String,
    required: true
  },
  selectedMonth: {
    type: String,
    required: true
  },
  totalSales: {
    type: Number,
    required: true
  },
  months: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  isAdmin: { type: Boolean, default: false }
});

const emit = defineEmits(['update:searchQuery', 'update:selectedMonth', 'generate-report']);

const updateSearch = (event) => {
  emit('update:searchQuery', event.target.value);
};

const updateMonth = async (event) => {
  emit('update:selectedMonth', event.target.value);
};

const handleGenerateReport = () => {
  emit('generate-report');
};
</script>

<template>
  <div class="mb-4">
    <div class="row g-3">
      <!-- Search Bar (First) -->
      <div class="col-12">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search"></i>
          </span>
          <input
            type="text"
            class="form-control border-start-0 ps-0"
            placeholder="Search..."
            :value="searchQuery"
            @input="updateSearch"
            :disabled="loading"
          />
        </div>
      </div>

      <!-- Generate Report Button (Second) - NO LOADING SPINNER -->
      <div class="col-12">
        <a 
          class="btn btn-primary w-100 btn-match-input" 
          href="generate-report" 
          rel="noopener"
          @click.prevent="handleGenerateReport"
          :disabled="loading"
        >
          <i class="bi bi-file-earmark-text me-2"></i>
          Generate Report
        </a>
      </div>

      <!-- Month and Total Sales Row (Third) -->
      <div class="col-12">
        <div class="d-flex justify-content-center align-items-center gap-3 flex-wrap">
          <!-- Total Sales Display -->
          <div class="d-flex align-items-center">
            <span class="text-muted small">Total Sales:</span>
            <span class="fw-bold text-success ms-2">
              ₹{{ totalSales.toFixed(2) }}
            </span>
          </div>

          <!-- Month Dropdown - Disabled during loading -->
          <div class="d-flex align-items-center">
            <label class="small fw-semibold mb-0 me-2">Month:</label>
            <select
              :value="selectedMonth"
              @change="updateMonth"
              :disabled="loading"
              class="form-select form-select-sm borderless-select-mobile"
            >
              <option
                v-for="month in months"
                :key="month.value"
                :value="month.value"
              >
                {{ month.label }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- BODY LOADER - Shows where data would display -->
      <div 
        v-if="loading"
        class="col-12 mt-3"
      >
        <div class="data-loader-overlay text-center py-4">
          <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <span class="text-muted">Loading transactions...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Button to match input height (Mobile) */
.btn-match-input {
  height: calc(1.5em + 0.75rem + 2px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 0.375rem;
  padding-bottom: 0.375rem;
}

.btn-match-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

/* BODY LOADER - Full width overlay style */
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

/* Input disabled state */
input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
