<script setup>
import { ref } from 'vue';

const paperSize = ref('A4');
const paperSizes = ['A4', 'Letter', 'A5', 'Thermal'];
</script>

<template>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h6 class="fw-semibold mb-4">Invoice and Printing</h6>
      
      <!-- Invoice paper size -->
      <div>
        <div class="fw-semibold mb-2">Invoice paper size</div>
        <p class="text-muted small mb-3">Choose the paper size for printing invoices</p>
        
        <select 
          class="form-select" 
          v-model="paperSize"
          style="cursor: pointer;"
        >
          <option v-for="size in paperSizes" :key="size" :value="size">
            {{ size }}
          </option>
        </select>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form-select:focus {
  border-color: #5EBE9B;
  box-shadow: 0 0 0 0.25rem rgba(94, 190, 155, 0.25);
}
</style>
