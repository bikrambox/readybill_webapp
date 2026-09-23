<template>
  <div class="actions-wrapper">
    <div class="actions-container">
      <!-- Grand Total - Left Side -->
      <div class="grand-total">
        <span class="total-label">{{ $t("common.Grand Total") }}</span>
        <span v-if="quickSellStore.totalAmount" class="total-amount">
          <span v-if="quickSellStore.totalAmount < 0">-</span>
          {{ $formatCurrency(Math.abs(quickSellStore.totalAmount)) }}
        </span>
        <span v-else class="total-amount">0.00</span>
      </div>

      <!-- Action Buttons - Right Side -->
      <div class="action-buttons">
        <!-- Send SMS button -->
        <button
          class="btn btn-whatsapp"
          @click="handleSendSms"
          :disabled="!hasItems || quickSellStore.loading"
          v-if="$canAccess()"
        >
          <span class="btn-text">{{ $t("common.Send SMS") }}</span>
        </button>

        <button
          class="btn btn-print"
          @click="handlePrint"
          :disabled="!hasItems || quickSellStore.loading"
        >
          <span v-if="quickSellStore.loading && pendingAction === 'print'">
            <i class="bi bi-arrow-repeat spin"></i>
            {{ $t("common.Processing") }}...
          </span>
          <span v-else>
            <i class="bi bi-printer"></i>
            <span class="btn-text px-2">{{ $t("common.Print") }}</span>
          </span>
        </button>

        <button
          class="btn btn-cancel"
          @click="handleCancel"
          :disabled="!hasItems || quickSellStore.loading"
        >
          {{ $t("common.Cancel") }}
        </button>

        <button
          class="btn btn-save"
          @click="handleSave"
          :disabled="!hasItems || quickSellStore.loading"
        >
          <span v-if="quickSellStore.loading && pendingAction === 'save'">
            <i class="bi bi-arrow-repeat spin"></i>
            {{ $t("common.Saving") }}...
          </span>
          <span v-else>{{ $t("common.Save") }}</span>
        </button>
      </div>
    </div>
  </div>

  <CustomerInfoModal
    :show="showCustomerModal"
    :loading="quickSellStore.loading"
    :mode="isGstCompliant ? 'customer' : 'sms'"
    :confirm-label="pendingActionLabel"
    :server-errors="customerModalErrors"
    @submit="handleCustomerSubmit"
    @close="handleCustomerModalClose"
  />

  <!-- Result Modal -->
  <ResultModal
    :show="showResultModal"
    :status="resultData.status"
    :message="resultData.message"
    :title="resultData.title"
    @close="closeResultModal"
  />

  <!-- Confirm Modal -->
  <ConfirmModal
    ref="confirmModal"
    :message="confirmData.modalMessage"
    :title="confirmData.modalTitle"
    @confirm="handleDelete"
  />
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useQuickSellStore } from "@/modules/GroceryIndia/stores/quickSellStore";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { useRouter } from "vue-router";
import CustomerInfoModal from "@/modules/GroceryIndia/components/quicksell/CustomerInfoModal.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const quickSellStore = useQuickSellStore();
const router = useRouter();
const confirmModal = ref(null);

// ── Preferences ──────────────────────────────────────────────────────────────
const preferencesStore = useUserPreferencesStore();
const { preferences } = storeToRefs(preferencesStore);

onMounted(async () => {
  await preferencesStore.fetchUserPreferences();
});

// GST compliance — drives field visibility in CustomerInfoModal
const isGstCompliant = computed(
  () =>
    preferences.value.preference_invoice_gst_complaint === 1 ||
    preferences.value.preference_invoice_gst_complaint === true
);

// ── Customer Info Modal State ─────────────────────────────────────────────────
const showCustomerModal = ref(false);
const pendingAction = ref(null); // 'sms' | 'print' | 'save' | null
const customerModalErrors = ref({}); // server-side field errors → passed to modal

const pendingActionLabel = computed(() => {
  if (pendingAction.value === "sms") return t("common.Send SMS");
  if (pendingAction.value === "print") return t("common.Print");
  return t("common.Save");
});

const handleCustomerModalClose = () => {
  showCustomerModal.value = false;
  pendingAction.value = null;
  customerModalErrors.value = {};
};

// ── Result Modal State ────────────────────────────────────────────────────────
const showResultModal = ref(false);
const resultData = ref({ status: "", message: "", title: "" });
const confirmData = ref({ modalMessage: "", modalTitle: "" });

// ── Computed ──────────────────────────────────────────────────────────────────
const hasItems = computed(() => quickSellStore.items.length > 0);

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Returns true when the result has field-level validation errors.
 * In that case the modal should stay open.
 */
const hasFieldErrors = (result) =>
  result?.errors && Object.keys(result.errors).length > 0;

// ── Send SMS ──────────────────────────────────────────────────────────────────
const handleSendSms = () => {
  if (!hasItems.value) return;
  pendingAction.value = "sms";
  showCustomerModal.value = true;
};

const executeSendSms = async (customerData) => {
  const result = await quickSellStore.submitBilling({
    print: false,
    isSendSms: true,
    customer_mobile: customerData.customer_mobile,
    country_code: customerData.country_code,
    dial_code: customerData.dial_code,
    customer_name: customerData.customer_name || null,
    customer_address: customerData.customer_address || null,
    customer_gstin: customerData.customer_gstin || null,
  });

  if (result.success) {
    resultData.value = {
      status: "success",
      title: t("common.SMS Sent"),
      message: result.message || t("common.Invoice sent successfully via SMS"),
    };
    await quickSellStore.deleteAllCartItems();
    showResultModal.value = true;
  } else if (!hasFieldErrors(result)) {
    // Generic API error — show result modal (field errors handled in handleCustomerSubmit)
    resultData.value = {
      status: "error",
      title: t("common.SMS Failed"),
      message: result.message || t("common.Failed to send SMS"),
    };
    showResultModal.value = true;
  }

  return result;
};

// ── Print ─────────────────────────────────────────────────────────────────────
const handlePrint = () => {
  if (!hasItems.value) return;

  if (isGstCompliant.value) {
    pendingAction.value = "print";
    showCustomerModal.value = true;
  } else {
    executePrint();
  }
};

const executePrint = async (customerData = null) => {
  const result = await quickSellStore.submitBilling({
    print: true,
    isSendSms: false,
    ...(customerData || {}),
  });

  if (result.success) {
    const billId = result.data.bill_id || result.data.invoice_id || result.data.id;

    const token = result.data.token;
    console.log("result", result.data.token);

    if (billId) {
      // // const routeData = router.resolve({
      // //   name: "Invoice",
      // //   params: { bill_id: billId },
      // // });
      const routeData = router.resolve({
        name: "Invoice",
        params: { bill_id: token },
      });
      // window.open(routeData.href, "_blank");
      window.open(routeData.href, "_blank", "noopener,noreferrer");

      await quickSellStore.deleteAllCartItems();
      // setTimeout(() => window.location.reload(), 500);
    } else {
      alert("Invoice generated but ID not found");
      await quickSellStore.deleteAllCartItems();
      window.location.reload();
    }
  } else if (!hasFieldErrors(result)) {
    alert(result.message || "Failed to generate invoice");
  }

  return result;
};

// ── Cancel ────────────────────────────────────────────────────────────────────
const handleCancel = () => {
  if (!hasItems.value) return;
  confirmModal.value.show();
};

async function handleDelete() {
  try {
    const result = await quickSellStore.deleteAllCartItems();
    if (result.success) {
      window.location.reload();
    } else {
      alert(result.message || "Failed to cancel order");
    }
    confirmModal.value.hide();
  } catch (error) {
    console.error("Delete failed:", error);
    confirmModal.value.isLoading = false;
  }
}

// ── Save ──────────────────────────────────────────────────────────────────────
const handleSave = () => {
  if (!hasItems.value) {
    resultData.value = {
      status: "error",
      title: t("common.No Items"),
      message: t("common.Please add items to save the order"),
    };
    showResultModal.value = true;
    return;
  }

  if (isGstCompliant.value) {
    pendingAction.value = "save";
    showCustomerModal.value = true;
  } else {
    executeSave();
  }
};

const executeSave = async (customerData = null) => {
  const result = await quickSellStore.submitBilling({
    print: false,
    isSendSms: false,
    ...(customerData || {}),
  });

  if (result.success) {
    resultData.value = {
      status: "success",
      title: t("common.Order Saved"),
      message: result.message || t("common.Order saved successfully!"),
    };
    await quickSellStore.deleteAllCartItems();
    showResultModal.value = true;
  } else if (!hasFieldErrors(result)) {
    resultData.value = {
      status: "error",
      title: t("common.Save Failed"),
      message: result.message || t("common.Failed to save order"),
    };
    showResultModal.value = true;
  }

  return result;
};

// ── Customer Modal Submit — routes to correct execute fn ──────────────────────
const handleCustomerSubmit = async (customerData) => {
  customerModalErrors.value = {}; // reset before every attempt

  let result = null;

  if (pendingAction.value === "sms") {
    result = await executeSendSms(customerData);
  } else if (pendingAction.value === "print") {
    result = await executePrint(customerData);
  } else if (pendingAction.value === "save") {
    result = await executeSave(customerData);
  }

  // Field-level validation errors → keep modal open, surface errors inline
  if (hasFieldErrors(result)) {
    customerModalErrors.value = result.errors;
    return; // modal stays open
  }

  // Success or generic error → close modal
  showCustomerModal.value = false;
  pendingAction.value = null;
};

// ── Close Result Modal ────────────────────────────────────────────────────────
const closeResultModal = () => {
  showResultModal.value = false;
};
</script>

<style scoped>
.actions-wrapper {
  border-top: 2px solid #e9ecef;
  padding-top: 25px;
  margin-top: 25px;
}
.actions-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
}
.grand-total {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.total-label {
  font-size: 16px;
  font-weight: 500;
  color: #6c757d;
}
.total-amount {
  font-size: 28px;
  font-weight: 700;
  color: #0066cc;
}
.action-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.action-buttons .btn {
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-whatsapp {
  background-color: #ffffff;
  color: #333;
  border: 1px solid #dee2e6;
}
.btn-whatsapp:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #25d366;
  color: #25d366;
}
.btn-print {
  background-color: #ffffff;
  color: #333;
  border: 1px solid #dee2e6;
}
.btn-print:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #6c757d;
  color: #6c757d;
}
.btn-cancel {
  background-color: #ffffff;
  color: #333;
  border: 1px solid #dee2e6;
}
.btn-cancel:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #dc3545;
  color: #dc3545;
}
.btn-save {
  background-color: #0066cc;
  color: white;
  border: 1px solid #0066cc;
}
.btn-save:hover:not(:disabled) {
  background-color: #0052a3;
  border-color: #0052a3;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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
@media (max-width: 768px) {
  .actions-container {
    flex-direction: column;
    align-items: stretch;
  }
  .grand-total {
    align-items: center;
    padding-bottom: 15px;
    border-bottom: 1px solid #e9ecef;
  }
  .total-label {
    font-size: 14px;
  }
  .total-amount {
    font-size: 24px;
  }
  .action-buttons {
    flex-direction: column;
    width: 100%;
  }
  .action-buttons .btn {
    width: 100%;
    justify-content: center;
  }
}
@media (max-width: 480px) {
  .total-amount {
    font-size: 20px;
  }
  .action-buttons .btn {
    padding: 10px 20px;
    font-size: 13px;
  }
}
</style>
