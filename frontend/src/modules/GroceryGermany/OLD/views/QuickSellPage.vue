<template>
  <div class="quick-sell-page">
    <div class="page-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="breadcrumb-section">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Quick Sell</li>
            </ol>
          </nav>
          <h1 class="page-title">Quick Sell</h1>
        </div>
      </div>

      <!-- Search Component -->
      <QuickSellSearch @add-item="handleAddItem" />

      <!-- Item List Card -->
      <div class="item-list-card">
        <div class="list-header">
          <h5 class="list-title">Item List ({{ quickSellStore.itemCount }})</h5>
          <div class="total-display">
            Total:
            <!-- <span class="total-amount">₹{{ quickSellStore.totalAmount.toFixed(2) }}</span> -->
            <span
              class="total-amount"
              :class="{ 'total-negative': quickSellStore.totalAmount < 0 }"
            >
              {{ formatTotal(quickSellStore.totalAmount) }}
            </span>
          </div>
        </div>

        <!-- Desktop Table -->
        <div class="desktop-table">
          <QuickSellTable
            :items="quickSellStore.items"
            @delete-item="handleDeleteItem"
            @update-item="handleUpdateItem"
            location="sell"
          />
        </div>

        <!-- Mobile List -->
        <div class="mobile-list">
          <QuickSellItemList location="sell" />
        </div>

        <!-- Actions Component -->
        <QuickSellActions
          :items="quickSellStore.items"
          :loading="quickSellStore.loading"
          @send-whatsapp="sendToWhatsApp"
          @cancel="cancelOrder"
          @save="saveOrder"
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
import { useQuickSellStore } from "@/modules/GroceryGermany/stores/quickSellStore";
import QuickSellSearch from "../components/quicksell/QuickSellSearch.vue";
import QuickSellTable from "../components/quicksell/QuickSellTable.vue";
import QuickSellItemList from "../components/quicksell/QuickSellItemList.vue";
import QuickSellActions from "../components/quicksell/QuickSellActions.vue";
import Footer from "@/modules/GroceryGermany/components/Footer.vue";

const quickSellStore = useQuickSellStore();

// Result Modal State
const showResultModal = ref(false);
const resultData = ref({
  status: "",
  message: "",
  title: "",
});

const formatTotal = (amount) => {
  const absAmount = Math.abs(parseFloat(amount || 0));
  // Use minus sign (−) for negative amounts
  const sign = amount < 0 ? "− " : "";
  return `${sign}₹${absAmount.toFixed(2)}`;
};

const handleAddItem = (item) => {
  console.log("Item added:", item);
};

const handleUpdateItem = async (index, item) => {
  const cartItem = quickSellStore.items[index];
  const result = await quickSellStore.updateCartItem(cartItem.cartId, item);

  if (!result.success) {
    alert(result.message || "Failed to update item");
  }
};

const handleDeleteItem = async (index) => {
  const cartItem = quickSellStore.items[index];

  // if (confirm('Are you sure you want to delete this item?')) {
  //   const result = await quickSellStore.deleteCartItem(cartItem.cartId);

  //   if (!result.success) {
  //     alert(result.message || 'Failed to delete item');
  //   }
  // }

  // const result = await quickSellStore.deleteCartItem(cartItem.cartId);

  // if (!result.success) {
  //   alert(result.message || "Failed to delete item");
  // }
  
  
};

const sendToWhatsApp = () => {
  let message = "Quick Sell Order:\n\n";

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

const cancelOrder = async () => {
  if (
    confirm(
      "Are you sure you want to cancel this order? All items will be removed."
    )
  ) {
    // Delete all items from cart
    for (const item of quickSellStore.items) {
      await quickSellStore.deleteCartItem(item.cartId);
    }
    console.log("Order cancelled");
  }
};

// const saveOrder = () => {
//   if (quickSellStore.items.length === 0) {
//     alert('Please add items to save the order');
//     return;
//   }

//   alert('Order saved successfully!');
//   console.log('Order saved with', quickSellStore.items);
// };

const saveOrder = async () => {
  if (quickSellStore.items.length === 0) {
    resultData.value = {
      status: "error",
      title: "No Items",
      message: "Please add items to save the order",
    };
    showResultModal.value = true;
    return;
  }

  try {
    // Call your store action to save the order
    const result = await quickSellStore.saveOrder();

    if (result.success) {
      resultData.value = {
        status: "success",
        title: "Order Saved",
        message: result.message || "Order saved successfully!",
      };
    } else {
      resultData.value = {
        status: "error",
        title: "Save Failed",
        message: result.message || "Failed to save order",
      };
    }
  } catch (error) {
    resultData.value = {
      status: "error",
      title: "Error",
      message: error.message || "An error occurred while saving the order",
    };
  }

  showResultModal.value = true;
};

const closeResultModal = () => {
  showResultModal.value = false;

  // Optional: Clear items if save was successful
  if (resultData.value.status === "success") {
    // You can add logic here to clear the cart or redirect
    // For example: quickSellStore.clearCart();
  }
};

onMounted(async () => {
  // Set location to 'sell' for this page
  quickSellStore.setLocation("sell");

  // Fetch cart items when component mounts
  await quickSellStore.fetchCartItems();
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
