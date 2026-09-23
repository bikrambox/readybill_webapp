<!-- components/inventory/BarcodeScannerInput.vue -->
<template>
  <div class="barcode-scanner-wrapper">

    <!-- ── State: idle ─────────────────────────────────────────────────── -->
    <div v-if="state === 'idle'">

      <!-- Error -->
      <div v-if="errorMsg" class="alert alert-danger d-flex align-items-start gap-2 mb-3">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div class="flex-grow-1">
          {{ errorMsg }}
          <div class="mt-2">
            <button class="btn btn-sm btn-outline-danger" @click="resetScanner">
              <i class="bi bi-arrow-clockwise me-1"></i>Try Again
            </button>
          </div>
        </div>
        <button class="btn-close btn-sm" @click="$emit('cancel')"></button>
      </div>

      <!-- Scan options -->
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <button class="btn btn-sm btn-primary" @click="startScanner">
          <i class="bi bi-camera-video-fill me-1"></i>Open Camera
        </button>

        <label class="btn btn-sm btn-outline-primary mb-0" style="cursor: pointer;">
          <i class="bi bi-image me-1"></i>Scan from Photo
          <input
            type="file"
            accept="image/*"
            class="d-none"
            ref="fileInputRef"
            @change="scanFromPhoto"
          />
        </label>

        <button class="btn btn-sm btn-outline-secondary" @click="$emit('cancel')">
          <i class="bi bi-x me-1"></i>Cancel
        </button>
      </div>
    </div>

    <!-- ── State: scanning (live camera) ──────────────────────────────── -->
    <div v-if="state === 'scanning'">
      <div id="qr-reader-scanner" style="max-width: 480px;"></div>

      <div v-if="scannedBarcode" class="text-center mt-2 mb-2">
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
          <i class="bi bi-upc-scan me-1"></i>
          <code>{{ scannedBarcode }}</code>
        </span>
      </div>

      <div class="mt-2">
        <button class="btn btn-sm btn-outline-secondary" @click="stopScanner">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
      </div>
    </div>

    <!-- ── State: loading ─────────────────────────────────────────────── -->
    <div v-if="state === 'loading'" class="d-flex align-items-center gap-2 py-2">
      <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
      <span class="text-muted small">
        Fetching product for <code>{{ scannedBarcode }}</code>...
      </span>
    </div>

    <!-- ── State: result — editable form ──────────────────────────────── -->
    <div v-if="state === 'result'">

      <!-- Top bar: Scan Again + Barcode (non-editable) -->
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <button class="btn btn-sm btn-outline-secondary" @click="resetScanner">
          <i class="bi bi-upc-scan me-1"></i>Scan Again
        </button>
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small fw-semibold">Barcode:</span>
          <code class="barcode-display">{{ scannedBarcode }}</code>
          <span
            v-if="isExistingProduct"
            class="badge bg-info-subtle text-info border border-info-subtle"
          >
            <i class="bi bi-pencil-square me-1"></i>Editing
          </span>
          <span
            v-else
            class="badge bg-warning-subtle text-warning border border-warning-subtle"
          >
            <i class="bi bi-plus-circle me-1"></i>New Product
          </span>
        </div>
      </div>

      <!-- Editable form -->
      <div class="row g-3">

        <!-- Item Name -->
        <div class="col-12 col-md-6">
          <label class="form-label">{{ $t('common.Item Name') }}</label>
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
          <label class="form-label">Unit</label>
          <select
            class="form-select"
            v-model="editForm.selectedUnit"
            :disabled="isSaving"
          >
            <option :value="null">{{ $t('inventory_page.Select Unit') }}</option>
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
            {{ $t('inventory_page.Stock Quantity') }}
          </label>
          <input
            type="number"
            class="form-control"
            placeholder="Stock Quantity"
            v-model.number="editForm.stockQuantity"
            :disabled="isSaving"
            min="0"
            step="1"
          />
        </div>

        <!-- Min Stock Alert -->
        <div class="col-12 col-md-6">
          <label class="form-label">{{ $t('inventory_page.Minimum Stock Alert') }}</label>
          <input
            type="number"
            class="form-control"
            placeholder="Minimum Stock Alert"
            v-model.number="editForm.minStockAlert"
            :disabled="isSaving"
            min="0"
            step="1"
          />
        </div>

        <!-- HSN/SAC Code -->
        <div class="col-12 col-md-6">
          <label class="form-label">{{ $t('inventory_page.HSN/ SAC Code') }}</label>
          <input
            type="text"
            class="form-control"
            placeholder="HSN/ SAC Code"
            v-model="editForm.hsnCode"
            :disabled="isSaving"
          />
        </div>

        <!-- Barcode (readonly) -->
        <div class="col-12 col-md-6">
          <label class="form-label">Barcode</label>
          <input
            type="text"
            class="form-control"
            :value="scannedBarcode"
            readonly
          />
        </div>

        <!-- MRP -->
        <div class="col-12 col-md-6">
          <label class="form-label">{{ $t('inventory_page.MRP') }}</label>
          <div class="input-group">
            <span class="input-group-text">{{ currency }}</span>
            <input
              type="number"
              class="form-control"
              placeholder="Price"
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
            {{ $t('inventory_page.Rate') }}
            <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">{{ currency }}</span>
            <input
              type="number"
              class="form-control"
              placeholder="Rate"
              v-model.number="editForm.rate"
              step="0.01"
              min="0"
              :disabled="isSaving"
            />
          </div>
        </div>

        <!-- Tax Section -->
        <div class="col-12">
          <div class="row g-3">

            <!-- Tax 1 -->
            <div class="col-12 col-md-6">
              <label class="form-label">
                {{ $t('inventory_page.Tax') }}
                <span class="text-danger">*</span>
              </label>
              <select
                class="form-select form-control-height"
                v-model="editForm.taxRates[0].tax"
                :disabled="isSaving"
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

            <!-- Tax Rate 1 -->
            <div class="col-12 col-md-6">
              <label class="form-label">
                Rate <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input
                  type="number"
                  class="form-control form-control-height"
                  v-model.number="editForm.taxRates[0].rate"
                  placeholder="0"
                  step="0.01"
                  min="0"
                  :disabled="isSaving"
                />
                <span class="input-group-text">%</span>
                <button
                  type="button"
                  class="btn btn-primary btn-height"
                  @click="addTaxRate"
                  :disabled="isSaving || !canAddMoreTax"
                  :title="canAddMoreTax ? 'Add another tax' : 'Maximum 2 taxes allowed'"
                >
                  <i class="bi bi-plus"></i>
                </button>
              </div>
            </div>

            <!-- Tax 2 (Conditional) -->
            <template v-if="editForm.taxRates.length > 1">
              <div class="col-12 col-md-6">
                <label class="form-label">Tax 2</label>
                <select
                  class="form-select form-control-height"
                  v-model="editForm.taxRates[1].tax"
                  :disabled="isSaving"
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
                    v-model.number="editForm.taxRates[1].rate"
                    placeholder="0"
                    step="0.01"
                    min="0"
                    :disabled="isSaving"
                  />
                  <span class="input-group-text">%</span>
                  <button
                    type="button"
                    class="btn btn-danger btn-height"
                    @click="removeTaxRate(1)"
                    :disabled="isSaving"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </template>

          </div>
        </div>

      </div><!-- /row -->

      <!-- Save error -->
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
          {{ isSaving ? 'Saving...' : (isExistingProduct ? 'Update Product' : 'Add Product') }}
        </button>
        <button
          class="btn btn-outline-secondary"
          @click="$emit('cancel')"
          :disabled="isSaving"
        >
          Cancel
        </button>
      </div>

    </div><!-- /result -->

    <!-- Hidden div required by html5-qrcode scanFile() -->
    <div id="qr-reader-offscreen" style="display: none;"></div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick, getCurrentInstance } from "vue";
import { Html5Qrcode } from "html5-qrcode";
import { useInventoryStore } from "@/modules/GroceryIndia/stores/inventory";
import { useConfigStore } from "@/stores/config";
import { storeToRefs } from "pinia";

const emit = defineEmits(["scanned", "cancel"]);

const props = defineProps({
  prefilledProduct: { type: Object, default: null },
  prefilledBarcode: { type: String, default: ""   },
});

const inventoryStore = useInventoryStore();
const configStore    = useConfigStore();

const { unitsArray, taxesArray } = storeToRefs(configStore);

const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;

// ── State machine: idle | scanning | loading | result ─────────────────────────
const state          = ref("idle");
const scannedBarcode = ref("");
const product        = ref(null);
const errorMsg       = ref("");
const saveError      = ref("");
const fileInputRef   = ref(null);
const isSaving       = ref(false);

// ── Is existing product (found in DB) or new ──────────────────────────────────
const isExistingProduct = computed(() => !!product.value?.id);

// ── Max 2 tax entries ─────────────────────────────────────────────────────────
const MAX_TAX_ENTRIES = 2;
const canAddMoreTax   = computed(() => editForm.value.taxRates.length < MAX_TAX_ENTRIES);

// ── Editable form state ───────────────────────────────────────────────────────
const editForm = ref({
  itemName:      "",
  stockQuantity: null,
  minStockAlert: null,
  selectedUnit:  null,
  hsnCode:       "",
  mrp:           null,
  rate:          null,
  taxRates:      [{ tax: "", rate: null }],
});

let html5QrCode = null;

// ── If parent passes prefilled product, show result directly ──────────────────
onMounted(async () => {
  await configStore.fetchConfigData();
  if (props.prefilledProduct) {
    product.value        = props.prefilledProduct;
    scannedBarcode.value = props.prefilledBarcode;
    populateEditForm(props.prefilledProduct);
    state.value = "result";
  }
});

onBeforeUnmount(() => stopScanner());

// ── Expose openScanner so parent can call via ref ─────────────────────────────
defineExpose({ openScanner: startScanner });

// ── Populate editForm from API product data ───────────────────────────────────
function populateEditForm(p) {
  // Match unit from unitsArray by shortUnit or fullUnit
  const matchedUnit = unitsArray.value.find(
    (u) => u.shortUnit === p.short_unit || u.fullUnit === p.full_unit
  );

  // Build taxRates
  const taxRates = [];
  if (p.tax1 && p.tax1 !== '–') {
    taxRates.push({ tax: p.tax1, rate: parseFloat(p.rate1) || null });
  } else {
    taxRates.push({ tax: "", rate: null });
  }
  if (p.tax2 && p.tax2 !== '–') {
    taxRates.push({ tax: p.tax2, rate: parseFloat(p.rate2) || null });
  }

  editForm.value = {
    itemName:      p.item_name                   ?? "",
    stockQuantity: parseFloat(p.quantity)        || null,
    minStockAlert: parseFloat(p.min_stock_alert) || null,
    selectedUnit:  matchedUnit?.value            ?? null,
    hsnCode:       (p.hsn && p.hsn !== '–')      ? p.hsn : "",
    mrp:           parseFloat(p.mrp)             || null,
    rate:          parseFloat(p.sale_price)      || null,
    taxRates,
  };
}

// ── Tax helpers ───────────────────────────────────────────────────────────────
function addTaxRate() {
  if (editForm.value.taxRates.length < MAX_TAX_ENTRIES) {
    editForm.value.taxRates.push({ tax: "", rate: null });
  }
}

function removeTaxRate(index) {
  if (index > 0) editForm.value.taxRates.splice(index, 1);
}

// ── Start live camera ─────────────────────────────────────────────────────────
async function startScanner() {
  errorMsg.value       = "";
  saveError.value      = "";
  product.value        = null;
  scannedBarcode.value = "";
  state.value          = "scanning";

  await nextTick();

  try {
    html5QrCode = new Html5Qrcode("qr-reader-scanner");

    await html5QrCode.start(
      { facingMode: "environment" },
      {
        fps: 10,
        qrbox: { width: 280, height: 160 },
        aspectRatio: 1.7,
        supportedScanTypes: [],
      },
      async (decodedText) => {
        scannedBarcode.value = decodedText;
        await stopScanner();
        await fetchProduct(decodedText);
      },
      () => {}
    );
  } catch (e) {
    state.value    = "idle";
    errorMsg.value = "Could not start camera: " + (e?.message ?? e);
  }
}

// ── Stop live camera ──────────────────────────────────────────────────────────
async function stopScanner() {
  if (html5QrCode) {
    try {
      const s = html5QrCode.getState();
      if (s === 2 || s === 3) await html5QrCode.stop();
    } catch (e) {
      console.warn("stop error:", e);
    }
    html5QrCode = null;
  }
  if (state.value === "scanning") state.value = "idle";
}

// ── Scan from photo ───────────────────────────────────────────────────────────
async function scanFromPhoto(event) {
  const file = event.target.files?.[0];
  if (fileInputRef.value) fileInputRef.value.value = "";
  if (!file) return;

  errorMsg.value       = "";
  saveError.value      = "";
  product.value        = null;
  scannedBarcode.value = "";
  state.value          = "loading";

  try {
    const scanner = new Html5Qrcode("qr-reader-offscreen");
    const result  = await scanner.scanFile(file, false);
    scannedBarcode.value = result;
    await fetchProduct(result);
  } catch (e) {
    errorMsg.value = "No barcode found in this photo. Try a clearer, closer image.";
    state.value    = "idle";
  }
}

// ── Fetch product from API ────────────────────────────────────────────────────
async function fetchProduct(barcode) {
  state.value = "loading";
  try {
    const result = await inventoryStore.getProductByBarcode(barcode);
    if (result.success) {
      const data = Array.isArray(result.data) ? result.data[0] : result.data;
      product.value = data;
      populateEditForm(data);
      state.value = "result";
    } else {
      // Product not found — allow adding as new
      product.value = null;
      editForm.value = {
        itemName:      "",
        stockQuantity: null,
        minStockAlert: null,
        selectedUnit:  null,
        hsnCode:       "",
        mrp:           null,
        rate:          null,
        taxRates:      [{ tax: "", rate: null }],
      };
      state.value = "result";
    }
  } catch (e) {
    errorMsg.value = "Failed to fetch product details.";
    state.value    = "idle";
  }
}

// ── Save — update if existing, add if new ────────────────────────────────────
async function handleSave() {
  saveError.value = "";
  isSaving.value  = true;

  const selectedUnitObj = unitsArray.value.find(
    (u) => u.value === editForm.value.selectedUnit
  );

  const payload = {
    itemName:      editForm.value.itemName,
    stockQuantity: editForm.value.stockQuantity,
    minStockAlert: editForm.value.minStockAlert,
    fullUnit:      selectedUnitObj?.fullUnit  ?? "",
    shortUnit:     selectedUnitObj?.shortUnit ?? "",
    hsnCode:       editForm.value.hsnCode,
    mrp:           editForm.value.mrp,
    rate:          editForm.value.rate,
    taxRates:      editForm.value.taxRates,
    barcode:       scannedBarcode.value,
  };

  try {
    let result;

    if (isExistingProduct.value) {
      // ── Update existing product ───────────────────────────────────────
      result = await inventoryStore.updateItem({
        ...payload,
        id: product.value.id,
      });
    } else {
      // ── Add new product ───────────────────────────────────────────────
      result = await inventoryStore.addItem(payload);
    }

    if (result.success) {
      emit("scanned", { ...payload, id: product.value?.id ?? null });

      setTimeout(() => inventoryStore.clearSuccessMessage(), 5000);
    } else {
      saveError.value = result.message
        ?? (result.errors ? Object.values(result.errors).flat().join(", ") : "Save failed.");
    }
  } catch (e) {
    saveError.value = "An unexpected error occurred.";
  }

  isSaving.value = false;
}

// ── Reset to idle ─────────────────────────────────────────────────────────────
function resetScanner() {
  product.value        = null;
  errorMsg.value       = "";
  saveError.value      = "";
  scannedBarcode.value = "";
  state.value          = "idle";
}
</script>

<style scoped>
.barcode-scanner-wrapper {
  width: 100%;
}

.barcode-display {
  background: #f1f3f5;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 3px 10px;
  font-size: 13px;
  color: #333;
  user-select: all;
}

/* Match InventoryForm styles exactly */
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

/* Clean up html5-qrcode default UI */
#qr-reader-scanner {
  border: none !important;
}
#qr-reader-scanner__scan_region {
  border-radius: 8px;
  overflow: hidden;
}
#qr-reader-scanner__dashboard {
  padding: 8px 0 0 0 !important;
}
#qr-reader-scanner__dashboard_section_csr button {
  background: #0066cc;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 14px;
  cursor: pointer;
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
