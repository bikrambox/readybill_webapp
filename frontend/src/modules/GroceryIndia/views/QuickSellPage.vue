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
              <li class="breadcrumb-item active">
                {{ $t("common.Quick Sell") }}
              </li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t("common.Quick Sell") }}</h1>
        </div>
      </div>

      <!-- ── Global Error Alert ─────────────────────────────────────────── -->
      <div v-if="allErrors.length > 0" class="mb-3">
        <FormErrorBox :messages="allErrors" @clear="handleClearErrors" />
      </div>

      <!-- Search Component -->
      <QuickSellSearch @add-item="handleAddItem" @error="handleComponentError" />

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
              {{ $formatCurrency(quickSellStore.totalAmount) }}
            </span>
            <span class="total-amount" v-else>0.00</span>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="desktop-table">
          <QuickSellTable
            :items="quickSellStore.items"
            location="sell"
            @delete-item="handleDeleteItem"
            @update-item="handleUpdateItem"
            @error="handleComponentError"
          />
        </div>

        <!-- Mobile List -->
        <div class="mobile-list">
          <QuickSellItemList location="sell" @error="handleComponentError" />
        </div>

        <!-- Actions Component -->
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

    <!-- Result Modal -->
    <ResultModal
      :show="showResultModal"
      :status="resultData.status"
      :message="resultData.message"
      :title="resultData.title"
      @close="closeResultModal"
    />

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useQuickSellStore } from "@/modules/GroceryIndia/stores/quickSellStore";
import QuickSellSearch from "../components/quicksell/QuickSellSearch.vue";
import QuickSellTable from "../components/quicksell/QuickSellTable.vue";
import QuickSellItemList from "../components/quicksell/QuickSellItemList.vue";
import QuickSellActions from "../components/quicksell/QuickSellActions.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const quickSellStore = useQuickSellStore();

// ─── Global Error State ────────────────────────────────────────────────────
const allErrors = ref([]);

/**
 * Push one or multiple error messages into the global error box
 * Can be called from:
 *   - this page's own logic (saveOrder, cancelOrder, etc.)
 *   - child components via @error emit
 */
const pushError = (messages) => {
  const list = Array.isArray(messages) ? messages : [messages];
  // Merge — avoid exact duplicates
  list.forEach((msg) => {
    if (msg && !allErrors.value.includes(msg)) {
      allErrors.value.push(msg);
    }
  });
  // Auto-scroll to top so user sees the error
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleClearErrors = () => {
  allErrors.value = [];
};

/**
 * Receives @error emits from child components
 * Each child emits: @error="'some message'" or @error="['msg1','msg2']"
 */
const handleComponentError = (errorOrErrors) => {
  pushError(errorOrErrors);
};

// ─── Result Modal State ────────────────────────────────────────────────────
const showResultModal = ref(false);
const resultData = ref({
  status: "",
  message: "",
  title: "",
});

// ─── Child Event Handlers ──────────────────────────────────────────────────
const handleAddItem = (item) => {
  // Item added successfully — clear any stale errors
  handleClearErrors();
};

const handleUpdateItem = async (index, item) => {
  const cartItem = quickSellStore.items[index];
  const result = await quickSellStore.updateCartItem(cartItem.cartId, item);

  if (!result.success) {
    pushError(result.message || t("common.Failed to update item"));
  }
};

const handleDeleteItem = async (index) => {
  // Handled inside QuickSellTable — errors bubble up via @error
};

// ─── WhatsApp ──────────────────────────────────────────────────────────────
const sendToWhatsApp = () => {
  let message = "Quick Sell Order:\\n\\n";

  quickSellStore.items.forEach((item, index) => {
    message += `${index + 1}. ${item.name}\\n`;
    message += `   Qty: ${item.quantity} ${item.unit}\\n`;
    message += `   Rate: ₹${item.rate}\\n`;
    message += `   Amount: ₹${item.amount.toFixed(2)}\\n\\n`;
  });

  message += `*Grand Total: ₹${quickSellStore.totalAmount.toFixed(2)}*`;

  const encodedMessage = encodeURIComponent(message);
  const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
  window.open(whatsappUrl, "_blank");
};

// ─── Cancel Order ──────────────────────────────────────────────────────────
const cancelOrder = async () => {
  if (confirm(t("common.order_cancel_message"))) {
    handleClearErrors();

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
  }
};

// ─── Save Order ────────────────────────────────────────────────────────────
const saveOrder = async () => {
  handleClearErrors();

  if (quickSellStore.items.length === 0) {
    // Minor validation — show inline in modal, not in top error box
    resultData.value = {
      status: "error",
      title: t("common.No Items"),
      message: t("common.Please add items to save the order"),
    };
    showResultModal.value = true;
    return;
  }

  try {
    const result = await quickSellStore.saveOrder();

    if (result.success) {
      resultData.value = {
        status: "success",
        title: t("common.Order Saved"),
        message: result.message || t("common.Order saved successfully!"),
      };
    } else {
      // API returned failure — show in top error box
      pushError(result.message || t("common.Failed to save order"));

      resultData.value = {
        status: "error",
        title: t("common.Save Failed"),
        message: result.message || t("common.Failed to save order"),
      };
    }
  } catch (error) {
    // Unexpected/network error — show in top error box
    pushError(error.message || t("common.An error occurred while saving the order"));

    resultData.value = {
      status: "error",
      title: t("common.Error"),
      message: error.message || t("common.An error occurred while saving the order"),
    };
  }

  showResultModal.value = true;
};

// ─── Close Result Modal ────────────────────────────────────────────────────
const closeResultModal = () => {
  showResultModal.value = false;

  if (resultData.value.status === "success") {
    handleClearErrors();
    // Add any post-success logic here e.g. quickSellStore.clearCart()
  }
};

// ─── Lifecycle ─────────────────────────────────────────────────────────────
onMounted(async () => {
  quickSellStore.setLocation("sell");

  try {
    await quickSellStore.fetchCartItems();
  } catch (error) {
    pushError(error.message || t("common.Failed to load cart items"));
  }

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
