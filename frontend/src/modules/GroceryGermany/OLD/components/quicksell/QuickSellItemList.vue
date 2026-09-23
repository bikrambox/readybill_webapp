<template>
  <div class="mobile-item-container">
    <div v-if="quickSellStore.items.length === 0" class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>No items added yet</p>
    </div>

    <div class="mobile-item-scroll" v-else>
      <div
        class="item-card"
        v-for="(item, index) in quickSellStore.items"
        :key="item.cartId"
      >
        <div class="item-card-header">
          <h6 class="item-name">{{ item.name }}</h6>
          <span v-if="item.isRefund === 1" class="refund-badge">Refund</span>
          <button
            class="btn-delete-mobile"
            @click="handleDelete(item)"
            title="Delete item"
            :disabled="quickSellStore.loading"
          >
            <i class="bi bi-trash"></i>
          </button>
        </div>
        <div class="item-card-body">
          <div class="item-row">
            <span class="label">Quantity</span>
            <input
              type="number"
              class="input-mobile"
              v-model.number="item.quantity"
              @blur="updateAmount(index, item)"
              min="0.1"
              step="0.1"
            />
          </div>
          <div class="item-row">
            <span class="label">Unit</span>
            <span class="value">{{ item.unit }}</span>
          </div>
          <div class="item-row">
            <span class="label">Rate</span>
            <input
              type="number"
              class="input-mobile"
              v-model.number="item.rate"
              @blur="updateAmount(index, item)"
              min="0"
              step="0.01"
            />
          </div>
          <div class="item-row">
            <span class="label">Amount</span>
            <span
              class="value amount"
              :class="{ 'amount-negative': item.isRefund === 1 }"
            >
              {{ formatCurrency(item.amount) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Single modal instance -->
  <ConfirmModal
    ref="confirmModal"
    :message="confirmData.modalMessage"
    :title="confirmData.modalTitle"
    @confirm="deleteItem"
  />
</template>

<script setup>
import { computed, watch, ref } from "vue";
import { useQuickSellStore } from "@/modules/GroceryGermany/stores/quickSellStore";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";

// Accept location as prop
const props = defineProps({
  location: {
    type: String,
    required: false,
    default: "sell",
    validator: (value) => ["sell", "refund"].includes(value),
  },
});

const quickSellStore = useQuickSellStore();
const confirmModal = ref(null);
const currentItemId = ref(null);

const confirmData = ref({
  message: "",
  titel: "",
});

// Watch for location changes and update store
watch(
  () => props.location,
  (newLocation) => {
    if (newLocation) {
      quickSellStore.setLocation(newLocation);
    }
  },
  { immediate: true }
);

const formatCurrency = (amount) => {
  const absAmount = Math.abs(amount || 0);
  const sign = amount < 0 ? "-" : "";
  return `${sign}₹${absAmount.toFixed(2)}`;
};

const updateAmount = async (index, item) => {
  // Recalculate amount based on isRefund status
  const baseAmount = item.quantity * item.rate;
  item.amount = item.isRefund === 1 ? -Math.abs(baseAmount) : baseAmount;

  // Update cart item via API
  const result = await quickSellStore.updateCartItem(item.cartId, item);

  if (!result.success) {
    alert(result.message || "Failed to update item");
  }
};

const handleDelete = async (item) => {
  modalMessage.value = `Are you sure you want to delete ${item.name}?`;
  currentItemId.value = item.id;
  confirmModal.value.show();

  // // First confirmation
  // const firstConfirm = confirm(
  //   `Are you sure you want to delete "${item.name}"?`
  // );

  // if (!firstConfirm) return;

  // // Second confirmation
  // const secondConfirm = confirm(
  //   `This action cannot be undone. Do you really want to remove "${item.name}" from the cart?`
  // );

  // if (!secondConfirm) return;

  // // Proceed with deletion
  // const result = await quickSellStore.deleteCartItem(item.cartId);

  // if (!result.success) {
  //   alert(result.message || 'Failed to delete item');
  // }
};

async function deleteItem() {
  // Proceed with deletion
  const result = await quickSellStore.deleteCartItem(item.cartId);

  // if (!result.success) {
  //   alert(result.message || 'Failed to delete item');
  // }
}
</script>

<style scoped>
/* Previous styles remain the same */
.mobile-item-container {
  width: 100%;
}

.mobile-item-scroll {
  max-height: 400px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-right: 5px;
}

.mobile-item-scroll::-webkit-scrollbar {
  width: 6px;
}

.mobile-item-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.mobile-item-scroll::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.mobile-item-scroll::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.empty-state {
  padding: 40px 20px;
  text-align: center;
  color: #6c757d;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 10px;
  display: block;
}

.empty-state p {
  margin: 0;
  font-size: 16px;
}

.item-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 15px;
}

.item-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  gap: 8px;
}

.item-name {
  font-size: 15px;
  font-weight: 600;
  color: #333;
  margin: 0;
  flex: 1;
  padding-right: 10px;
}

.refund-badge {
  background-color: #dc3545;
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
}

.btn-delete-mobile {
  background: transparent;
  border: none;
  color: #dc3545;
  cursor: pointer;
  padding: 4px;
  transition: opacity 0.2s;
}

.btn-delete-mobile:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-delete-mobile i {
  font-size: 18px;
}

.item-card-body {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
}

.item-row .label {
  color: #6c757d;
  flex: 0 0 auto;
}

.item-row .value {
  color: #333;
  font-weight: 500;
}

.input-mobile {
  width: 100px;
  padding: 6px 10px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
  text-align: right;
  transition: border-color 0.2s;
}

.input-mobile:focus {
  border-color: #0066cc;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

.input-mobile::-webkit-outer-spin-button,
.input-mobile::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.input-mobile {
  -moz-appearance: textfield;
}

.item-row .amount {
  font-weight: 600;
  color: #0066cc;
}

.item-row .amount-negative {
  color: #dc3545;
}
</style>
