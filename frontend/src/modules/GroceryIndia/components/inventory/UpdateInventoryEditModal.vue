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
    <div
      class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down mx-auto"
    >
      <div class="modal-content border-0 rounded-4 shadow modal-content-visible">
        <div class="modal-header bg-light border-bottom">
          <div>
            <h5 class="modal-title fw-bold mb-1" id="itemDetailsModalLabel">
              {{ $t("inventory_page.Item Details") }}
            </h5>
            <p class="text-muted mb-0 small">
              {{ $t("common.Update item information and pricing details") }}
            </p>
          </div>

          <button
            type="button"
            class="btn-close"
            @click="handleClose"
            aria-label="Close"
          ></button>
        </div>

        <div class="modal-body bg-light p-0 position-static modal-body-visible">
          <FormErrorBox :messages="errorMessages" @clear="clearErrors" class="m-3 mb-0" />

          <div class="card border-0 shadow-sm rounded-4 m-3 card-visible">
            <div class="card-body p-4">
              <p
                class="text-muted small mb-4"
                v-html="
                  $t('common.mandatoryfieldsnote', {
                    star: `<span class='text-danger'>*</span>`,
                  })
                "
              ></p>

              <form @submit.prevent="handleSubmit">
                <template v-if="showBarcodeField && !isBarcodeSet">
                  <div class="mb-4">
                    <div
                      v-if="!showScanner"
                      class="d-flex align-items-center gap-2 border rounded-3 bg-white px-3 py-3"
                    >
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
                </template>

                <template v-else-if="isBarcodeSet">
                  <div class="mb-4">
                    <label for="barcode" class="form-label fw-medium">
                      {{ $t("common.Barcode") }}
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      id="barcode"
                      v-model="formData.barcode"
                      disabled
                    />
                  </div>
                </template>

                <div class="row g-3 mb-4">
                  <div class="col-12 col-sm-6 col-md-4">
                    <label for="itemName" class="form-label fw-medium">
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
                      placeholder="Enter item name"
                    />
                  </div>

                  <div class="col-12 col-sm-6 col-md-4">
                    <label for="unit" class="form-label fw-medium">
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
                      <option
                        v-for="unit in unitsArray"
                        :key="unit.value"
                        :value="unit.value"
                      >
                        {{ unit.label }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Category + SKU -->
                <div class="row g-3 mb-3">
                  <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label">
                      {{ $t("common.Category") }} <span class="text-danger">*</span>
                    </label>
                    <select
                      class="form-select"
                      v-model="formData.category_id"
                      :disabled="loading || configLoading"
                    >
                      <option :value="null">{{ $t("common.Select Category") }}</option>
                      <option
                        v-for="category in itemCategories"
                        :key="category.id"
                        :value="category.id"
                      >
                        {{ category.name }}
                      </option>
                    </select>
                  </div>

                  <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label">
                      {{ $t("common.SKU") }} <span class="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="SKU"
                      v-model="formData.sku"
                      :disabled="loading || configLoading"
                    />
                    <small v-if="isGeneratingSku" class="text-muted d-block mt-1">
                      Generating SKU...
                    </small>
                    <small v-else-if="validatingSku" class="text-muted d-block mt-1">
                      Checking SKU...
                    </small>
                    <small
                      v-else-if="skuStatus"
                      class="d-block mt-1"
                      :class="skuStatus.valid ? 'text-success' : 'text-danger'"
                    >
                      {{ skuStatus.message }}
                    </small>
                  </div>
                </div>

                <div class="row g-3 mb-4">
                  <div class="col-12 col-sm-6 col-md-4">
                    <label for="stockQuantity" class="form-label fw-medium">
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
                      placeholder="0"
                    />
                  </div>

                  <div class="col-12 col-sm-6 col-md-4">
                    <label for="minStockAlert" class="form-label fw-medium">
                      {{ $t("common.Minimum Stock Alert") }}
                    </label>
                    <input
                      type="number"
                      step="0.01"
                      class="form-control"
                      id="minStockAlert"
                      v-model="formData.minStockAlert"
                      :disabled="props.saving"
                      placeholder="0"
                    />
                  </div>
                </div>

                <div class="row g-3 mb-4">
                  <div class="col-12 col-sm-6 col-md-4">
                    <label for="hsnCode" class="form-label fw-medium">
                      {{ $t("inventory_page.HSN/ SAC Code") }}
                      <span v-if="isHsnRequired" class="text-danger">*</span>
                    </label>
                    <input
                      type="text"
                      class="form-control"
                      id="hsnCode"
                      v-model="formData.hsnCode"
                      :disabled="props.saving"
                      placeholder="Enter HSN / SAC code"
                    />
                  </div>
                </div>

                <div class="card border rounded-4 mb-4">
                  <div class="card-body p-3 p-md-4">
                    <h6 class="fw-bold mb-1">{{ $t("common.Pricing") }}</h6>
                    <p class="text-muted small mb-3">
                      {{ $t("common.Set MRP, sale price and purchase price") }}
                    </p>

                    <div class="row g-3">
                      <div class="col-12 col-sm-12 col-md-4">
                        <label for="mrp" class="form-label fw-medium">
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
                            placeholder="0.00"
                          />
                          <span class="input-group-text">
                            {{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}
                          </span>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6 col-md-4">
                        <label for="rate" class="form-label fw-medium">
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
                            placeholder="0.00"
                          />
                          <span class="input-group-text">
                            {{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}
                          </span>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6 col-md-4">
                        <label for="purchasePrice" class="form-label fw-medium">
                          {{ $t("common.Purchase Price") }}
                        </label>
                        <div class="input-group">
                          <span class="input-group-text">{{ currency }}</span>
                          <input
                            type="number"
                            step="0.01"
                            class="form-control"
                            id="purchasePrice"
                            v-model="formData.purchase_price"
                            :disabled="props.saving"
                            placeholder="0.00"
                          />
                          <span class="input-group-text">
                            {{ $t("common.per") }} {{ formData.shortUnit || "PCS" }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card border rounded-4 tax-section-card">
                  <div class="card-body p-3 p-md-4 overflow-visible">
                    <h6 class="fw-bold mb-1">{{ $t("inventory_page.Tax") }}</h6>
                    <p class="text-muted small mb-3">
                      {{ $t("common.Select or type custom GST and CESS rates") }}
                    </p>

                    <div class="row g-3 overflow-visible">
                      <div class="col-12 col-sm-6 col-md-4 overflow-visible">
                        <div class="d-flex align-items-center gap-2 mb-2 tax-label-row">
                          <span
                            class="badge rounded-pill text-primary bg-primary-subtle border"
                          >
                            GST
                          </span>
                          <span
                            v-if="taxRates[0].rate !== null"
                            class="fw-semibold text-success small"
                          >
                            {{ taxRates[0].rate }}%
                          </span>
                        </div>

                        <div class="position-relative tax-combobox">
                          <input
                            type="text"
                            class="form-control"
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
                            class="list-group position-absolute start-0 end-0 mt-1 shadow tax-dropdown-list"
                          >
                            <li
                              v-for="tax in filteredTaxes(0)"
                              :key="tax.value"
                              @mousedown.prevent="selectTax(0, tax)"
                              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                              :class="{ active: taxSearch[0] === tax.label }"
                            >
                              <span>{{ tax.label }}</span>
                              <span v-if="taxSearch[0] === tax.label">✓</span>
                            </li>
                          </ul>

                          <div
                            v-if="
                              taxDropdownOpen[0] &&
                              taxSearch[0] &&
                              filteredTaxes(0).length === 0
                            "
                            class="position-absolute start-0 end-0 mt-1 bg-white border rounded-3 shadow-sm px-3 py-2 tax-dropdown-empty"
                          >
                            <small class="text-muted">
                              Custom rate: <strong>{{ taxSearch[0] }}%</strong> will be
                              applied
                            </small>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-sm-6 col-md-4 overflow-visible">
                        <div class="d-flex align-items-center gap-2 mb-2 tax-label-row">
                          <span
                            class="badge rounded-pill text-warning-emphasis bg-warning-subtle border"
                          >
                            CESS
                          </span>
                          <span
                            v-if="taxRates[1].rate !== null"
                            class="fw-semibold text-success small"
                          >
                            {{ taxRates[1].rate }}%
                          </span>
                        </div>

                        <div class="position-relative tax-combobox">
                          <input
                            type="text"
                            class="form-control"
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
                            class="list-group position-absolute start-0 end-0 mt-1 shadow tax-dropdown-list"
                          >
                            <li
                              v-for="tax in filteredTaxes(1)"
                              :key="tax.value"
                              @mousedown.prevent="selectTax(1, tax)"
                              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                              :class="{ active: taxSearch[1] === tax.label }"
                            >
                              <span>{{ tax.label }}</span>
                              <span v-if="taxSearch[1] === tax.label">✓</span>
                            </li>
                          </ul>

                          <div
                            v-if="
                              taxDropdownOpen[1] &&
                              taxSearch[1] &&
                              filteredTaxes(1).length === 0
                            "
                            class="position-absolute start-0 end-0 mt-1 bg-white border rounded-3 shadow-sm px-3 py-2 tax-dropdown-empty"
                          >
                            <small class="text-muted">
                              Custom rate: <strong>{{ taxSearch[1] }}%</strong> will be
                              applied
                            </small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light border-top">
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
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";

const { t } = useI18n();

const showScanner = ref(false);
const barcodeScannerRef = ref(null);
const isRateEdited = ref(false);

const userStore = useUserDetailsStore();

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

const { preferences } = storeToRefs(preferencesStore);
const errorMessages = ref([]);

const { unitsArray, gstArray, cessArray, loading: configLoading } = storeToRefs(
  configStore
);
const { errors: storeErrors } = storeToRefs(inventoryStore);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

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

// const handleTaxInput = (index, value) => {
//   const numeric = value.replace(/[^0-9.]/g, "");
//   taxSearch.value[index] = numeric;
//   taxDropdownOpen.value[index] = true;

//   const options = getTaxOptions(index);
//   const match = options.find((t) => t.label.toLowerCase() === value.toLowerCase());

//   if (match) {
//     taxSearch.value[index] = match.label;
//     taxRates.value[index] = {
//       tax: index === 0 ? "GST" : "CESS",
//       rate: parseRateFromTax(match),
//     };
//   } else {
//     taxRates.value[index] = {
//       tax: index === 0 ? "GST" : "CESS",
//       rate: numeric !== "" ? Number(numeric) : null,
//     };
//   }
// };

const handleTaxInput = (index, value) => {
  const raw = String(value ?? "").trim();

  taxDropdownOpen.value[index] = true;

  const previousSearch = taxSearch.value[index] ?? "";
  const previousRate = taxRates.value[index]?.rate ?? null;

  const options = getTaxOptions(index);
  const match = options.find((t) => t.label.toLowerCase() === raw.toLowerCase());

  if (match) {
    const matchedRate = parseRateFromTax(match);

    if (matchedRate >= 0 && matchedRate <= 100) {
      taxSearch.value[index] = match.label;
      taxRates.value[index] = {
        tax: index === 0 ? "GST" : "CESS",
        rate: matchedRate,
      };
    } else {
      taxSearch.value[index] = previousSearch;
      taxRates.value[index] = {
        tax: index === 0 ? "GST" : "CESS",
        rate: previousRate,
      };
    }
    return;
  }

  let sanitized = raw.replace(/[^\d.]/g, "");
  sanitized = sanitized.replace(/^\./, "");

  const parts = sanitized.split(".");
  if (parts.length > 2) {
    sanitized = parts[0] + "." + parts.slice(1).join("");
  }

  if (sanitized.includes(".")) {
    const [integerPart, decimalPart = ""] = sanitized.split(".");
    sanitized = integerPart + "." + decimalPart.slice(0, 2);
  }

  if (sanitized === "") {
    taxSearch.value[index] = "";
    taxRates.value[index] = {
      tax: index === 0 ? "GST" : "CESS",
      rate: null,
    };
    return;
  }

  const isValidFormat = /^\d+(\.\d{0,2})?$/.test(sanitized);
  if (!isValidFormat) {
    taxSearch.value[index] = previousSearch;
    taxRates.value[index] = {
      tax: index === 0 ? "GST" : "CESS",
      rate: previousRate,
    };
    return;
  }

  const numericRate = Number(sanitized);

  if (numericRate > 100) {
    taxSearch.value[index] = previousSearch;
    taxRates.value[index] = {
      tax: index === 0 ? "GST" : "CESS",
      rate: previousRate,
    };
    return;
  }

  taxSearch.value[index] = sanitized;
  taxRates.value[index] = {
    tax: index === 0 ? "GST" : "CESS",
    rate: numericRate,
  };
};

const closeTaxDropdown = (index) => {
  setTimeout(() => {
    taxDropdownOpen.value[index] = false;
  }, 200);
};

const formData = ref({
  id: null,
  sku: "",
  category_id: "",
  name: "",
  stock: 0,
  minStockAlert: 0,
  shortUnit: null,
  fullUnit: "",
  hsnCode: "",
  mrp: 0,
  rate: 0,
  purchase_price: 0,
  tax1: "GST",
  rate1: 0,
  tax2: "CESS",
  rate2: 0,
  tags: [],
  barcode: "",
});

const { item_categories } = storeToRefs(userStore);
const itemCategories = computed(() => item_categories.value || []);

watch(
  () => formData.value.mrp,
  (newValue) => {
    if (!isRateEdited.value) {
      formData.value.rate = newValue;
    }
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

watch(
  () => formData.value.shortUnit,
  (newShortUnit) => {
    const selectedUnitObj = unitsArray.value.find((unit) => unit.value === newShortUnit);
    formData.value.fullUnit = selectedUnitObj?.fullUnit || "";
  }
);

const isSKURequired = computed(
  () =>
    preferences.value.preference_sku === 1 || preferences.value.preference_sku === true
);

const isCategoryRequired = computed(
  () =>
    preferences.value.preference_category === 1 ||
    preferences.value.preference_category === true
);

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
  return !!barcode && String(barcode).trim() !== "" && barcode !== "–";
});

const isFormValid = computed(() => {
  return (
    !!formData.value.name && !!formData.value.shortUnit && Number(formData.value.rate) > 0
  );
});

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
  const match = options.find((t) => Math.abs(parseRateFromTax(t) - numericRate) < 0.001);

  taxSearch.value[index] = match ? match.label : String(numericRate);

  taxRates.value[index] = {
    tax: index === 0 ? "GST" : "CESS",
    rate: numericRate,
  };
};

watch(
  () => props.item,
  (newItem) => {
    clearErrors();

    if (newItem) {
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
        sku: newItem.sku == "NA" ? "" : newItem.sku,
        category_id: newItem.category_id ?? 1,
        name: newItem.name || "",
        stock: newItem.stock || 0,
        minStockAlert: newItem.minStockAlert || 0,
        shortUnit: newItem.shortUnit || null,
        fullUnit: newItem.fullUnit || "",
        hsnCode: !newItem.hsnCode || newItem.hsnCode === "–" ? "" : newItem.hsnCode,
        mrp: newItem.mrp || 0,
        rate: newItem.rate || 0,
        purchase_price: newItem.purchase_price || 0,
        tax1: newItem.tax1 || "GST",
        rate1: newItem.rate1 || 0,
        tax2: hasTax2 ? newItem.tax2 : "CESS",
        rate2: hasRate2 ? parseFloat(newItem.rate2) : 0,
        tags: newItem.tags || [],
        barcode: newItem.barcode || "",
      };

      initTaxSearch(0, newItem.rate1 ?? null);
      initTaxSearch(1, hasRate2 ? newItem.rate2 : null);
      isRateEdited.value = false;
    }
  },
  { immediate: true, deep: true }
);

const handleSubmit = async () => {
  if (!isFormValid.value || props.saving) return;

  if (showScanner.value && barcodeScannerRef.value) {
    const rawValue = barcodeScannerRef.value.barcodeValue?.trim();

    if (rawValue && !barcodeScannerRef.value.scanned) {
      await barcodeScannerRef.value.triggerScan();

      if (barcodeScannerRef.value.barcodeError || barcodeScannerRef.value.isDuplicate) {
        return;
      }

      await nextTick();
    }
  }

  clearErrors();

  const payload = {
    ...formData.value,
    tax1: "GST",
    rate1: taxRates.value[0]?.rate ?? 0,
    tax2: "CESS",
    rate2: taxRates.value[1]?.rate ?? 0,
    barcode: formData.value.barcode || null,
  };

  emit("save", payload);
};

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

onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(),
    configStore.switchLocale(countryName),
    preferencesStore.fetchUserPreferences(),
    userStore.fetchCategories(),
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
.tax-section-card,
.tax-section-card .card-body,
.tax-section-card .row,
.tax-section-card [class*="col-"],
.tax-combobox {
  overflow: visible !important;
}

.tax-dropdown-list,
.tax-dropdown-empty {
  z-index: 1080;
  max-height: 220px;
  overflow-y: auto;
}

.tax-label-row {
  min-height: 24px;
}

.modal-dialog {
  margin-left: auto !important;
  margin-right: auto !important;
}

.modal-content-visible {
  overflow: visible !important;
}

.modal-body-visible {
  overflow: visible !important;
}

.card-visible {
  overflow: visible !important;
}

@media (max-width: 575.98px) {
  .modal-dialog {
    margin: 0.5rem auto !important;
  }
}
</style>
