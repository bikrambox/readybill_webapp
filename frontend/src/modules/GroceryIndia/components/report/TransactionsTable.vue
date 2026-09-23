<template>
  <div class="transactions-section">
    <!-- Desktop Table View -->
    <div class="table-wrapper d-none d-md-block">
      <div
        v-if="showOverlayLoading"
        class="table-loading-overlay"
        aria-live="polite"
        aria-busy="true"
      >
        <div
          class="d-inline-flex align-items-center justify-content-center search-loading-pill"
        >
          <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
          </div>
          <span class="ms-2 text-muted fw-medium">
            {{ $t("common.Searching") || "Searching transactions..." }}
          </span>
        </div>
      </div>

      <div class="table-responsive results-container">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>{{ $t("common.Invoice") }}</th>
              <th>{{ $t("common.Products") }}</th>
              <th>{{ $t("common.Total") }}</th>
              <th>{{ $t("common.Payment Status") }}</th>
              <th>{{ $t("common.User") }}</th>
              <th>{{ $t("common.Date") }}</th>
            </tr>
          </thead>

          <tbody>
            <!-- Initial Loading State -->
            <template v-if="showInitialLoading">
              <tr>
                <td colspan="6" class="text-center py-4">
                  <div
                    class="spinner-border spinner-border-sm text-primary"
                    role="status"
                  >
                    <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
                  </div>
                  <span class="ms-2 text-muted">{{ $t("common.Loading data") }}...</span>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <template v-else-if="showEmptyState">
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
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
            <template v-else-if="showErrorState">
              <tr>
                <td colspan="6" class="text-center py-4">
                  <i
                    class="bi bi-exclamation-triangle fs-1 mb-3 d-block text-warning"
                  ></i>
                  <span class="text-muted">Unable to load transactions</span>
                </td>
              </tr>
            </template>

            <!-- Grouped Data Rows -->
            <template v-else>
              <template v-for="group in groupedTransactions" :key="group.month">
                <!-- Month Header Row -->
                <tr class="month-header-row">
                  <td colspan="2">
                    <i class="bi bi-calendar-month me-2 text-primary"></i>
                    <strong>{{ group.month }}</strong>
                    <span class="ms-2 text-muted small">
                      ({{ group.transactions.length }} transaction{{
                        group.transactions.length !== 1 ? "s" : ""
                      }})
                    </span>
                  </td>
                  <td colspan="4" class="text-end">
                    <span class="month-total-badge">
                      <i class="bi bi-currency-rupee"></i>
                      Total: <strong>{{ $formatCurrency(group.total) }}</strong>
                    </span>
                  </td>
                </tr>

                <!-- Transaction Rows -->
                <tr
                  v-for="transaction in group.transactions"
                  :key="transaction.id"
                  @click="handleRowClick(transaction.id)"
                  class="clickable-row"
                  :class="{ 'highlight-new-row': transaction.isNew }"
                >
                  <td class="text-primary fw-semibold ps-4">
                    {{ transaction.invoice_number || "N/A" }}
                  </td>

                  <td>
                    <div v-if="transaction.parsedItems.length > 0">
                      <div
                        v-for="(item, index) in transaction.parsedItems.slice(0, 2)"
                        :key="index"
                        class="product-item small mb-1"
                      >
                        <span class="fw-medium">{{ item.itemName || "N/A" }}</span>
                        <span class="text-muted ms-1">
                          ({{ formatQuantity(item.quantity) }}
                          {{ item.selectedUnit || "" }})
                        </span>
                      </div>

                      <small v-if="transaction.parsedItems.length > 2" class="text-muted">
                        +{{ transaction.parsedItems.length - 2 }} more
                      </small>
                    </div>

                    <span v-else class="text-muted small">No items</span>
                  </td>

                  <td class="fw-semibold">
                    {{ $formatCurrency(transaction.total_price) }}
                  </td>

                  <td>
                    <span
                      v-if="transaction.payment_status == 1"
                      class="badge rounded-pill bg-success text-white fw-bold"
                    >
                      {{ $t("common.Paid") }}
                    </span>

                    <span
                      v-else-if="transaction.payment_status == 0"
                      class="badge rounded-pill bg-danger text-white fw-bold"
                    >
                      {{ $t("common.Unpaid") }}
                    </span>
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
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Card View -->
    <div class="mobile-results-wrapper d-block d-md-none">
      <div
        v-if="showOverlayLoading"
        class="table-loading-overlay mobile-overlay"
        aria-live="polite"
        aria-busy="true"
      >
        <div
          class="d-inline-flex align-items-center justify-content-center search-loading-pill"
        >
          <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
          </div>
          <span class="ms-2 text-muted fw-medium">
            {{ $t("common.Searching") || "Searching transactions..." }}
          </span>
        </div>
      </div>

      <div v-if="showInitialLoading" class="text-center py-4 results-container">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
          <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
        </div>
        <p class="mt-2 text-muted small">{{ $t("common.Loading data") }}...</p>
      </div>

      <div
        v-else-if="showEmptyState"
        class="text-center py-4 text-muted results-container"
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

      <div v-else-if="showErrorState" class="text-center py-4 results-container">
        <i class="bi bi-exclamation-triangle fs-1 mb-3 d-block text-warning"></i>
        <p class="text-muted small">Unable to load transactions</p>
      </div>

      <!-- Mobile Grouped Cards -->
      <div v-else class="results-container">
        <div v-for="group in groupedTransactions" :key="group.month" class="mb-4">
          <!-- Month Header Card -->
          <div class="month-header-mobile mb-2">
            <div class="d-flex justify-content-between align-items-center">
              <span>
                <i class="bi bi-calendar-month me-2 text-primary"></i>
                <strong>{{ group.month }}</strong>
                <span class="ms-2 text-muted small">
                  ({{ group.transactions.length }} txn{{
                    group.transactions.length !== 1 ? "s" : ""
                  }})
                </span>
              </span>
              <strong class="text-success">{{ $formatCurrency(group.total) }}</strong>
            </div>
          </div>

          <!-- Transaction Cards -->
          <div
            v-for="transaction in group.transactions"
            :key="transaction.id"
            class="transaction-card mb-2"
            :class="{ 'highlight-new-row': transaction.isNew }"
            @click="handleRowClick(transaction.id)"
          >
            <div class="card transaction-mobile-card border-0 shadow-sm">
              <div class="card-body p-3">
                <!-- Top Row -->
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                  <div class="min-w-0 flex-grow-1">
                    <div class="text-primary fw-semibold small text-truncate">
                      {{ transaction.invoice_number || "N/A" }}
                    </div>
                    <small class="text-muted d-block mt-1">
                      {{ formatDateTime(transaction.created_at) }}
                    </small>
                  </div>

                  <div class="text-end flex-shrink-0">
                    <div class="fw-bold text-success">
                      {{ $formatCurrency(transaction.total_price) }}
                    </div>
                  </div>
                </div>

                <!-- Meta Row -->
                <div class="transaction-meta-row mb-2">
                  <span class="badge user-badge">
                    <i class="bi bi-person-circle me-1"></i>
                    {{ transaction.user_name || "N/A" }}
                  </span>

                  <span
                    v-if="transaction.payment_status == 1"
                    class="badge payment-badge payment-paid"
                  >
                    <i class="bi bi-check-circle-fill me-1"></i>
                    {{ $t("common.Paid") }}
                  </span>

                  <span
                    v-else-if="transaction.payment_status == 0"
                    class="badge payment-badge payment-unpaid"
                  >
                    <i class="bi bi-x-circle-fill me-1"></i>
                    {{ $t("common.Unpaid") }}
                  </span>
                </div>

                <!-- Items -->
                <div class="mt-2 pt-2 border-top transaction-items">
                  <div v-if="transaction.parsedItems.length > 0">
                    <div
                      v-for="(item, index) in transaction.parsedItems.slice(0, 2)"
                      :key="index"
                      class="product-item small mb-1"
                    >
                      <span class="fw-medium">{{ item.itemName || "N/A" }}</span>
                      <span class="text-muted ms-1">
                        ({{ formatQuantity(item.quantity) }}
                        {{ item.selectedUnit || "" }})
                      </span>
                    </div>

                    <small v-if="transaction.parsedItems.length > 2" class="text-muted">
                      +{{ transaction.parsedItems.length - 2 }} more items
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
        v-if="
          !reportStore.loadingTransactions &&
          !reportStore.error &&
          reportStore.transactions?.length > 0
        "
        class="pagination-container mt-4"
      >
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
          <div class="d-flex align-items-center gap-2">
            <span class="text-muted" style="font-size: 0.9rem">Rows per page:</span>
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

          <div class="d-flex align-items-center gap-3">
            <span style="font-size: 0.9rem">
              {{ displayPageStart }}-{{ currentPageEnd }} of
              {{ reportStore.filteredRecords || 0 }}
            </span>
            <div class="pagination-nav">
              <button
                class="btn-pagination"
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1 || isFetching"
                aria-label="Previous page"
              >
                <i class="bi bi-chevron-left"></i>
              </button>
              <button
                class="btn-pagination"
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages || isFetching"
                aria-label="Next page"
              >
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop Pagination -->
    <div
      v-if="
        !reportStore.loadingTransactions &&
        !reportStore.error &&
        reportStore.transactions?.length > 0
      "
      class="pagination-container mt-4 d-none d-md-block"
    >
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted" style="font-size: 0.9rem">Rows per page:</span>
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

        <div class="d-flex align-items-center gap-3">
          <span style="font-size: 0.9rem">
            {{ displayPageStart }}-{{ currentPageEnd }} of
            {{ reportStore.filteredRecords || 0 }}
          </span>
          <div class="pagination-nav">
            <button
              class="btn-pagination"
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1 || isFetching"
              aria-label="Previous page"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              class="btn-pagination"
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages || isFetching"
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
import { ref, computed, watch, onBeforeUnmount } from "vue";
import { useReportStore } from "@/modules/GroceryIndia/stores/report.js";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  dateFrom: { type: String, default: "" },
  dateTo: { type: String, default: "" },
  searchQuery: { type: String, default: "" },
  filterColumn: { type: String, default: "invoice_number" },
  pageSize: { type: Number, default: 10 },
});

const emit = defineEmits(["row-click", "update:pageSize"]);
const reportStore = useReportStore();

const drawCounter = ref(1);
const currentPage = ref(1);
const localPageSize = ref(props.pageSize);

const isSearching = ref(false);
const isFetching = ref(false);
const hasLoadedOnce = ref(false);

let searchTimer = null;
let requestToken = 0;

onBeforeUnmount(() => {
  if (searchTimer) clearTimeout(searchTimer);
});

const parseItems = (itemListString) => {
  try {
    if (!itemListString) return [];
    const items = JSON.parse(itemListString);
    return Array.isArray(items) ? items : [];
  } catch {
    return [];
  }
};

const normalizedTransactions = computed(() => {
  return (reportStore.transactions || []).map((transaction) => ({
    ...transaction,
    parsedItems: parseItems(transaction.item_list),
  }));
});

const groupedTransactions = computed(() => {
  const transactions = normalizedTransactions.value;
  const groups = {};

  transactions.forEach((transaction) => {
    if (transaction?.created_at) {
      const date = new Date(transaction.created_at);

      if (!isNaN(date.getTime())) {
        const monthKey = date.toLocaleDateString("en-IN", {
          year: "numeric",
          month: "long",
        });

        if (!groups[monthKey]) {
          groups[monthKey] = {
            month: monthKey,
            total: 0,
            transactions: [],
            sortDate: new Date(date.getFullYear(), date.getMonth(), 1),
          };
        }

        groups[monthKey].transactions.push(transaction);
        groups[monthKey].total += parseFloat(transaction.total_price || 0);
      }
    }
  });

  return Object.values(groups).sort((a, b) => b.sortDate - a.sortDate);
});

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

const totalPages = computed(() =>
  Math.max(1, Math.ceil((reportStore.filteredRecords || 0) / localPageSize.value))
);

const currentPageStart = computed(() => (currentPage.value - 1) * localPageSize.value);

const currentPageEnd = computed(() => {
  const end = currentPage.value * localPageSize.value;
  return Math.min(end, reportStore.filteredRecords || 0);
});

const displayPageStart = computed(() => {
  if ((reportStore.filteredRecords || 0) === 0) return 0;
  return currentPageStart.value + 1;
});

const showInitialLoading = computed(() => {
  return reportStore.loadingTransactions && !hasLoadedOnce.value && !isSearching.value;
});

const showOverlayLoading = computed(() => {
  return hasLoadedOnce.value && (isSearching.value || isFetching.value);
});

const showEmptyState = computed(() => {
  return (
    !reportStore.loadingTransactions &&
    !isSearching.value &&
    !reportStore.error &&
    hasLoadedOnce.value &&
    (reportStore.transactions?.length || 0) === 0
  );
});

const showErrorState = computed(() => {
  return !reportStore.loadingTransactions && !isSearching.value && !!reportStore.error;
});

const formatDateForAPI = (dateString) => {
  if (!dateString) return "";

  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "";

  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();

  return `${day}/${month}/${year}`;
};

const fetchData = async ({ searching = false, resetPage = false } = {}) => {
  if (!props.dateFrom || !props.dateTo) {
    reportStore.clearTransactions?.();
    hasLoadedOnce.value = false;
    isSearching.value = false;
    isFetching.value = false;
    return;
  }

  if (resetPage) {
    currentPage.value = 1;
    drawCounter.value = 1;
  }

  const currentRequest = ++requestToken;
  isFetching.value = true;
  isSearching.value = searching;

  try {
    const transactionParams = {
      draw: drawCounter.value,
      start: currentPageStart.value,
      length: localPageSize.value,
      date_from: formatDateForAPI(props.dateFrom),
      date_to: formatDateForAPI(props.dateTo),
      search: props.searchQuery?.trim() || "",
      order: [{ column: 4, dir: "desc" }],
      filter_option: props.filterColumn,
    };

    await reportStore.fetchTransactions(transactionParams);

    if (currentRequest === requestToken) {
      drawCounter.value++;
      hasLoadedOnce.value = true;
    }
  } catch (error) {
    console.error("❌ Transaction API Error:", error);
  } finally {
    if (currentRequest === requestToken) {
      isFetching.value = false;
      isSearching.value = false;
    }
  }
};

const refreshTable = async () => {
  await fetchData({ resetPage: true });
};

const goToPage = async (page) => {
  if (page < 1 || page > totalPages.value || isFetching.value) return;
  currentPage.value = page;
  await fetchData();
};

const handlePageSizeChange = async () => {
  emit("update:pageSize", localPageSize.value);
  await fetchData({ resetPage: true });
};

const handleRowClick = (transactionId) => {
  if (isSearching.value || isFetching.value) return;
  emit("row-click", transactionId);
};

watch(
  () => props.pageSize,
  (newSize) => {
    localPageSize.value = newSize;
  }
);

watch(
  () => [props.dateFrom, props.dateTo],
  async ([newFrom, newTo]) => {
    if (!newFrom || !newTo) {
      reportStore.clearTransactions?.();
      hasLoadedOnce.value = false;
      return;
    }

    await refreshTable();
  },
  { immediate: true }
);

watch(
  () => [props.searchQuery, props.filterColumn],
  () => {
    if (!props.dateFrom || !props.dateTo) return;

    if (searchTimer) clearTimeout(searchTimer);

    isSearching.value = true;

    searchTimer = setTimeout(async () => {
      await fetchData({ searching: true, resetPage: true });
    }, 300);
  }
);

defineExpose({ refreshTable, fetchData });
</script>

<style scoped>
.transactions-section {
  margin-top: 1.5rem;
}

.table-wrapper,
.mobile-results-wrapper {
  position: relative;
}

.results-container {
  min-height: 420px;
}

.table {
  table-layout: fixed;
  width: 100%;
}

.table thead th {
  font-weight: 600;
  font-size: 0.875rem;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.table tbody td {
  padding: 12px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
}

.table-loading-overlay {
  position: absolute;
  inset: 0;
  z-index: 10;
  background: rgba(255, 255, 255, 0.55);
  backdrop-filter: blur(1px);
  -webkit-backdrop-filter: blur(1px);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  pointer-events: all;
}

.mobile-overlay {
  border-radius: 0.75rem;
}

.search-loading-pill {
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 999px;
  padding: 0.5rem 0.9rem;
  box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.05);
}

/* Month Header Row */
.month-header-row td {
  background: linear-gradient(135deg, #e8f4fd, #f0f7ff);
  border-top: 2px solid #0d6efd !important;
  border-bottom: 1px solid #b8d9f8 !important;
  padding: 10px 12px !important;
  font-size: 0.88rem;
}

.month-total-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  border-radius: 20px;
  padding: 0.2rem 0.75rem;
  font-size: 0.82rem;
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

/* Mobile Month Header */
.month-header-mobile {
  background: linear-gradient(135deg, #e8f4fd, #f0f7ff);
  border: 1px solid #b8d9f8;
  border-left: 4px solid #0d6efd;
  border-radius: 10px;
  padding: 0.7rem 0.9rem;
  font-size: 0.88rem;
}

/* Mobile Card Design */
.transaction-mobile-card {
  border-radius: 12px;
  overflow: hidden;
  transition: box-shadow 0.2s ease;
  background: #ffffff;
}

.transaction-mobile-card .card-body {
  padding: 0.9rem !important;
}

.transaction-meta-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.user-badge {
  background: #e8f5e9;
  color: #2e7d32;
  font-weight: 600;
  padding: 0.45rem 0.7rem;
  border-radius: 999px;
}

.payment-badge {
  font-weight: 700;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  white-space: nowrap;
}

.payment-paid {
  background: #198754;
  color: #fff;
}

.payment-unpaid {
  background: #dc3545;
  color: #fff;
}

.transaction-items {
  font-size: 0.82rem;
}

.transaction-card {
  cursor: pointer;
}

.transaction-card:active {
  transform: scale(0.99);
}

.min-w-0 {
  min-width: 0;
}

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

/* Pagination */
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

@media (max-width: 768px) {
  .results-container {
    min-height: 320px;
  }

  .transaction-card .card-body {
    padding: 0.85rem !important;
  }

  .product-item {
    font-size: 0.8rem;
    line-height: 1.4;
  }

  .transaction-meta-row {
    align-items: flex-start;
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
