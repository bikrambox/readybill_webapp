<template>
  <div class="quick-sell-page">
    <div class="page-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="sell">{{ $t("common.Home") }}</a>
              </li>
              <li class="breadcrumb-item active">{{ $t("common.Refund") }}</li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t("common.Refund") }}</h1>
        </div>
      </div>

      <!-- Global Error Alert -->
      <div v-if="allErrors.length > 0" class="mb-3">
        <FormErrorBox :messages="allErrors" @clear="handleClearErrors" />
      </div>

      <!-- Search Component -->
      <RefundSearch @add-item="handleAddItem" @error="handleComponentError" />

      <!-- Item List Card -->
      <div class="item-list-card">
        <div class="list-header">
          <h5 class="list-title">
            {{ $t("common.Item List") }} ({{ quickSellStore.itemCount }})
          </h5>

          <div class="total-display">
            {{ $t("common.Total") }}:
            <span
              v-if="quickSellStore.totalAmount"
              class="total-amount"
              :class="{ 'total-negative': quickSellStore.totalAmount < 0 }"
            >
              <span v-if="quickSellStore.totalAmount < 0">-</span>
              {{ $formatCurrency(Math.abs(quickSellStore.totalAmount)) }}
            </span>
            <span class="total-amount" v-else>0.00</span>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="desktop-table">
          <QuickSellTable
            :items="quickSellStore.items"
            location="refund"
            @delete-item="handleDeleteItem"
            @update-item="handleUpdateItem"
            @error="handleComponentError"
          />
        </div>

        <!-- Mobile List -->
        <div class="mobile-list">
          <QuickSellItemList location="refund" @error="handleComponentError" />
        </div>

        <!-- Actions -->
        <QuickSellActions
          :items="quickSellStore.items"
          :loading="quickSellStore.loading"
          @send-whatsapp="sendToWhatsApp"
          @cancel="cancelOrder"
          @save="saveOrder"
          @error="handleComponentError"
        />
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from "vue";
import { useI18n } from "vue-i18n";
import RefundSearch from "../components/quicksell/RefundSearch.vue";
import QuickSellTable from "../components/quicksell/QuickSellTable.vue";
import QuickSellItemList from "../components/quicksell/QuickSellItemList.vue";
import QuickSellActions from "../components/quicksell/QuickSellActions.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useQuickSellStore } from "@/modules/GroceryIndia/stores/quickSellStore";

const { t } = useI18n();
const quickSellStore = useQuickSellStore();

// Global error state
const allErrors = ref([]);

const pushError = (messages) => {
  const list = Array.isArray(messages) ? messages : [messages];

  list.forEach((msg) => {
    if (msg && !allErrors.value.includes(msg)) {
      allErrors.value.push(msg);
    }
  });

  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleClearErrors = () => {
  allErrors.value = [];
};

const handleComponentError = (errorOrErrors) => {
  pushError(errorOrErrors);
};

// Child handlers
const handleAddItem = () => {
  handleClearErrors();
};

const handleUpdateItem = async (index, item) => {
  const cartItem = quickSellStore.items[index];

  if (!cartItem?.cartId) {
    pushError(t("common.Invalid cart item"));
    return;
  }

  const result = await quickSellStore.updateCartItem(cartItem.cartId, item);

  if (!result.success) {
    pushError(result.message || t("common.Failed to update item"));
  }
};

const handleDeleteItem = async () => {
  // Child component already handles deletion.
  // Errors bubble up via @error
};

// WhatsApp
const sendToWhatsApp = () => {
  let message = "Refund Order:\n\n";

  quickSellStore.items.forEach((item, index) => {
    message += `${index + 1}. ${item.name}\n`;
    message += `   Qty: ${item.quantity} ${item.unit}\n`;
    message += `   Rate: ₹${item.rate}\n`;
    message += `   Amount: ₹${item.amount.toFixed(2)}\n\n`;
  });

  message += `*Grand Total: ₹${quickSellStore.totalAmount.toFixed(2)}*`;

  const encodedMessage = encodeURIComponent(message);
  const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
  window.open(whatsappUrl, "_blank");
};

// Cancel refund cart
const cancelOrder = async () => {
  handleClearErrors();

  if (quickSellStore.items.length === 0) {
    pushError(t("common.No Items"));
    return;
  }

  const failedItems = [];

  for (const item of quickSellStore.items) {
    const result = await quickSellStore.deleteCartItem(item.cartId);
    if (!result.success) {
      failedItems.push(item.name);
    }
  }

  if (failedItems.length > 0) {
    pushError(`${t("common.Failed to remove")}: ${failedItems.join(", ")}`);
  }
};

// Save refund order
const saveOrder = async () => {
  handleClearErrors();

  if (quickSellStore.items.length === 0) {
    pushError(t("common.Please add items to save the order"));
    return;
  }

  try {
    const result = await quickSellStore.saveOrder();

    if (!result.success) {
      pushError(result.message || t("common.Failed to save refund"));
    }
  } catch (error) {
    pushError(error.message || t("common.An error occurred while saving the refund"));
  }
};

// Lifecycle
onMounted(async () => {
  quickSellStore.setLocation("refund");

  try {
    await quickSellStore.fetchCartItems();
  } catch (error) {
    pushError(error.message || t("common.Failed to load cart items"));
  }

  quickSellStore.clearSuggestions();
});

onBeforeUnmount(() => {
  quickSellStore.clearSuggestions();
});
</script>

<style scoped>
.quick-sell-page {
  display: flex;
  flex-direction: column;
  min-height: 100%;
}

.page-content {
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  flex: 1;
}

.page-header {
  margin-bottom: 25px;
}

.breadcrumb {
  margin-bottom: 10px;
  background: transparent;
  padding: 0;
  font-size: 14px;
}

.breadcrumb-item a {
  color: #6c757d;
  text-decoration: none;
}

.breadcrumb-item.active {
  color: #333;
}

.page-title {
  font-size: 28px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.mb-3 {
  margin-bottom: 1rem;
}

.item-list-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  margin-bottom: 30px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.list-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.total-display {
  font-size: 16px;
  font-weight: 500;
  color: #333;
}

.total-amount {
  font-size: 20px;
  font-weight: 700;
  color: #0066cc;
  margin-left: 8px;
}

.total-negative {
  color: #dc3545;
}

.desktop-table {
  display: block;
}

.mobile-list {
  display: none;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 24px;
  }

  .item-list-card {
    padding: 20px;
  }

  .list-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .list-title {
    font-size: 16px;
  }

  .desktop-table {
    display: none;
  }

  .mobile-list {
    display: block;
  }
}

@media (max-width: 480px) {
  .item-list-card {
    padding: 15px;
  }
}
</style>
