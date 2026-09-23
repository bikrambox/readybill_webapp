<template>
  <div class="quick-sell-page">
    <div class="page-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="sell">{{ $t('common.Home') }}</a></li>
              <li class="breadcrumb-item active">{{ $t('common.Refund') }}</li>
            </ol>
          </nav>
          <h1 class="page-title">{{ $t('common.Refund') }}</h1>
        </div>
      </div>

      <!-- Search Component -->
      <RefundSearch @add-item="addItem" @search="handleSearch" />

      <!-- Item List Card -->
      <div class="item-list-card">
        <!-- <h5 class="list-title">Item List</h5> -->

        <div class="list-header">
          <h5 class="list-title">Item List ({{ quickSellStore.itemCount }})</h5>
          <div class="total-display">
            Total:
            <!-- <span class="total-amount">₹{{ quickSellStore.totalAmount.toFixed(2) }}</span> -->
            <span v-if="quickSellStore.totalAmount"
              class="total-amount"
              :class="{ 'total-negative': quickSellStore.totalAmount < 0 }"
            >
            <span v-if=" quickSellStore.totalAmount < 0">-</span>  {{ $formatCurrency(Math.abs(quickSellStore.totalAmount)) }}
            </span>
            <span class="total-amount" v-else>
                0.00
            </span>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="desktop-table">
          <QuickSellTable
            :items="quickSellStore.items"
            @delete-item="deleteItem"
            @update-item="updateItem"
            location="refund"
          />
        </div>

        <!-- Mobile List -->
        <div class="mobile-list">
          <QuickSellItemList location="refund" />
        </div>

        <!-- Actions Component -->
        <QuickSellActions
          :items="items"
          @send-whatsapp="sendToWhatsApp"
          @cancel="cancelOrder"
          @save="saveOrder"
        />
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import RefundSearch from "../components/quicksell/RefundSearch.vue";
import QuickSellTable from "../components/quicksell/QuickSellTable.vue";
import QuickSellItemList from "../components/quicksell/QuickSellItemList.vue";
import QuickSellActions from "../components/quicksell/QuickSellActions.vue";
import Footer from "@/modules/GroceryGermany/components/Footer.vue";
import { useQuickSellStore } from "@/modules/GroceryGermany/stores/quickSellStore";

const quickSellStore = useQuickSellStore();

// const formatTotal = (amount) => {
//   const absAmount = Math.abs(parseFloat(amount || 0));
//   // Use minus sign (−) for negative amounts
//   const sign = amount < 0 ? "− " : "";
//   return `${sign}₹${absAmount.toFixed(2)}`;
// };

const items = ref([]);

const addItem = (item) => {
  const existingIndex = items.value.findIndex(
    (i) => i.name === item.name && i.unit === item.unit
  );

  if (existingIndex !== -1) {
    items.value[existingIndex].quantity += item.quantity;
    items.value[existingIndex].amount =
      items.value[existingIndex].quantity * items.value[existingIndex].rate;
  } else {
    items.value.push(item);
  }

  // console.log("Item added:", item);
};

const updateItem = (index, item) => {
  items.value[index] = item;
  // console.log("Item updated at index:", index, item);
};

const deleteItem = (index) => {
  items.value.splice(index, 1);
  // console.log("Item deleted at index:", index);
};

const handleSearch = (query) => {
  // console.log("Searching for:", query);
};

const sendToWhatsApp = () => {
  let message = "Quick Sell Order:\n\n";

  items.value.forEach((item, index) => {
    message += `${index + 1}. ${item.name}\n`;
    message += `   Qty: ${item.quantity} ${item.unit}\n`;
    message += `   Rate: £${item.rate}\n`;
    message += `   Amount: £${item.amount.toFixed(2)}\n\n`;
  });

  const total = items.value.reduce((sum, item) => sum + item.amount, 0);
  message += `*Grand Total: £${total.toFixed(2)}*`;

  const encodedMessage = encodeURIComponent(message);
  const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;

  window.open(whatsappUrl, "_blank");
};

const cancelOrder = () => {
  items.value = [];
  // console.log("Order cancelled");
};

const saveOrder = () => {
  const orderData = {
    items: items.value,
    total: items.value.reduce((sum, item) => sum + item.amount, 0),
    timestamp: new Date().toISOString(),
  };

  // console.log("Saving order:", orderData);
  alert(t('common.Order saved successfully!'));
};

onMounted(async () => {
  // Set location to 'refund' for this page
  quickSellStore.setLocation("refund");

  // Fetch cart items when component mounts
  await quickSellStore.fetchCartItems();
  quickSellStore.clearSuggestions();
});

onBeforeUnmount(() => {
  // Reset location when leaving the page
  quickSellStore.setLocation("refund");
});
</script>

<style scoped>
/* Previous styles remain the same */
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
  margin-bottom: 20px;
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