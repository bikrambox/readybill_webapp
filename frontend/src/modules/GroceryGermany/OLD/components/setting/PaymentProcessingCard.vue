<script setup>
import { ref } from 'vue';

const autoMarkPaid = ref(false);
const adminOnlyMarking = ref(true);
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h6 class="fw-semibold mb-4">Payment Processing</h6>
      
      <!-- Auto-mark transactions as paid -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1">Auto-mark transactions as paid</div>
            <p class="text-muted small mb-0">
              Automatically mark transactions as paid when saved
            </p>
          </div>
          <div class="form-check form-switch ms-3">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              v-model="autoMarkPaid"
              style="width: 3rem; height: 1.5rem; cursor: pointer;"
            >
          </div>
        </div>
      </div>

      <!-- Admin-only payment marking -->
      <div>
        <div class="d-flex justify-content-between align-items-start">
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1">Admin-only payment marking</div>
            <p class="text-muted small mb-0">
              Only admin/owner can mark transactions as paid
            </p>
          </div>
          <div class="form-check form-switch ms-3">
            <input 
              class="form-check-input" 
              type="checkbox" 
              role="switch" 
              v-model="adminOnlyMarking"
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
