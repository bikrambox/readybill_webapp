<script setup>
import { ref, nextTick, onMounted, computed, getCurrentInstance, watch } from "vue";
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";
import { useConfigStore } from "@/stores/config";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";
import { storeToRefs } from "pinia";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import BarcodeScanner from "@/modules/GroceryIndia/components/inventory/BarcodeScanner.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

// Barcode scanner toggle
const showScanner = ref(false);
const barcodeScannerRef = ref(null);
const isRateEdited = ref(false);

const openBarcodeScanner = async () => {
  showScanner.value = true;
  await nextTick();
  // openScanner() resets the field and focuses it — ready for hardware scan
  barcodeScannerRef.value?.openScanner();
};

const handleBarcodeScanned = (code) => {
  formData.value.barcode = code;
  // showScanner.value = false;
};

const handleScannerCancel = () => {
  showScanner.value = false;
};

const emit = defineEmits(["submit", "cancel"]);

const inventoryStore = useInventoryStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();

const { loading, errors, successMessage } = storeToRefs(inventoryStore);
const { unitsArray, taxesArray, loading: configLoading } = storeToRefs(configStore);
const { preferences } = storeToRefs(preferencesStore);

console.log("taxesArray", taxesArray.value);

const formData = ref({
  itemName: "",
  stockQuantity: null,
  minStockAlert: null,
  selectedUnit: null,
  hsnCode: "",
  mrp: null,
  rate: null,
  barcode: "",
});

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

console.log("countryName", countryName);

watch(
  () => formData.value.mrp,
  (newValue) => {
    if (!isRateEdited.value) {
      formData.value.rate = newValue;
    }
  }
);

// Single tax entry
const taxRates = ref([{ tax: "", rate: null }]);

// Method to handle tax change
const handleTaxChange = () => {
  const newTaxValue = taxRates.value[0].tax;
  console.log("Tax changed to:", newTaxValue);
  console.log("Available taxesArray:", taxesArray.value);

  if (newTaxValue) {
    const selectedTax = taxesArray.value.find((tax) => tax.value === newTaxValue);
    console.log("Selected tax object:", selectedTax);

    if (selectedTax) {
      // Method 1: Check if rate property exists directly
      if (selectedTax.rate !== undefined && selectedTax.rate !== null) {
        taxRates.value[0].rate = parseFloat(selectedTax.rate);
        console.log("Rate set from rate property:", taxRates.value[0].rate);
      }
      // Method 2: Try to parse rate from label (e.g., "GST 7%" -> 7)
      else if (selectedTax.label) {
        const rateMatch = selectedTax.label.match(/(\d+\.?\d*)\s*%?/);
        if (rateMatch) {
          taxRates.value[0].rate = parseFloat(rateMatch[1]);
          console.log("Rate parsed from label:", taxRates.value[0].rate);
        }
      }
      // Method 3: If rate is in the value itself (e.g., value: "7")
      else if (!isNaN(parseFloat(selectedTax.value))) {
        taxRates.value[0].rate = parseFloat(selectedTax.value);
        console.log("Rate set from value:", taxRates.value[0].rate);
      }
    } else {
      console.log("Tax not found in taxesArray");
    }
  } else {
    taxRates.value[0].rate = null;
    console.log("Tax cleared, rate set to null");
  }
};

// Watch for changes in tax selection and auto-populate rate (backup method)
watch(
  () => taxRates.value[0].tax,
  (newTaxValue) => {
    console.log("Watcher triggered - Tax value:", newTaxValue);
    handleTaxChange();
  },
  { immediate: false }
);

// Computed properties based on user preferences
const isMrpRequired = computed(() => {
  return (
    preferences.value.preference_mrp === 1 || preferences.value.preference_mrp === true
  );
});

const isStockQuantityRequired = computed(() => {
  return (
    preferences.value.preference_quantity === 1 ||
    preferences.value.preference_quantity === true
  );
});

const showHsnField = computed(() => {
  return (
    preferences.value.preference_hsn === 1 || preferences.value.preference_hsn === true
  );
});

const showBarcodeField = computed(() => {
  return (
    preferences.value.preference_barcode === 1 ||
    preferences.value.preference_barcode === true
  );
});

const isHsnRequired = computed(() => {
  return (
    preferences.value.preference_hsn === 1 || preferences.value.preference_hsn === true
  );
});

// Computed property for combined errors
const allErrors = computed(() => {
  return [...errors.value, ...configStore.errors, ...preferencesStore.errors];
});

// Load config data and preferences on mount
onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(false, countryName.toLowerCase()),
    // Switch to German locale
    configStore.switchLocale(countryName.toLowerCase()),
    preferencesStore.fetchUserPreferences(),
  ]);

  // DEBUG: Log taxesArray structure
  console.log("Mounted - Full taxesArray:", JSON.stringify(taxesArray.value, null, 2));
  if (taxesArray.value && taxesArray.value.length > 0) {
    console.log("First tax item:", taxesArray.value[0]);
    console.log("Tax object keys:", Object.keys(taxesArray.value[0]));
  }
});

const handleSubmit = async () => {
  // Find the selected unit object from unitsArray
  const selectedUnitObj = unitsArray.value.find(
    (unit) => unit.value === formData.value.selectedUnit
  );

  const data = {
    itemName: formData.value.itemName,
    stockQuantity: formData.value.stockQuantity,
    minStockAlert: formData.value.minStockAlert,
    fullUnit: selectedUnitObj?.fullUnit || "",
    shortUnit: selectedUnitObj?.shortUnit || "",
    hsnCode: formData.value.hsnCode,
    mrp: formData.value.mrp,
    rate: formData.value.rate,
    taxRates: taxRates.value,
    barcode: formData.value.barcode,
  };

  const result = await inventoryStore.addItem(data);

  if (result.success) {
    emit("submit", result.data);

    // Reset form on success
    formData.value = {
      itemName: "",
      stockQuantity: null,
      minStockAlert: null,
      selectedUnit: null,
      hsnCode: "",
      mrp: null,
      rate: null,
      barcode: "",
    };
    // Reset to default single tax entry
    taxRates.value = [{ tax: "", rate: null }];

    showScanner.value = false;

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
        <span class="visually-hidden">{{ $t("common.Loading configuration") }}...</span>
      </div>
      <span class="ms-2 text-muted">{{ $t("common.Loading configuration") }}...</span>
    </div>

    <form @submit.prevent="handleSubmit" class="inventory-form">
      <!-- Barcode Scanner Toggle -->
      <div class="mb-3" v-if="showBarcodeField">
        <!-- Show scanner trigger button when scanner is hidden -->
        <div v-if="!showScanner" class="d-flex align-items-center gap-2">
          <i class="bi bi-upc-scan fs-4 text-secondary"></i>
          <a
            href="#"
            class="text-primary fw-medium text-decoration-none"
            @click.prevent="openBarcodeScanner"
          >
            {{ $t("common.Add Bar Code") }}
          </a>
        </div>

        <!-- Show BarcodeScanner component when active -->
        <BarcodeScanner
          v-else
          ref="barcodeScannerRef"
          @scanned="handleBarcodeScanned"
          @cancel="handleScannerCancel"
        />
      </div>

      <div class="row g-3">
        <!-- Item Name -->
        <div class="col-12 col-md-6">
          <label class="form-label"
            >{{ $t("common.Item Name") }}
            <span class="text-danger">*</span>
          </label>
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
          <label class="form-label"
            >Unit
            <span class="text-danger">*</span>
          </label>
          <select
            class="form-select"
            v-model="formData.selectedUnit"
            :disabled="loading || configLoading"
          >
            <option :value="null">
              {{ $t("inventory_page.Select Unit") }}
            </option>
            <option v-for="unit in unitsArray" :key="unit.value" :value="unit.value">
              {{ unit.label }}
            </option>
          </select>
        </div>

        <!-- Stock Quantity -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            {{ $t("inventory_page.Stock Quantity") }}
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
          <label class="form-label">{{ $t("inventory_page.Minimum Stock Alert") }}</label>
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
          <label class="form-label"
            >{{ $t("inventory_page.HSN/ SAC Code") }}
            <span v-if="isHsnRequired" class="text-danger">*</span>
          </label>
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
            {{ $t("inventory_page.MRP") }}
            <span v-if="isMrpRequired" class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">{{ currency }}</span>
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
            {{ $t("common.Sale Price") }}
            <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">{{ currency }}</span>
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

        <!-- Tax Section - Single Tax Only -->
        <div class="col-12">
          <div class="row g-3">
            <!-- Tax Dropdown -->
            <div class="col-12 col-md-6">
              <label class="form-label">
                {{ $t("inventory_page.Tax") }}
                <span class="text-danger">*</span>
              </label>
              <select
                class="form-select form-control-height"
                v-model="taxRates[0].tax"
                @change="handleTaxChange"
                :disabled="loading || configLoading"
              >
                <option value="">Select Tax</option>
                <option v-for="tax in taxesArray" :key="tax.value" :value="tax.value">
                  {{ tax.label }}
                </option>
              </select>
            </div>

            <!-- Tax Rate (Auto-populated and readonly) -->
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
                  readonly
                  :disabled="loading || configLoading"
                />
                <span class="input-group-text">%</span>
              </div>
              <small class="text-muted d-block mt-1"
                >Rate is auto-filled based on tax selection</small
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="form-actions">
        <button
          type="submit"
          class="btn btn-primary"
          :disabled="loading || configLoading"
        >
          <span
            v-if="loading"
            class="spinner-border spinner-border-sm me-2"
            role="status"
            aria-hidden="true"
          ></span>
          {{ loading ? "Submitting..." : "Submit" }}
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

/* Readonly styling */
.form-control:read-only {
  background-color: #f8f9fa;
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

.btn-outline-primary {
  color: #0066cc;
  border-color: #0066cc;
}

.btn-outline-primary:hover:not(:disabled) {
  background-color: #0066cc;
  border-color: #0066cc;
  color: white;
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

.text-muted {
  font-size: 12px;
  color: #6c757d;
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
