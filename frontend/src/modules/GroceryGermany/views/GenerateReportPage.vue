<template>
  <div class="generate-report-page">
    <div class="container-fluid px-3 px-lg-4 py-3">
      <PageHeader title="Generate Report" :breadcrumbs="breadcrumbs" />

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

              <!-- ✅ Add FormErrorBox -->
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

              <!-- ✅ Updated TransactionFilters with v-model -->
              <TransactionFilters
                v-model:pageSize="transactionPageSize"
                v-model:searchQuery="transactionSearch"
                :loading="reportStore.loading"
              />

              <TransactionsTable
                ref="transactionsTableRef"
                :date-from="dateFrom"
                :date-to="dateTo"
                :search-query="transactionSearch"
                :page-size="transactionPageSize"
                @row-click="handleTransactionClick"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <TransactionModal
      ref="transactionModalRef"
      :transaction-id="selectedTransactionId"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import PageHeader from "../components/report/PageHeader.vue";
import DateRangeFilter from "../components/report/DateRangeFilter.vue";
import ReportButtons from "../components/report/ReportButtons.vue";
import ReportsTable from "../components/report/ReportsTable.vue";
import TransactionFilters from "../components/report/TransactionFilters.vue";
import TransactionsTable from "../components/report/TransactionsTable.vue";
import TransactionModal from "../components/report/TransactionModal.vue";
import { useReportStore } from "@/modules/GroceryGermany/stores/report.js";
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

const breadcrumbs = [
  { label: "Home", href: "/home" },
  { label: "Generate Report", active: true },
];

const dateFrom = ref("");
const dateTo = ref("");
const transactionPageSize = ref(10);
const transactionSearch = ref("");
const selectedTransactionId = ref(null);

const reportsTableRef = ref(null);
const transactionsTableRef = ref(null);
const transactionModalRef = ref(null);

const isDateRangeValid = computed(() => {
  return dateFrom.value && dateTo.value;
});

const reportStore = useReportStore();

// const handleDateRangeChange = async () => {
//   console.log("📅 Date range changed:", dateFrom.value, "to", dateTo.value);

//   if (transactionsTableRef.value) {
//     console.log("🔄 Refreshing transactions table...");
//     await transactionsTableRef.value.refreshTable();
//   }
// };

// Update handleDateRangeChange - add clearErrors()
const handleDateRangeChange = async () => {
  console.log('📅 Date range changed:', dateFrom.value, 'to', dateTo.value);
  
  clearErrors(); // ✅ Add this line
  
  if (transactionsTableRef.value) {
    console.log('🔄 Refreshing transactions table...');
    await transactionsTableRef.value.refreshTable();
  }
};

const formatDateForAPI = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, "0");
  const month = (date.getMonth() + 1).toString().padStart(2, "0");
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
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
      reportsTableRef.value.refreshTable();
    }
  } catch (error) {
    showToast("error", error.response?.data?.message || "Request failed");
  }
};

const handleTransactionClick = (transactionId) => {
  selectedTransactionId.value = transactionId;
};

const showToast = (type, message) => {
  console.log(`${type}: ${message}`);
};

const loadReportsOnce = async () => {
  try {
    await reportStore.fetchReports();
    if (reportStore.reports.length > 0 && reportStore.reports[0]?.parameters) {
      const params = reportStore.reports[0].parameters;
      const fromDate = new Date(params.date_from.split("/").reverse().join("-"))
        .toISOString()
        .split("T")[0];
      const toDate = new Date(params.date_to.split("/").reverse().join("-"))
        .toISOString()
        .split("T")[0];

      dateFrom.value = fromDate;
      dateTo.value = toDate;
    }
  } catch (error) {
    console.error("Error loading reports:", error);
  }
};


// Add watch before onMounted
watch([() => dateFrom.value, () => dateTo.value], () => {
  clearErrors();
});

onMounted(async () => {
  await loadReportsOnce();
});


// Add computed property after reportStore
const errorMessages = computed(() => {
  if (!reportStore.error) return [];
  
  const errors = [];
  
  if (reportStore.error.message) {
    errors.push(reportStore.error.message);
  }
  
  // if (reportStore.error.data?.subscription_type) {
  //   errors.push(`Current plan: ${reportStore.error.data.subscription_type}`);
  // }
  
  return errors;
});

// Add function after showToast
const clearErrors = () => {
  reportStore.clearError();
};

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
