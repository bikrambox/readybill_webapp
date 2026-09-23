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
                {{ $formatCurrency(monthData.total) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Desktop Table View -->
    <div class="table-responsive d-none d-md-block">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>{{ $t('common.Invoice') }}</th>
            <th>{{ $t('common.Products') }}</th>
            <th>{{ $t('common.Total') }}</th>
            <th>{{ $t('common.User') }}</th>
            <th>{{ $t('common.Date') }}</th>
          </tr>
        </thead>
        <tbody>
          <!-- Loading State -->
          <template v-if="reportStore.loading">
            <tr>
              <td colspan="5" class="text-center py-4">
                <div
                  class="spinner-border spinner-border-sm text-primary"
                  role="status"
                >
                  <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
                </div>
                <span class="ms-2 text-muted">{{ $t('common.Loading data') }}...</span>
              </td>
            </tr>
          </template>


          <!-- Empty State -->
          <template v-else-if="reportStore.transactions?.length === 0 && !reportStore.error">
            <tr>
              <td colspan="5" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                {{
                  props.dateFrom && props.dateTo
                    ? `No transactions found for ${props.dateFrom} to ${props.dateTo}`
                    : "Please select a date range to view transactions"
                }}
              </td>
            </tr>
          </template>


          <!-- Error State -->
          <template v-else-if="reportStore.error">
            <tr>
              <td colspan="5" class="text-center py-4">
                <i class="bi bi-exclamation-triangle fs-1 mb-3 d-block text-warning"></i>
                <span class="text-muted">Unable to load transactions</span>
              </td>
            </tr>
          </template>


          <!-- Data Rows -->
          <template v-else>
            <tr
              v-for="transaction in reportStore.transactions"
              :key="transaction.id"
              @click="handleRowClick(transaction.id)"
              class="clickable-row"
              :class="{ 'highlight-new-row': transaction.isNew }"
            >
              <td class="text-primary fw-semibold">
                {{ transaction.invoice_number || "N/A" }}
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
                    ).slice(0, 2)"
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
                    v-if="parseItems(transaction.item_list).length > 2"
                    class="text-muted"
                  >
                    +{{ parseItems(transaction.item_list).length - 2 }} more
                  </small>
                </div>
                <span v-else class="text-muted small">No items</span>
              </td>


              <td class="fw-semibold">
                {{ $formatCurrency(transaction.total_price) }}
              </td>


              <td>
                <span class="badge bg-success-subtle text-success">
                  {{ transaction.user_name || "N/A" }}
                </span>
              </td>


              <td>
                <small>{{ formatDateTime(transaction.created_at) }}</small>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>


    <!-- Mobile Card View -->
    <div class="d-block d-md-none">
      <!-- Loading State -->
      <div v-if="reportStore.loading" class="text-center py-4">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
          <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
        </div>
        <p class="mt-2 text-muted small">{{ $t('common.Loading data') }}...</p>
      </div>


      <!-- Empty State -->
      <div
        v-else-if="reportStore.transactions?.length === 0 && !reportStore.error"
        class="text-center py-4 text-muted"
      >
        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
        <p class="small">
          {{
            props.dateFrom && props.dateTo
              ? `No transactions found for ${props.dateFrom} to ${props.dateTo}`
              : "Please select a date range to view transactions"
          }}
        </p>
      </div>


      <!-- Error State -->
      <div v-else-if="reportStore.error" class="text-center py-4">
        <i class="bi bi-exclamation-triangle fs-1 mb-3 d-block text-warning"></i>
        <p class="text-muted small">Unable to load transactions</p>
      </div>


      <!-- Transaction Cards -->
      <div v-else>
        <div
          v-for="transaction in reportStore.transactions"
          :key="transaction.id"
          class="transaction-card mb-3"
          :class="{ 'highlight-new-row': transaction.isNew }"
          @click="handleRowClick(transaction.id)"
        >
          <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
              <!-- Header Row -->
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-primary fw-semibold small">
                  {{ transaction.invoice_number || "N/A" }}
                </span>
                <span class="fw-bold text-success">
                  {{ $formatCurrency(transaction.total_price) }}
                </span>
              </div>


              <!-- User and Date -->
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-success-subtle text-success small">
                  {{ transaction.user_name || "N/A" }}
                </span>
                <small class="text-muted">
                  {{ formatDateTime(transaction.created_at) }}
                </small>
              </div>


              <!-- Products -->
              <div class="mt-2 pt-2 border-top">
                <div
                  v-if="
                    transaction.item_list &&
                    parseItems(transaction.item_list).length > 0
                  "
                >
                  <div
                    v-for="(item, index) in parseItems(
                      transaction.item_list
                    ).slice(0, 2)"
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
                    v-if="parseItems(transaction.item_list).length > 2"
                    class="text-muted"
                  >
                    +{{ parseItems(transaction.item_list).length - 2 }} more items
                  </small>
                </div>
                <span v-else class="text-muted small">No items</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- Pagination -->
    <div
      v-if="!reportStore.loading && !reportStore.error && reportStore.transactions?.length > 0"
      class="pagination-container mt-4"
    >
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- Left: Rows per page dropdown -->
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted" style="font-size: 0.9rem;">Rows per page:</span>
          <select
            v-model="localPageSize"
            @change="handlePageSizeChange"
            class="form-select form-select-sm pagination-select"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>


        <!-- Right: Page info and navigation -->
        <div class="d-flex align-items-center gap-3">
          <!-- Page range text -->
          <span class="" style="font-size: 0.9rem;">
            {{ currentPageStart + 1 }}-{{ currentPageEnd }} of {{ reportStore.filteredRecords || 0 }}
          </span>


          <!-- Navigation buttons -->
          <div class="pagination-nav">
            <button
              class="btn-pagination"
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              aria-label="Previous page"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              class="btn-pagination"
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              aria-label="Next page"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, watch } from "vue";
import { useReportStore } from "@/modules/GroceryGermany/stores/report.js";
import { useI18n } from "vue-i18n";


const { t } = useI18n();


const props = defineProps({
  dateFrom: String,
  dateTo: String,
  searchQuery: String,
  pageSize: {
    type: Number,
    default: 10,
  },
});


const emit = defineEmits(["row-click", "update:pageSize"]);
const reportStore = useReportStore();
const drawCounter = ref(1);
const currentPage = ref(1);
const localPageSize = ref(props.pageSize);


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


// Pagination Computed Properties
const totalPages = computed(() => {
  return Math.ceil((reportStore.filteredRecords || 0) / localPageSize.value);
});


const currentPageStart = computed(() => {
  return (currentPage.value - 1) * localPageSize.value;
});


const currentPageEnd = computed(() => {
  const end = currentPage.value * localPageSize.value;
  return Math.min(end, reportStore.filteredRecords || 0);
});


const formatDateForAPI = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);


  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();


  return `${day}/${month}/${year}`;
};


const fetchData = async () => {
  if (!props.dateFrom || !props.dateTo) {
    if (reportStore.clearTransactions) {
      reportStore.clearTransactions();
    }
    return;
  }


  try {
    const params = {
      draw: drawCounter.value,
      start: currentPageStart.value,
      length: localPageSize.value,
      date_from: formatDateForAPI(props.dateFrom),
      date_to: formatDateForAPI(props.dateTo),
      search: props.searchQuery || "",
      order: [{ column: 4, dir: "desc" }],
    };


    console.log("🔔 Fetching transactions with params:", params);


    await reportStore.fetchTransactions(params);
    drawCounter.value++;
  } catch (error) {
    console.error("❌ Transaction API Error:", error);
    if (error.response?.data?.message) {
      console.error("API Error Message:", error.response.data.message);
    }
  }
};


const refreshTable = async () => {
  currentPage.value = 1;
  drawCounter.value = 1;
  await fetchData();
};


const goToPage = async (page) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  await fetchData();
};


const handlePageSizeChange = () => {
  emit("update:pageSize", localPageSize.value);
  currentPage.value = 1;
  fetchData();
};


const handleRowClick = (transactionId) => {
  console.log("🔍 Transaction clicked:", transactionId);
  emit("row-click", transactionId);
};


// Watch for prop changes
watch(
  () => props.pageSize,
  (newSize) => {
    localPageSize.value = newSize;
  }
);


// Watch for date changes - reset to page 1
watch(
  [() => props.dateFrom, () => props.dateTo],
  ([newFrom, newTo]) => {
    console.log("👀 Date props changed:", { newFrom, newTo });


    if (newFrom && newTo) {
      console.log("✅ Both dates present, fetching data...");
      refreshTable();
    } else {
      console.log("⏸️ Waiting for both dates...");
      if (reportStore.clearTransactions) {
        reportStore.clearTransactions();
      }
    }
  }
);


// Watch for search changes - reset to page 1
watch(
  () => props.searchQuery,
  (newSearch) => {
    console.log("🔍 Search changed:", newSearch);
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


/* Desktop Table Styles */
.table thead th {
  font-weight: 600;
  font-size: 0.875rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}


.table tbody td {
  padding: 14px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
}


.clickable-row {
  cursor: pointer;
  transition: background-color 0.2s ease;
}


.clickable-row:hover {
  background-color: #f8f9fa;
}


.badge {
  padding: 0.375rem 0.75rem;
  font-weight: 500;
}


.product-item {
  display: block;
  line-height: 1.3;
  word-break: break-word;
}


/* Pagination Styles */
.pagination-container {
  padding: 1rem 0;
  border-top: 1px solid #dee2e6;
}


.pagination-container .text-muted {
  font-weight: 600;
  color: #495057 !important;
}


.pagination-select {
  width: 70px;
  border: none;
  border-radius: 0.25rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  background-color: transparent;
  color: #212529;
}


.pagination-select:focus {
  border: none;
  outline: 0;
  box-shadow: none;
  background-color: #f8f9fa;
}


.pagination-select:hover {
  background-color: #f8f9fa;
}


.pagination-nav {
  display: flex;
  gap: 0.5rem;
}


.btn-pagination {
  width: 36px;
  height: 36px;
  border: 1px solid #dee2e6;
  border-radius: 0.25rem;
  background-color: #fff;
  color: #6c757d;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 1rem;
  padding: 0;
}


.btn-pagination:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #adb5bd;
  color: #212529;
}


.btn-pagination:active:not(:disabled) {
  background-color: #e9ecef;
}


.btn-pagination:disabled {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  color: #c8ccd0;
  cursor: not-allowed;
  opacity: 0.6;
}


.btn-pagination i {
  font-size: 0.9rem;
}


/* Mobile Card Styles */
.transaction-card {
  cursor: pointer;
  transition: all 0.2s ease;
}


.transaction-card:hover {
  transform: translateY(-2px);
}


.transaction-card .card {
  transition: box-shadow 0.2s ease;
}


.transaction-card:hover .card {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}


/* Monthly Distribution */
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


/* WebSocket Animation Styles */
.highlight-new-row {
  background-color: #d1ecf1 !important;
  animation: pulse 0.5s ease-in-out;
}


@keyframes pulse {
  0% {
    background-color: #fff3cd;
  }
  50% {
    background-color: #d1ecf1;
  }
  100% {
    background-color: #d1ecf1;
  }
}


.transition-new-row {
  animation: slideInFromTop 0.5s ease-out;
}


@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}


.transition-fade-out {
  animation: fadeOut 0.6s ease-out forwards;
}


@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
    transform: translateX(-20px);
  }
}


/* Responsive adjustments */
@media (max-width: 768px) {
  .monthly-card {
    margin-bottom: 0.5rem;
  }


  .transaction-card .card-body {
    padding: 0.75rem !important;
  }


  .product-item {
    font-size: 0.8rem;
  }


  .pagination-container {
    padding: 0.75rem 0;
  }


  .pagination-container > div {
    flex-direction: column;
    align-items: flex-start !important;
  }


  .pagination-container > div > div:last-child {
    width: 100%;
    justify-content: space-between;
  }
}
</style>
