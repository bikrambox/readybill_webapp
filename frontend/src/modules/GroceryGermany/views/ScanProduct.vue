<template>
  <div class="add-inventory-page">
    <div class="page-content">

      <!-- Page Header -->
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <router-link to="sell">{{ $t('common.Home') }}</router-link>
              </li>
              <li class="breadcrumb-item active">{{ $t('common.Scan Product') }}</li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t('common.Scan Product') }}</h1>
        </div>
      </div>

      <!-- Main Card -->
      <div class="inventory-card">

        <!-- Error Alert -->
        <div v-if="errorMsg" class="alert alert-danger d-flex align-items-start gap-2">
          <i class="bi bi-exclamation-triangle-fill mt-1"></i>
          <div>
            {{ errorMsg }}
            <div class="mt-2">
              <button class="btn btn-sm btn-outline-danger" @click="handleRetry">
                <i class="bi bi-arrow-clockwise me-1"></i>{{ $t('common.Try Again') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Step 1: Idle — Barcode Input -->
        <div v-if="state === 'idle'" class="text-center py-5">
          <i class="bi bi-upc-scan text-primary" style="font-size: 56px;"></i>
          <h5 class="mt-3 mb-2">{{ $t('common.Scan a Product Barcode') }}</h5>
          <p class="text-muted small mb-4">
            {{ $t('common.Point your barcode scanner at a product to fetch its details') }}
          </p>

          <div class="scanner-input-wrapper mx-auto">
            <div class="input-group" :class="{ 'is-invalid-group': barcodeError }">
              <span class="input-group-text bg-primary text-white border-primary">
                <i class="bi bi-upc-scan"></i>
              </span>
              <input
                ref="barcodeInputRef"
                v-model="barcodeInputValue"
                type="text"
                class="form-control form-control-lg barcode-input"
                :class="{ 'is-invalid': barcodeError }"
                :placeholder="$t('common.scan_or_type')"
                autocomplete="off"
                spellcheck="false"
                @keydown.enter.prevent="onBarcodeEnter"
                @input="clearBarcodeError"
              />
              <button
                class="btn btn-primary px-4"
                :disabled="!barcodeInputValue.trim()"
                @click="onBarcodeEnter"
              >
                <i class="bi bi-search me-1"></i>{{ $t('common.Search') }}
              </button>
            </div>

            <!-- Inline validation error -->
            <div v-if="barcodeError" class="invalid-feedback d-block text-start mt-1">
              <i class="bi bi-exclamation-circle me-1"></i>{{ barcodeError }}
            </div>

            <p class="text-muted small mt-2 mb-0">
              <i class="bi bi-info-circle me-1"></i>
              {{ $t('common.Click the field and scan, or type the barcode manually') }}
            </p>
          </div>
        </div>

        <!-- Step 2: Loading -->
        <div v-if="state === 'loading'" class="text-center py-5">
          <div class="spinner-border text-primary mb-3" role="status"></div>
          <p class="fw-medium mb-1">{{ $t('common.Loading') }}...</p>
          <p class="text-muted small">
            {{ $t('common.Fetching details for barcode') }}:
            <code>{{ scannedBarcode }}</code>
          </p>
        </div>

        <!-- Step 3: Result — rendered by BarcodeScannerInput -->
        <BarcodeScannerInput
          v-if="state === 'result' && product"
          :prefilled-product="product"
          :prefilled-barcode="scannedBarcode"
          @scanned="handleScanned"
          @cancel="handleRetry"
        />

      </div><!-- /inventory-card -->

    </div><!-- /page-content -->

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, getCurrentInstance } from "vue";
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";
import BarcodeScannerInput from "@/modules/GroceryGermany/components/inventory/BarcodeScannerInput.vue";

const emit = defineEmits(["view"]);

const inventoryStore = useInventoryStore();
const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;

// ── State machine: idle | loading | result ────────────────────────────────────
const state             = ref("idle");
const scannedBarcode    = ref("");
const product           = ref(null);
const errorMsg          = ref("");
const barcodeError      = ref("");   // ← inline field validation error
const barcodeInputRef   = ref(null);
const barcodeInputValue = ref("");

// ── Validation rules ──────────────────────────────────────────────────────────
const BARCODE_MIN_LENGTH = 4;
const BARCODE_MAX_LENGTH = 50;
// Allows digits, letters, hyphens, underscores, dots, slashes (common barcode charsets)
const BARCODE_PATTERN    = /^[a-zA-Z0-9\-_.\/]+$/;

function validateBarcode(value) {
  const v = value.trim();

  if (!v) {
    return "Barcode is required.";
  }
  if (v.length < BARCODE_MIN_LENGTH) {
    return `Barcode must be at least ${BARCODE_MIN_LENGTH} characters.`;
  }
  if (v.length > BARCODE_MAX_LENGTH) {
    return `Barcode must not exceed ${BARCODE_MAX_LENGTH} characters.`;
  }
  if (!BARCODE_PATTERN.test(v)) {
    return "Barcode contains invalid characters. Only letters, digits, -, _, ., / are allowed.";
  }

  return ""; // valid
}

// ── Clear inline error on input ───────────────────────────────────────────────
const clearBarcodeError = () => {
  if (barcodeError.value) barcodeError.value = "";
};

// ── Auto-focus on mount and on return to idle ─────────────────────────────────
onMounted(() => focusBarcodeInput());

const focusBarcodeInput = async () => {
  await nextTick();
  barcodeInputRef.value?.focus();
};

// ── Handle Enter key or Search button click ───────────────────────────────────
const onBarcodeEnter = async () => {
  const barcode = barcodeInputValue.value.trim();

  // Run validation first
  const validationError = validateBarcode(barcode);
  if (validationError) {
    barcodeError.value = validationError;
    barcodeInputRef.value?.focus();
    return;
  }

  barcodeError.value      = "";
  scannedBarcode.value    = barcode;
  barcodeInputValue.value = "";
  errorMsg.value          = "";
  product.value           = null;

  await fetchProduct(barcode);
};

// ── Fetch product from API ────────────────────────────────────────────────────
const fetchProduct = async (barcode) => {
  state.value = "loading";
  try {
    const result = await inventoryStore.getProductByBarcode(barcode);
    if (result.success) {
      product.value = Array.isArray(result.data)
        ? result.data[0]
        : result.data;
      state.value = "result";
    } else {
      errorMsg.value = result.message ?? "No product found for this barcode.";
      state.value    = "idle";
      focusBarcodeInput();
    }
  } catch (e) {
    errorMsg.value = "Failed to fetch product details.";
    state.value    = "idle";
    focusBarcodeInput();
  }
};

// ── Handle scanned emit from BarcodeScannerInput ──────────────────────────────
const handleScanned = (data) => {
  emit("view", data);
};

// ── Retry ─────────────────────────────────────────────────────────────────────
const handleRetry = async () => {
  product.value           = null;
  errorMsg.value          = "";
  barcodeError.value      = "";
  scannedBarcode.value    = "";
  barcodeInputValue.value = "";
  state.value             = "idle";
  focusBarcodeInput();
};
</script>

<style scoped>
.add-inventory-page {
  display: flex;
  flex-direction: column;
  min-height: 100%;
}
.page-content {
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  flex: 1;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
}
.breadcrumb {
  margin-bottom: 10px;
  background: transparent;
  padding: 0;
  font-size: 14px;
}
.breadcrumb-item a { color: #6c757d; text-decoration: none; }
.breadcrumb-item.active { color: #333; }
.page-title { font-size: 28px; font-weight: 600; color: #333; margin: 0; }
.inventory-card {
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

/* Barcode input */
.scanner-input-wrapper {
  max-width: 480px;
}
.barcode-input {
  font-family: monospace;
  font-size: 1.1rem;
  letter-spacing: 1px;
}
.barcode-input:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.2);
}

/* Red border on the input-group when invalid */
.is-invalid-group .input-group-text {
  border-color: #dc3545;
}
.is-invalid-group .btn {
  border-color: #dc3545;
}

@media (max-width: 768px) {
  .inventory-card { padding: 20px; }
  .page-title { font-size: 22px; }
}
</style>
