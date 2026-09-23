<template>
  <div
    class="modal fade"
    id="itemDetailsModal"
    ref="modalElement"
    tabindex="-1"
    aria-labelledby="itemDetailsModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
          <h5 class="modal-title" id="itemDetailsModalLabel">
            {{ $t("inventory_page.Item Details") }}
          </h5>
          <button
            type="button"
            class="btn-close"
            @click="handleClose"
            aria-label="Close"
          ></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
          <!-- Error Box -->
          <FormErrorBox :messages="errorMessages" @clear="clearErrors" class="mb-4" />

          <p
            class="text-muted small mb-4"
            v-html="
              $t('common.mandatoryfieldsnote', {
                star: `<span class='text-danger'>*</span>`,
              })
            "
          ></p>

          <form @submit.prevent="handleSubmit">
            <!-- Barcode Scanner Toggle -->
            <div class="mb-3" v-if="showBarcodeField && !isBarcodeSet">
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

            <div class="mb-3" v-else-if="isBarcodeSet">
              <label for="barcode" class="form-label">
                {{ $t("common.Barcode") }}
              </label>
              <input
                type="text"
                class="form-control"
                id="barcode"
                v-model="formData.barcode"
                required
                disabled
              />
            </div>

            <!-- Item Name -->
            <div class="mb-3">
              <label for="itemName" class="form-label">
                {{ $t("common.Item Name") }}
                <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                id="itemName"
                v-model="formData.name"
                required
                :disabled="props.saving"
              />
            </div>

            <!-- Stock Quantity & Minimum Stock Alert -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="stockQuantity" class="form-label">
                  {{ $t("inventory_page.Stock Quantity") }}
                  <span v-if="isStockQuantityRequired" class="text-danger">*</span>
                </label>
                <input
                  type="number"
                  step="0.01"
                  class="form-control"
                  id="stockQuantity"
                  v-model="formData.stock"
                  :disabled="props.saving"
                />
              </div>
              <div class="col-md-6">
                <label for="minStockAlert" class="form-label">
                  {{ $t("common.Minimum Stock Alert") }}
                </label>
                <input
                  type="number"
                  step="0.01"
                  class="form-control"
                  id="minStockAlert"
                  v-model="formData.minStockAlert"
                  :disabled="props.saving"
                />
              </div>
            </div>

            <!-- Unit -->
            <div class="mb-3">
              <label for="unit" class="form-label">
                {{ $t("inventory_page.Unit") }}
                <span class="text-danger">*</span>
              </label>
              <select
                class="form-select"
                id="unit"
                v-model="formData.shortUnit"
                required
                :disabled="props.saving"
              >
                <option :value="null">
                  {{ $t("inventory_page.Select Unit") }}
                </option>
                <option v-for="unit in unitsArray" :key="unit.value" :value="unit.value">
                  {{ unit.label }}
                </option>
              </select>
            </div>

            <!-- HSN/SAC Code (Conditional based on preference) -->
            <div v-if="showHsnField" class="mb-3">
              <label for="hsnCode" class="form-label">
                {{ $t("inventory_page.HSN/ SAC Code") }}
                <span v-if="isHsnRequired" class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                id="hsnCode"
                v-model="formData.hsnCode"
                :disabled="props.saving"
              />
            </div>

            <!-- MRP & Rate -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="mrp" class="form-label">
                  {{ $t("inventory_page.MRP") }}
                  <span v-if="isMrpRequired" class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    id="mrp"
                    v-model="formData.mrp"
                    :disabled="props.saving"
                  />
                  <span class="input-group-text text-muted"
                    >{{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}</span
                  >
                </div>
              </div>
              <div class="col-md-6">
                <label for="rate" class="form-label">
                  {{ $t("inventory_page.Rate") }}
                  <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    id="rate"
                    v-model="formData.rate"
                    required
                    :disabled="props.saving"
                  />
                  <span class="input-group-text text-muted"
                    >{{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Tax & Rate -->
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="tax" class="form-label">
                  {{ $t("inventory_page.Tax") }}
                  <span class="text-danger">*</span>
                </label>
                <select
                  class="form-select form-control-height"
                  id="tax"
                  v-model="formData.tax1"
                  @change="handleTax1Change"
                  required
                  :disabled="props.saving"
                >
                  <option value="">Select Tax</option>
                  <option v-for="tax in taxesArray" :key="tax.value" :value="tax.value">
                    {{ tax.label }}
                  </option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="taxRate" class="form-label">
                  {{ $t("inventory_page.Rate") }}
                  <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input
                    type="number"
                    step="0.01"
                    class="form-control form-control-height"
                    id="taxRate"
                    v-model="formData.rate1"
                    readonly
                    required
                    :disabled="props.saving"
                  />
                  <span class="input-group-text">%</span>
                  <button
                    class="btn btn-primary btn-height"
                    type="button"
                    @click="toggleSecondaryTax"
                    :disabled="props.saving || showSecondaryTax"
                    :title="
                      showSecondaryTax ? 'Maximum 2 taxes allowed' : 'Add another tax'
                    "
                  >
                    <i class="bi bi-plus"></i>
                  </button>
                </div>
                <small class="text-muted d-block mt-1"
                  >Rate is auto-filled based on tax selection</small
                >
              </div>
            </div>

            <!-- Secondary Tax (if enabled) -->
            <div v-if="showSecondaryTax" class="row mb-3">
              <div class="col-md-6">
                <label for="tax2" class="form-label"
                  >{{ $t("inventory_page.Tax") }} 2</label
                >
                <select
                  class="form-select form-control-height"
                  id="tax2"
                  v-model="formData.tax2"
                  @change="handleTax2Change"
                  :disabled="props.saving"
                >
                  <option value="">Select Tax</option>
                  <option v-for="tax in taxesArray" :key="tax.value" :value="tax.value">
                    {{ tax.label }}
                  </option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="taxRate2" class="form-label"
                  >{{ $t("inventory_page.Rate") }} 2</label
                >
                <div class="input-group">
                  <input
                    type="number"
                    step="0.01"
                    class="form-control form-control-height"
                    id="taxRate2"
                    v-model="formData.rate2"
                    readonly
                    :disabled="props.saving"
                  />
                  <span class="input-group-text">%</span>
                  <button
                    class="btn btn-outline-danger btn-height"
                    type="button"
                    @click="removeSecondaryTax"
                    :disabled="props.saving"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
                <small class="text-muted d-block mt-1"
                  >Rate is auto-filled based on tax selection</small
                >
              </div>
            </div>
          </form>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-outline-secondary"
            @click="handleClose"
            :disabled="props.saving"
          >
            {{ $t("common.Cancel") }}
          </button>
          <button
            type="button"
            class="btn btn-primary"
            @click="handleSubmit"
            :disabled="props.saving || !isFormValid"
          >
            <span
              v-if="props.saving"
              class="spinner-border spinner-border-sm me-2"
              role="status"
            ></span>
            <span v-if="props.saving">{{ $t("common.Updating") }}...</span>
            <span v-else>{{ $t("common.Update") }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onUnmounted,
  getCurrentInstance,
  nextTick,
} from "vue";
import { Modal } from "bootstrap";
import { storeToRefs } from "pinia";
import { useConfigStore } from "@/stores/config";
import { useInventoryManagementStore } from "@/modules/GroceryGermany/stores/inventoryManagement";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import BarcodeScanner from "@/modules/GroceryGermany/components/inventory/BarcodeScanner.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

// Barcode scanner toggle
const showScanner = ref(false);
const barcodeScannerRef = ref(null);
const isRateEdited = ref(false);

const openBarcodeScanner = async () => {
  showScanner.value = true;
  await nextTick();
  barcodeScannerRef.value?.openScanner();
};

const handleBarcodeScanned = (code) => {
  formData.value.barcode = code; // or whichever field stores barcode
  // showScanner.value = false;
};

const handleScannerCancel = () => {
  showScanner.value = false;
};

const props = defineProps({
  item: {
    type: Object,
    default: null,
  },
  saving: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["save", "close"]);

const configStore = useConfigStore();
const inventoryStore = useInventoryManagementStore();
const preferencesStore = useUserPreferencesStore();

const modalElement = ref(null);
let modalInstance = null;

const showSecondaryTax = ref(false);

// Get preferences
const { preferences } = storeToRefs(preferencesStore);

// Error handling
const errorMessages = ref([]);

const { unitsArray, taxesArray, loading: configLoading } = storeToRefs(configStore);
const { errors: storeErrors } = storeToRefs(inventoryStore);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

// Form data
const formData = ref({
  id: null,
  name: "",
  stock: 0,
  minStockAlert: 0,
  shortUnit: "PCS",
  fullUnit: "Piece",
  hsnCode: "",
  mrp: 0,
  rate: 0,
  tax1: "",
  rate1: 0,
  tax2: "",
  rate2: 0,
  tags: [],
  barcode: "",
});

watch(
  () => formData.value.mrp,
  (newValue) => {
    if (!isRateEdited.value) {
      formData.value.rate = newValue;
    }
  }
);

// Handle tax1 change - auto-populate rate1
const handleTax1Change = () => {
  const newTaxValue = formData.value.tax1;
  console.log("Tax1 changed to:", newTaxValue);
  console.log("Available taxesArray:", taxesArray.value);

  if (newTaxValue) {
    const selectedTax = taxesArray.value.find((tax) => tax.value === newTaxValue);
    console.log("Selected tax1 object:", selectedTax);

    if (selectedTax) {
      // Check if rate property exists directly
      if (selectedTax.rate !== undefined && selectedTax.rate !== null) {
        formData.value.rate1 = parseFloat(selectedTax.rate);
        console.log("Rate1 set from rate property:", formData.value.rate1);
      }
      // Try to parse rate from label (e.g., "GST 7%" -> 7)
      else if (selectedTax.label) {
        const rateMatch = selectedTax.label.match(/(\d+\.?\d*)\s*%?/);
        if (rateMatch) {
          formData.value.rate1 = parseFloat(rateMatch[1]);
          console.log("Rate1 parsed from label:", formData.value.rate1);
        }
      }
    } else {
      console.log("Tax1 not found in taxesArray");
    }
  } else {
    formData.value.rate1 = 0;
    console.log("Tax1 cleared, rate1 set to 0");
  }
};

// Handle tax2 change - auto-populate rate2
const handleTax2Change = () => {
  const newTaxValue = formData.value.tax2;
  console.log("Tax2 changed to:", newTaxValue);

  if (newTaxValue) {
    const selectedTax = taxesArray.value.find((tax) => tax.value === newTaxValue);
    console.log("Selected tax2 object:", selectedTax);

    if (selectedTax) {
      // Check if rate property exists directly
      if (selectedTax.rate !== undefined && selectedTax.rate !== null) {
        formData.value.rate2 = parseFloat(selectedTax.rate);
        console.log("Rate2 set from rate property:", formData.value.rate2);
      }
      // Try to parse rate from label
      else if (selectedTax.label) {
        const rateMatch = selectedTax.label.match(/(\d+\.?\d*)\s*%?/);
        if (rateMatch) {
          formData.value.rate2 = parseFloat(rateMatch[1]);
          console.log("Rate2 parsed from label:", formData.value.rate2);
        }
      }
    } else {
      console.log("Tax2 not found in taxesArray");
    }
  } else {
    formData.value.rate2 = 0;
    console.log("Tax2 cleared, rate2 set to 0");
  }
};

// Watch for tax1 change (backup method)
watch(
  () => formData.value.tax1,
  (newTaxValue) => {
    console.log("Watcher triggered - Tax1 value:", newTaxValue);
    handleTax1Change();
  },
  { immediate: false }
);

// Watch for tax2 change (backup method)
watch(
  () => formData.value.tax2,
  (newTaxValue) => {
    console.log("Watcher triggered - Tax2 value:", newTaxValue);
    handleTax2Change();
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

const isHsnRequired = computed(() => {
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

const isBarcodeSet = computed(() => {
  const barcode = props.item?.barcode;
  return !!barcode && barcode.trim() !== "" && barcode !== "–";
});

// Form validation
const isFormValid = computed(() => {
  return (
    formData.value.name &&
    formData.value.shortUnit &&
    formData.value.rate > 0 &&
    formData.value.tax1 &&
    formData.value.rate1 >= 0
  );
});

// Clear errors
const clearErrors = () => {
  errorMessages.value = [];
  inventoryStore.clearErrors();
};

// Watch for store errors
watch(
  storeErrors,
  (newErrors) => {
    if (newErrors && newErrors.length > 0) {
      const extractedErrors = [];
      newErrors.forEach((error) => {
        if (typeof error === "string") {
          extractedErrors.push(error);
        } else if (typeof error === "object" && error !== null) {
          Object.keys(error).forEach((field) => {
            const fieldErrors = error[field];
            if (Array.isArray(fieldErrors)) {
              extractedErrors.push(...fieldErrors);
            } else if (typeof fieldErrors === "string") {
              extractedErrors.push(fieldErrors);
            }
          });
        }
      });
      errorMessages.value = extractedErrors;
    } else {
      errorMessages.value = [];
    }
  },
  { deep: true }
);

// Watch for item changes
watch(
  () => props.item,
  (newItem) => {
    clearErrors();

    if (newItem) {
      console.log("Editing item:", newItem);

      // Process tax2 value - handle "–" and empty values
      const hasTax2 = newItem.tax2 && newItem.tax2 !== "–" && newItem.tax2.trim() !== "";
      const hasRate2 =
        newItem.rate2 !== null &&
        newItem.rate2 !== undefined &&
        newItem.rate2 !== "" &&
        newItem.rate2 !== "–" &&
        !isNaN(Number(newItem.rate2)) &&
        Number(newItem.rate2) > 0;

      formData.value = {
        id: newItem.id,
        name: newItem.name || "",
        stock: newItem.stock || 0,
        minStockAlert: newItem.minStockAlert || 0,
        shortUnit: newItem.shortUnit || "PCS",
        fullUnit: newItem.fullUnit || "Piece",
        hsnCode: !newItem.hsnCode || newItem.hsnCode === "–" ? "" : newItem.hsnCode,
        mrp: newItem.mrp || 0,
        rate: newItem.rate || 0,
        tax1: newItem.tax1 || "",
        rate1: newItem.rate1 || 0,
        tax2: hasTax2 ? newItem.tax2 : "",
        rate2: hasRate2 ? parseFloat(newItem.rate2) : 0,
        tags: newItem.tags || [],
        barcode: newItem.barcode,
      };

      // Show secondary tax if both tax2 and rate2 have valid values
      showSecondaryTax.value = hasTax2 && hasRate2;

      console.log("Secondary tax status:", {
        hasTax2,
        hasRate2,
        showSecondaryTax: showSecondaryTax.value,
        tax2Value: formData.value.tax2,
        rate2Value: formData.value.rate2,
      });
    }
  },
  { immediate: true, deep: true }
);

// Toggle secondary tax
const toggleSecondaryTax = () => {
  showSecondaryTax.value = !showSecondaryTax.value;
  if (!showSecondaryTax.value) {
    formData.value.tax2 = "";
    formData.value.rate2 = 0;
  }
};

// Remove secondary tax
const removeSecondaryTax = () => {
  showSecondaryTax.value = false;
  formData.value.tax2 = "";
  formData.value.rate2 = 0;
};

// Handle submit
const handleSubmit = async () => {
  if (!isFormValid.value || props.saving) {
    return;
  }

  clearErrors();

  console.log("Saving item data:", formData.value);

  try {
    emit("save", { ...formData.value });
  } catch (error) {
    console.error("Error saving item:", error);
  }
};

// Handle close
const handleClose = () => {
  if (props.saving) {
    showScanner.value = false;
    return;
  }
  clearErrors();
  hide();
  emit("close");
};

// Show modal
const show = () => {
  if (modalInstance) {
    showScanner.value = false;
    modalInstance.show();
  }
};

// Hide modal
const hide = () => {
  if (modalInstance) {
    showScanner.value = false;
    modalInstance.hide();
  }
};

// Initialize modal
onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(false, countryName.toLowerCase()),
    configStore.switchLocale(countryName.toLowerCase()),
    preferencesStore.fetchUserPreferences(),
  ]);

  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value, {
      backdrop: "static",
      keyboard: false,
    });

    modalElement.value.addEventListener("hidden.bs.modal", () => {
      emit("close");
    });
  }
});

// Cleanup
onUnmounted(() => {
  if (modalInstance) {
    modalInstance.dispose();
  }
});

// Expose methods
defineExpose({
  show,
  hide,
});
</script>

<style scoped>
.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
}

.modal-body {
  padding: 1.5rem;
  max-height: calc(100vh - 200px);
  overflow-y: auto;
}

.form-label {
  font-weight: 500;
  color: #495057;
  margin-bottom: 0.5rem;
}

.text-danger {
  color: #dc3545 !important;
}

.text-muted {
  font-size: 12px;
  color: #6c757d;
}

.form-control:focus,
.form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Readonly styling */
.form-control:read-only {
  background-color: #f8f9fa;
  cursor: not-allowed;
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #ced4da;
  color: #6c757d;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
}

.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

/* Scrollbar styling */
.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Ensure consistent height for tax dropdown and input */
.form-control-height,
.form-select.form-control-height {
  height: 44px;
  padding: 10px 12px;
}

/* Ensure button height matches input height */
.btn-height {
  height: 44px;
  padding: 10px 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-dialog {
    margin: 0.5rem;
  }

  .modal-body {
    padding: 1rem;
    max-height: calc(100vh - 150px);
  }
}
</style>
