<!-- components/inventory/BarcodeScanner.vue -->
<script setup>
import { ref, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";

const { t } = useI18n();

const emit = defineEmits(["scanned", "cancel"]);

const inventoryStore = useInventoryStore();

const barcodeInput = ref(null);
const barcodeValue = ref("");
const barcodeError = ref("");
const scanned      = ref(false);
const checking     = ref(false);
const isDuplicate  = ref(false);

// ── Validation ────────────────────────────────────────────────────────────────
const BARCODE_MIN_LENGTH = 4;
const BARCODE_MAX_LENGTH = 50;
const BARCODE_PATTERN    = /^[a-zA-Z0-9\-_.\/]+$/;

function validateBarcode(value) {
  const v = value.trim();
  if (!v)                            return t("common.Barcode is required");
  if (v.length < BARCODE_MIN_LENGTH) return t("common.Barcode min length", { min: BARCODE_MIN_LENGTH });
  if (v.length > BARCODE_MAX_LENGTH) return t("common.Barcode max length", { max: BARCODE_MAX_LENGTH });
  if (!BARCODE_PATTERN.test(v))      return t("common.Barcode invalid characters");
  return "";
}

// ── Called from parent via template ref ──────────────────────────────────────
const openScanner = async () => {
  barcodeValue.value = "";
  barcodeError.value = "";
  scanned.value      = false;
  checking.value     = false;
  isDuplicate.value  = false;
  await nextTick();
  barcodeInput.value?.focus();
};

// ── Hardware scanner fires characters rapidly and ends with Enter ─────────────
const handleScan = async () => {
  // Step 1: frontend validation
  const error = validateBarcode(barcodeValue.value);
  if (error) {
    barcodeError.value = error;
    barcodeInput.value?.focus();
    return;
  }

  barcodeError.value = "";
  isDuplicate.value  = false;
  const code = barcodeValue.value.trim();

  // Step 2: API check — does this barcode already exist?
  checking.value = true;
  const result = await inventoryStore.checkBarcodeExists(code);
  checking.value = false;

  // Step 3: API call failed
  if (!result.success) {
    barcodeError.value = result.message ?? t("common.Failed to check barcode");
    barcodeInput.value?.focus();
    return;
  }

  // Step 4: Barcode already exists — lock field
  if (result.exists) {
    isDuplicate.value = true;
    return;
  }

  // Step 5: Barcode is new — allow
  emit("scanned", code);
  scanned.value = true;
};

// ── Clear error as user types ─────────────────────────────────────────────────
const clearError = () => {
  if (barcodeError.value) barcodeError.value = "";
};

// ── Cancel ────────────────────────────────────────────────────────────────────
const handleCancel = () => {
  barcodeValue.value = "";
  barcodeError.value = "";
  scanned.value      = false;
  checking.value     = false;
  isDuplicate.value  = false;
  emit("cancel");
};

defineExpose({ openScanner });
</script>

<template>
  <div>
    <label class="form-label">{{ t("common.Barcode") }}</label>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <div class="barcode-field-wrapper">
        <input
          ref="barcodeInput"
          type="text"
          class="form-control barcode-input"
          :class="{
            'is-invalid'   : barcodeError,
            'is-scanned'   : scanned,
            'is-duplicate' : isDuplicate,
          }"
          :placeholder="scanned ? '' : t('common.Scan barcode')"
          autocomplete="off"
          spellcheck="false"
          v-model="barcodeValue"
          :disabled="scanned || checking || isDuplicate"
          @keydown.enter.prevent="handleScan"
          @input="clearError"
        />
      </div>

      <!-- Checking spinner -->
      <div
        v-if="checking"
        class="d-flex align-items-center gap-1 text-muted small"
      >
        <div
          class="spinner-border spinner-border-sm text-primary"
          role="status"
        ></div>
        <span>{{ t("common.Checking") }}...</span>
      </div>

      <!-- Scan Again — shown after successful scan OR duplicate -->
      <button
        v-else-if="scanned || isDuplicate"
        type="button"
        class="btn btn-link text-primary p-0 fw-medium action-btn"
        @click="openScanner"
      >
        <i class="bi bi-arrow-clockwise me-1"></i>{{ t("common.Scan Again") }}
      </button>

      <!-- Cancel — shown before any scan -->
      <button
        v-else
        type="button"
        class="btn btn-link text-danger p-0 fw-medium action-btn"
        @click="handleCancel"
      >
        {{ t("common.Cancel") }}
      </button>
    </div>

    <!-- Duplicate locked notice -->
    <div v-if="isDuplicate" class="duplicate-notice mt-2">
      <i class="bi bi-lock-fill me-1"></i>
      {{ t("common.Barcode already exists") }}
      <span class="ms-1 text-muted small">—</span>
      <span class="ms-1 text-muted small">{{ t("common.Scan a different barcode") }}</span>
    </div>

    <!-- Other validation / API errors -->
    <div v-else-if="barcodeError" class="invalid-feedback d-block mt-1">
      <i class="bi bi-exclamation-circle me-1"></i>{{ barcodeError }}
    </div>

    <!-- Success hint -->
    <div v-if="scanned" class="form-text text-success mt-1">
      <i class="bi bi-check-circle me-1"></i>{{ t("common.Barcode captured") }}
    </div>
  </div>
</template>

<style scoped>
.barcode-field-wrapper {
  flex: 1;
  max-width: 440px;
}

.barcode-input {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 14px;
  font-family: monospace;
  letter-spacing: 1px;
  width: 100%;
  transition: all 0.2s;
}

.barcode-input:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

/* Validation error state */
.barcode-input.is-invalid {
  border-color: #dc3545;
}
.barcode-input.is-invalid:focus {
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.2);
}

/* Duplicate / locked state */
.barcode-input.is-duplicate {
  background-color: #fff5f5;
  border-color: #dc3545;
  color: #dc3545;
  cursor: not-allowed;
  opacity: 1;
}

/* Scanned / success state */
.barcode-input.is-scanned {
  background-color: #f0fff4;
  border-color: #198754;
  color: #198754;
  font-weight: 600;
  cursor: not-allowed;
  opacity: 1;
}

.duplicate-notice {
  font-size: 13px;
  font-weight: 500;
  color: #dc3545;
}

.action-btn {
  font-size: 14px;
  white-space: nowrap;
  text-decoration: none;
}

.action-btn:hover {
  text-decoration: underline;
}
</style>
