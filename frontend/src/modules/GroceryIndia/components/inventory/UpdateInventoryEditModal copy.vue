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
              <BarcodeScanner
                v-else
                ref="barcodeScannerRef"
                :item-id="props.item?.id"
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
                  <span class="input-group-text text-muted">
                    {{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label for="rate" class="form-label">
                  {{ $t("common.Sale Price") }}
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
                  <span class="input-group-text text-muted">
                    {{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}
                  </span>
                </div>
              </div>
            </div>

            <!-- ─── Tax Section ────────────────────────────────────────────────── -->
            <div class="row mb-3">
              <div class="col-12">
                <label class="form-label mb-2">{{ $t("inventory_page.Tax") }}</label>

                <div class="tax-row">
                  <!-- GST Combobox — uses gstArray -->
                  <div class="tax-field">
                    <div class="tax-field-label">
                      <span class="tax-label-tag gst">GST</span>
                      <span v-if="taxRates[0].rate !== null" class="tax-rate-preview">
                        {{ taxRates[0].rate }}%
                      </span>
                    </div>
                    <div class="tax-combobox">
                      <input
                        type="text"
                        class="form-control form-control-height"
                        placeholder="e.g. GST @ 18% or 18"
                        :value="taxSearch[0]"
                        @input="handleTaxInput(0, $event.target.value)"
                        @focus="taxDropdownOpen[0] = true"
                        @blur="closeTaxDropdown(0)"
                        :disabled="props.saving || configLoading"
                        autocomplete="off"
                        inputmode="decimal"
                      />
                      <ul
                        v-if="taxDropdownOpen[0] && filteredTaxes(0).length > 0"
                        class="tax-dropdown-list"
                      >
                        <li
                          v-for="tax in filteredTaxes(0)"
                          :key="tax.value"
                          @mousedown.prevent="selectTax(0, tax)"
                          :class="{ active: taxSearch[0] === tax.label }"
                        >
                          <span class="dropdown-label">{{ tax.label }}</span>
                          <span v-if="taxSearch[0] === tax.label" class="dropdown-check"
                            >✓</span
                          >
                        </li>
                      </ul>
                      <div
                        v-if="
                          taxDropdownOpen[0] &&
                          taxSearch[0] &&
                          filteredTaxes(0).length === 0
                        "
                        class="tax-dropdown-empty"
                      >
                        <i class="bi bi-calculator me-1"></i>
                        <small>
                          Custom rate: <strong>{{ taxSearch[0] }}%</strong> will be
                          applied
                        </small>
                      </div>
                    </div>
                  </div>

                  <!-- CESS Combobox — uses cessArray -->
                  <div class="tax-field">
                    <div class="tax-field-label">
                      <span class="tax-label-tag cess">CESS</span>
                      <span v-if="taxRates[1].rate !== null" class="tax-rate-preview">
                        {{ taxRates[1].rate }}%
                      </span>
                    </div>
                    <div class="tax-combobox">
                      <input
                        type="text"
                        class="form-control form-control-height"
                        placeholder="e.g. CESS @ 1% or 1"
                        :value="taxSearch[1]"
                        @input="handleTaxInput(1, $event.target.value)"
                        @focus="taxDropdownOpen[1] = true"
                        @blur="closeTaxDropdown(1)"
                        :disabled="props.saving || configLoading"
                        autocomplete="off"
                        inputmode="decimal"
                      />
                      <ul
                        v-if="taxDropdownOpen[1] && filteredTaxes(1).length > 0"
                        class="tax-dropdown-list"
                      >
                        <li
                          v-for="tax in filteredTaxes(1)"
                          :key="tax.value"
                          @mousedown.prevent="selectTax(1, tax)"
                          :class="{ active: taxSearch[1] === tax.label }"
                        >
                          <span class="dropdown-label">{{ tax.label }}</span>
                          <span v-if="taxSearch[1] === tax.label" class="dropdown-check"
                            >✓</span
                          >
                        </li>
                      </ul>
                      <div
                        v-if="
                          taxDropdownOpen[1] &&
                          taxSearch[1] &&
                          filteredTaxes(1).length === 0
                        "
                        class="tax-dropdown-empty"
                      >
                        <i class="bi bi-calculator me-1"></i>
                        <small>
                          Custom rate: <strong>{{ taxSearch[1] }}%</strong> will be
                          applied
                        </small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- ─── End Tax Section ───────────────────────────────────────────── -->
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
import { useInventoryManagementStore } from "@/modules/GroceryIndia/stores/inventoryManagement";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import BarcodeScanner from "@/modules/GroceryIndia/components/inventory/BarcodeScanner.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

// ─── Barcode Scanner ─────────────────────────────────────────────────────────
const showScanner = ref(false);
const barcodeScannerRef = ref(null);
const isRateEdited = ref(false);

const openBarcodeScanner = async () => {
  showScanner.value = true;
  await nextTick();
  barcodeScannerRef.value?.openScanner();
};

const handleBarcodeScanned = (code) => {
  console.log("✅ Modal received barcode:", code); // ← ADD
  formData.value.barcode = code;
  console.log("✅ formData.barcode after set:", formData.value.barcode); // ← ADD
};

const handleScannerCancel = () => {
  showScanner.value = false;
};

// ─── Props & Emits ───────────────────────────────────────────────────────────
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

// ─── Stores ──────────────────────────────────────────────────────────────────
const configStore = useConfigStore();
const inventoryStore = useInventoryManagementStore();
const preferencesStore = useUserPreferencesStore();

const modalElement = ref(null);
let modalInstance = null;

const { preferences } = storeToRefs(preferencesStore);
const errorMessages = ref([]);

const { unitsArray, gstArray, cessArray, loading: configLoading } = storeToRefs(
  configStore
);

const { errors: storeErrors } = storeToRefs(inventoryStore);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

// ─── Tax State ────────────────────────────────────────────────────────────────
// Reactive arrays — reassign whole objects to guarantee Vue reactivity
const taxRates = ref([
  { tax: "GST", rate: null },
  { tax: "CESS", rate: null },
]);

const taxSearch = ref(["", ""]);
const taxDropdownOpen = ref([false, false]);

// index 0 → gstArray, index 1 → cessArray
const getTaxOptions = (index) => {
  return index === 0 ? gstArray.value : cessArray.value;
};

// tax.value from store is already the numeric rate string e.g. "18"
const parseRateFromTax = (tax) => {
  if (tax.value !== undefined && tax.value !== null) return Number(tax.value);
  if (tax.rate !== undefined && tax.rate !== null) return Number(tax.rate);
  const match = String(tax.label).match(/(\d+(\.\d+)?)/);
  return match ? Number(match[1]) : 0;
};

const filteredTaxes = (index) => {
  const options = getTaxOptions(index);
  const search = taxSearch.value[index]?.toLowerCase() || "";
  if (!search) return options;
  return options.filter((t) => t.label.toLowerCase().includes(search));
};

// User picks from dropdown — reassign whole object for Vue reactivity
const selectTax = (index, tax) => {
  taxSearch.value[index] = tax.label;
  taxRates.value[index] = {
    tax: index === 0 ? "GST" : "CESS",
    rate: parseRateFromTax(tax),
  };
  taxDropdownOpen.value[index] = false;
};

// User types manually — only numeric allowed
const handleTaxInput = (index, value) => {
  const numeric = value.replace(/[^0-9.]/g, "");
  taxSearch.value[index] = numeric;
  taxDropdownOpen.value[index] = true;

  const options = getTaxOptions(index);
  const match = options.find((t) => t.label.toLowerCase() === value.toLowerCase());

  if (match) {
    taxSearch.value[index] = match.label;
    taxRates.value[index] = {
      tax: index === 0 ? "GST" : "CESS",
      rate: parseRateFromTax(match),
    };
  } else {
    taxRates.value[index] = {
      tax: index === 0 ? "GST" : "CESS",
      rate: numeric !== "" ? Number(numeric) : null,
    };
  }
};

const closeTaxDropdown = (index) => {
  setTimeout(() => {
    taxDropdownOpen.value[index] = false;
  }, 200);
};
// ─────────────────────────────────────────────────────────────────────────────

// ─── Form Data ───────────────────────────────────────────────────────────────
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
  tax1: "GST",
  rate1: 0,
  tax2: "CESS",
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

// ─── Computed Preferences ────────────────────────────────────────────────────
const isMrpRequired = computed(
  () =>
    preferences.value.preference_mrp === 1 || preferences.value.preference_mrp === true
);

const isStockQuantityRequired = computed(
  () =>
    preferences.value.preference_quantity === 1 ||
    preferences.value.preference_quantity === true
);

const showHsnField = computed(
  () =>
    preferences.value.preference_hsn === 1 || preferences.value.preference_hsn === true
);

const isHsnRequired = computed(
  () =>
    preferences.value.preference_hsn === 1 || preferences.value.preference_hsn === true
);

const showBarcodeField = computed(
  () =>
    preferences.value.preference_barcode === 1 ||
    preferences.value.preference_barcode === true
);

const isBarcodeSet = computed(() => {
  const barcode = props.item?.barcode;
  return !!barcode && barcode.trim() !== "" && barcode !== "–";
});

// ─── Form Validation ─────────────────────────────────────────────────────────
const isFormValid = computed(() => {
  return formData.value.name && formData.value.shortUnit && formData.value.rate > 0;
});

// ─── Error Handling ──────────────────────────────────────────────────────────
const clearErrors = () => {
  errorMessages.value = [];
  inventoryStore.clearErrors();
};

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

// ─── Helper: initialise tax comboboxes from a rate value ─────────────────────
const initTaxSearch = (index, rateValue) => {
  if (
    rateValue === null ||
    rateValue === undefined ||
    rateValue === "" ||
    rateValue === "–"
  ) {
    taxRates.value[index] = { tax: index === 0 ? "GST" : "CESS", rate: null };
    taxSearch.value[index] = "";
    return;
  }

  const numericRate = parseFloat(rateValue);
  if (isNaN(numericRate)) {
    taxRates.value[index] = { tax: index === 0 ? "GST" : "CESS", rate: null };
    taxSearch.value[index] = "";
    return;
  }

  const options = getTaxOptions(index);
  // Try to find a matching label in the store options
  const match = options.find((t) => Math.abs(parseRateFromTax(t) - numericRate) < 0.001);

  if (match) {
    taxSearch.value[index] = match.label;
  } else {
    taxSearch.value[index] = String(numericRate);
  }

  taxRates.value[index] = {
    tax: index === 0 ? "GST" : "CESS",
    rate: numericRate,
  };
};

// ─── Watch Item Prop ──────────────────────────────────────────────────────────
watch(
  () => props.item,
  (newItem) => {
    clearErrors();

    if (newItem) {
      console.log("Editing item:", newItem);

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
        tax1: newItem.tax1 || "GST",
        rate1: newItem.rate1 || 0,
        tax2: hasTax2 ? newItem.tax2 : "CESS",
        rate2: hasRate2 ? parseFloat(newItem.rate2) : 0,
        tags: newItem.tags || [],
        barcode: newItem.barcode,
      };

      // Initialise GST combobox
      initTaxSearch(0, newItem.rate1 ?? null);
      // Initialise CESS combobox
      initTaxSearch(1, hasRate2 ? newItem.rate2 : null);
    }
  },
  { immediate: true, deep: true }
);

// ─── Submit ───────────────────────────────────────────────────────────────────
// const handleSubmit = async () => {
//   if (!isFormValid.value || props.saving) return;

//   clearErrors();

//   const payload = {
//     ...formData.value,
//     barcode: formData.value.barcode || null,
//     tax1: "GST",
//     rate1: taxRates.value[0]?.rate ?? 0,
//     tax2: "CESS",
//     rate2: taxRates.value[1]?.rate ?? 0,
//   };

//   console.log("📦 handleSubmit payload.barcode:", payload.barcode); // ← ADD
//   emit("save", payload);

//   try {
//     emit("save", payload);
//   } catch (error) {
//     console.error("Error saving item:", error);
//   }
// };

const handleSubmit = async () => {
  if (!isFormValid.value || props.saving) return;

  // ─── Force scan if scanner is open and has an unconfirmed value ───────────
  if (showScanner.value && barcodeScannerRef.value) {
    const rawValue = barcodeScannerRef.value.barcodeValue?.trim();

    if (rawValue && !barcodeScannerRef.value.scanned) {
      // Trigger the scan and wait for it to complete
      await barcodeScannerRef.value.triggerScan();

      // If scan failed (duplicate/invalid), block submit
      if (barcodeScannerRef.value.barcodeError || barcodeScannerRef.value.isDuplicate) {
        return;
      }

      // Wait for formData.barcode to be updated via handleBarcodeScanned
      await nextTick();
    }
  }
  // ─────────────────────────────────────────────────────────────────────────

  clearErrors();

  const payload = {
    ...formData.value,
    tax1: "GST",
    rate1: taxRates.value[0]?.rate ?? 0,
    tax2: "CESS",
    rate2: taxRates.value[1]?.rate ?? 0,
    barcode: formData.value.barcode || null,
  };

  console.log("📦 handleSubmit payload.barcode:", payload.barcode);
  emit("save", payload);
};

// ─── Modal Controls ───────────────────────────────────────────────────────────
const handleClose = () => {
  showScanner.value = false;
  if (props.saving) return;
  clearErrors();
  hide();
  emit("close");
};

const show = () => {
  if (modalInstance) modalInstance.show();
};

const hide = () => {
  if (modalInstance) modalInstance.hide();
};

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(),
    configStore.switchLocale(countryName),
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

onUnmounted(() => {
  if (modalInstance) modalInstance.dispose();
});

defineExpose({ show, hide });
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

.form-control:focus,
.form-select:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
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

/* ── Tax Row ─────────────────────────────────────────────────────────────── */
.tax-row {
  display: flex;
  align-items: flex-end;
  gap: 16px;
}

.tax-field {
  flex: 1;
  min-width: 0;
}

.tax-field-label {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 6px;
}

/* ── Tax Combobox ──────────────────────────────────────────────────────────── */
.tax-combobox {
  position: relative;
}

.tax-dropdown-list,
.tax-dropdown-empty {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  z-index: 1060;
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.tax-dropdown-list {
  max-height: 200px;
  overflow-y: auto;
  list-style: none;
  margin: 0;
  padding: 4px 0;
}

.tax-dropdown-list li {
  padding: 9px 14px;
  cursor: pointer;
  font-size: 14px;
  color: #333;
  transition: background 0.15s, color 0.15s;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.tax-dropdown-list li:hover {
  background-color: #e8f0fe;
  color: #0066cc;
}

.tax-dropdown-list li.active {
  background-color: #dbeafe;
  color: #0052a3;
  font-weight: 600;
}

.dropdown-check {
  font-size: 12px;
  color: #0052a3;
}

.tax-dropdown-list::-webkit-scrollbar {
  width: 4px;
}
.tax-dropdown-list::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}
.tax-dropdown-list::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.tax-dropdown-empty {
  padding: 9px 14px;
  color: #6c757d;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ── Badges ──────────────────────────────────────────────────────────────── */
.tax-label-tag {
  display: inline-flex;
  align-items: center;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.6px;
  padding: 2px 8px;
  border-radius: 20px;
  text-transform: uppercase;
  line-height: 1.4;
}

.tax-label-tag.gst {
  background-color: #dbeafe;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.tax-label-tag.cess {
  background-color: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
}

.tax-rate-preview {
  font-size: 12px;
  font-weight: 600;
  color: #198754;
}

/* ── Consistent Heights ──────────────────────────────────────────────────── */
.form-control-height,
.form-select.form-control-height {
  height: 44px;
  padding: 10px 12px;
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
  .tax-row {
    flex-direction: column;
    gap: 16px;
  }
}
</style>
