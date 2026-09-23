<template>
  <div class="transactions-section">
    <!-- Monthly Distribution Summary -->
    <div
      class="row mb-4"
      v-if="monthlyDistribution && monthlyDistribution.length > 0"
    >
      <div class="col-12">
        <h6 class="mb-2">
          <i class="bi bi-bar-chart-line me-2 text-primary"></i>
          Monthly Distribution
        </h6>
        <div class="row g-2">
          <div
            v-for="(monthData, index) in monthlyDistribution"
            :key="index"
            class="col-md-3 col-sm-6 col-12"
          >
            <div class="monthly-card p-3 bg-light border rounded h-100">
              <div class="month-name fw-bold text-muted mb-1">
                {{ monthData.month }}
              </div>
              <div class="month-amount text-success fw-semibold fs-5">
                ₹{{ formatCurrency(monthData.total) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th style="width: 15%">Invoice No</th>
            <th style="width: 35%">Products</th>
            <th style="width: 15%">Total</th>
            <th style="width: 20%">User Name</th>
            <th style="width: 15%">Date</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading State -->
          <tr v-if="reportStore.loading">
            <td colspan="5" class="text-center py-5">
              <div
                class="spinner-border spinner-border-sm text-primary me-2"
                role="status"
              >
                <span class="visually-hidden">Loading...</span>
              </div>
              Loading transactions...
            </td>
          </tr>

          <!-- Empty State -->
          <tr v-else-if="reportStore.transactions?.length === 0">
            <td colspan="5" class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
              {{
                props.dateFrom && props.dateTo
                  ? `No transactions found for ${props.dateFrom} to ${props.dateTo}`
                  : "Please select a date range to view transactions"
              }}
            </td>
          </tr>

          <!-- Data Rows -->
          <tr
            v-for="transaction in reportStore.transactions"
            :key="transaction.id"
            class="transaction-row align-middle"
            @click="handleRowClick(transaction.id)"
          >
            <td>
              <strong class="text-dark">{{
                transaction.invoice_number || "N/A"
              }}</strong>
            </td>

            <td>
              <div
                v-if="
                  transaction.item_list &&
                  parseItems(transaction.item_list).length > 0
                "
              >
                <div
                  v-for="(item, index) in parseItems(
                    transaction.item_list
                  ).slice(0, 3)"
                  :key="index"
                  class="product-item small mb-1"
                >
                  <span class="fw-medium">{{ item.itemName || "N/A" }}</span>
                  <span class="text-muted ms-1">
                    ({{ formatQuantity(item.quantity) }}
                    {{ item.selectedUnit || "" }})
                  </span>
                </div>
                <small
                  v-if="parseItems(transaction.item_list).length > 3"
                  class="text-muted"
                >
                  +{{ parseItems(transaction.item_list).length - 3 }} more items
                </small>
              </div>
              <span v-else class="text-muted small">No items</span>
            </td>

            <td>
              <strong
                :class="[
                  'amount-text',
                  parseFloat(transaction.total_price || 0) < 0
                    ? 'text-danger'
                    : 'text-primary',
                ]"
              >
                ₹{{ formatCurrency(transaction.total_price) }}
              </strong>
            </td>

            <td>
              <span class="fw-semibold">{{
                transaction.user_name || "N/A"
              }}</span>
            </td>

            <td>
              <small class="text-muted">{{
                formatDateTime(transaction.created_at)
              }}</small>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Info -->
      <div
        v-if="!reportStore.loading && reportStore.transactions?.length > 0"
        class="d-flex justify-content-between align-items-center mt-3 small text-muted px-2"
      >
        <span>
          Showing {{ reportStore.transactions.length }} of
          {{ reportStore.filteredRecords || 0 }}
          transactions
          <span v-if="reportStore.filteredRecords < reportStore.totalRecords">
            (filtered from {{ reportStore.totalRecords }} total)
          </span>
        </span>
        <span>
          Page {{ Math.floor(0 / props.pageSize) + 1 }} of
          {{ Math.ceil((reportStore.totalRecords || 0) / props.pageSize) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useReportStore } from "@/modules/GroceryGermany/stores/report.js";

const props = defineProps({
  dateFrom: String,
  dateTo: String,
  searchQuery: String,
  pageSize: {
    type: Number,
    default: 100,
  },
});

const emit = defineEmits(["row-click"]);
const reportStore = useReportStore();
const drawCounter = ref(1);

const parseItems = (itemListString) => {
  try {
    if (!itemListString) return [];
    const items = JSON.parse(itemListString);
    return Array.isArray(items) ? items : [];
  } catch (error) {
    console.error("Error parsing items:", error);
    return [];
  }
};

const formatCurrency = (amount) => {
  if (!amount && amount !== 0) return "0.00";
  return parseFloat(amount).toLocaleString("en-IN", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

const formatQuantity = (quantity) => {
  return parseFloat(quantity || 0).toLocaleString("en-IN", {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  });
};

const formatDateTime = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "Invalid Date";

  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();

  let hours = date.getHours();
  const minutes = date.getMinutes().toString().padStart(2, "0");
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12 || 12;

  return `${day}/${month}/${year} - ${hours}:${minutes} ${ampm}`;
};

// ✅ SAFE monthlyDistribution computed - FIXED ERROR
const monthlyDistribution = computed(() => {
  const transactions = reportStore.transactions || [];
  const distribution = {};

  transactions.forEach((transaction) => {
    if (transaction?.created_at) {
      const date = new Date(transaction.created_at);
      if (!isNaN(date.getTime())) {
        const monthKey = date.toLocaleDateString("en-IN", {
          year: "numeric",
          month: "short",
        });

        if (!distribution[monthKey]) {
          distribution[monthKey] = { month: monthKey, total: 0 };
        }

        distribution[monthKey].total += parseFloat(
          transaction.total_price || 0
        );
      }
    }
  });

  return Object.values(distribution).sort((a, b) => {
    const aDate = new Date(a.month.split(" ")[1] + "-" + a.month.split(" ")[0]);
    const bDate = new Date(b.month.split(" ")[1] + "-" + b.month.split(" ")[0]);
    return bDate - aDate;
  });
});

const formatDateForAPI = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);

  // ✅ Fixed format: "01/11/2025" exactly as requested
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();

  return `${day}/${month}/${year}`; // ✅ "01/11/2025", "30/12/2025"
};

const fetchData = async () => {
  if (!props.dateFrom || !props.dateTo) {
    if (reportStore.clearTransactions) {
      reportStore.clearTransactions();
    }
    return;
  }

  try {
    // ✅ FLAT structure - NO nested objects
    const formData = new FormData();
    formData.append("draw", drawCounter.value);
    formData.append("start", "0");
    formData.append("length", props.pageSize);
    formData.append("date_from", formatDateForAPI(props.dateFrom)); // "01/11/2025"
    formData.append("date_to", formatDateForAPI(props.dateTo)); // "30/12/2025"
    formData.append("search[value]", props.searchQuery || "");
    formData.append("search[regex]", "false");

    // ✅ Flat columns - Laravel expects this exact format
    formData.append("columns[0][data]", "invoice_number");
    formData.append("columns[0][name]", "");
    formData.append("columns[0][searchable]", "true");
    formData.append("columns[0][orderable]", "true");
    formData.append("columns[0][search][value]", "");
    formData.append("columns[0][search][regex]", "false");

    formData.append("columns[1][data]", "");
    formData.append("columns[1][name]", "");
    formData.append("columns[1][searchable]", "true");
    formData.append("columns[1][orderable]", "true");
    formData.append("columns[1][search][value]", "");
    formData.append("columns[1][search][regex]", "false");

    formData.append("columns[2][data]", "");
    formData.append("columns[2][name]", "");
    formData.append("columns[2][searchable]", "true");
    formData.append("columns[2][orderable]", "true");
    formData.append("columns[2][search][value]", "");
    formData.append("columns[2][search][regex]", "false");

    formData.append("columns[3][data]", "user_name");
    formData.append("columns[3][name]", "");
    formData.append("columns[3][searchable]", "true");
    formData.append("columns[3][orderable]", "true");
    formData.append("columns[3][search][value]", "");
    formData.append("columns[3][search][regex]", "false");

    formData.append("columns[4][data]", "");
    formData.append("columns[4][name]", "");
    formData.append("columns[4][searchable]", "true");
    formData.append("columns[4][orderable]", "true");
    formData.append("columns[4][search][value]", "");
    formData.append("columns[4][search][regex]", "false");

    // ✅ Order parameters
    formData.append("order[0][column]", "4");
    formData.append("order[0][dir]", "desc");

    console.log("🔔 Sending FormData to transaction-report API");

    await reportStore.fetchTransactions(formData);
    drawCounter.value++;
  } catch (error) {
    console.error("❌ Transaction API Error:", error);
    // ✅ Show API error to user
    if (error.response?.data?.message) {
      console.error("API Error Message:", error.response.data.message);
    }
  }
};

const refreshTable = async () => {
  drawCounter.value = 1;
  await fetchData();
};

const handleRowClick = (transactionId) => {
  emit("row-click", transactionId);
};

// ✅ Triggers API call on date change
watch(
  [() => props.dateFrom, () => props.dateTo],
  () => {
    refreshTable();
  },
  { immediate: true }
);

watch(
  () => props.searchQuery,
  () => {
    if (props.dateFrom && props.dateTo) {
      refreshTable();
    }
  }
);

defineExpose({
  refreshTable,
  fetchData,
});
</script>

<style scoped>
.transactions-section {
  margin-top: 1.5rem;
}

.transaction-row {
  cursor: pointer;
  transition: all 0.2s ease;
}

.transaction-row:hover {
  background-color: rgba(0, 123, 255, 0.08) !important;
  transform: scale(1.01);
}

.product-item {
  display: block;
  line-height: 1.3;
  word-break: break-word;
}

.monthly-card {
  transition: all 0.3s ease;
  border-left: 4px solid #28a745;
  cursor: default;
}

.monthly-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
}

.month-name {
  color: #6c757d;
  font-size: 0.9rem;
}

.amount-text {
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.table {
  font-size: 0.9rem;
}

.table th {
  font-weight: 600;
  border: none;
  position: sticky;
  top: 0;
  z-index: 10;
}

.table td {
  vertical-align: middle;
  border-color: rgba(0, 0, 0, 0.03);
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

@media (max-width: 768px) {
  .monthly-card {
    margin-bottom: 1rem;
  }

  .table-responsive {
    font-size: 0.85rem;
  }
}
</style>
