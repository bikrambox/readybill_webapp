<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";
import { useTransactionStore } from "@/modules/GroceryGermany/stores/transactionStore";
import TransactionCard from "../components/transactions/TransactionCard.vue";
import TransactionTable from "../components/transactions/TransactionTable.vue";
import TransactionDetailModal from "../components/transactions/TransactionDetailModal.vue";
import ConfirmDialog from "../components/transactions/ConfirmDialog.vue";
import TransactionsMobileView from "../components/transactions/TransactionsMobileView.vue";
import Footer from "@/modules/GroceryGermany/components/Footer.vue";
import { useAuthStore } from "@/modules/Authentication/stores/authStore";

const transactionStore = useTransactionStore();

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.isAdmin === 1);


const activeTab = ref("unpaid");
const searchQuery = ref("");
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

const filteredTransactions = (status) => {
  return transactions.value.filter((t) => t.status === status);
};

const markPaid = (invoiceId) => {
  pendingMarkPaidId.value = invoiceId;
  showConfirmDialog.value = true;
};

const confirmMarkPaid = async () => {
  try {
    const transaction = transactions.value.find(
      (t) => t.id === pendingMarkPaidId.value
    );

    if (transaction) {
      await transactionStore.markAsPaid(transaction.rawId);
    }

    showConfirmDialog.value = false;
    pendingMarkPaidId.value = null;
    showModal.value = false;
  } catch (error) {
    console.error("Error marking transaction as paid:", error);
    alert("Failed to mark transaction as paid. Please try again.");
  }
};

const cancelMarkPaid = () => {
  showConfirmDialog.value = false;
  pendingMarkPaidId.value = null;
};

const handleRowClick = async (transaction) => {
  try {
    const details = await transactionStore.fetchTransactionDetails(
      transaction.rawId
    );
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

// Watch for search query changes with debounce
let searchTimeout;
watch(searchQuery, (newValue) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    transactionStore.applySearch(newValue);
  }, 500);
});

// Watch for rows per page changes - dynamically fetch new data
watch(rowsPerPage, async (newValue) => {
  await transactionStore.setRowsPerPage(newValue);
});

// Watch for month changes
watch(selectedMonth, (newValue) => {
  transactionStore.selectedMonth = newValue;
});

// Watch for tab changes - fetch new data and update total sales
watch(activeTab, async (newValue) => {
  if (newValue === "unpaid") {
    await transactionStore.changeTabToUnpaid();
  } else {
    await transactionStore.changeTabToPaid();
  }
});

// Pagination handlers - dynamic
const handleNextPage = async () => {
  await transactionStore.nextPage();
};

const handlePrevPage = async () => {
  await transactionStore.prevPage();
};

// Dynamic months - LAST TWO YEARS starting from current month
const months = computed(() => {
  const currentDate = new Date();
  const currentYear = currentDate.getFullYear();
  const currentMonth = currentDate.getMonth(); // 0-11 (January = 0)
  
  const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const allMonths = [];

  // Year 2: Two years ago - ALL 12 months
  const year2 = currentYear - 2;
  for (let i = 0; i < 12; i++) {
    allMonths.push({
      value: `${monthNames[i].toLowerCase()}-${year2}`,
      label: `${monthNames[i]} ${year2}`
    });
  }

  // Year 1: Last year - ALL 12 months  
  const year1 = currentYear - 1;
  for (let i = 0; i < 12; i++) {
    allMonths.push({
      value: `${monthNames[i].toLowerCase()}-${year1}`,
      label: `${monthNames[i]} ${year1}`
    });
  }

  // Current Year: ONLY up to current month
  for (let i = 0; i <= currentMonth; i++) {
    allMonths.push({
      value: `${monthNames[i].toLowerCase()}-${currentYear}`,
      label: `${monthNames[i]} ${currentYear}`
    });
  }

  // Auto-select current month
  if (!selectedMonth.value) {
    selectedMonth.value = `${monthNames[currentMonth].toLowerCase()}-${currentYear}`;
  }

  return allMonths;
});

// Watch for month changes
watch(selectedMonth, async (newValue) => {
  const [monthName, year] = newValue.split('-');
  await transactionStore.setDateRange(monthName, parseInt(year));
});

// Load initial data and setup WebSocket
onMounted(async () => {
  // Initialize WebSocket connection
  await transactionStore.initializeWebSocket();
  
  // Load initial transactions (unpaid)
  await transactionStore.changeTabToUnpaid();

  // Set initial month if not set
  if (!selectedMonth.value) {
    const currentDate = new Date();
    const monthNames = [
      "January", "February", "March", "April", "May", "June",
      "July", "August", "September", "October", "November", "December"
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
</script>

<template>
  <div>
    <div class="container-fluid px-3 px-md-4 py-4">
      <!-- Header -->
      <div class="mb-4">
        <h2 class="fw-bold mb-1">Transactions</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="#" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              Transactions
            </li>
          </ol>
        </nav>
      </div>

      <!-- Main Content - Always Visible -->
      <div>
        <!-- Mobile: Search and Filter Section -->
        <div class="d-md-none mb-4">
          <TransactionsMobileView
            v-model:search-query="searchQuery"
            v-model:selected-month="selectedMonth"
            :total-sales="totalSales"
            :months="months"
            :loading="loading"
            :is-admin="isAdmin"
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
              Unpaid
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
              Paid
            </button>
          </li>
        </ul>

        <!-- Desktop: Search Bar Row -->
        <div class="d-none d-md-block mb-4">
          <div class="row g-3 align-items-center">
            <!-- Search Bar -->
            <div class="col-auto" style="min-width: 280px; max-width: 350px">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  type="text"
                  class="form-control border-start-0 ps-0"
                  placeholder="Search..."
                  v-model="searchQuery"
                />
              </div>
            </div>

            <div class="col"></div>

            <!-- Total Sales -->
            <div class="col-auto">
              <span class="text-muted small">Total Sales:</span>
              <span class="fw-bold text-success ms-2 fs-6"
                >₹{{ totalSales.toFixed(2) }}</span
              >
            </div>

            <!-- Month Dropdown -->
            <div class="col-auto">
              <div class="d-flex align-items-center">
                <label
                  class="col-form-label col-form-label-sm fw-semibold mb-0 me-2"
                  >Month:</label
                >
                <select
                  v-model="selectedMonth"
                  class="form-select form-select-sm borderless-select-tight"
                >
                  <option
                    v-for="month in months"
                    :key="month.value"
                    :value="month.value"
                  >
                    {{ month.label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Generate Report -->
            <div class="col-auto">
              <a
                class="btn btn-primary btn-sm px-3 btn-match-search"
                href="generate-report"
                rel="noopener"
              >
                <i class="bi bi-file-earmark-text me-1"></i>
                Generate Report
              </a>
            </div>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Unpaid Tab -->
          <div v-show="activeTab === 'unpaid'">
            <!-- Desktop Table -->
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

            <!-- Mobile Cards -->
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

            <!-- Empty State (only when not loading and no data) -->
            <div
              v-if="!loading && filteredTransactions('unpaid').length === 0"
              class="text-center py-5"
            >
              <i class="bi bi-inbox fs-1 text-muted"></i>
              <p class="text-muted mt-3">No unpaid transactions found</p>
            </div>
          </div>

          <!-- Paid Tab -->
          <div v-show="activeTab === 'paid'">
            <!-- Desktop Table -->
            <div class="d-none d-md-block">
              <TransactionTable
                :transactions="filteredTransactions('paid')"
                :show-mark-paid="false"
                :loading="loading"
                @row-click="handleRowClick"
              />
            </div>

            <!-- Mobile Cards -->
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

            <!-- Empty State (only when not loading and no data) -->
            <div
              v-if="!loading && filteredTransactions('paid').length === 0"
              class="text-center py-5"
            >
              <i class="bi bi-inbox fs-1 text-muted"></i>
              <p class="text-muted mt-3">No paid transactions found</p>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="!loading && filteredRecords > 0"
          class="d-flex justify-content-end align-items-center mt-4 flex-wrap gap-4"
        >
          <!-- Rows per page -->
          <div class="d-flex align-items-center gap-2">
            <span class="small fw-semibold text-nowrap">Rows per page:</span>
            <select
              v-model="rowsPerPage"
              class="form-select form-select-custom borderless-select-tight"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>

          <!-- Page info and navigation -->
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

.btn-match-search {
  height: 31px;
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
</style>
