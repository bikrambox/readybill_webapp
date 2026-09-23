<template>
  <div class="actions-wrapper">
    <div class="actions-container">
      <!-- Grand Total - Left Side -->
      <div class="grand-total">
        <span class="total-label">Grand Total</span>
        <span class="total-amount">{{
          formatCurrency(quickSellStore.totalAmount)
        }}</span>
      </div>

      <!-- Action Buttons - Right Side -->
      <div class="action-buttons">
        <button
          class="btn btn-whatsapp"
          @click="handleSendSms"
          :disabled="!hasItems || quickSellStore.loading"
        >
          <span class="btn-text">Send SMS</span>
        </button>
        <button
          class="btn btn-print"
          @click="handlePrint"
          :disabled="!hasItems || quickSellStore.loading"
        >
          <span v-if="quickSellStore.loading">
            <i class="bi bi-arrow-repeat spin"></i>
            Processing...
          </span>
          <span v-else>
            <i class="bi bi-printer"></i>
            <span class="btn-text">Print</span>
          </span>
        </button>
        <button
          class="btn btn-cancel"
          @click="handleCancel"
          :disabled="!hasItems || quickSellStore.loading"
        >
          Cancel
        </button>
        <button
          class="btn btn-save"
          @click="handleSave"
          :disabled="!hasItems || quickSellStore.loading"
        >
          <span v-if="quickSellStore.loading">
            <i class="bi bi-arrow-repeat spin"></i>
            Saving...
          </span>
          <span v-else> Save </span>
        </button>
      </div>
    </div>
  </div>

  <!-- Send SMS Modal -->
  <SendSmsModal
    ref="smsModal"
    @send="handleSmsSubmit"
    @close="handleModalClose"
  />

  <!-- Result Modal -->
  <ResultModal
    :show="showResultModal"
    :status="resultData.status"
    :message="resultData.message"
    :title="resultData.title"
    @close="closeResultModal"
  />

  <!-- Single modal instance -->
  <ConfirmModal
    ref="confirmModal"
    :message="confirmData.modalMessage"
    :title="confirmData.modalTitle"
    @confirm="handleDelete"
  />
</template>

<script setup>
import { computed, ref } from "vue";
import { useQuickSellStore } from "@/modules/GroceryGermany/stores/quickSellStore";
import { useRouter } from "vue-router";
import SendSmsModal from "@/modules/GroceryGermany/components/quicksell/SendSmsModal.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";

const quickSellStore = useQuickSellStore();
const router = useRouter();
const smsModal = ref(null);
const confirmModal = ref(null);

// Result Modal State
const showResultModal = ref(false);
const resultData = ref({
  status: "",
  message: "",
  title: "",
});

const confirmData = ref({
  message: "",
  titel: "",
});

const hasItems = computed(() => {
  return quickSellStore.items.length > 0;
});

// const formatCurrency = (amount) => {
//   return `₹${parseFloat(amount || 0).toFixed(2)}`;
// };

const formatCurrency = (amount) => {
  const absAmount = Math.abs(parseFloat(amount || 0));
  // Use minus sign (−) for negative amounts
  const sign = amount < 0 ? "− " : "";
  return `${sign}₹${absAmount.toFixed(2)}`;
};

const handleSendSms = () => {
  if (!hasItems.value) return;
  smsModal.value.show();
};

const handleSmsSubmit = async (formData) => {
  smsModal.value.setLoading(true);

  const result = await quickSellStore.submitBilling({
    print: false,
    isSendSms: true,
    mobile: formData.mobile,
    countryCode: formData.countryCode,
  });

  smsModal.value.setLoading(false);

  if (result.success) {
    // alert("Invoice sent successfully via SMS!");
    smsModal.value.hide();

    resultData.value = {
      status: "success",
      title: "SMS Sent",
      message: "Invoice sent successfully via SMS!",
    };
    showResultModal.value = true;

    // Delete all cart items and reload page
    await quickSellStore.deleteAllCartItems();
    // window.location.reload();
  } else {
    alert(result.message || "Failed to send SMS");
  }
};

const handleModalClose = () => {
  // Modal closed without sending
};

const handlePrint = async () => {
  if (!hasItems.value) return;

  const result = await quickSellStore.submitBilling({
    print: true,
    isSendSms: false,
  });

  if (result.success) {
    // Get bill_id from response
    const billId =
      result.data.bill_id || result.data.invoice_id || result.data.id;

    if (billId) {
      // Open invoice page in new tab using router.resolve
      const routeData = router.resolve({
        name: "Invoice",
        params: { bill_id: billId },
      });
      window.open(routeData.href, "_blank");

      // Reset the sell page - delete all items and reload
      await quickSellStore.deleteAllCartItems();

      // Small delay to ensure new tab opens before reload
      setTimeout(() => {
        window.location.reload();
      }, 500);
    } else {
      alert("Invoice generated but ID not found");
      await quickSellStore.deleteAllCartItems();
      window.location.reload();
    }
  } else {
    alert(result.message || "Failed to generate invoice");
  }
};

const handleCancel = async () => {
  if (!hasItems.value) {
    return;
  }

   confirmModal.value.show()

  // // First confirmation
  // const firstConfirm = confirm("Are you sure you want to cancel this order?");

  // if (!firstConfirm) return;

  // // Second confirmation with item count
  // const secondConfirm = confirm(
  //   `This will remove all ${quickSellStore.itemCount} items from the cart. This action cannot be undone. Continue?`
  // );

  // if (!secondConfirm) return;

  // const result = await quickSellStore.deleteAllCartItems();

  // if (result.success) {
  //   window.location.reload();
  // } else {
  //   alert(result.message || "Failed to cancel order");
  // }
};

async function handleDelete() {
  try {
    // Simulate API call
    // await deleteItemAPI(currentItemId.value)

    const result = await quickSellStore.deleteAllCartItems();

    if (result.success) {
      window.location.reload();
    } else {
      alert(result.message || "Failed to cancel order");
    }

    // Close modal after successful deletion
    confirmModal.value.hide();

    // Show success message
    console.log("Item deleted successfully");
  } catch (error) {
    console.error("Delete failed:", error);
    confirmModal.value.isLoading = false;
  }
}

const handleSave = async () => {
  if (!hasItems.value) {
    // alert("Please add items to save the order");

    resultData.value = {
      status: "error",
      title: "No Items",
      message: "Please add items to save the order",
    };
    showResultModal.value = true;

    return;
  }

  const result = await quickSellStore.submitBilling({
    print: false,
    isSendSms: false,
  });

  if (result.success) {
    // alert("Order saved successfully!");

    resultData.value = {
      status: "success",
      title: "Order Saved",
      message: result.message || "Order saved successfully!",
    };

    await quickSellStore.deleteAllCartItems();
    // window.location.reload();
    showResultModal.value = true;
  } else {
    // alert(result.message || "Failed to save order");

    resultData.value = {
      status: "error",
      title: "Save Failed",
      message: result.message || "Failed to save order",
    };
    showResultModal.value = true;
  }
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
