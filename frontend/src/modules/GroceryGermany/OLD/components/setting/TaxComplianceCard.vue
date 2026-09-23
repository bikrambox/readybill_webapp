<script setup>
import { ref } from 'vue';

const useHSNSAC = ref(false);
const showHSNSACInvoices = ref(false);
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h6 class="fw-semibold mb-4">Tax & Compliance</h6>
      
      <!-- Use HSN/SAC codes -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1">Use HSN/SAC codes</div>
            <p class="text-muted small mb-0">
              Include HSN (Harmonized System of Nomenclature) or SAC codes for tax compliance
            </p>
          </div>
          <div class="form-check form-switch ms-3">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              v-model="useHSNSAC"
              style="width: 3rem; height: 1.5rem; cursor: pointer;"
            >
          </div>
        </div>
      </div>

      <!-- Show HSN/SAC codes in invoices -->
      <div>
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1">Show HSN/SAC codes in invoices</div>
            <p class="text-muted small mb-0">
              Display HSN/SAC codes on customer invoices
            </p>
          </div>
          <div class="form-check form-switch ms-3">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              v-model="showHSNSACInvoices"
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
