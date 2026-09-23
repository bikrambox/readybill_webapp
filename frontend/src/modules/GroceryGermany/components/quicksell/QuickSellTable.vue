<template>
  <div class="table-container">
    <div class="table-scroll">
      <table class="table">
        <thead>
          <tr>
            <th>{{ $t('common.Name') }}</th>
            <th>{{ $t('common.Quantity') }}</th>
            <th>{{ $t('common.Unit') }}</th>
            <th>{{ $t('common.Rate') }}</th>
            <th>{{ $t('common.Amount') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="quickSellStore.items.length === 0">
            <td colspan="6" class="text-center empty-state">
              <i class="bi bi-inbox"></i>
              <p>{{ $t('common.No items added yet') }}</p>
            </td>
          </tr>
          <tr v-for="(item, index) in quickSellStore.items" :key="item.cartId">
            <td>
              <div class="item-name-wrapper">
                {{ item.name }}
                <!-- <span v-if="item.isRefund === 1" class="refund-badge">Refund</span> -->
              </div>
            </td>
            <td>
              <input
                type="number"
                class="input-quantity"
                v-model.number="item.quantity"
                @blur="updateAmount(index, item)"
                min="0.1"
                step="0.1"
              />
            </td>
            <td>{{ item.unit }}</td>
            <td>
              <input
                type="number"
                class="input-rate"
                v-model.number="item.rate"
                @blur="updateAmount(index, item)"
                min="0"
                step="0.01"
              />
            </td>
            <td
              class="amount"
              :class="{ 'amount-negative': item.isRefund === 1 }"
            >
              <span class="amount-text">
                <span v-if=" item.isRefund === 1">-</span> {{ $formatCurrency(Math.abs(item.amount)) }}</span>
            </td>
            <td>
              <button
                class="btn-delete"
                @click="handleDelete(index, item)"
                title="Delete item"
                :disabled="quickSellStore.loading"
              >
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
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
import { watch, ref } from "vue";
import { useQuickSellStore } from "@/modules/GroceryGermany/stores/quickSellStore";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

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
const currentItemIndex = ref(null);
const modalMessage = ref("");
const modalTitle = ref("Confirm Deletion");

const confirmData = ref({
  message: "",
  titel: "",
});

const emit = defineEmits(["delete-item", "update-item"]);

// Watch for location changes and update store
watch(
  () => props.location,
  (newLocation) => {
    console.log("fetchCartItems newLocation", newLocation);

    if (newLocation) {
      quickSellStore.setLocation(newLocation);
    }
  },
  { immediate: true }
);

// const formatCurrency = (amount) => {
//   const absAmount = Math.abs(amount || 0);
//   // Use minus sign (−) for negative amounts on the same line
//   const sign = amount < 0 ? "−" : "";
//   return `${sign}₹${absAmount.toFixed(2)}`;
// };

const updateAmount = async (index, item) => {
  // Recalculate amount based on isRefund status
  const baseAmount = item.quantity * item.rate;
  item.amount = item.isRefund === 1 ? -Math.abs(baseAmount) : baseAmount;

  // Update cart item via API
  const result = await quickSellStore.updateCartItem(item.cartId, item);

  if (!result.success) {
    alert(result.message || t('common.Failed to update item'));
  }

  emit("update-item", index, item);
};

const handleDelete = async (index, item) => {
  modalMessage.value = `${t('common.Are you sure you want to delete')} ${item.name}?`;
  currentItemIndex.value = index;
  currentItemId.value = item.id;
  confirmModal.value.show();
};

const deleteItem = async () => {
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
  // const result = await quickSellStore.deleteCartItem(currentItemId.value);

  // if (!result.success) {
  //   alert(result.message || "Failed to delete item");
  // }

  // emit("delete-item", currentItemIndex.value);

  const cartItem = quickSellStore.items[currentItemIndex.value];

  const result = await quickSellStore.deleteCartItem(cartItem.cartId);

  if (!result.success) {
    alert(result.message || t('common.Failed to delete item'));
  }

  confirmModal.value.hide();
};
</script>

<style scoped>
.table-container {
  width: 100%;
  overflow: hidden;
}

.table-scroll {
  max-height: 400px;
  overflow-y: auto;
  overflow-x: auto;
  border: 1px solid #e9ecef;
  border-radius: 8px;
}

/* Custom Scrollbar */
.table-scroll::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.table-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-scroll::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.table-scroll::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.table {
  width: 100%;
  margin-bottom: 0;
}

.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #e9ecef;
  padding: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
  text-align: left;
  white-space: nowrap;
  position: sticky;
  top: 0;
  z-index: 10;
}

.table tbody td {
  padding: 12px;
  border-bottom: 1px solid #f0f0f0;
  font-size: 14px;
  color: #333;
  vertical-align: middle;
}

.empty-state {
  padding: 40px 20px;
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

.item-name-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.refund-badge {
  background-color: #dc3545;
  color: white;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  white-space: nowrap;
}

.input-quantity,
.input-rate {
  width: 80px;
  padding: 6px 10px;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  font-size: 14px;
  text-align: center;
  transition: border-color 0.2s;
}

.input-quantity:focus,
.input-rate:focus {
  border-color: #0066cc;
  outline: none;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

/* Remove spinner arrows for number inputs */
.input-quantity::-webkit-outer-spin-button,
.input-quantity::-webkit-inner-spin-button,
.input-rate::-webkit-outer-spin-button,
.input-rate::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.input-quantity,
.input-rate {
  -moz-appearance: textfield;
}

.amount {
  font-weight: 600;
  color: #0066cc;
  white-space: nowrap;
}

.amount-text {
  display: inline-block;
  white-space: nowrap;
}

.amount-negative {
  color: #dc3545;
}

.btn-delete {
  background: transparent;
  border: none;
  color: #dc3545;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  transition: background-color 0.2s, opacity 0.2s;
}

.btn-delete:hover:not(:disabled) {
  background-color: #fff5f5;
}

.btn-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-delete i {
  font-size: 18px;
}

.text-center {
  text-align: center;
}
</style>
