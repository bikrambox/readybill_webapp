<template>
  <div class="search-card">
    <div class="search-row">
      <div class="form-group search-group">
        <label for="itemName">Item Name</label>
        <div class="search-wrapper">
          <input 
            type="text" 
            id="itemName"
            class="form-control" 
            placeholder="Search"
            v-model="formData.itemName"
            @input="handleSearch"
            @focus="handleFocus"
            autocomplete="off"
          />
          
          <!-- Loading Spinner -->
          <div v-if="quickSellStore.searchLoading" class="search-loading">
            <i class="bi bi-arrow-repeat spin"></i>
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
                  <span v-if="suggestion.unit" class="unit-badge">{{ suggestion.unit }}</span>
                  <span v-if="parseFloat(suggestion.stock) > 0" class="stock-info">
                    Stock: {{ parseFloat(suggestion.stock).toFixed(2) }}
                  </span>
                  <span v-else class="stock-info out-of-stock">
                    Out of Stock
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- No Results -->
          <div 
            v-if="showSuggestions && !quickSellStore.searchLoading && formData.itemName.length >= 1 && quickSellStore.suggestions.length === 0" 
            class="suggestions-dropdown no-results"
          >
            <div class="no-results-text">
              <i class="bi bi-search"></i>
              No items found
            </div>
          </div>
        </div>
      </div>
      
      <div class="form-group">
        <label for="quantity">Quantity</label>
        <input 
          type="number" 
          id="quantity"
          class="form-control" 
          placeholder="20"
          v-model="formData.quantity"
          min="0.1"
          step="0.1"
        />
      </div>

      <div class="form-group unit-group">
        <label for="unit">Unit</label>
        <select 
          id="unit" 
          class="form-select" 
          v-model="formData.unit"
          :disabled="unitOptions.length === 0"
        >
          <option value="">Unit</option>
          <option 
            v-for="unit in unitOptions" 
            :key="unit" 
            :value="unit"
          >
            {{ unit }}
          </option>
        </select>
      </div>

      <div class="form-group button-group">
        <label class="invisible-label">Actions</label>
        <div class="action-buttons">
          <button 
            type="button"
            class="btn btn-add" 
            @click="handleAdd(false)" 
            :disabled="!isFormValid || quickSellStore.loading"
          >
            <span v-if="quickSellStore.loading && !isRefundAction">
              <i class="bi bi-arrow-repeat spin"></i>
              Adding...
            </span>
            <span v-else>
              <i class="bi bi-plus"></i>
              Add
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
              Processing...
            </span>
            <span v-else>
              <i class="bi bi-arrow-return-left"></i>
              Refund
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { useQuickSellStore } from '@/modules/GroceryGermany/stores/quickSellStore';

const quickSellStore = useQuickSellStore();
const emit = defineEmits(['add-item']);

const formData = reactive({
  itemName: '',
  quantity: '',
  unit: '',
  rate: 0,
  itemId: null
});

const showSuggestions = ref(false);
const unitOptions = ref([]);
const isRefundAction = ref(false);
let searchTimeout = null;

const isFormValid = computed(() => {
  return formData.itemName && formData.quantity && formData.unit && formData.itemId;
});

const handleSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

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

const handleAdd = async (isRefund) => {
  if (!isFormValid.value) return;

  // Set which action is being performed
  isRefundAction.value = isRefund;

  // Check stock quantity first
  const stockCheck = await quickSellStore.checkStockQuantity(
    formData.itemId,
    formData.quantity,
    formData.unit
  );

  if (!stockCheck.success) {
    alert(stockCheck.message || 'Failed to check stock');
    isRefundAction.value = false;
    return;
  }

  // If stockStatus is 1, proceed to add
  if (stockCheck.stockStatus === '1') {
    // Use the sale_price from stock check response
    const salePrice = parseFloat(stockCheck.itemData.sale_price);
    const calculatedAmount = parseFloat(formData.quantity) * salePrice;

    // For refund (isRefund = 1), make amount negative
    const finalAmount = isRefund ? -Math.abs(calculatedAmount) : calculatedAmount;

    const newItem = {
      id: formData.itemId,
      name: stockCheck.itemData.item_name,
      quantity: parseFloat(formData.quantity),
      unit: formData.unit,
      rate: salePrice,
      amount: finalAmount,
      isRefund: isRefund ? 1 : 0,
      location: 'refund',
    };

    // Add item to cart via API
    const result = await quickSellStore.addItemToCart(newItem);

    if (result.success) {
      emit('add-item', newItem);
      
      // Reset form
      formData.itemName = '';
      formData.quantity = '';
      formData.unit = '';
      formData.rate = 0;
      formData.itemId = null;
      unitOptions.value = [];
      quickSellStore.clearSuggestions();
    } else {
      alert(result.message || 'Failed to add item to cart');
    }
  } else {
    alert('Item cannot be added. Stock status check failed.');
  }
  
  isRefundAction.value = false;
};

const handleClickOutside = (event) => {
  if (!event.target.closest('.search-wrapper')) {
    showSuggestions.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
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
  transition: border-color 0.2s;
}

.form-control:focus,
.form-select:focus {
  border-color: #0066cc;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

.search-loading {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #0066cc;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
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

.stock-info {
  color: #28a745;
  font-weight: 500;
}

.stock-info.out-of-stock {
  color: #dc3545;
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
