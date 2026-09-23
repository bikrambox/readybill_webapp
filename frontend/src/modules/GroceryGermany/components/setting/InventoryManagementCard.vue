<script setup>
import { ref } from 'vue';

const maintainMRP = ref(true);
const maintainStock = ref(false);
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h6 class="fw-semibold mb-4">{{ $t('common.Inventory Management') }}</h6>
      
      <!-- Maintain MRP -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1">{{ $t('common.Maintain MRP') }} ({{ $t('common.Maximum Retail Price') }})</div>
            <p class="text-muted small mb-0">
              {{ $t('common.Track and manage maximum retail prices for your products') }}
            </p>
          </div>
          <div class="form-check form-switch ms-3">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              v-model="maintainMRP"
              style="width: 3rem; height: 1.5rem; cursor: pointer;"
            >
          </div>
        </div>
      </div>

      <!-- Maintain Stock Levels -->
      <div>
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1">{{ $t('common.Maintain Stock Levels') }}</div>
            <p class="text-muted small mb-0">
              {{ $t('common.Track inventory quantities and stock movements') }}
            </p>
          </div>
          <div class="form-check form-switch ms-3">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              v-model="maintainStock"
              style="width: 3rem; height: 1.5rem; cursor: pointer;"
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form-check-input:checked {
  background-color: #5EBE9B;
  border-color: #5EBE9B;
}

.form-check-input:focus {
  border-color: #5EBE9B;
  box-shadow: 0 0 0 0.25rem rgba(94, 190, 155, 0.25);
}
</style>
