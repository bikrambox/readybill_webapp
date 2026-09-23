<template>
  <div class="search-card">
    <div class="search-row">
      <!-- Item Name -->
      <div class="form-group search-group">
        <label for="itemName">{{ $t("common.Item Name") }}</label>
        <div class="search-wrapper">
          <input
            type="text"
            id="itemName"
            class="form-control search-input"
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
            :title="isScanMode ? $t('common.Stop Scanning') : $t('common.Scan Barcode')"
            :class="{ scanning: isScanMode }"
          >
            <i class="bi bi-upc-scan"></i>
          </button>

          <!-- Loading Spinner -->
          <div v-if="quickSellStore.searchLoading" class="search-loading">
            <i class="bi bi-arrow-repeat spin"></i>
          </div>

          <!-- Scan Mode -->
          <div v-if="isScanMode" class="scan-mode-badge">
            {{ $t("common.Scan now") }}...
          </div>

          <!-- Suggestions -->
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
                  <span v-if="suggestion.unit" class="unit-badge">
                    {{ suggestion.unit }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- No results -->
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

      <!-- Actions -->
      <div class="form-group button-group">
        <label class="invisible-label">{{ $t("common.Actions") }}</label>
        <div class="action-buttons">
          <button
            type="button"
            class="btn btn-add"
            @click="handleAdd(false)"
            :disabled="!isFormValid || quickSellStore.loading"
          >
            <span v-if="quickSellStore.loading && !isRefundAction">
              <i class="bi bi-arrow-repeat spin"></i>
              {{ $t("common.Adding") }}...
            </span>
            <span v-else>
              <i class="bi bi-plus"></i>
              {{ $t("common.Add") }}
            </span>
          </button>

          <button
            type="button"
            class="btn btn-refund"
            @click="handleAdd(true)"
            :disabled="!isFormValid || quickSellStore.loading"
          >
            <span v-if="quickSellStore.loading && isRefundAction">
              <i class="bi bi-arrow-repeat spin"></i>
              {{ $t("common.Processing") }}...
            </span>
            <span v-else>
              <i class="bi bi-arrow-return-left"></i>
              {{ $t("common.Refund") }}
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { useQuickSellStore } from "@/modules/GroceryIndia/stores/quickSellStore";
import { useInventoryStore } from "@/modules/GroceryIndia/stores/inventory";

const { t } = useI18n();

const quickSellStore = useQuickSellStore();
const inventoryStore = useInventoryStore();

const emit = defineEmits(["add-item", "error"]);

// Decimal unit config
const decimalUnits = ["g", "kg", "ml", "l"];

const quantityConfig = computed(() => {
  const isDecimal = decimalUnits.includes(formData.unit?.toLowerCase());
  return {
    min: isDecimal ? 0.1 : 1,
    step: isDecimal ? 0.1 : 1,
  };
});

// Form state
const formData = reactive({
  itemName: "",
  quantity: "",
  unit: "",
  rate: 0,
  itemId: null,
});

const showSuggestions = ref(false);
const unitOptions = ref([]);
const isRefundAction = ref(false);
let searchTimeout = null;

const isFormValid = computed(
  () => !!(formData.itemName && formData.quantity && formData.unit && formData.itemId)
);

// Barcode scanner
const barcodeBuffer = ref("");
const isScanMode = ref(false);
let lastKeyTime = 0;
let scannerTimeout = null;

const activateBarcodeScan = () => {
  if (isScanMode.value) {
    isScanMode.value = false;
    document.removeEventListener("keydown", handleGlobalKeydown);
    barcodeBuffer.value = "";
    return;
  }

  isScanMode.value = true;
  barcodeBuffer.value = "";
  lastKeyTime = 0;
  document.addEventListener("keydown", handleGlobalKeydown);
};

const handleGlobalKeydown = (event) => {
  const now = Date.now();
  const timeDiff = now - lastKeyTime;
  lastKeyTime = now;

  if (event.key === "Enter") {
    const scannedCode = barcodeBuffer.value.trim();
    barcodeBuffer.value = "";

    if (scannedCode) {
      event.preventDefault();
      fetchProduct(scannedCode);
    }
    return;
  }

  if (event.key.length !== 1 || event.ctrlKey || event.altKey || event.metaKey) {
    return;
  }

  if (barcodeBuffer.value.length === 0 || timeDiff < 60) {
    barcodeBuffer.value += event.key;

    if (scannerTimeout) clearTimeout(scannerTimeout);
    scannerTimeout = setTimeout(() => {
      barcodeBuffer.value = "";
    }, 300);
  }
};

// Fetch by barcode
const fetchProduct = async (barcode) => {
  quickSellStore.searchLoading = true;
  formData.itemName = barcode;

  try {
    const result = await inventoryStore.getProductByBarcode(barcode);

    if (result.success && result.data) {
      const product = Array.isArray(result.data) ? result.data[0] : result.data;

      if (!product) {
        showSuggestions.value = true;
        await quickSellStore.fetchSuggestions(barcode);
        return;
      }

      const suggestion = {
        id: product.id,
        name: product.name ?? product.item_name,
        rate: product.sale_price ?? product.rate ?? 0,
        unit: product.unit ?? product.short_unit ?? "",
      };

      await selectSuggestion(suggestion);

      if (!formData.quantity) {
        formData.quantity = 1;
      }
    } else {
      showSuggestions.value = true;
      await quickSellStore.fetchSuggestions(barcode);
    }
  } catch (e) {
    emit("error", t("common.Failed to fetch product details."));
    formData.itemName = "";
  } finally {
    quickSellStore.searchLoading = false;
  }
};

// Search handlers
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
  if (formData.itemName.trim().length >= 1 && quickSellStore.suggestions.length > 0) {
    showSuggestions.value = true;
  }
};

const selectSuggestion = async (suggestion) => {
  formData.itemName = suggestion.name;
  formData.itemId = suggestion.id;
  formData.rate = suggestion.rate || 0;
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

// Add / Refund
const handleAdd = async (isRefund) => {
  if (!isFormValid.value) return;

  isRefundAction.value = isRefund;

  const stockCheck = await quickSellStore.checkStockQuantity(
    formData.itemId,
    formData.quantity,
    formData.unit,
    isRefund ? 1 : 0
  );

  if (!stockCheck.success) {
    emit("error", stockCheck.message || t("common.Failed to check stock"));
    isRefundAction.value = false;
    return;
  }

  if (stockCheck.stockStatus === "1") {
    const salePrice = parseFloat(stockCheck.itemData.sale_price);
    const calculatedAmount = parseFloat(formData.quantity) * salePrice;
    const finalAmount = isRefund ? -Math.abs(calculatedAmount) : calculatedAmount;

    const newItem = {
      id: formData.itemId,
      name: stockCheck.itemData.item_name,
      quantity: parseFloat(formData.quantity),
      unit: formData.unit,
      rate: salePrice,
      amount: finalAmount,
      isRefund: isRefund ? 1 : 0,
      location: "refund",
    };

    const result = await quickSellStore.addItemToCart(newItem);

    if (result.success) {
      emit("add-item", newItem);
      resetForm();
    } else {
      emit("error", result.message || t("common.Failed to add item to cart"));
    }
  } else if (stockCheck.stockStatus === "2") {
    emit(
      "error",
      t("common.low_stock_v1", {
        qauantity_added_on_cart: parseFloat(stockCheck.qauantity_added_on_cart).toFixed(
          2
        ),
        available_stock: parseFloat(stockCheck.available_stock).toFixed(2),
      })
    );
  } else {
    emit(
      "error",
      t("common.low_stock") + ": " + parseFloat(stockCheck.available_stock).toFixed(2)
    );
  }

  isRefundAction.value = false;
};

// Reset
const resetForm = () => {
  formData.itemName = "";
  formData.quantity = "";
  formData.unit = "";
  formData.rate = 0;
  formData.itemId = null;
  unitOptions.value = [];
  quickSellStore.clearSuggestions();
};

// Click outside
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
  document.removeEventListener("keydown", handleGlobalKeydown);

  if (searchTimeout) clearTimeout(searchTimeout);
  if (scannerTimeout) clearTimeout(scannerTimeout);
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

.button-group {
  min-width: 200px;
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
  padding: 0 12px;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-wrapper .search-input {
  padding-right: 70px;
}

.form-control:focus,
.form-select:focus {
  border-color: #0066cc;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

.search-input.scan-active {
  border-color: #28a745;
  box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.2);
  background-color: #f6fff8;
}

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

.barcode-scan-btn.scanning {
  color: #28a745;
  animation: pulse 1s ease-in-out infinite;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.4;
  }
}

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
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
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

.suggestion-item:last-child {
  border-bottom: none;
}

.suggestion-item:hover {
  background-color: #f8f9fa;
}

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

.no-results {
  padding: 20px;
  text-align: center;
}

.no-results-text {
  color: #6c757d;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-buttons .btn {
  height: 42px;
  border: none;
  border-radius: 8px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  font-size: 14px;
  flex: 1;
}

.btn-add {
  background-color: #0066cc;
  color: white;
}

.btn-add:hover:not(:disabled) {
  background-color: #0052a3;
}

.btn-refund {
  background-color: #dc3545;
  color: white;
}

.btn-refund:hover:not(:disabled) {
  background-color: #c82333;
}

.btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

@media (max-width: 768px) {
  .search-card {
    padding: 20px;
  }

  .search-row {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .button-group {
    margin-top: 5px;
    min-width: auto;
  }

  .invisible-label {
    display: none;
  }

  .action-buttons {
    flex-direction: column;
    gap: 10px;
  }

  .action-buttons .btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .search-card {
    padding: 15px;
  }
}
</style>
