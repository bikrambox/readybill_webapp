<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { useTransactionStore } from "@/modules/GroceryIndia/stores/transactionStore";
import TransactionCard from "../components/transactions/TransactionCard.vue";
import TransactionTable from "../components/transactions/TransactionTable.vue";
import TransactionDetailModal from "../components/transactions/TransactionDetailModal.vue";
import ConfirmDialog from "../components/transactions/ConfirmDialog.vue";
import TransactionsMobileView from "../components/transactions/TransactionsMobileView.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";
import { useAuthStore } from "@/modules/Authentication/stores/authStore";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const transactionStore = useTransactionStore();

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.isAdmin === 1);

const activeTab = ref("unpaid");
const searchQuery = ref("");
const filterColumn = ref("invoice_number");
const selectedMonth = ref("");
const rowsPerPage = ref(10);
const selectedTransaction = ref(null);
const showModal = ref(false);
const showConfirmDialog = ref(false);
const pendingMarkPaidId = ref(null);

// Computed properties from store
const transactions = computed(() => transactionStore.transactions);
const totalSales = computed(() => transactionStore.totalSales);
const loading = computed(() => transactionStore.loading);
const startRecord = computed(() => transactionStore.startRecord);
const endRecord = computed(() => transactionStore.endRecord);
const filteredRecords = computed(() => transactionStore.filteredRecords);
const totalPages = computed(() => transactionStore.totalPages);
const currentPage = computed(() => transactionStore.currentPage);

// Dynamic search placeholder — shows DD/MM/YYYY when date column is selected
const searchPlaceholder = computed(() => {
  const placeholders = {
    invoice_number: t("common.Search by Invoice Number"),
    date: t("common.search_by_date"),
    user: t("common.Search by User"),
    total: t("common.Search by Total"),
  };
  return placeholders[filterColumn.value] || t("common.Search...");
});

const filteredTransactions = (status) => {
  return transactions.value.filter((t) => t.status === status);
};

const markPaid = (invoiceId) => {
  pendingMarkPaidId.value = invoiceId;
  showConfirmDialog.value = true;
};

const confirmMarkPaid = async () => {
  try {
    const transaction = transactions.value.find((t) => t.id === pendingMarkPaidId.value);
    if (transaction) {
      await transactionStore.markAsPaid(transaction.rawId);
    }
    showConfirmDialog.value = false;
    pendingMarkPaidId.value = null;
    showModal.value = false;
  } catch (error) {
    alert(t("common.Failed to mark transaction as paid. Please try again."));
  }
};

const cancelMarkPaid = () => {
  showConfirmDialog.value = false;
  pendingMarkPaidId.value = null;
};

const handleRowClick = async (transaction) => {
  try {
    const details = await transactionStore.fetchTransactionDetails(transaction.rawId);
    selectedTransaction.value = details;
    showModal.value = true;
  } catch (error) {
    console.error("Error fetching transaction details:", error);
    selectedTransaction.value = transaction;
    showModal.value = true;
  }
};

const closeModal = () => {
  showModal.value = false;
  selectedTransaction.value = null;
};

// Handle filter column change — clears search and re-fetches
const handleFilterColumnChange = (event) => {
  filterColumn.value = event.target.value;
  searchQuery.value = "";
  transactionStore.setFilterColumn(filterColumn.value);
  transactionStore.applySearch("");
};

// Debounced search input handler
let searchTimeout;
const handleSearchChange = (event) => {
  searchQuery.value = event.target.value;
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    transactionStore.applySearch(searchQuery.value);
  }, 500);
};

// Watch for rows per page changes
watch(rowsPerPage, async (newValue) => {
  await transactionStore.setRowsPerPage(newValue);
});

// Watch for tab changes
watch(activeTab, async (newValue) => {
  if (newValue === "unpaid") {
    await transactionStore.changeTabToUnpaid();
  } else {
    await transactionStore.changeTabToPaid();
  }
});

// Pagination handlers
const handleNextPage = async () => {
  await transactionStore.nextPage();
};

const handlePrevPage = async () => {
  await transactionStore.prevPage();
};

// Dynamic months — last two years up to current month
const months = computed(() => {
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonth = currentDate.getMonth();

  const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December",
  ];

  const allMonths = [];

  const year2 = currentYear - 2;
  for (let i = 0; i < 12; i++) {
    allMonths.push({
      value: `${monthNames[i].toLowerCase()}-${year2}`,
      label: `${monthNames[i]} ${year2}`,
    });
  }

  const year1 = currentYear - 1;
  for (let i = 0; i < 12; i++) {
    allMonths.push({
      value: `${monthNames[i].toLowerCase()}-${year1}`,
      label: `${monthNames[i]} ${year1}`,
    });
  }

  for (let i = 0; i <= currentMonth; i++) {
    allMonths.push({
      value: `${monthNames[i].toLowerCase()}-${currentYear}`,
      label: `${monthNames[i]} ${currentYear}`,
    });
  }

  if (!selectedMonth.value) {
    selectedMonth.value = `${monthNames[currentMonth].toLowerCase()}-${currentYear}`;
  }

  return allMonths;
});

// Watch for month changes and update date range in store
watch(selectedMonth, async (newValue) => {
  const [monthName, year] = newValue.split("-");
  await transactionStore.setDateRange(monthName, parseInt(year));
});

// Load initial data and setup WebSocket
onMounted(async () => {
  await transactionStore.initializeWebSocket();
  await transactionStore.changeTabToUnpaid();

  if (!selectedMonth.value) {
    const currentDate = new Date();
    const monthNames = [
      "January",
      "February",
      "March",
      "April",
      "May",
      "June",
      "July",
      "August",
      "September",
      "October",
      "November",
      "December",
    ];
    const currentMonthName = monthNames[currentDate.getMonth()];
    const currentYear = currentDate.getFullYear();
    selectedMonth.value = `${currentMonthName.toLowerCase()}-${currentYear}`;
    await transactionStore.setDateRange(currentMonthName.toLowerCase(), currentYear);
  }
});

// Cleanup WebSocket on component unmount
onBeforeUnmount(() => {
  transactionStore.disconnectWebSocket();
});

// Handle search triggered from mobile view
const handleMobileSearch = async (query, column) => {
  transactionStore.setFilterColumn(column);
  await transactionStore.applySearch(query);
};
</script>

<template>
  <div>
    <div class="container-fluid px-3 px-md-4 py-4">
      <!-- Header -->
      <div class="mb-4">
        <h2 class="fw-bold mb-1">{{ $t("transaction_page.Transactions") }}</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="sell" class="text-decoration-none">{{ $t("common.Home") }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ $t("transaction_page.Transactions") }}
            </li>
          </ol>
        </nav>
      </div>

      <!-- Main Content -->
      <div>
        <!-- Mobile: Search and Filter Section -->
        <div class="d-md-none mb-4">
          <!-- <TransactionsMobileView
            v-model:search-query="searchQuery"
            v-model:selected-month="selectedMonth"
            :total-sales="totalSales"
            :months="months"
            :loading="loading"
            :is-admin="isAdmin"
          /> -->

          <TransactionsMobileView
            v-model:search-query="searchQuery"
            v-model:selected-month="selectedMonth"
            :total-sales="totalSales"
            :months="months"
            :loading="loading"
            :is-admin="isAdmin"
            @search="handleMobileSearch"
          />
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'unpaid' }"
              @click="activeTab = 'unpaid'"
              type="button"
              role="tab"
            >
              {{ $t("common.Unpaid") }}
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'paid' }"
              @click="activeTab = 'paid'"
              type="button"
              role="tab"
            >
              {{ $t("common.Paid") }}
            </button>
          </li>
        </ul>

        <!-- Desktop: Search Bar Row -->
        <div class="d-none d-md-block mb-4">
          <div class="row g-3 align-items-center">
            <!-- Attached Search Input + Filter Column Dropdown -->
            <!-- Attached Search Input + Filter Column Dropdown -->
            <div class="col-12 col-lg-6">
              <div class="search-input-group">
                <span class="search-icon">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  type="text"
                  class="search-input"
                  :placeholder="searchPlaceholder"
                  :value="searchQuery"
                  @input="handleSearchChange"
                />
                <div class="filter-divider"></div>
                <select
                  class="filter-select"
                  :value="filterColumn"
                  @change="handleFilterColumnChange"
                >
                  <option value="invoice_number">
                    {{ $t("common.Invoice Number") }}
                  </option>
                  <option value="date">{{ $t("common.Date") }}</option>
                  <option value="user">{{ $t("common.User") }}</option>
                  <option value="total">{{ $t("common.Total") }}</option>
                </select>
              </div>
            </div>

            <div class="col"></div>

            <!-- Total Sales -->
            <div class="col-auto">
              <span class="text-muted small">{{ $t("common.Total Sales") }}:</span>
              <span class="fw-bold text-success ms-2 fs-6">
                {{ $formatCurrency(totalSales) }}
              </span>
            </div>

            <!-- Month Dropdown -->
            <div class="col-auto">
              <div class="d-flex align-items-center">
                <label class="col-form-label col-form-label-sm fw-semibold mb-0 me-2">
                  {{ $t("common.Month") }}:
                </label>
                <select
                  v-model="selectedMonth"
                  class="form-select form-select-sm borderless-select-tight"
                >
                  <option v-for="month in months" :key="month.value" :value="month.value">
                    {{ month.label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Generate Report -->
            <div v-if="isAdmin" class="col-auto">
              <a
                class="btn btn-primary btn-sm px-3 btn-match-search"
                href="generate-report"
                rel="noopener"
              >
                <i class="bi bi-file-earmark-text me-1"></i>
                {{ $t("common.Generate Report") }}
              </a>
            </div>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Unpaid Tab -->
          <div v-show="activeTab === 'unpaid'">
            <div class="d-none d-md-block">
              <TransactionTable
                :transactions="filteredTransactions('unpaid')"
                :show-mark-paid="true"
                :loading="loading"
                @mark-paid="markPaid"
                @row-click="handleRowClick"
                :is-admin="isAdmin"
              />
            </div>
            <div class="d-md-none">
              <TransactionCard
                v-for="transaction in filteredTransactions('unpaid')"
                :key="transaction.id"
                :transaction="transaction"
                :show-mark-paid="true"
                @mark-paid="markPaid"
                @card-click="handleRowClick"
                :is-admin="isAdmin"
              />
            </div>
            <div
              v-if="!loading && filteredTransactions('unpaid').length === 0"
              class="text-center py-5"
            >
              <i class="bi bi-inbox fs-1 text-muted"></i>
              <p class="text-muted mt-3">
                {{ $t("common.No unpaid transactions found") }}
              </p>
            </div>
          </div>

          <!-- Paid Tab -->
          <div v-show="activeTab === 'paid'">
            <div class="d-none d-md-block">
              <TransactionTable
                :transactions="filteredTransactions('paid')"
                :show-mark-paid="false"
                :loading="loading"
                @row-click="handleRowClick"
              />
            </div>
            <div class="d-md-none">
              <TransactionCard
                v-for="transaction in filteredTransactions('paid')"
                :key="transaction.id"
                :transaction="transaction"
                :show-mark-paid="false"
                @card-click="handleRowClick"
                :is-admin="isAdmin"
              />
            </div>
            <div
              v-if="!loading && filteredTransactions('paid').length === 0"
              class="text-center py-5"
            >
              <i class="bi bi-inbox fs-1 text-muted"></i>
              <p class="text-muted mt-3">{{ $t("common.No paid transactions found") }}</p>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="!loading && filteredRecords > 0"
          class="d-flex justify-content-end align-items-center mt-4 flex-wrap gap-4"
        >
          <div class="d-flex align-items-center gap-2">
            <span class="small fw-semibold text-nowrap">
              {{ $t("common.Rows per page") }}:
            </span>
            <select
              v-model="rowsPerPage"
              class="form-select form-select-custom borderless-select-tight"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="small text-nowrap">
              {{ startRecord + 1 }}-{{ endRecord }} of {{ filteredRecords }}
            </span>
            <button
              class="btn btn-sm btn-outline-secondary"
              @click="handlePrevPage"
              :disabled="currentPage === 1 || loading"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              class="btn btn-sm btn-outline-secondary"
              @click="handleNextPage"
              :disabled="currentPage >= totalPages || loading"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Modals -->
      <TransactionDetailModal
        :show="showModal"
        :transaction="selectedTransaction"
        :show-mark-paid="true"
        @close="closeModal"
        @mark-paid="markPaid"
      />
      <ConfirmDialog
        :show="showConfirmDialog"
        title="Confirm Payment"
        message="Are you sure you want to mark this transaction as paid?"
        @confirm="confirmMarkPaid"
        @cancel="cancelMarkPaid"
      />
    </div>

    <Footer />
  </div>
</template>

<style scoped>
.nav-tabs .nav-link {
  color: #6c757d;
  border: none;
  border-bottom: 2px solid transparent;
  padding: 0.75rem 1.5rem;
}

.nav-tabs .nav-link.active {
  color: #0d6efd;
  border-bottom-color: #0d6efd;
  background-color: transparent;
}

.nav-tabs {
  border-bottom: 1px solid #dee2e6;
}

/* Attached search + filter dropdown */
.input-group-filter-select {
  border: 1px solid #dee2e6;
  border-left: 1px solid #dee2e6;
  border-radius: 0 0.375rem 0.375rem 0;
  background-color: #f8f9fa;
  padding: 0.375rem 2rem 0.375rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 12px 12px;
  min-width: 145px;
}

.input-group-filter-select:focus {
  outline: none;
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  z-index: 3;
}

.input-group .form-control:focus {
  z-index: 3;
}

.btn-match-search {
  height: 38px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.borderless-select-tight {
  border: none;
  background-color: transparent;
  box-shadow: none;
  padding-left: 0;
  padding-right: 1.1rem;
  cursor: pointer;
  font-weight: 500;
  min-width: auto;
  background-position: right 0.1rem center;
  background-size: 10px 10px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

.borderless-select-tight:focus {
  border: none;
  box-shadow: none;
  background-color: transparent;
  outline: none;
}

.borderless-select-tight option {
  background-color: white;
  padding: 0.5rem;
}

.form-select-custom {
  height: 36px;
  min-width: 70px;
  font-size: 0.9rem;
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
  padding-left: 0.25rem;
  padding-right: 1.75rem;
  background-position: right 0.35rem center;
  background-size: 12px 12px;
  line-height: 1.3;
}

.form-select-custom:focus {
  box-shadow: none;
  border: none;
}

.text-nowrap {
  white-space: nowrap;
}

/* Unified search group */
.search-input-group {
  display: flex;
  align-items: center;
  border: 1.5px solid #dee2e6;
  border-radius: 0.5rem;
  background-color: #fff;
  overflow: hidden;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.search-input-group:focus-within {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.2);
}

.search-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.65rem 0 0.85rem;
  color: #6c757d;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.search-input {
  flex: 1 1 auto;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  padding: 0.45rem 0.5rem 0.45rem 0;
  font-size: 0.875rem;
  color: #212529;
  width: 100%;
}

.search-input::placeholder {
  color: #adb5bd;
}

.filter-divider {
  width: 1px;
  height: 22px;
  background-color: #dee2e6;
  flex-shrink: 0;
}

.filter-select {
  border: none;
  outline: none;
  background-color: #f8f9fa;
  padding: 0.45rem 2rem 0.45rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  flex-shrink: 0;
  min-width: 120px;
  max-width: 155px;
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 11px 11px;
}

.filter-select:focus {
  outline: none;
  box-shadow: none;
}

.filter-select option {
  background-color: #fff;
  font-size: 0.875rem;
}
</style>
