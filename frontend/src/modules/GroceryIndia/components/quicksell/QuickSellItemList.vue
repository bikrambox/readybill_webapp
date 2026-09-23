<template>
  <div class="mobile-item-container">
    <FormErrorBox :messages="validationErrors" @clear="clearErrors" />

    <div v-if="quickSellStore.items.length === 0" class="empty-state">
      <i class="bi bi-inbox"></i>
      <p>{{ $t("common.No items added yet") }}</p>
    </div>

    <div class="mobile-item-scroll" v-else>
      <div
        class="item-card"
        v-for="(item, index) in quickSellStore.items"
        :key="item.cartId"
      >
        <div class="item-card-header">
          <h6 class="item-name">{{ item.name }}</h6>
          <span v-if="item.isRefund === 1" class="refund-badge">
            {{ $t("common.Refund") }}
          </span>
          <button
            class="btn-delete-mobile"
            @click="handleDelete(item)"
            :disabled="quickSellStore.loading"
          >
            <i class="bi bi-trash"></i>
          </button>
        </div>

        <div class="item-card-body">
          <div class="item-row">
            <span class="label">{{ $t("common.Quantity") }}</span>
            <!-- ── Quantity (input always uses dot) ── -->
            <input
              type="text"
              inputmode="decimal"
              class="input-mobile"
              v-model="item.quantity"
              @keydown="restrictQuantityKeys($event, item)"
              @input="sanitizeQuantityInput($event, item)"
              @blur="updateAmount(index, item)"
              :placeholder="getQuantityConfig(item.unit).isDecimal ? '0.0' : '0'"
            />
          </div>
          <div class="item-row">
            <span class="label">{{ $t("common.Unit") }}</span>
            <span class="value">{{ item.unit }}</span>
          </div>
          <div class="item-row">
            <span class="label">{{ $t("common.Rate") }}</span>
            <!-- ── Rate (input always uses dot) ── -->
            <input
              type="text"
              inputmode="decimal"
              class="input-mobile"
              v-model="item.rate"
              @keydown="restrictRateKeys($event)"
              @input="sanitizeRateInput($event)"
              @blur="updateAmount(index, item)"
              placeholder="0.00"
            />
          </div>
          <div class="item-row">
            <span class="label">{{ $t("common.Amount") }}</span>
            <!-- ── Amount (display formatted per country) ── -->
            <span
              class="value amount"
              :class="{ 'amount-negative': item.isRefund === 1 }"
            >
              <span v-if="item.isRefund === 1">−</span>
              {{ formatDisplay(Math.abs(item.amount)) }}
            </span>
          </div>
        </div>
      </div>
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
const currentItem = ref(null);

const confirmData = ref({
  modalMessage: "",
  modalTitle: "",
});

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

  const qty = parseValue(item.quantity);
  const rate = parseValue(item.rate);
  const baseAmount = qty * rate;
  item.amount = item.isRefund === 1 ? -Math.abs(baseAmount) : baseAmount;

  const result = await quickSellStore.updateCartItem(item.cartId, item);

  if (!result.success) {
    validationErrors.value = [result.message || t("common.Failed to update item")];
  }
};

const handleDelete = (item) => {
  confirmData.value.modalMessage = `${t("common.Are you sure you want to delete")} ${
    item.name
  }?`;
  confirmData.value.modalTitle = t("common.Confirm Deletion");
  currentItem.value = item;
  confirmModal.value.show();
};

const deleteItem = async () => {
  const result = await quickSellStore.deleteCartItem(currentItem.value.cartId);

  if (!result.success) {
    validationErrors.value = [result.message || t("common.Failed to delete item")];
  }

  confirmModal.value.hide();
};
</script>

<style scoped>
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

.item-row .amount {
  font-weight: 600;
  color: #0066cc;
}
.item-row .amount-negative {
  color: #dc3545;
}
</style>
