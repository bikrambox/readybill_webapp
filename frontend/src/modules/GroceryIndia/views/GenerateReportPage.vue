<template>
  <div class="generate-report-page">
    <div class="container-fluid px-3 px-lg-4 py-3">
      <PageHeader :title="$t('common.Generate Report')" :breadcrumbs="breadcrumbs" />

      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <DateRangeFilter
                v-model:dateFrom="dateFrom"
                v-model:dateTo="dateTo"
                @date-range-changed="handleDateRangeChange"
              />

              <ReportButtons
                :disabled="!isDateRangeValid"
                @request-report="handleRequestReport"
              />

              <!-- Error Box -->
              <FormErrorBox
                v-if="errorMessages.length > 0"
                title="Subscription Alert"
                :messages="errorMessages"
                @clear="clearErrors"
                class="mb-4"
              />

              <ReportsTable
                ref="reportsTableRef"
                :date-from="dateFrom"
                :date-to="dateTo"
              />

              <hr class="divider-line" />

              <TransactionFilters
                v-model:pageSize="transactionPageSize"
                v-model:searchQuery="transactionSearch"
                v-model:filterColumn="transactionFilterColumn"
                :loading="reportStore.loading"
                @search="handleTransactionSearch"
              />

              <TransactionsTable
                ref="transactionsTableRef"
                :date-from="dateFrom"
                :date-to="dateTo"
                :search-query="transactionSearch"
                :filter-column="transactionFilterColumn"
                :page-size="transactionPageSize"
                @row-click="handleRowClick"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <TransactionDetailModal
      :show="showModal"
      :transaction="selectedTransaction"
      :show-mark-paid="false"
      @close="closeModal"
      @mark-paid="markPaid"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import PageHeader from "../components/report/PageHeader.vue";
import DateRangeFilter from "../components/report/DateRangeFilter-new.vue";
import ReportButtons from "../components/report/ReportButtons.vue";
import ReportsTable from "../components/report/ReportsTable.vue";
import TransactionFilters from "../components/report/TransactionFilters.vue";
import TransactionsTable from "../components/report/TransactionsTable.vue";
import { useReportStore } from "@/modules/GroceryIndia/stores/report.js";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import TransactionDetailModal from "../components/transactions/TransactionDetailModal.vue";
import { useTransactionStore } from "@/modules/GroceryIndia/stores/transactionStore";

// ─── Store instances ─────────────────────────────────────────────────────────
const reportStore = useReportStore();
const transactionStore = useTransactionStore();

// ─── Refs ────────────────────────────────────────────────────────────────────
const breadcrumbs = [
  { label: "Home", href: "/home" },
  { label: "Generate Report", active: true },
];

const dateFrom = ref("");
const dateTo = ref("");
const transactionPageSize = ref(10);
const transactionSearch = ref("");
const transactionFilterColumn = ref("invoice_number");
const selectedTransaction = ref(null);
const showModal = ref(false);

const reportsTableRef = ref(null);
const transactionsTableRef = ref(null);

// ─── Computed ─────────────────────────────────────────────────────────────────
const isDateRangeValid = computed(() => {
  return dateFrom.value && dateTo.value;
});

// Declared before watch/onMounted to avoid temporal dead zone crash
const errorMessages = computed(() => {
  if (!reportStore.error) return [];
  const errors = [];
  if (reportStore.error.message) {
    errors.push(reportStore.error.message);
  }
  return errors;
});

// ─── Helpers ──────────────────────────────────────────────────────────────────
// Declared before watch/onMounted to avoid temporal dead zone crash
const clearErrors = () => {
  reportStore.clearError();
};

const showToast = (type, message) => {
  console.log(`${type}: ${message}`);
};

const formatDateForAPI = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
};

const loadReportsOnce = async () => {
  try {
    await reportStore.fetchReports();
  } catch (error) {
    console.error("Error loading reports:", error);
  }
};

// ─── Event Handlers ───────────────────────────────────────────────────────────
const handleDateRangeChange = async () => {
  clearErrors();
  if (transactionsTableRef.value) {
    await transactionsTableRef.value.refreshTable();
  }
};

const handleRequestReport = async (reportType) => {
  if (!isDateRangeValid.value) {
    showToast("error", "Please select both From Date and To Date");
    return;
  }
  try {
    await reportStore.requestReport({
      date_from: formatDateForAPI(dateFrom.value),
      date_to: formatDateForAPI(dateTo.value),
      report_type: reportType,
    });
    showToast("success", "Report requested successfully");
    if (reportsTableRef.value) {
      await reportsTableRef.value.refreshTable();
    }
  } catch (error) {
    showToast("error", error.response?.data?.message || "Request failed");
  }
};

// Updates refs then calls refreshTable — avoids the .search() is not a function error
const handleTransactionSearch = async (query, column) => {
  transactionSearch.value = query;
  transactionFilterColumn.value = column;
  if (transactionsTableRef.value) {
    await transactionsTableRef.value.refreshTable();
  }
};

const handleRowClick = async (transactionId) => {
  try {
    const details = await transactionStore.fetchTransactionDetails(transactionId);
    selectedTransaction.value = details;
    showModal.value = true;
  } catch (error) {
    console.error("Error fetching transaction details:", error);
    showModal.value = true;
  }
};

const markPaid = () => {
  // Mark paid not applicable in report view
};

const closeModal = () => {
  showModal.value = false;
  selectedTransaction.value = null;
};

// ─── Watchers ─────────────────────────────────────────────────────────────────
watch([() => dateFrom.value, () => dateTo.value], () => {
  clearErrors();
});

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  await loadReportsOnce();
});
</script>

<style scoped>
.generate-report-page {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.divider-line {
  margin: 2rem 0;
  border: 0;
  border-top: 3px double #000;
}
</style>
