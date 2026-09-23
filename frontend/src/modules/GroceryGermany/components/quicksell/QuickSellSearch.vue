<template>
  <div class="search-card">
    <div class="search-row">
      <!-- Item Name with Barcode Button -->
      <div class="form-group search-group">
        <label for="itemName">{{ $t("common.Item Name") }}</label>
        <div class="search-wrapper">
          <input
            type="text"
            id="itemName"
            class="form-control"
            :class="{ 'scan-active': isScanMode }"
            placeholder="Search or scan barcode"
            v-model="formData.itemName"
            @input="handleSearch"
            @focus="handleFocus"
            autocomplete="off"
          />

          <!-- Barcode Scan Button -->
          <button
            class="barcode-scan-btn"
            type="button"
            @click="activateBarcodeScan"
            :title="$t('common.Scan Barcode')"
            :class="{ 'scanning': isScanMode }"
          >
            <i class="bi bi-upc-scan"></i>
          </button>

          <!-- Loading Spinner -->
          <div v-if="quickSellStore.searchLoading" class="search-loading">
            <i class="bi bi-arrow-repeat spin"></i>
          </div>

          <!-- Scan Mode Indicator -->
          <div v-if="isScanMode" class="scan-mode-badge">
            {{ $t("common.Scan now") }}...
          </div>

          <!-- Suggestions Dropdown -->
          <div
            v-if="showSuggestions && quickSellStore.suggestions.length > 0"
            class="suggestions-dropdown"
          >
            <div
              v-for="suggestion in quickSellStore.suggestions"
              :key="suggestion.id"
              class="suggestion-item"
              @click="selectSuggestion(suggestion)"
            >
              <div class="suggestion-content">
                <div class="suggestion-name">{{ suggestion.name }}</div>
                <div class="suggestion-info">
                  <span v-if="suggestion.unit" class="unit-badge">{{
                    suggestion.unit
                  }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- No Results -->
          <div
            v-if="
              showSuggestions &&
              !quickSellStore.searchLoading &&
              formData.itemName.length >= 1 &&
              quickSellStore.suggestions.length === 0
            "
            class="suggestions-dropdown no-results"
          >
            <div class="no-results-text">
              <i class="bi bi-search"></i>
              {{ $t("common.No items found") }}
            </div>
          </div>
        </div>
      </div>

      <!-- Quantity -->
      <div class="form-group">
        <label for="quantity">{{ $t("common.Quantity") }}</label>
        <input
          type="number"
          id="quantity"
          class="form-control"
          placeholder="0"
          v-model="formData.quantity"
          :min="quantityConfig.min"
          :step="quantityConfig.step"
          :disabled="unitOptions.length === 0"
        />
      </div>

      <!-- Unit -->
      <div class="form-group unit-group">
        <label for="unit">{{ $t("common.Unit") }}</label>
        <select
          id="unit"
          class="form-select"
          v-model="formData.unit"
          :disabled="unitOptions.length === 0"
        >
          <option value="">Unit</option>
          <option v-for="unit in unitOptions" :key="unit" :value="unit">
            {{ unit }}
          </option>
        </select>
      </div>

      <!-- Add Button -->
      <div class="form-group add-btn-group">
        <label class="invisible-label">{{ $t("common.Add") }}</label>
        <button
          class="btn btn-add"
          @click="handleAdd"
          :disabled="!isFormValid || quickSellStore.loading"
        >
          <span v-if="quickSellStore.loading">
            <i class="bi bi-arrow-repeat spin"></i>
            {{ $t("common.Adding") }}...
          </span>
          <span v-else>
            <i class="bi bi-plus"></i>
            {{ $t("common.Add") }}
          </span>
        </button>
      </div>
    </div>
  </div>

  <!-- Hidden barcode input — captures hardware scanner keystrokes -->
  <input
    ref="barcodeInputRef"
    type="text"
    class="barcode-hidden-input"
    v-model="barcodeBuffer"
    @keydown="handleBarcodeKeydown"
    @blur="handleBarcodeBlur"
    autocomplete="off"
  />
</template>

<script setup>
import { reactive, computed, ref, onMounted, onBeforeUnmount } from "vue";
import { useQuickSellStore } from "@/modules/GroceryGermany/stores/quickSellStore";
import { useInventoryStore } from "@/modules/GroceryGermany/stores/inventory";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const quickSellStore = useQuickSellStore();
const inventoryStore = useInventoryStore();

const emit = defineEmits(["add-item"]);

// ─── Decimal unit config ───────────────────────────────────────────────────
const decimalUnits = ["g", "kg", "ml", "l"];

const quantityConfig = computed(() => {
  const isDecimal = decimalUnits.includes(formData.unit?.toLowerCase());
  return {
    min:  isDecimal ? 0.1 : 1,
    step: isDecimal ? 0.1 : 1,
  };
});

// ─── Form State ────────────────────────────────────────────────────────────
const formData = reactive({
  itemName: "",
  quantity: "",
  unit:     "",
  rate:     0,
  itemId:   null,
});

const showSuggestions = ref(false);
const unitOptions      = ref([]);
let   searchTimeout    = null;

const isFormValid = computed(() =>
  formData.itemName && formData.quantity && formData.unit && formData.itemId
);

// ─── Hardware Barcode Scanner ──────────────────────────────────────────────
const barcodeInputRef = ref(null);
const barcodeBuffer   = ref("");
const isScanMode      = ref(false);
let   scanModeTimeout = null;

/**
 * Called when user clicks the barcode icon button.
 * Clears state and focuses the hidden input for scanner capture.
 */
const activateBarcodeScan = () => {
  isScanMode.value      = true;
  barcodeBuffer.value   = "";
  formData.itemName     = "";       // clear stale name
  showSuggestions.value = false;    // hide old dropdown
  quickSellStore.clearSuggestions();

  barcodeInputRef.value?.focus();

  if (scanModeTimeout) clearTimeout(scanModeTimeout);
  scanModeTimeout = setTimeout(() => {
    isScanMode.value = false;
  }, 10000);
};

/**
 * Hardware scanners fire characters rapidly and end with Enter.
 * Mirror each keystroke into the visible field, then fetch on Enter.
 */
const handleBarcodeKeydown = (event) => {
  if (event.key === "Enter") {
    event.preventDefault();
    const scannedCode   = barcodeBuffer.value.trim();
    barcodeBuffer.value = "";
    isScanMode.value    = false;

    if (scanModeTimeout) clearTimeout(scanModeTimeout);

    if (scannedCode) {
      fetchProduct(scannedCode);
    }
  } else {
    // Mirror characters into visible input so user sees barcode being typed
    formData.itemName = barcodeBuffer.value;
  }
};

const handleBarcodeBlur = () => {
  setTimeout(() => {
    isScanMode.value = false;
  }, 200);
};

// ─── Fetch Product by Barcode ──────────────────────────────────────────────
const fetchProduct = async (barcode) => {
  quickSellStore.searchLoading = true;
  formData.itemName = barcode; // show scanned value in field while loading

  try {
    const result = await inventoryStore.getProductByBarcode(barcode);

    if (result.success && result.data) {
      const product = Array.isArray(result.data) ? result.data[0] : result.data;

      if (!product) {
        // No product found — fall back to name/tag suggestion search
        showSuggestions.value = true;
        await quickSellStore.fetchSuggestions(barcode);
        return;
      }

      const suggestion = {
        id:   product.id,
        name: product.name ?? product.item_name,
        rate: product.sale_price ?? product.rate ?? 0,
        unit: product.unit ?? product.short_unit ?? "",
      };

      // Auto-select — no dropdown needed for exact barcode match
      await selectSuggestion(suggestion);

      if (!formData.quantity) {
        formData.quantity = 0;
      }
    } else {
      // Barcode not in DB — fall back to suggestion search with barcode string
      showSuggestions.value = true;
      await quickSellStore.fetchSuggestions(barcode);
    }
  } catch (e) {
    alert(t("common.Failed to fetch product details."));
    formData.itemName = "";
  } finally {
    quickSellStore.searchLoading = false;
  }
};

// ─── Search Handlers ───────────────────────────────────────────────────────
const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);

  showSuggestions.value = true;

  searchTimeout = setTimeout(() => {
    if (formData.itemName.trim().length >= 1) {
      quickSellStore.fetchSuggestions(formData.itemName.trim());
    } else {
      quickSellStore.clearSuggestions();
    }
  }, 300);
};

const handleFocus = () => {
  if (
    formData.itemName.trim().length >= 1 &&
    quickSellStore.suggestions.length > 0
  ) {
    showSuggestions.value = true;
  }
};

const selectSuggestion = async (suggestion) => {
  formData.itemName = suggestion.name;
  formData.itemId   = suggestion.id;
  formData.rate     = suggestion.rate || 0;
  formData.quantity = 1;
  
  showSuggestions.value = false;
  quickSellStore.clearSuggestions();
  quickSellStore.setSelectedItem(suggestion);

  const unitsData = await quickSellStore.fetchRelatedUnits(suggestion.id);

  if (unitsData.units && unitsData.units.length > 0) {
    unitOptions.value = unitsData.units;
    if (unitsData.defaultUnit) {
      formData.unit = unitsData.defaultUnit;
    }
  }
};

// ─── Add to Cart ───────────────────────────────────────────────────────────
const handleAdd = async () => {
  if (!isFormValid.value) return;

  const stockCheck = await quickSellStore.checkStockQuantity(
    formData.itemId,
    formData.quantity,
    formData.unit
  );

  if (!stockCheck.success) {
    alert(stockCheck.message || t("common.Failed to check stock"));
    return;
  }

  if (stockCheck.stockStatus === "1") {
    const salePrice = parseFloat(stockCheck.itemData.sale_price);

    const newItem = {
      id:       formData.itemId,
      name:     stockCheck.itemData.item_name,
      quantity: parseFloat(formData.quantity),
      unit:     formData.unit,
      rate:     salePrice,
      amount:   stockCheck.amount,
      location: "sell",
    };

    const result = await quickSellStore.addItemToCart(newItem);

    if (result.success) {
      emit("add-item", newItem);
      resetForm();
    } else {
      alert(result.message || t("common.Failed to add item to cart"));
    }
  } else if (stockCheck.stockStatus === "2") {

    // Quantity 2 already present in the cart, where as item quantity avaialbe: 3, Sorry cannot add item.
    alert(
      t("common.low_stock_v1", {
        qauantity_added_on_cart: parseFloat(
          stockCheck.qauantity_added_on_cart
        ).toFixed(2),
        available_stock: parseFloat(stockCheck.available_stock).toFixed(2),
      })
    );

  } else {
    alert(
      t("common.low_stock") +
        ": " +
        parseFloat(stockCheck.available_stock).toFixed(2)
    );
  }
};

// ─── Reset Form ────────────────────────────────────────────────────────────
const resetForm = () => {
  formData.itemName = "";
  formData.quantity = "";
  formData.unit     = "";
  formData.rate     = 0;
  formData.itemId   = null;
  unitOptions.value = [];
  quickSellStore.clearSuggestions();
};

// ─── Click Outside ─────────────────────────────────────────────────────────
const handleClickOutside = (event) => {
  if (!event.target.closest(".search-wrapper")) {
    showSuggestions.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
  if (searchTimeout)   clearTimeout(searchTimeout);
  if (scanModeTimeout) clearTimeout(scanModeTimeout);
});
</script>

<style scoped>
.search-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 25px;
}

.search-row {
  display: grid;
  grid-template-columns: 1fr 150px 150px auto;
  gap: 15px;
  align-items: end;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.search-group {
  position: relative;
}

.search-wrapper {
  position: relative;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}

.invisible-label {
  visibility: hidden;
}

.form-control,
.form-select {
  height: 42px;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  /*padding: 0 70px 0 12px;*/
  padding: 0 12px;  
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus,
.form-select:focus {
  border-color: #0066cc;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

/* Green border + tint when scan mode is active */
.form-control.scan-active {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.2);
  background-color: #f6fff8;
}

/* Barcode icon button inside input */
.barcode-scan-btn {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #0066cc;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  z-index: 2;
  transition: color 0.2s;
}

.barcode-scan-btn:hover {
  color: #0052a3;
}

/* Pulsing green when scan mode is active */
.barcode-scan-btn.scanning {
  color: #28a745;
  animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}

/* "Scan now..." badge below input */
.scan-mode-badge {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  background: #28a745;
  color: white;
  font-size: 11px;
  font-weight: 500;
  padding: 2px 10px;
  border-radius: 20px;
  z-index: 999;
  white-space: nowrap;
}

/* Loading spinner sits left of barcode button */
.search-loading {
  position: absolute;
  right: 38px;
  top: 50%;
  transform: translateY(-50%);
  color: #0066cc;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  margin-top: 4px;
  max-height: 300px;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
}

.suggestion-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s;
}

.suggestion-item:last-child { border-bottom: none; }
.suggestion-item:hover      { background-color: #f8f9fa; }

.suggestion-content {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.suggestion-name {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  line-height: 1.4;
}

.suggestion-info {
  display: flex;
  gap: 10px;
  align-items: center;
  font-size: 12px;
}

.unit-badge {
  background-color: #e3f2fd;
  color: #1976d2;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 500;
}

.no-results      { padding: 20px; text-align: center; }
.no-results-text {
  color: #6c757d;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

/* Hidden input that captures hardware scanner keystrokes */
.barcode-hidden-input {
  position: fixed;
  top: -9999px;
  left: -9999px;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.btn-add {
  height: 42px;
  background-color: #0066cc;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-add:hover:not(:disabled) { background-color: #0052a3; }
.btn-add:disabled              { background-color: #ccc; cursor: not-allowed; }

@media (max-width: 768px) {
  .search-card  { padding: 20px; }
  .search-row   { grid-template-columns: 1fr; gap: 15px; }
  .add-btn-group { margin-top: 5px; }
  .invisible-label { display: none; }
  .btn-add { width: 100%; justify-content: center; }
}

@media (max-width: 480px) {
  .search-card { padding: 15px; }
}
</style>
