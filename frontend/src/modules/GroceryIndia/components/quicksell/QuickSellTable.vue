<template>
  <div class="table-container">
    <FormErrorBox :messages="validationErrors" @clear="clearErrors" />

    <div class="table-scroll">
      <table class="table">
        <thead>
          <tr>
            <th>{{ $t("common.Name") }}</th>
            <th>{{ $t("common.Quantity") }}</th>
            <th>{{ $t("common.Unit") }}</th>
            <th>{{ $t("common.Rate") }}</th>
            <th>{{ $t("common.Amount") }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="quickSellStore.items.length === 0">
            <td colspan="6" class="text-center empty-state">
              <i class="bi bi-inbox"></i>
              <p>{{ $t("common.No items added yet") }}</p>
            </td>
          </tr>
          <tr v-for="(item, index) in quickSellStore.items" :key="item.cartId">
            <td>
              <div class="item-name-wrapper">{{ item.name }}</div>
            </td>

            <!-- ── Quantity (input always uses dot) ── -->
            <td>
              <input
                type="text"
                inputmode="decimal"
                class="input-quantity"
                v-model="item.quantity"
                @keydown="restrictQuantityKeys($event, item)"
                @input="sanitizeQuantityInput($event, item)"
                @blur="updateAmount(index, item)"
                :placeholder="getQuantityConfig(item.unit).isDecimal ? '0.0' : '0'"
              />
            </td>

            <td>{{ item.unit }}</td>

            <!-- ── Rate (input always uses dot) ── -->
            <td>
              <input
                type="text"
                inputmode="decimal"
                class="input-rate"
                v-model="item.rate"
                @keydown="restrictRateKeys($event)"
                @input="sanitizeRateInput($event)"
                @blur="updateAmount(index, item)"
                placeholder="0.00"
              />
            </td>

            <!-- ── Amount (display formatted per country) ── -->
            <td
              class="amount"
              :class="{ 'amount-negative': item.isRefund === 1 }"
            >
              <span class="amount-text">
                <span v-if="item.isRefund === 1">−</span>
                {{ formatDisplay(Math.abs(item.amount)) }}
              </span>
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

  <ConfirmModal
    ref="confirmModal"
    :message="confirmData.modalMessage"
    :title="confirmData.modalTitle"
    @confirm="deleteItem"
  />
</template>

<script setup>
import { watch, ref } from "vue";
import { useQuickSellStore } from "@/modules/GroceryIndia/stores/quickSellStore";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useInputValidation } from "@/composables/useInputValidation";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

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
const currentItemIndex = ref(null);

const confirmData = ref({
  modalMessage: "",
  modalTitle: "",
});

const emit = defineEmits(["delete-item", "update-item"]);

const {
  validationErrors,
  restrictQuantityKeys,
  restrictRateKeys,
  sanitizeQuantityInput,
  sanitizeRateInput,
  validateCartItem,
  getQuantityConfig,
  parseValue,
  formatDisplay,
  clearErrors,
} = useInputValidation();

watch(
  () => props.location,
  (newLocation) => {
    if (newLocation) quickSellStore.setLocation(newLocation);
  },
  { immediate: true }
);

const updateAmount = async (index, item) => {
  const errors = validateCartItem(item, t);
  if (errors.length > 0) {
    validationErrors.value = errors;
    return;
  }

  clearErrors();

  // parseValue always returns plain JS float — safe for calculation
  const qty = parseValue(item.quantity);
  const rate = parseValue(item.rate);
  const baseAmount = qty * rate;
  item.amount = item.isRefund === 1 ? -Math.abs(baseAmount) : baseAmount;

  const result = await quickSellStore.updateCartItem(item.cartId, item);

  if (!result.success) {
    validationErrors.value = [
      result.message || t("common.Failed to update item"),
    ];
  }

  emit("update-item", index, item);
};

const handleDelete = (index, item) => {
  confirmData.value.modalMessage = `${t("common.Are you sure you want to delete")} ${item.name}?`;
  confirmData.value.modalTitle = t("common.Confirm Deletion");
  currentItemIndex.value = index;
  confirmModal.value.show();
};

const deleteItem = async () => {
  const cartItem = quickSellStore.items[currentItemIndex.value];
  const result = await quickSellStore.deleteCartItem(cartItem.cartId);

  if (!result.success) {
    validationErrors.value = [
      result.message || t("common.Failed to delete item"),
    ];
  }

  confirmModal.value.hide();
  emit("delete-item", currentItemIndex.value);
};
</script>

<style scoped>
.table-container { width: 100%; overflow: hidden; }

.table-scroll {
  max-height: 400px;
  overflow-y: auto;
  overflow-x: auto;
  border: 1px solid #e9ecef;
  border-radius: 8px;
}

.table-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
.table-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.table-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
.table-scroll::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

.table { width: 100%; margin-bottom: 0; }

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

.empty-state { padding: 40px 20px; color: #6c757d; }
.empty-state i { font-size: 48px; margin-bottom: 10px; display: block; }
.empty-state p { margin: 0; font-size: 16px; }

.item-name-wrapper { display: flex; align-items: center; gap: 8px; }

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

.amount { font-weight: 600; color: #0066cc; white-space: nowrap; }
.amount-text { display: inline-block; white-space: nowrap; }
.amount-negative { color: #dc3545; }

.btn-delete {
  background: transparent;
  border: none;
  color: #dc3545;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  transition: background-color 0.2s, opacity 0.2s;
}
.btn-delete:hover:not(:disabled) { background-color: #fff5f5; }
.btn-delete:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-delete i { font-size: 18px; }
.text-center { text-align: center; }
</style>