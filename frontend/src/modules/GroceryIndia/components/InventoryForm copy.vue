<script setup>
import { ref, watch, nextTick, onMounted, computed, getCurrentInstance } from "vue";
import { useInventoryStore } from "@/modules/GroceryIndia/stores/inventory";
import { useConfigStore } from "@/stores/config";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { storeToRefs } from "pinia";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import BarcodeScanner from "@/modules/GroceryIndia/components/inventory/BarcodeScanner.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

// Barcode scanner
const showScanner = ref(false);
const barcodeScannerRef = ref(null);
const isRateEdited = ref(false);

const openBarcodeScanner = async () => {
  showScanner.value = true;
  await nextTick();
  barcodeScannerRef.value?.openScanner();
};

const handleBarcodeScanned = (code) => {
  formData.value.barcode = code;
};

const handleScannerCancel = () => {
  showScanner.value = false;
};

const emit = defineEmits(["submit", "cancel"]);

const inventoryStore = useInventoryStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();

const { loading, errors, successMessage } = storeToRefs(inventoryStore);
const { unitsArray, gstArray, cessArray, loading: configLoading } = storeToRefs(
  configStore
);
const { preferences } = storeToRefs(preferencesStore);

const formData = ref({
  itemName: "",
  stockQuantity: null,
  minStockAlert: null,
  selectedUnit: null,
  hsnCode: "",
  purchase_price: null,
  mrp: null,
  rate: null,
  barcode: "",
});

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

watch(
  () => formData.value.mrp,
  (newValue) => {
    if (!isRateEdited.value) formData.value.rate = newValue;
  }
);

watch(
  () => formData.value.rate,
  (newValue, oldValue) => {
    if (newValue !== oldValue && newValue !== formData.value.mrp) {
      isRateEdited.value = true;
    }
  }
);

// Tax State
const taxRates = ref([
  { tax: "GST", rate: null },
  { tax: "CESS", rate: null },
]);

const taxSearch = ref(["", ""]);
const taxDropdownOpen = ref([false, false]);

const getTaxOptions = (index) => {
  return index === 0 ? gstArray.value : cessArray.value;
};

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

const selectTax = (index, tax) => {
  taxSearch.value[index] = tax.label;
  taxRates.value[index] = {
    tax: index === 0 ? "GST" : "CESS",
    rate: parseRateFromTax(tax),
  };
  taxDropdownOpen.value[index] = false;
};

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

const isMrpRequired = computed(
  () =>
    preferences.value.preference_mrp === 1 || preferences.value.preference_mrp === true
);

const isPurchasePriceRequired = computed(
  () =>
    preferences.value.preference_purchase_price === 1 ||
    preferences.value.preference_purchase_price === true
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

const showBarcodeField = computed(
  () =>
    preferences.value.preference_barcode === 1 ||
    preferences.value.preference_barcode === true
);

const isHsnRequired = computed(
  () =>
    preferences.value.preference_hsn === 1 || preferences.value.preference_hsn === true
);

const allErrors = computed(() => [
  ...errors.value,
  ...configStore.errors,
  ...preferencesStore.errors,
]);

onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(),
    configStore.switchLocale(countryName),
    preferencesStore.fetchUserPreferences(),
  ]);
});

const handleSubmit = async () => {
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
    purchase_price: formData.value.purchase_price,
    mrp: formData.value.mrp,
    rate: formData.value.rate,
    barcode: formData.value.barcode,
    tax1: "GST",
    rate1: taxRates.value[0]?.rate ?? 0,
    tax2: "CESS",
    rate2: taxRates.value[1]?.rate ?? 0,
  };

  const result = await inventoryStore.addItem(data);

  if (result.success) {
    emit("submit", result.data);

    formData.value = {
      itemName: "",
      stockQuantity: null,
      minStockAlert: null,
      selectedUnit: null,
      hsnCode: "",
      purchase_price: null,
      mrp: null,
      rate: null,
      barcode: "",
    };

    taxRates.value = [
      { tax: "GST", rate: null },
      { tax: "CESS", rate: null },
    ];
    taxSearch.value = ["", ""];
    taxDropdownOpen.value = [false, false];
    showScanner.value = false;
    isRateEdited.value = false;

    setTimeout(() => inventoryStore.clearSuccessMessage(), 3000);
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
    <div v-if="allErrors.length > 0" class="mb-3">
      <FormErrorBox :messages="allErrors" @clear="handleClearErrors" />
    </div>

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

    <div v-if="configLoading" class="text-center py-3 mb-3">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">{{ $t("common.Loading configuration") }}...</span>
      </div>
      <span class="ms-2 text-muted">{{ $t("common.Loading configuration") }}...</span>
    </div>

    <form @submit.prevent="handleSubmit" class="inventory-form">
      <div class="mb-3" v-if="showBarcodeField">
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
          @scanned="handleBarcodeScanned"
          @cancel="handleScannerCancel"
        />
      </div>

      <div class="inventory-form-groups">
        <!-- Item name + Unit -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ $t("common.Item Name") }} <span class="text-danger">*</span>
            </label>
            <input
              type="text"
              class="form-control"
              placeholder="Amul Butter"
              v-model="formData.itemName"
              :disabled="loading || configLoading"
            />
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">Unit <span class="text-danger">*</span></label>
            <select
              class="form-select"
              v-model="formData.selectedUnit"
              :disabled="loading || configLoading"
            >
              <option :value="null">{{ $t("inventory_page.Select Unit") }}</option>
              <option v-for="unit in unitsArray" :key="unit.value" :value="unit.value">
                {{ unit.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Stock quantity + minimum stock quantity -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
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

          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">{{
              $t("inventory_page.Minimum Stock Alert")
            }}</label>
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
        </div>

        <!-- Optional HSN -->
        <div v-if="showHsnField" class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ $t("inventory_page.HSN/ SAC Code") }}
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
        </div>

        <!-- Pricing -->
        <div class="pricing-block mb-3">
          <!-- Desktop / md+ : all 3 in one row -->
          <div class="row g-3 d-none d-md-flex">
            <div class="col-md-4">
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

            <div class="col-md-4">
              <label class="form-label">
                {{ $t("common.Sale Price") }} <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text">{{ currency }}</span>
                <input
                  type="number"
                  class="form-control"
                  placeholder="Sale Price"
                  v-model.number="formData.rate"
                  step="0.01"
                  min="0"
                  :disabled="loading || configLoading"
                />
              </div>
            </div>

            <div v-if="isPurchasePriceRequired" class="col-md-4">
              <label class="form-label">
                {{ $t("common.Purchase Price") }}
                <span v-if="isPurchasePriceRequired" class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text">{{ currency }}</span>
                <input
                  type="number"
                  class="form-control"
                  placeholder="Purchase Price"
                  v-model.number="formData.purchase_price"
                  step="0.01"
                  min="0"
                  :disabled="loading || configLoading"
                />
              </div>
            </div>
          </div>

          <!-- Small screen / sm only : MRP one row -->
          <div class="row g-3 d-none d-sm-flex d-md-none mb-3">
            <div class="col-sm-6">
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
          </div>

          <!-- Small screen / sm only : Sale + Purchase same row -->
          <div class="row g-3 d-none d-sm-flex d-md-none">
            <div class="col-sm-6">
              <label class="form-label">
                {{ $t("common.Sale Price") }} <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text">{{ currency }}</span>
                <input
                  type="number"
                  class="form-control"
                  placeholder="Sale Price"
                  v-model.number="formData.rate"
                  step="0.01"
                  min="0"
                  :disabled="loading || configLoading"
                />
              </div>
            </div>

            <div v-if="isPurchasePriceRequired" class="col-sm-6">
              <label class="form-label">
                {{ $t("common.Purchase Price") }}
                <span v-if="isPurchasePriceRequired" class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text">{{ currency }}</span>
                <input
                  type="number"
                  class="form-control"
                  placeholder="Purchase Price"
                  v-model.number="formData.purchase_price"
                  step="0.01"
                  min="0"
                  :disabled="loading || configLoading"
                />
              </div>
            </div>
          </div>

          <!-- Mobile / xs : all one column -->
          <div class="d-block d-sm-none">
            <div class="row g-3 mb-3">
              <div class="col-12">
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
            </div>

            <div class="row g-3 mb-3">
              <div class="col-12">
                <label class="form-label">
                  {{ $t("common.Sale Price") }} <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    placeholder="Sale Price"
                    v-model.number="formData.rate"
                    step="0.01"
                    min="0"
                    :disabled="loading || configLoading"
                  />
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">
                  {{ $t("common.Purchase Price") }}
                  <span v-if="isPurchasePriceRequired" class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    placeholder="Purchase Price"
                    v-model.number="formData.purchase_price"
                    step="0.01"
                    min="0"
                    :disabled="loading || configLoading"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tax in one row -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label mb-2">{{ $t("inventory_page.Tax") }}</label>
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
                :disabled="loading || configLoading"
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
                  <span v-if="taxSearch[0] === tax.label" class="dropdown-check">✓</span>
                </li>
              </ul>
              <div
                v-if="taxDropdownOpen[0] && taxSearch[0] && filteredTaxes(0).length === 0"
                class="tax-dropdown-empty"
              >
                <i class="bi bi-calculator me-1"></i>
                <small>
                  Custom rate: <strong>{{ taxSearch[0] }}%</strong> will be applied
                </small>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label mb-2 d-none d-sm-block">&nbsp;</label>
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
                :disabled="loading || configLoading"
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
                  <span v-if="taxSearch[1] === tax.label" class="dropdown-check">✓</span>
                </li>
              </ul>
              <div
                v-if="taxDropdownOpen[1] && taxSearch[1] && filteredTaxes(1).length === 0"
                class="tax-dropdown-empty"
              >
                <i class="bi bi-calculator me-1"></i>
                <small>
                  Custom rate: <strong>{{ taxSearch[1] }}%</strong> will be applied
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>

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

.form-control-height {
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

.tax-field-label {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 6px;
  min-height: 24px;
}

.tax-combobox {
  position: relative;
}

.tax-dropdown-list,
.tax-dropdown-empty {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  z-index: 1050;
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

.text-danger {
  color: #dc3545;
  font-weight: 600;
}

@media (max-width: 575.98px) {
  .form-actions {
    flex-direction: column;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
