<!-- components/inventory/BarcodeScannerInput.vue -->
<template>
  <div class="barcode-scanner-wrapper">
    <!-- ── FormErrorBox ───────────────────────────────────────────────── -->
    <div v-if="allErrors.length > 0" class="mb-3">
      <FormErrorBox :messages="allErrors" @clear="handleClearErrors" />
    </div>

    <!-- ── Success Alert ─────────────────────────────────────────────── -->
    <div
      v-if="successMessage"
      class="alert alert-success alert-dismissible fade show mb-3"
      role="alert"
    >
      <i class="bi bi-check-circle-fill me-2"></i>
      {{ successMessage }}
      <button type="button" class="btn-close" @click="clearSuccessMsg"></button>
    </div>

    <!-- ── Loading Config ────────────────────────────────────────────── -->
    <div v-if="configLoading" class="text-center py-3 mb-3">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden"
          >{{ t("common.Loading configuration") }}...</span
        >
      </div>
      <span class="ms-2 text-muted"
        >{{ t("common.Loading configuration") }}...</span
      >
    </div>

    <!-- ── State: loading (fetching product) ─────────────────────────── -->
    <div
      v-if="state === 'loading'"
      class="d-flex align-items-center gap-2 py-2"
    >
      <div
        class="spinner-border spinner-border-sm text-primary"
        role="status"
      ></div>
      <span class="text-muted small">
        {{ t("common.Fetching details for barcode") }}:
        <code>{{ scannedBarcode }}</code
        >...
      </span>
    </div>

    <!-- ── State: result — editable form ──────────────────────────────── -->
    <div v-if="state === 'result'">
      <!-- Top bar: Scan Again + status badge -->
      <div
        class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3"
      >
        <button class="btn btn-sm btn-outline-secondary" @click="resetScanner">
          <i class="bi bi-upc-scan me-1"></i>{{ t("common.Scan Again") }}
        </button>
        <div class="d-flex align-items-center gap-2">
          <span
            v-if="isExistingProduct"
            class="badge bg-info-subtle text-info border border-info-subtle"
          >
            <i class="bi bi-pencil-square me-1"></i>{{ t("common.Editing") }}
          </span>
          <span
            v-else
            class="badge bg-warning-subtle text-warning border border-warning-subtle"
          >
            <i class="bi bi-plus-circle me-1"></i>{{ t("common.New Product") }}
          </span>
        </div>
      </div>

      <!-- Editable form -->
      <div class="row g-3">
        <!-- ── Barcode ─────────────────────────────────────────────── -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            {{ t("common.Barcode") }} <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <input
              type="text"
              class="form-control barcode-input"
              placeholder="Scan or type barcode"
              v-model="scannedBarcode"
              ref="barcodeInputRef"
              :disabled="isSaving"
              @keydown.enter.prevent="fetchProduct(scannedBarcode)"
            />
            <button
              class="btn btn-outline-secondary"
              type="button"
              @click="fetchProduct(scannedBarcode)"
              :disabled="isSaving || !scannedBarcode"
              title="Lookup barcode"
            >
              <i class="bi bi-search"></i>
            </button>
          </div>
          <div class="form-text text-muted">
            <i class="bi bi-info-circle me-1"></i>
            {{
              t("common.Edit barcode and press Enter or click search to lookup")
            }}
          </div>
        </div>

        <!-- Item Name -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            {{ t("common.Item Name") }} <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            placeholder="Amul Butter"
            v-model="editForm.itemName"
            :disabled="isSaving"
          />
        </div>

        <!-- Unit -->
        <div class="col-12 col-md-6">
          <label class="form-label">{{ t("inventory_page.Unit") }}</label>
          <select
            class="form-select"
            v-model="editForm.selectedUnit"
            :disabled="isSaving || configLoading"
          >
            <option :value="null">{{ t("inventory_page.Select Unit") }}</option>
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
            {{ t("inventory_page.Stock Quantity") }}
            <span v-if="isStockQuantityRequired" class="text-danger">*</span>
          </label>
          <input
            type="number"
            class="form-control"
            :placeholder="t('inventory_page.Stock Quantity')"
            v-model.number="editForm.stockQuantity"
            :disabled="isSaving"
            min="0"
            step="1"
          />
        </div>

        <!-- Min Stock Alert -->
        <div class="col-12 col-md-6">
          <label class="form-label">{{
            t("inventory_page.Minimum Stock Alert")
          }}</label>
          <input
            type="number"
            class="form-control"
            :placeholder="t('inventory_page.Minimum Stock Alert')"
            v-model.number="editForm.minStockAlert"
            :disabled="isSaving"
            min="0"
            step="1"
          />
        </div>

        <!-- HSN/SAC Code -->
        <div class="col-12 col-md-6" v-if="showHsnField">
          <label class="form-label">
            {{ t("inventory_page.HSN/ SAC Code") }}
            <span v-if="isHsnRequired" class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control"
            :placeholder="t('inventory_page.HSN/ SAC Code')"
            v-model="editForm.hsnCode"
            :disabled="isSaving"
          />
        </div>

        <!-- MRP -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            {{ t("inventory_page.MRP") }}
            <span v-if="isMrpRequired" class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">{{ currency }}</span>
            <input
              type="number"
              class="form-control"
              placeholder="0.00"
              v-model.number="editForm.mrp"
              step="0.01"
              min="0"
              :disabled="isSaving"
            />
          </div>
        </div>

        <!-- Rate -->
        <div class="col-12 col-md-6">
          <label class="form-label">
            {{ t("inventory_page.Rate") }} <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">{{ currency }}</span>
            <input
              type="number"
              class="form-control"
              placeholder="0.00"
              v-model.number="editForm.rate"
              step="0.01"
              min="0"
              :disabled="isSaving"
            />
          </div>
        </div>

        <div class="col-12">
          <!-- ── Tax Rate (readonly, auto-filled) ───────────────────── -->
          <div class="row g-3">
            <!-- ── Tax Dropdown ────────────────────────────────────────── -->
            <div class="col-12 col-md-6">
              <label class="form-label">
                {{ t("inventory_page.Tax") }} <span class="text-danger">*</span>
              </label>
              <select
                class="form-select"
                v-model="editForm.selectedTax"
                @change="handleTaxChange"
                :disabled="isSaving || configLoading"
              >
                <option value="">{{ t("common.Select Tax") }}</option>
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
              <label class="form-label">{{
                t("common.Tax Rate")
              }}</label>
              <div class="input-group">
                <input
                  type="number"
                  class="form-control"
                  v-model.number="editForm.taxRate"
                  placeholder="0"
                  step="0.01"
                  min="0"
                  readonly
                  :disabled="isSaving || configLoading"
                />
                <span class="input-group-text">%</span>
              </div>
              <small class="text-muted d-block mt-1">
                {{ t("common.Rate is auto-filled based on tax selection") }}
              </small>
            </div>
            
          </div>
        </div>
      </div>
      <!-- /row -->

      <!-- Inline save error -->
      <div v-if="saveError" class="alert alert-danger mt-3 mb-0 py-2">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ saveError }}
      </div>

      <!-- Actions -->
      <div class="form-actions">
        <button
          class="btn btn-primary"
          @click="handleSave"
          :disabled="isSaving"
        >
          <span
            v-if="isSaving"
            class="spinner-border spinner-border-sm me-2"
            role="status"
            aria-hidden="true"
          ></span>
          {{
            isSaving
              ? t("common.Saving") + "..."
              : isExistingProduct
              ? t("common.Update Product")
              : t("common.Add Product")
          }}
        </button>
        <button
          class="btn btn-outline-secondary"
          @click="$emit('cancel')"
          :disabled="isSaving"
        >
          {{ t("common.Cancel") }}
        </button>
      </div>
    </div>
    <!-- /result -->
  </div>
</template>


<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  nextTick,
  getCurrentInstance,
} from "vue";
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";
import { useInventoryManagementStore } from "@/modules/GroceryGermany/stores/inventoryManagement";
import { useConfigStore } from "@/stores/config";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";
import { storeToRefs } from "pinia";
import { useI18n } from "vue-i18n";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";

const { t } = useI18n();

const emit = defineEmits(["scanned", "cancel"]);

const props = defineProps({
  prefilledProduct: { type: Object, default: null },
  prefilledBarcode: { type: String, default: "" },
});

// ── Stores ────────────────────────────────────────────────────────────────────
const inventoryStore = useInventoryStore();
const inventoryManagementStore = useInventoryManagementStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();

const {
  unitsArray,
  taxesArray,
  loading: configLoading,
} = storeToRefs(configStore);
const { preferences } = storeToRefs(preferencesStore);

const { errors: addErrors, successMessage: addSuccessMessage } =
  storeToRefs(inventoryStore);
const { errors: updateErrors, successMessage: updateSuccessMessage } =
  storeToRefs(inventoryManagementStore);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

// ── State ─────────────────────────────────────────────────────────────────────
const state = ref("result");
const scannedBarcode = ref("");
const product = ref(null);
const saveError = ref("");
const barcodeInputRef = ref(null);
const isSaving = ref(false);
const errorMessages = ref([]);

// ── Derived ───────────────────────────────────────────────────────────────────
const isExistingProduct = computed(() => !!product.value?.id);

const successMessage = computed(
  () => updateSuccessMessage.value || addSuccessMessage.value || ""
);

function clearSuccessMsg() {
  inventoryStore.clearSuccessMessage();
  inventoryManagementStore.clearSuccessMessage();
}

// ── Error helpers ─────────────────────────────────────────────────────────────
function extractErrorsFromStoreArray(errorsArray) {
  const extracted = [];
  errorsArray.forEach((error) => {
    if (typeof error === "string") {
      extracted.push(error);
    } else if (typeof error === "object" && error !== null) {
      Object.values(error).forEach((msgs) => {
        if (Array.isArray(msgs)) extracted.push(...msgs);
        else if (typeof msgs === "string") extracted.push(msgs);
      });
    }
  });
  return extracted;
}

function extractErrors(result) {
  const extracted = [];
  if (Array.isArray(result.data)) {
    result.data.forEach((errorObj) => {
      if (typeof errorObj === "object" && errorObj !== null) {
        Object.values(errorObj).forEach((msgs) => {
          if (Array.isArray(msgs)) extracted.push(...msgs);
          else if (typeof msgs === "string") extracted.push(msgs);
        });
      }
    });
  }
  if (result.errors && typeof result.errors === "object") {
    Object.values(result.errors).forEach((msgs) => {
      if (Array.isArray(msgs)) extracted.push(...msgs);
      else if (typeof msgs === "string") extracted.push(msgs);
    });
  }
  if (extracted.length === 0 && result.message) extracted.push(result.message);
  return extracted;
}

watch(
  updateErrors,
  (newErrors) => {
    if (!newErrors?.length) return;
    errorMessages.value = extractErrorsFromStoreArray(newErrors);
  },
  { deep: true }
);

watch(
  addErrors,
  (newErrors) => {
    if (!newErrors?.length) {
      errorMessages.value = [];
      return;
    }
    errorMessages.value = extractErrorsFromStoreArray(newErrors);
  },
  { deep: true }
);

const allErrors = computed(() => [
  ...errorMessages.value,
  ...(configStore.errors ?? []),
  ...(preferencesStore.errors ?? []),
]);

function handleClearErrors() {
  errorMessages.value = [];
  saveError.value = "";
  inventoryStore.clearErrors();
  inventoryManagementStore.clearErrors();
  configStore.clearErrors();
  preferencesStore.clearErrors();
}

// ── Preferences ───────────────────────────────────────────────────────────────
const isMrpRequired = computed(
  () =>
    preferences.value.preference_mrp === 1 ||
    preferences.value.preference_mrp === true
);
const isStockQuantityRequired = computed(
  () =>
    preferences.value.preference_quantity === 1 ||
    preferences.value.preference_quantity === true
);
const showHsnField = computed(
  () =>
    preferences.value.preference_hsn === 1 ||
    preferences.value.preference_hsn === true
);
const isHsnRequired = computed(() => showHsnField.value);

// ── Editable form ─────────────────────────────────────────────────────────────
const editForm = ref({
  itemName: "",
  stockQuantity: null,
  minStockAlert: null,
  selectedUnit: null,
  hsnCode: "",
  mrp: null,
  rate: null,
  selectedTax: "",
  taxRate: null,
});

// ── Tax change handler (user manually selects) ────────────────────────────────
function handleTaxChange() {
  const selectedValue = editForm.value.selectedTax;

  if (!selectedValue) {
    editForm.value.taxRate = null;
    return;
  }

  const matched = taxesArray.value.find((t) => t.value === selectedValue);
  if (!matched) return;

  if (matched.rate !== undefined && matched.rate !== null) {
    editForm.value.taxRate = parseFloat(matched.rate);
  } else if (matched.label) {
    const m = matched.label.match(/(\d+\.?\d*)\s*%?/);
    if (m) editForm.value.taxRate = parseFloat(m[1]);
  } else if (!isNaN(parseFloat(matched.value))) {
    editForm.value.taxRate = parseFloat(matched.value);
  }
}

// ── ✅ resolveTaxByRate — match tax option using rate1 from API ────────────────
// API returns rate1: "7.00" or "19.00" — this is the only reliable key.
// Tax name (tax1: "VAT") is ambiguous since both VAT 7% and VAT 19% share it.
function resolveTaxByRate(rawRate) {
  if (!rawRate) return "";

  const targetRate = parseFloat(rawRate);
  if (isNaN(targetRate)) return "";

  // Strategy 1: tax has a direct .rate property
  const byRate = taxesArray.value.find(
    (t) => parseFloat(t.rate) === targetRate
  );
  if (byRate) return byRate.value;

  // Strategy 2: parse rate from label e.g. "VAT 7%" → 7, "MwSt. 19%" → 19
  const byLabelRate = taxesArray.value.find((t) => {
    const m = String(t.label).match(/(\d+\.?\d*)\s*%?/);
    return m && parseFloat(m[1]) === targetRate;
  });
  if (byLabelRate) return byLabelRate.value;

  // Strategy 3: tax.value itself is the numeric rate
  const byValueNum = taxesArray.value.find(
    (t) => parseFloat(t.value) === targetRate
  );
  if (byValueNum) return byValueNum.value;

  return "";
}

// ── Mount ─────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(false, countryName.toLowerCase()),
    configStore.switchLocale(countryName),
    preferencesStore.fetchUserPreferences(),
  ]);

  if (props.prefilledProduct) {
    product.value = props.prefilledProduct;
    scannedBarcode.value = props.prefilledBarcode;
    populateEditForm(props.prefilledProduct);
  }

  await nextTick();
  barcodeInputRef.value?.focus();
});

// ── Populate form for existing product ───────────────────────────────────────
function populateEditForm(p) {
  const matchedUnit = unitsArray.value.find(
    (u) => u.shortUnit === p.short_unit || u.fullUnit === p.full_unit
  );

  // ✅ Match tax by rate1 value — not by tax1 name string
  const resolvedTax = resolveTaxByRate(p.rate1);
  const resolvedRate = parseFloat(p.rate1) || null;

  editForm.value = {
    itemName: p.item_name ?? "",
    stockQuantity: parseFloat(p.quantity) || null,
    minStockAlert: parseFloat(p.min_stock_alert) || null,
    selectedUnit: matchedUnit?.value ?? null,
    hsnCode: p.hsn && p.hsn !== "–" ? p.hsn : "",
    mrp: parseFloat(p.mrp) || null,
    rate: parseFloat(p.sale_price) || null,
    selectedTax: resolvedTax,
    taxRate: resolvedRate,
  };
}

function resetFormForNew() {
  editForm.value = {
    itemName: "",
    stockQuantity: null,
    minStockAlert: null,
    selectedUnit: null,
    hsnCode: "",
    mrp: null,
    rate: null,
    selectedTax: "",
    taxRate: null,
  };
}

// ── Fetch product by barcode ──────────────────────────────────────────────────
async function fetchProduct(barcode) {
  if (!barcode) return;
  saveError.value = "";
  errorMessages.value = [];
  state.value = "loading";

  try {
    const result = await inventoryStore.getProductByBarcode(barcode);

    if (result.success) {
      const data = Array.isArray(result.data) ? result.data[0] : result.data;
      product.value = data;
      populateEditForm(data);

      // ✅ Force Vue to re-evaluate <select> v-model after DOM update
      await nextTick();
      editForm.value = { ...editForm.value };
    } else {
      product.value = null;
      resetFormForNew();
    }

    state.value = "result";
    await nextTick();
    barcodeInputRef.value?.focus();
  } catch (e) {
    saveError.value = "Failed to fetch product details.";
    state.value = "result";
  }
}

// ── Save ──────────────────────────────────────────────────────────────────────
async function handleSave() {
  saveError.value = "";
  errorMessages.value = [];
  isSaving.value = true;

  inventoryStore.clearErrors();
  inventoryManagementStore.clearErrors();

  const selectedUnitObj = unitsArray.value.find(
    (u) => u.value === editForm.value.selectedUnit
  );

  try {
    let result;

    if (isExistingProduct.value) {
      result = await inventoryManagementStore.updateItem({
        id: product.value.id,
        name: editForm.value.itemName,
        stock: editForm.value.stockQuantity,
        minStockAlert: editForm.value.minStockAlert,
        fullUnit: selectedUnitObj?.fullUnit ?? "",
        shortUnit: selectedUnitObj?.shortUnit ?? "",
        hsn: editForm.value.hsnCode,
        mrp: editForm.value.mrp,
        rate: editForm.value.rate,
        tax1: editForm.value.selectedTax,
        rate1: editForm.value.taxRate ?? 0,
        tax2: "",
        rate2: 0,
        barcode: scannedBarcode.value,
      });
    } else {
      result = await inventoryStore.addItem({
        itemName: editForm.value.itemName,
        stockQuantity: editForm.value.stockQuantity,
        minStockAlert: editForm.value.minStockAlert,
        fullUnit: selectedUnitObj?.fullUnit ?? "",
        shortUnit: selectedUnitObj?.shortUnit ?? "",
        hsnCode: editForm.value.hsnCode,
        mrp: editForm.value.mrp,
        rate: editForm.value.rate,
        taxRates: [
          {
            tax: editForm.value.selectedTax,
            rate: editForm.value.taxRate ?? 0,
          },
        ],
        barcode: scannedBarcode.value,
      });
    }

    if (result.success) {
      emit("scanned", {
        id: isExistingProduct.value
          ? product.value.id
          : result.data?.id ?? null,
        barcode: scannedBarcode.value,
        itemName: editForm.value.itemName,
      });

      setTimeout(() => clearSuccessMsg(), 5000);

      if (!isExistingProduct.value) {
        product.value = null;
        scannedBarcode.value = "";
        resetFormForNew();
        await nextTick();
        barcodeInputRef.value?.select();
        barcodeInputRef.value?.focus();
      }
    } else {
      const extracted = extractErrors(result);
      if (extracted.length > 0) {
        errorMessages.value = extracted;
      } else {
        saveError.value = "Save failed. Please check the form and try again.";
      }
    }
  } catch (e) {
    saveError.value = "An unexpected error occurred.";
  }

  isSaving.value = false;
}

// ── Reset ─────────────────────────────────────────────────────────────────────
function resetScanner() {
  emit("cancel");
}
</script>


<style scoped>
.barcode-scanner-wrapper {
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
.form-control[readonly] {
  background-color: #f8f9fa;
  color: #6c757d;
  cursor: default;
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
.barcode-input {
  font-family: monospace;
  font-size: 1rem;
  letter-spacing: 1px;
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
