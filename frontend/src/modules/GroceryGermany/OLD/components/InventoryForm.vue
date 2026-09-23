<script setup>
import { ref, onMounted, computed } from 'vue';
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";
import { useConfigStore } from "@/stores/config";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";
import { storeToRefs } from 'pinia';
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue';

const emit = defineEmits(['submit', 'cancel']);

const inventoryStore = useInventoryStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();

const { loading, errors, successMessage } = storeToRefs(inventoryStore);
const { unitsArray, taxesArray, loading: configLoading } = storeToRefs(configStore);
const { preferences } = storeToRefs(preferencesStore);

const formData = ref({
  itemName: '',
  stockQuantity: null,
  minStockAlert: null,
  selectedUnit: null,
  hsnCode: '',
  mrp: null,
  rate: null
});

// Default first tax entry always visible
const taxRates = ref([
  { tax: '', rate: null }
]);

// Maximum tax entries allowed
const MAX_TAX_ENTRIES = 2;

// Check if max tax entries reached
const canAddMoreTax = computed(() => {
  return taxRates.value.length < MAX_TAX_ENTRIES;
});

// Computed properties based on user preferences
const isMrpRequired = computed(() => {
  return preferences.value.preference_mrp === 1 || preferences.value.preference_mrp === true;
});

const isStockQuantityRequired = computed(() => {
  return preferences.value.preference_quantity === 1 || preferences.value.preference_quantity === true;
});

const showHsnField = computed(() => {
  return preferences.value.preference_hsn === 1 || preferences.value.preference_hsn === true;
});

// Computed property for combined errors
const allErrors = computed(() => {
  return [...errors.value, ...configStore.errors, ...preferencesStore.errors]
});

// Load config data and preferences on mount
onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(),
    preferencesStore.fetchUserPreferences()
  ]);
});

const addTaxRate = () => {
  if (taxRates.value.length < MAX_TAX_ENTRIES) {
    taxRates.value.push({
      tax: '',
      rate: null
    });
  }
};

const removeTaxRate = (index) => {
  // Prevent removing the first tax entry
  if (index > 0) {
    taxRates.value.splice(index, 1);
  }
};

const handleSubmit = async () => {
  // Find the selected unit object from unitsArray
  const selectedUnitObj = unitsArray.value.find(
    unit => unit.value === formData.value.selectedUnit
  );

  const data = {
    itemName: formData.value.itemName,
    stockQuantity: formData.value.stockQuantity,
    minStockAlert: formData.value.minStockAlert,
    fullUnit: selectedUnitObj?.fullUnit || '',
    shortUnit: selectedUnitObj?.shortUnit || '',
    hsnCode: formData.value.hsnCode,
    mrp: formData.value.mrp,
    rate: formData.value.rate,
    taxRates: taxRates.value
  };
  
  const result = await inventoryStore.addItem(data);
  
  if (result.success) {
    emit('submit', result.data);
    
    // Reset form on success
    formData.value = {
      itemName: '',
      stockQuantity: null,
      minStockAlert: null,
      selectedUnit: null,
      hsnCode: '',
      mrp: null,
      rate: null
    };
    // Reset to default single tax entry
    taxRates.value = [{ tax: '', rate: null }];

    // Auto-hide success message after 3 seconds
    setTimeout(() => {
      inventoryStore.clearSuccessMessage();
    }, 3000);
  }
};

const handleClearErrors = () => {
  inventoryStore.clearErrors();
  configStore.clearErrors();
  preferencesStore.clearErrors();
};
</script>

<template>
  <div>
    <!-- Error Alert using FormErrorBox -->
    <div v-if="allErrors.length > 0" class="mb-3">
      <FormErrorBox :messages="allErrors" @clear="handleClearErrors" />
    </div>

    <!-- Success Alert -->
    <div
      v-if="successMessage"
      class="alert alert-success alert-dismissible fade show mb-3"
      role="alert"
    >
      <i class="bi bi-check-circle-fill me-2"></i>
      {{ successMessage }}
      <button
        type="button"
        class="btn-close"
        @click="inventoryStore.clearSuccessMessage()"
      ></button>
    </div>

    <!-- Loading Config Data -->
    <div v-if="configLoading" class="text-center py-3 mb-3">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Loading configuration...</span>
      </div>
      <span class="ms-2 text-muted">Loading configuration...</span>
    </div>

    <form @submit.prevent="handleSubmit" class="inventory-form">
      <div class="row g-3">
        <!-- Item Name -->
        <div class="col-12 col-md-6">
          <label class="form-label">Item Name</label>
          <input 
            type="text" 
            class="form-control" 
            placeholder="Amul Butter"
            v-model="formData.itemName"
            :disabled="loading || configLoading"
          />
        </div>

        <!-- Unit -->
        <div class="col-12 col-md-6">
          <label class="form-label">Unit</label>
          <select 
            class="form-select" 
            v-model="formData.selectedUnit" 
            :disabled="loading || configLoading"
          >
            <option :value="null">Select Unit</option>
            <option 
              v-for="unit in unitsArray" 
              :key="unit.value" 
              :value="unit.value"
            >
              {{ unit.label }}
            </option>
          </select>
        </div>

        <!-- Stock Quantity -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            Stock Quantity
            <span v-if="isStockQuantityRequired" class="text-danger">*</span>
          </label>
          <input 
            type="number" 
            class="form-control" 
            placeholder="Stock Quantity"
            v-model.number="formData.stockQuantity"
            :disabled="loading || configLoading"
            min="0"
            step="1"
          />
        </div>

        <!-- Min Stock Alert (Always visible with Stock Quantity) -->
        <div class="col-12 col-md-6">
          <label class="form-label">Min Stock Alert</label>
          <input 
            type="number" 
            class="form-control" 
            placeholder="Minimum Stock Alert"
            v-model.number="formData.minStockAlert"
            :disabled="loading || configLoading"
            min="0"
            step="1"
          />
        </div>

        <!-- HSN/SAC Code (Conditional based on preference) -->
        <div v-if="showHsnField" class="col-12 col-md-6">
          <label class="form-label">HSN/ SAC Code</label>
          <input 
            type="text" 
            class="form-control" 
            placeholder="HSN/ SAC Code"
            v-model="formData.hsnCode"
            :disabled="loading || configLoading"
          />
        </div>

        <!-- MRP (Conditional required based on preference) -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            MRP
            <span v-if="isMrpRequired" class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">₹</span>
            <input 
              type="number" 
              class="form-control" 
              placeholder="Price"
              v-model.number="formData.mrp"
              step="0.01"
              min="0"
              :disabled="loading || configLoading"
            />
          </div>
        </div>

        <!-- Rate -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            Rate
            <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">₹</span>
            <input 
              type="number" 
              class="form-control" 
              placeholder="Rate"
              v-model.number="formData.rate"
              step="0.01"
              min="0"
              :disabled="loading || configLoading"
            />
          </div>
        </div>

        <!-- Tax Section -->
        <div class="col-12">
          <div class="row g-3">
            <!-- First Tax (Always visible) -->
            <div class="col-12 col-md-6">
              <label class="form-label">
                Tax
                <span class="text-danger">*</span>
              </label>
              <select 
                class="form-select form-control-height" 
                v-model="taxRates[0].tax"
                :disabled="loading || configLoading"
              >
                <option value="">Select Tax</option>
                <option 
                  v-for="tax in taxesArray" 
                  :key="tax.value" 
                  :value="tax.value"
                >
                  {{ tax.label }}
                </option>
              </select>
            </div>

            <!-- First Tax Rate -->
            <div class="col-12 col-md-6">
              <label class="form-label">
                Rate
                <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input 
                  type="number" 
                  class="form-control form-control-height" 
                  v-model.number="taxRates[0].rate"
                  placeholder="0"
                  step="0.01"
                  min="0"
                  :disabled="loading || configLoading"
                />
                <span class="input-group-text">%</span>
                <button 
                  type="button" 
                  class="btn btn-primary btn-height"
                  @click="addTaxRate"
                  :disabled="loading || configLoading || !canAddMoreTax"
                  :title="canAddMoreTax ? 'Add another tax' : 'Maximum 2 taxes allowed'"
                >
                  <i class="bi bi-plus"></i>
                </button>
              </div>
            </div>

            <!-- Second Tax (Conditional) -->
            <template v-if="taxRates.length > 1">
              <div class="col-12 col-md-6">
                <label class="form-label">Tax 2</label>
                <select 
                  class="form-select form-control-height" 
                  v-model="taxRates[1].tax"
                  :disabled="loading || configLoading"
                >
                  <option value="">Select Tax</option>
                  <option 
                    v-for="tax in taxesArray" 
                    :key="tax.value" 
                    :value="tax.value"
                  >
                    {{ tax.label }}
                  </option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label class="form-label">Rate 2</label>
                <div class="input-group">
                  <input 
                    type="number" 
                    class="form-control form-control-height" 
                    v-model.number="taxRates[1].rate"
                    placeholder="0"
                    step="0.01"
                    min="0"
                    :disabled="loading || configLoading"
                  />
                  <span class="input-group-text">%</span>
                  <button 
                    type="button" 
                    class="btn btn-danger btn-height"
                    @click="removeTaxRate(1)"
                    :disabled="loading || configLoading"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="loading || configLoading">
          <span
            v-if="loading"
            class="spinner-border spinner-border-sm me-2"
            role="status"
            aria-hidden="true"
          ></span>
          {{ loading ? 'Submitting...' : 'Submit' }}
        </button>
        <button 
          type="button" 
          class="btn btn-outline-secondary" 
          @click="$emit('cancel')"
          :disabled="loading"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.inventory-form {
  width: 100%;
}

.form-label {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}

.form-control,
.form-select {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 14px;
  transition: all 0.2s;
}

/* Ensure consistent height for tax dropdown and input */
.form-control-height,
.form-select.form-control-height {
  height: 44px;
  padding: 10px 12px;
}

.form-control:focus,
.form-select:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

.form-control:disabled,
.form-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #6c757d;
  font-weight: 500;
  height: 44px;
  display: flex;
  align-items: center;
}

.input-group .btn {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
}

/* Ensure button height matches input height */
.btn-height {
  height: 44px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: #0066cc;
  border-color: #0066cc;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0052a3;
  border-color: #0052a3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-danger:hover:not(:disabled) {
  background-color: #bb2d3b;
  border-color: #b02a37;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 25px;
}

.form-actions .btn {
  padding: 12px 30px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
}

.btn-outline-secondary {
  color: #6c757d;
  border-color: #dee2e6;
}

.btn-outline-secondary:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  color: #333;
}

.btn-outline-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.text-danger {
  color: #dc3545;
  font-weight: 600;
}

@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
  }
  
  .form-actions .btn {
    width: 100%;
  }
}
</style>
