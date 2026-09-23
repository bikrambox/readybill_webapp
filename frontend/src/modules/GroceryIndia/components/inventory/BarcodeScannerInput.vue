<template>
  <div class="barcode-scanner-wrapper">
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
      <button type="button" class="btn-close" @click="clearSuccessMsg"></button>
    </div>

    <div v-if="configLoading" class="text-center py-3 mb-3">
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">{{ t("common.Loading configuration") }}...</span>
      </div>
      <span class="ms-2 text-muted">{{ t("common.Loading configuration") }}...</span>
    </div>

    <div v-if="state === 'loading'" class="d-flex align-items-center gap-2 py-2">
      <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
      <span class="text-muted small">
        {{ t("common.Fetching details for barcode") }}: <code>{{ scannedBarcode }}</code
        >...
      </span>
    </div>

    <div v-if="state === 'result'">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
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

      <div class="inventory-form-groups">
        <!-- Barcode: one full row -->
        <div class="row g-3 mb-3">
          <div class="col-12">
            <label class="form-label">
              {{ t("common.Barcode") }} <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <input
                type="text"
                class="form-control barcode-input"
                :class="{ 'is-invalid': barcodeFieldError }"
                placeholder="Scan or type barcode"
                v-model="scannedBarcode"
                ref="barcodeInputRef"
                :disabled="isSaving"
                @input="clearBarcodeFieldError"
                @keydown.enter.prevent="fetchProduct(scannedBarcode)"
              />
              <button
                class="btn btn-outline-secondary"
                type="button"
                @click="fetchProduct(scannedBarcode)"
                :disabled="isSaving || !scannedBarcode.trim()"
                title="Lookup barcode"
              >
                <i class="bi bi-search"></i>
              </button>
            </div>
            <div v-if="barcodeFieldError" class="invalid-feedback d-block">
              {{ barcodeFieldError }}
            </div>
            <div class="form-text text-muted">
              <i class="bi bi-info-circle me-1"></i>
              {{ t("common.Edit barcode and press Enter or click search to lookup") }}
            </div>
          </div>
        </div>

        <!-- Item Name + Unit -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("common.Item Name") }} <span class="text-danger">*</span>
            </label>
            <input
              type="text"
              class="form-control"
              :class="{ 'is-invalid': fieldErrors.itemName }"
              placeholder="Amul Butter"
              v-model="editForm.itemName"
              :disabled="isSaving"
              @input="fieldErrors.itemName = ''"
            />
            <div v-if="fieldErrors.itemName" class="invalid-feedback d-block">
              {{ fieldErrors.itemName }}
            </div>
            <small v-if="validatingItemName" class="text-muted d-block mt-1">
              Checking item name...
            </small>
            <small
              v-else-if="itemNameStatus"
              class="d-block mt-1"
              :class="itemNameStatus.valid ? 'text-success' : 'text-danger'"
            >
              {{ itemNameStatus.message }}
            </small>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("common.Unit") }} <span class="text-danger">*</span>
            </label>
            <select
              class="form-select"
              :class="{ 'is-invalid': fieldErrors.selectedUnit }"
              v-model="editForm.selectedUnit"
              :disabled="isSaving"
              @change="fieldErrors.selectedUnit = ''"
            >
              <option :value="null">{{ t("inventory_page.Select Unit") }}</option>
              <option v-for="unit in unitsArray" :key="unit.value" :value="unit.value">
                {{ unit.label }}
              </option>
            </select>
            <div v-if="fieldErrors.selectedUnit" class="invalid-feedback d-block">
              {{ fieldErrors.selectedUnit }}
            </div>
          </div>
        </div>

        <!-- Category + SKU -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("common.Category") }}
              <span v-if="isCategoryRequired" class="text-danger">*</span>
            </label>
            <select
              class="form-select"
              :class="{ 'is-invalid': fieldErrors.category_id }"
              v-model="editForm.category_id"
              :disabled="isSaving"
              @change="
                fieldErrors.category_id = '';
                if (!isExistingProduct) isSkuEdited = false;
              "
            >
              <option :value="null">{{ t("common.Select Category") }}</option>
              <option
                v-for="category in itemCategories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <div v-if="fieldErrors.category_id" class="invalid-feedback d-block">
              {{ fieldErrors.category_id }}
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("common.SKU") }}
              <span v-if="isSKURequired" class="text-danger">*</span>
            </label>
            <input
              type="text"
              class="form-control"
              :class="{ 'is-invalid': fieldErrors.sku }"
              placeholder="SKU"
              v-model="editForm.sku"
              :disabled="isSaving"
              @input="
                fieldErrors.sku = '';
                isSkuEdited = true;
              "
            />
            <div v-if="fieldErrors.sku" class="invalid-feedback d-block">
              {{ fieldErrors.sku }}
            </div>
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

        <!-- Stock quantity + minimum stock quantity -->
        <div class="row g-3 mb-3">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("inventory_page.Stock Quantity") }}
              <span v-if="isStockQuantityRequired" class="text-danger">*</span>
            </label>
            <input
              type="number"
              class="form-control"
              :class="{ 'is-invalid': fieldErrors.stockQuantity }"
              :placeholder="t('inventory_page.Stock Quantity')"
              v-model.number="editForm.stockQuantity"
              :disabled="isSaving"
              min="0"
              step="1"
              @input="fieldErrors.stockQuantity = ''"
            />
            <div v-if="fieldErrors.stockQuantity" class="invalid-feedback d-block">
              {{ fieldErrors.stockQuantity }}
            </div>
          </div>

          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("inventory_page.Minimum Stock Alert") }}
            </label>
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
        </div>

        <!-- HSN -->
        <div class="row g-3 mb-3" v-if="showHsnField">
          <div class="col-12 col-sm-6 col-md-4">
            <label class="form-label">
              {{ t("inventory_page.HSN/ SAC Code") }}
              <span v-if="isHsnRequired" class="text-danger">*</span>
            </label>
            <input
              type="text"
              class="form-control"
              :class="{ 'is-invalid': fieldErrors.hsnCode }"
              :placeholder="t('inventory_page.HSN/ SAC Code')"
              v-model="editForm.hsnCode"
              :disabled="isSaving"
              @input="fieldErrors.hsnCode = ''"
            />
            <div v-if="fieldErrors.hsnCode" class="invalid-feedback d-block">
              {{ fieldErrors.hsnCode }}
            </div>
          </div>
        </div>

        <!-- Pricing Section -->
        <div class="card border rounded-4 mb-3 pricing-section-card">
          <div class="card-body p-3 p-md-4">
            <h6 class="fw-bold mb-1">{{ t("common.Pricing") }}</h6>
            <p class="text-muted small mb-3">
              {{ t("common.Set MRP, sale price and purchase price") }}
            </p>

            <div class="row g-3 d-none d-md-flex">
              <div class="col-md-4">
                <label class="form-label">
                  {{ t("inventory_page.MRP") }}
                  <span v-if="isMrpRequired" class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.mrp }"
                    placeholder="Price"
                    v-model.number="editForm.mrp"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                    @input="fieldErrors.mrp = ''"
                  />
                </div>
                <div v-if="fieldErrors.mrp" class="invalid-feedback d-block">
                  {{ fieldErrors.mrp }}
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">
                  {{ t("common.Sale Price") }} <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.rate }"
                    placeholder="Sale Price"
                    v-model.number="editForm.rate"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                    @input="fieldErrors.rate = ''"
                  />
                </div>
                <div v-if="fieldErrors.rate" class="invalid-feedback d-block">
                  {{ fieldErrors.rate }}
                </div>
              </div>

              <div class="col-md-4" v-if="isPurchasePriceRequired">
                <label class="form-label">
                  {{ t("common.Purchase Price") }}
                  <span v-if="isPurchasePriceRequired" class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.purchase_price }"
                    placeholder="Purchase Price"
                    v-model.number="editForm.purchase_price"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                    @input="fieldErrors.purchase_price = ''"
                  />
                </div>
                <div v-if="fieldErrors.purchase_price" class="invalid-feedback d-block">
                  {{ fieldErrors.purchase_price }}
                </div>
              </div>
            </div>

            <div class="row g-3 d-none d-sm-flex d-md-none mb-3">
              <div class="col-sm-6">
                <label class="form-label">
                  {{ t("inventory_page.MRP") }}
                  <span v-if="isMrpRequired" class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.mrp }"
                    placeholder="Price"
                    v-model.number="editForm.mrp"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                    @input="fieldErrors.mrp = ''"
                  />
                </div>
                <div v-if="fieldErrors.mrp" class="invalid-feedback d-block">
                  {{ fieldErrors.mrp }}
                </div>
              </div>
            </div>

            <div class="row g-3 d-none d-sm-flex d-md-none">
              <div class="col-sm-6">
                <label class="form-label">
                  {{ t("common.Sale Price") }} <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.rate }"
                    placeholder="Sale Price"
                    v-model.number="editForm.rate"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                    @input="fieldErrors.rate = ''"
                  />
                </div>
                <div v-if="fieldErrors.rate" class="invalid-feedback d-block">
                  {{ fieldErrors.rate }}
                </div>
              </div>

              <div v-if="isPurchasePriceRequired" class="col-sm-6">
                <label class="form-label">
                  {{ t("common.Purchase Price") }}
                  <span v-if="isPurchasePriceRequired" class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text">{{ currency }}</span>
                  <input
                    type="number"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.purchase_price }"
                    placeholder="Purchase Price"
                    v-model.number="editForm.purchase_price"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                    @input="fieldErrors.purchase_price = ''"
                  />
                </div>
                <div v-if="fieldErrors.purchase_price" class="invalid-feedback d-block">
                  {{ fieldErrors.purchase_price }}
                </div>
              </div>
            </div>

            <div class="d-block d-sm-none">
              <div class="row g-3 mb-3">
                <div class="col-12">
                  <label class="form-label">
                    {{ t("inventory_page.MRP") }}
                    <span v-if="isMrpRequired" class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">{{ currency }}</span>
                    <input
                      type="number"
                      class="form-control"
                      :class="{ 'is-invalid': fieldErrors.mrp }"
                      placeholder="Price"
                      v-model.number="editForm.mrp"
                      step="0.01"
                      min="0"
                      :disabled="isSaving"
                      @input="fieldErrors.mrp = ''"
                    />
                  </div>
                  <div v-if="fieldErrors.mrp" class="invalid-feedback d-block">
                    {{ fieldErrors.mrp }}
                  </div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label">
                    {{ t("common.Sale Price") }} <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">{{ currency }}</span>
                    <input
                      type="number"
                      class="form-control"
                      :class="{ 'is-invalid': fieldErrors.rate }"
                      placeholder="Sale Price"
                      v-model.number="editForm.rate"
                      step="0.01"
                      min="0"
                      :disabled="isSaving"
                      @input="fieldErrors.rate = ''"
                    />
                  </div>
                  <div v-if="fieldErrors.rate" class="invalid-feedback d-block">
                    {{ fieldErrors.rate }}
                  </div>
                </div>

                <div v-if="isPurchasePriceRequired" class="col-12">
                  <label class="form-label">
                    {{ t("common.Purchase Price") }}
                    <span v-if="isPurchasePriceRequired" class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">{{ currency }}</span>
                    <input
                      type="number"
                      class="form-control"
                      :class="{ 'is-invalid': fieldErrors.purchase_price }"
                      placeholder="Purchase Price"
                      v-model.number="editForm.purchase_price"
                      step="0.01"
                      min="0"
                      :disabled="isSaving"
                      @input="fieldErrors.purchase_price = ''"
                    />
                  </div>
                  <div v-if="fieldErrors.purchase_price" class="invalid-feedback d-block">
                    {{ fieldErrors.purchase_price }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tax Section -->
        <div class="card border rounded-4 mb-3 tax-section-card">
          <div class="card-body p-3 p-md-4 overflow-visible">
            <h6 class="fw-bold mb-1">{{ t("inventory_page.Tax") }}</h6>
            <p class="text-muted small mb-3">
              {{ t("common.Select or type custom GST and CESS rates") }}
            </p>

            <div class="row g-3 overflow-visible">
              <div class="col-12 col-sm-6 col-md-4 overflow-visible">
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
                    :disabled="isSaving || configLoading"
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
                      taxDropdownOpen[0] && taxSearch[0] && filteredTaxes(0).length === 0
                    "
                    class="tax-dropdown-empty"
                  >
                    <i class="bi bi-calculator me-1"></i>
                    <small>
                      Custom rate: <strong>{{ taxSearch[0] }}%</strong> will be applied
                    </small>
                  </div>
                </div>
              </div>

              <div class="col-12 col-sm-6 col-md-4 overflow-visible">
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
                    :disabled="isSaving || configLoading"
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
                      taxDropdownOpen[1] && taxSearch[1] && filteredTaxes(1).length === 0
                    "
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
        </div>
      </div>
      <!-- /inventory-form-groups -->

      <div v-if="saveError" class="alert alert-danger mt-3 mb-0 py-2">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ saveError }}
      </div>

      <div class="form-actions">
        <button
          class="btn btn-primary"
          @click="handleSave"
          :disabled="isSaving || configLoading"
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
  </div>
</template>

<script setup>
import {
  ref,
  computed,
  watch,
  onMounted,
  onUnmounted,
  nextTick,
  getCurrentInstance,
} from "vue";
import { useInventoryStore } from "@/modules/GroceryIndia/stores/inventory";
import { useInventoryManagementStore } from "@/modules/GroceryIndia/stores/inventoryManagement";
import { useConfigStore } from "@/stores/config";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";
import { storeToRefs } from "pinia";
import { useI18n } from "vue-i18n";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";

const { t } = useI18n();

const emit = defineEmits(["scanned", "cancel"]);

const props = defineProps({
  prefilledProduct: { type: Object, default: null },
  prefilledBarcode: { type: String, default: "" },
});

const inventoryStore = useInventoryStore();
const inventoryManagementStore = useInventoryManagementStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();
const userStore = useUserDetailsStore();

const { unitsArray, gstArray, cessArray, loading: configLoading } = storeToRefs(
  configStore
);

const { preferences } = storeToRefs(preferencesStore);
const { item_categories } = storeToRefs(userStore);

const { errors: addErrors, successMessage: addSuccessMessage } = storeToRefs(
  inventoryStore
);
const { errors: updateErrors, successMessage: updateSuccessMessage } = storeToRefs(
  inventoryManagementStore
);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

const state = ref("result");
const scannedBarcode = ref("");
const product = ref(null);
const saveError = ref("");
const barcodeFieldError = ref("");
const barcodeInputRef = ref(null);
const isSaving = ref(false);
const errorMessages = ref([]);

const editForm = ref({
  itemName: "",
  category_id: null,
  sku: "",
  stockQuantity: null,
  minStockAlert: null,
  selectedUnit: null,
  hsnCode: "",
  purchase_price: null,
  mrp: null,
  rate: null,
});

const fieldErrors = ref({
  itemName: "",
  selectedUnit: "",
  category_id: "",
  sku: "",
  stockQuantity: "",
  hsnCode: "",
  purchase_price: "",
  mrp: "",
  rate: "",
});

const itemNameStatus = ref(null);
const skuStatus = ref(null);
const validatingItemName = ref(false);
const validatingSku = ref(false);
const isGeneratingSku = ref(false);

const isSkuEdited = ref(false);

const taxRates = ref([
  { tax: "GST", rate: null },
  { tax: "CESS", rate: null },
]);
const taxSearch = ref(["", ""]);
const taxDropdownOpen = ref([false, false]);

const BARCODE_MIN_LENGTH = 4;
const BARCODE_MAX_LENGTH = 50;
const BARCODE_PATTERN = /^[a-zA-Z0-9\-_.\/]+$/;

let fetchRequestId = 0;
let successTimer = null;
let itemNameTimer = null;
let skuTimer = null;
let generateSkuTimer = null;

let itemNameValidationReq = 0;
let skuValidationReq = 0;
let skuGenerateReq = 0;

const isExistingProduct = computed(() => !!product.value?.id);

const successMessage = computed(
  () => updateSuccessMessage.value || addSuccessMessage.value || ""
);

const itemCategories = computed(() => item_categories.value || []);

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

const isHsnRequired = computed(() => showHsnField.value);

const isSKURequired = computed(
  () =>
    preferences.value.preference_sku === 1 || preferences.value.preference_sku === true
);

const isCategoryRequired = computed(
  () =>
    preferences.value.preference_category === 1 ||
    preferences.value.preference_category === true
);

function clearSuccessMsg() {
  inventoryStore.clearSuccessMessage();
  inventoryManagementStore.clearSuccessMessage();
}

function validateBarcode(value) {
  const v = String(value || "").trim();

  if (!v) return "Barcode is required.";
  if (v.length < BARCODE_MIN_LENGTH) {
    return `Barcode must be at least ${BARCODE_MIN_LENGTH} characters.`;
  }
  if (v.length > BARCODE_MAX_LENGTH) {
    return `Barcode must not exceed ${BARCODE_MAX_LENGTH} characters.`;
  }
  if (!BARCODE_PATTERN.test(v)) {
    return "Barcode contains invalid characters. Only letters, digits, -, _, ., / are allowed.";
  }
  return "";
}

function clearBarcodeFieldError() {
  if (barcodeFieldError.value) barcodeFieldError.value = "";
}

function resetFieldErrors() {
  fieldErrors.value = {
    itemName: "",
    selectedUnit: "",
    category_id: "",
    sku: "",
    stockQuantity: "",
    hsnCode: "",
    purchase_price: "",
    mrp: "",
    rate: "",
  };
}

function resetValidationState() {
  itemNameStatus.value = null;
  skuStatus.value = null;
  validatingItemName.value = false;
  validatingSku.value = false;
  isGeneratingSku.value = false;
}

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

  if (Array.isArray(result?.data)) {
    result.data.forEach((errorObj) => {
      if (typeof errorObj === "object" && errorObj !== null) {
        Object.values(errorObj).forEach((msgs) => {
          if (Array.isArray(msgs)) extracted.push(...msgs);
          else if (typeof msgs === "string") extracted.push(msgs);
        });
      }
    });
  }

  if (result?.errors && typeof result.errors === "object") {
    Object.values(result.errors).forEach((msgs) => {
      if (Array.isArray(msgs)) extracted.push(...msgs);
      else if (typeof msgs === "string") extracted.push(msgs);
    });
  }

  if (extracted.length === 0 && result?.message) extracted.push(result.message);

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
  resetFieldErrors();
  barcodeFieldError.value = "";
  inventoryStore.clearErrors();
  inventoryManagementStore.clearErrors();
  configStore.clearErrors();
  preferencesStore.clearErrors();
}

const getTaxOptions = (index) => {
  return index === 0 ? gstArray.value : cessArray.value;
};

const parseRateFromTax = (tax) => {
  if (tax.value !== undefined && tax.value !== null && !isNaN(Number(tax.value))) {
    return Number(tax.value);
  }
  if (tax.rate !== undefined && tax.rate !== null && !isNaN(Number(tax.rate))) {
    return Number(tax.rate);
  }
  const match = String(tax.label ?? "").match(/(\d+(\.\d+)?)/);
  return match ? Number(match[1]) : 0;
};

const filteredTaxes = (index) => {
  const options = getTaxOptions(index);
  const search = taxSearch.value[index]?.toLowerCase() || "";
  if (!search) return options;
  return options.filter((t) => String(t.label).toLowerCase().includes(search));
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
//   const numeric = String(value).replace(/[^0-9.]/g, "");
//   taxSearch.value[index] = numeric;
//   taxDropdownOpen.value[index] = true;

//   const options = getTaxOptions(index);
//   const match = options.find(
//     (t) => String(t.label).toLowerCase() === String(value).toLowerCase()
//   );

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

function populateTaxStateFromProduct(p) {
  const gstRate =
    p?.tax1 === "GST" && p?.rate1 !== undefined && p?.rate1 !== null
      ? Number(p.rate1)
      : p?.tax2 === "GST" && p?.rate2 !== undefined && p?.rate2 !== null
      ? Number(p.rate2)
      : null;

  const cessRate =
    p?.tax1 === "CESS" && p?.rate1 !== undefined && p?.rate1 !== null
      ? Number(p.rate1)
      : p?.tax2 === "CESS" && p?.rate2 !== undefined && p?.rate2 !== null
      ? Number(p.rate2)
      : null;

  taxRates.value = [
    { tax: "GST", rate: !isNaN(gstRate) ? gstRate : null },
    { tax: "CESS", rate: !isNaN(cessRate) ? cessRate : null },
  ];

  taxSearch.value = [
    taxRates.value[0].rate !== null ? String(taxRates.value[0].rate) : "",
    taxRates.value[1].rate !== null ? String(taxRates.value[1].rate) : "",
  ];

  taxDropdownOpen.value = [false, false];
}

async function validateItemNameOnly() {
  const itemName = editForm.value.itemName?.trim();

  if (!itemName) {
    itemNameStatus.value = null;
    return;
  }

  const reqId = ++itemNameValidationReq;
  validatingItemName.value = true;

  try {
    const result = await inventoryStore.validateItemOrSku({
      item_name: itemName,
    });

    if (reqId !== itemNameValidationReq) return;

    if (result.success) {
      itemNameStatus.value = {
        valid: true,
        message: result.message || "Item name is available",
      };
    } else {
      itemNameStatus.value = {
        valid: false,
        message: result.message || "Item name already exists",
      };
    }
  } catch (e) {
    if (reqId !== itemNameValidationReq) return;
    itemNameStatus.value = {
      valid: false,
      message: "Unable to validate item name",
    };
  } finally {
    if (reqId === itemNameValidationReq) validatingItemName.value = false;
  }
}

async function generateSkuFromItemAndCategory() {
  const itemName = editForm.value.itemName?.trim();
  const categoryId = editForm.value.category_id;

  if (!itemName || !categoryId || isSkuEdited.value) return;

  const reqId = ++skuGenerateReq;
  isGeneratingSku.value = true;

  try {
    const result = await inventoryStore.validateItemOrSku({
      item_name: itemName,
      category_id: categoryId,
      sku: "__GENERATE__",
    });

    if (reqId !== skuGenerateReq) return;

    if (result.success && result.data?.generated_sku) {
      editForm.value.sku = result.data.generated_sku;
      skuStatus.value = {
        valid: true,
        message: "SKU generated successfully",
      };
    }
  } catch (e) {
    if (reqId !== skuGenerateReq) return;
    skuStatus.value = {
      valid: false,
      message: "Unable to generate SKU",
    };
  } finally {
    if (reqId === skuGenerateReq) isGeneratingSku.value = false;
  }
}

async function validateSkuInput() {
  const itemName = editForm.value.itemName?.trim();
  const categoryId = editForm.value.category_id;
  const sku = editForm.value.sku?.trim();

  if (!sku) {
    skuStatus.value = null;
    return;
  }

  if (!itemName || !categoryId) {
    skuStatus.value = {
      valid: false,
      message: "Item name and category are required to validate SKU",
    };
    return;
  }

  const reqId = ++skuValidationReq;
  validatingSku.value = true;

  try {
    const result = await inventoryStore.validateItemOrSku({
      item_name: itemName,
      category_id: categoryId,
      sku,
    });

    if (reqId !== skuValidationReq) return;

    if (result.success) {
      const exists = result.data?.entered_sku_exists;
      skuStatus.value = {
        valid: exists === false,
        message: exists === false ? "SKU is available" : "SKU already exists",
      };
    } else {
      skuStatus.value = {
        valid: false,
        message: result.message || "SKU validation failed",
      };
    }
  } catch (e) {
    if (reqId !== skuValidationReq) return;
    skuStatus.value = {
      valid: false,
      message: "Unable to validate SKU",
    };
  } finally {
    if (reqId === skuValidationReq) validatingSku.value = false;
  }
}

onMounted(async () => {
  await Promise.all([
    configStore.fetchConfigData(),
    configStore.switchLocale(countryName),
    preferencesStore.fetchUserPreferences(),
    userStore.fetchCategories(),
  ]);

  await nextTick();
  barcodeInputRef.value?.focus();
});

onUnmounted(() => {
  clearTimeout(successTimer);
  clearTimeout(itemNameTimer);
  clearTimeout(skuTimer);
  clearTimeout(generateSkuTimer);
});

watch(
  () => props.prefilledBarcode,
  (newBarcode) => {
    scannedBarcode.value = String(newBarcode || "").trim();
  },
  { immediate: true }
);

watch(
  () => props.prefilledProduct,
  (newProduct) => {
    product.value = newProduct ?? null;

    if (newProduct) {
      populateEditForm(newProduct);
      populateTaxStateFromProduct(newProduct);
    } else {
      resetFormForNew();
    }

    state.value = "result";
  },
  { immediate: true }
);

watch(
  () => editForm.value.itemName,
  (newVal) => {
    clearTimeout(itemNameTimer);
    clearTimeout(generateSkuTimer);

    itemNameStatus.value = null;

    if (!newVal?.trim()) {
      itemNameStatus.value = null;
      if (!editForm.value.category_id) {
        editForm.value.sku = "";
        skuStatus.value = null;
      }
      return;
    }

    itemNameTimer = setTimeout(() => {
      validateItemNameOnly();
    }, 500);

    if (editForm.value.category_id && !isSkuEdited.value) {
      generateSkuTimer = setTimeout(() => {
        generateSkuFromItemAndCategory();
      }, 600);
    }
  }
);

watch(
  () => editForm.value.category_id,
  (newVal) => {
    clearTimeout(generateSkuTimer);

    if (!newVal) {
      skuStatus.value = null;
      return;
    }

    if (editForm.value.itemName?.trim() && !isSkuEdited.value) {
      generateSkuTimer = setTimeout(() => {
        generateSkuFromItemAndCategory();
      }, 400);
    }
  }
);

watch(
  () => editForm.value.sku,
  (newVal, oldVal) => {
    clearTimeout(skuTimer);

    if (!newVal?.trim() || newVal === oldVal) {
      if (!newVal?.trim()) skuStatus.value = null;
      return;
    }

    skuTimer = setTimeout(() => {
      validateSkuInput();
    }, 500);
  }
);

function populateEditForm(p) {
  const matchedUnit = unitsArray.value.find(
    (u) => u.shortUnit === p.short_unit || u.fullUnit === p.full_unit
  );

  const matchedCategory = itemCategories.value.find(
    (c) =>
      c.id === p.category_id ||
      String(c.id) === String(p.category_id) ||
      String(c.name).toLowerCase() === String(p.category_name || "").toLowerCase()
  );

  editForm.value = {
    itemName: p.item_name ?? "",
    category_id: matchedCategory?.id ?? p.category_id ?? null,
    sku: p.sku ?? "",
    stockQuantity:
      p.quantity !== undefined && p.quantity !== null
        ? parseFloat(p.quantity) || 0
        : null,
    minStockAlert:
      p.min_stock_alert !== undefined && p.min_stock_alert !== null
        ? parseFloat(p.min_stock_alert) || 0
        : null,
    selectedUnit: matchedUnit?.value ?? null,
    hsnCode: p.hsn && p.hsn !== "–" ? p.hsn : "",
    purchase_price:
      p.purchase_price !== undefined && p.purchase_price !== null
        ? parseFloat(p.purchase_price) || 0
        : null,
    mrp: p.mrp !== undefined && p.mrp !== null ? parseFloat(p.mrp) || 0 : null,
    rate:
      p.sale_price !== undefined && p.sale_price !== null
        ? parseFloat(p.sale_price) || 0
        : p.rate !== undefined && p.rate !== null
        ? parseFloat(p.rate) || 0
        : null,
  };

  isSkuEdited.value = !!editForm.value.sku;

  resetFieldErrors();
  resetValidationState();
  barcodeFieldError.value = "";
  saveError.value = "";
  errorMessages.value = [];
}

function resetFormForNew() {
  editForm.value = {
    itemName: "",
    category_id: null,
    sku: "",
    stockQuantity: null,
    minStockAlert: null,
    selectedUnit: null,
    hsnCode: "",
    purchase_price: null,
    mrp: null,
    rate: null,
  };

  taxRates.value = [
    { tax: "GST", rate: null },
    { tax: "CESS", rate: null },
  ];
  taxSearch.value = ["", ""];
  taxDropdownOpen.value = [false, false];

  isSkuEdited.value = false;

  resetFieldErrors();
  resetValidationState();
  barcodeFieldError.value = "";
  saveError.value = "";
}

async function fetchProduct(barcode) {
  const normalizedBarcode = String(barcode || "").trim();
  const validationError = validateBarcode(normalizedBarcode);

  if (validationError) {
    barcodeFieldError.value = validationError;
    await nextTick();
    barcodeInputRef.value?.focus();
    return;
  }

  const requestId = ++fetchRequestId;

  barcodeFieldError.value = "";
  saveError.value = "";
  errorMessages.value = [];
  scannedBarcode.value = normalizedBarcode;
  state.value = "loading";

  try {
    const result = await inventoryStore.getProductByBarcode(normalizedBarcode);

    if (requestId !== fetchRequestId) return;

    if (result.success) {
      const data = Array.isArray(result.data)
        ? result.data[0] ?? null
        : result.data ?? null;
      product.value = data;

      if (data) {
        populateEditForm(data);
        populateTaxStateFromProduct(data);
      } else {
        resetFormForNew();
      }
    } else {
      product.value = null;
      resetFormForNew();
    }

    state.value = "result";
    await nextTick();
    barcodeInputRef.value?.focus();
  } catch (e) {
    if (requestId !== fetchRequestId) return;
    saveError.value = "Failed to fetch product details.";
    state.value = "result";
    await nextTick();
    barcodeInputRef.value?.focus();
  }
}

function validateBeforeSave() {
  let valid = true;
  resetFieldErrors();

  const normalizedBarcode = String(scannedBarcode.value || "").trim();
  const barcodeValidation = validateBarcode(normalizedBarcode);

  if (barcodeValidation) {
    barcodeFieldError.value = barcodeValidation;
    valid = false;
  } else {
    barcodeFieldError.value = "";
  }

  if (!String(editForm.value.itemName || "").trim()) {
    fieldErrors.value.itemName = "Item name is required.";
    valid = false;
  }

  if (!editForm.value.selectedUnit) {
    fieldErrors.value.selectedUnit = "Unit is required.";
    valid = false;
  }

  if (isCategoryRequired.value && !editForm.value.category_id) {
    fieldErrors.value.category_id = "Category is required.";
    valid = false;
  }

  if (isSKURequired.value && !String(editForm.value.sku || "").trim()) {
    fieldErrors.value.sku = "SKU is required.";
    valid = false;
  }

  if (isSKURequired.value && skuStatus.value && skuStatus.value.valid === false) {
    fieldErrors.value.sku = skuStatus.value.message || "SKU is invalid.";
    valid = false;
  }

  if (isStockQuantityRequired.value) {
    const qty = editForm.value.stockQuantity;
    if (qty === null || qty === "" || Number(qty) < 0) {
      fieldErrors.value.stockQuantity = "Valid stock quantity is required.";
      valid = false;
    }
  }

  if (
    showHsnField.value &&
    isHsnRequired.value &&
    !String(editForm.value.hsnCode || "").trim()
  ) {
    fieldErrors.value.hsnCode = "HSN / SAC code is required.";
    valid = false;
  }

  if (isPurchasePriceRequired.value) {
    if (
      editForm.value.purchase_price === null ||
      editForm.value.purchase_price === "" ||
      Number(editForm.value.purchase_price) < 0
    ) {
      fieldErrors.value.purchase_price = "Valid purchase price is required.";
      valid = false;
    }
  }

  if (isMrpRequired.value) {
    if (
      editForm.value.mrp === null ||
      editForm.value.mrp === "" ||
      Number(editForm.value.mrp) < 0
    ) {
      fieldErrors.value.mrp = "Valid MRP is required.";
      valid = false;
    }
  }

  if (
    editForm.value.rate === null ||
    editForm.value.rate === "" ||
    Number(editForm.value.rate) < 0
  ) {
    fieldErrors.value.rate = "Valid sale price is required.";
    valid = false;
  }

  return valid;
}

async function handleSave() {
  saveError.value = "";
  errorMessages.value = [];

  if (!validateBeforeSave()) return;

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
        name: String(editForm.value.itemName || "").trim(),
        category_id: editForm.value.category_id,
        sku: String(editForm.value.sku || "").trim(),
        stock: editForm.value.stockQuantity,
        minStockAlert: editForm.value.minStockAlert,
        fullUnit: selectedUnitObj?.fullUnit ?? "",
        shortUnit: selectedUnitObj?.shortUnit ?? "",
        hsn: String(editForm.value.hsnCode || "").trim(),
        purchase_price: editForm.value.purchase_price,
        mrp: editForm.value.mrp,
        rate: editForm.value.rate,
        tax1: "GST",
        rate1: taxRates.value[0]?.rate ?? 0,
        tax2: "CESS",
        rate2: taxRates.value[1]?.rate ?? 0,
        barcode: String(scannedBarcode.value || "").trim(),
      });
    } else {
      result = await inventoryStore.addItem({
        itemName: String(editForm.value.itemName || "").trim(),
        category_id: editForm.value.category_id,
        sku: String(editForm.value.sku || "").trim(),
        stockQuantity: editForm.value.stockQuantity,
        minStockAlert: editForm.value.minStockAlert,
        fullUnit: selectedUnitObj?.fullUnit ?? "",
        shortUnit: selectedUnitObj?.shortUnit ?? "",
        hsnCode: String(editForm.value.hsnCode || "").trim(),
        purchase_price: editForm.value.purchase_price,
        mrp: editForm.value.mrp,
        rate: editForm.value.rate,
        barcode: String(scannedBarcode.value || "").trim(),
        tax1: "GST",
        rate1: taxRates.value[0]?.rate ?? 0,
        tax2: "CESS",
        rate2: taxRates.value[1]?.rate ?? 0,
      });
    }

    if (result.success) {
      emit("scanned", {
        id: isExistingProduct.value ? product.value.id : result.data?.id ?? null,
        barcode: String(scannedBarcode.value || "").trim(),
        itemName: String(editForm.value.itemName || "").trim(),
        sku: String(editForm.value.sku || "").trim(),
      });

      clearTimeout(successTimer);
      successTimer = setTimeout(() => clearSuccessMsg(), 5000);

      if (!isExistingProduct.value) {
        product.value = null;
        scannedBarcode.value = "";
        resetFormForNew();
        await nextTick();
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
  } finally {
    isSaving.value = false;
  }
}

function resetScanner() {
  emit("cancel");
}
</script>

<style scoped>
.barcode-scanner-wrapper {
  width: 100%;
}

.inventory-form-groups {
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

.barcode-input {
  font-family: monospace;
  font-size: 1rem;
  letter-spacing: 1px;
}

.pricing-section-card,
.tax-section-card {
  background: #fff;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}

.tax-section-card,
.tax-section-card .card-body,
.tax-section-card .row,
.tax-section-card [class*="col-"],
.tax-combobox {
  overflow: visible !important;
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
