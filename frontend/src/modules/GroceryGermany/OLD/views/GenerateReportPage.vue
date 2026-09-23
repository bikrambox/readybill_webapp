<template>
  <div class="generate-report-page">
    <div class="container-fluid px-3 px-lg-4 py-3">
      <!-- Page Header -->
      <PageHeader 
        title="Generate Report"
        :breadcrumbs="breadcrumbs"
      />

      <!-- Alert Component (if needed) -->
      <div class="row mb-3" v-if="showAlert">
        <div class="col-xl-8">
          <AlertMessage 
            :message="alertMessage"
            :type="alertType"
          />
        </div>
      </div>

      <!-- Main Card -->
      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <!-- Date Range Filter -->
              <DateRangeFilter
                :dateFrom="dateFrom"
                :dateTo="dateTo"
                @date-range-changed="handleDateRangeChange"
              />

              <!-- Report Request Buttons -->
              <ReportButtons
                :disabled="!isDateRangeValid"
                @request-report="handleRequestReport"
              />

              <!-- Reports Table -->
              <ReportsTable
                ref="reportsTableRef"
                :date-from="dateFrom"
                :date-to="dateTo"
                @download-report="handleDownloadReport"
                @retry-report="handleRetryReport"
              />

              <hr class="divider-line" />

              <!-- Transaction Filters -->
              <TransactionFilters
                :pageSize="transactionPageSize"
                :searchQuery="transactionSearch"
              />

              <!-- Transactions Table -->
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

    <!-- Transaction Details Modal -->
    <TransactionModal
      ref="transactionModalRef"
      :transaction-id="selectedTransactionId"
      @save="handleTransactionUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch  } from 'vue';
import PageHeader from '../components/report/PageHeader.vue';
import AlertMessage from '../components/report/AlertMessage.vue';
import DateRangeFilter from '../components/report/DateRangeFilter.vue';
import ReportButtons from '../components/report/ReportButtons.vue';
import ReportsTable from '../components/report/ReportsTable.vue';
import TransactionFilters from '../components/report/TransactionFilters.vue';
import TransactionsTable from '../components/report/TransactionsTable.vue';
import TransactionModal from '../components/report/TransactionModal.vue';
import { useReportStore } from '@/modules/GroceryGermany/stores/report.js'

const breadcrumbs = [
  { label: 'Home', href: '/home' },
  { label: 'Generate Report', active: true }
];

const showAlert = ref(false);
const alertMessage = ref('');
const alertType = ref('info');

const dateFrom = ref('');
const dateTo = ref('');
const transactionPageSize = ref(100);
const transactionSearch = ref('');
const selectedTransactionId = ref(null);

const reportsTableRef = ref(null);
const transactionsTableRef = ref(null);
const transactionModalRef = ref(null);

const isDateRangeValid = computed(() => {
  return dateFrom.value && dateTo.value;
});


const reportStore = useReportStore()

// const handleDateRangeChange = () => {
//   if (transactionsTableRef.value) {
//     transactionsTableRef.value.refreshTable();
//   }
// };

const loadReportsOnce = async () => {
  try {
    await reportStore.fetchReports()
    // Auto-set dates from first report if available
    if (reportStore.reports.length > 0 && reportStore.reports[0]?.parameters) {
      const params = reportStore.reports[0].parameters
      const fromDate = new Date(params.date_from.split('/').reverse().join('-')).toISOString().split('T')[0]
      const toDate = new Date(params.date_to.split('/').reverse().join('-')).toISOString().split('T')[0]
      
      dateFrom.value = fromDate
      dateTo.value = toDate
    }
  } catch (error) {
    console.error('Error loading reports:', error)
  }
}

const handleRequestReport = async (reportType) => {
  if (!isDateRangeValid.value) {
    showToast('error', 'Please select both From Date and To Date')
    return
  }

  try {
    await reportStore.requestReport({
      date_from: formatDateForAPI(dateFrom.value),
      date_to: formatDateForAPI(dateTo.value),
      report_type: reportType
    })
    
    showToast('success', 'Report requested successfully')
    // Refresh reports after requesting new one
    if (reportsTableRef.value) {
      reportsTableRef.value.refreshTable()
    }
  } catch (error) {
    showToast('error', error.response?.data?.message || 'Request failed')
  }
}

const handleDownloadReport = async (reportId) => {
  try {
    // Download logic here
    console.log('Downloading report:', reportId);
  } catch (error) {
    showToast('error', 'Download failed');
  }
};

const handleRetryReport = async (reportId) => {
  try {
    // Retry logic here
    console.log('Retrying report:', reportId);
    if (reportsTableRef.value) {
      reportsTableRef.value.refreshTable();
    }
  } catch (error) {
    showToast('error', 'Retry failed');
  }
};

const handleTransactionClick = (transactionId) => {
  selectedTransactionId.value = transactionId;
  if (transactionModalRef.value) {
    transactionModalRef.value.show();
  }
};

const handleTransactionUpdate = () => {
  if (transactionsTableRef.value) {
    transactionsTableRef.value.refreshTable();
  }
  showToast('success', 'Transaction updated successfully');
};

const showToast = (type, message) => {
  // Implement toast notification
  console.log(`${type}: ${message}`);
};

// Placeholder API function
const requestReportAPI = async (params) => {
  // Replace with actual API call
  return { message: 'Report requested successfully' };
};


// const autoSetDatesFromReports = async () => {
//   try {
//     await reportStore.fetchReports()
//     if (reportStore.reports.length > 0 && reportStore.reports[0]?.parameters) {
//       const params = reportStore.reports[0].parameters
//       const fromDate = new Date(params.date_from.split('/').reverse().join('-')).toISOString().split('T')[0]
//       const toDate = new Date(params.date_to.split('/').reverse().join('-')).toISOString().split('T')[0]
      
//       dateFrom.value = fromDate
//       dateTo.value = toDate
//       emitDateRangeChange()
//     }
//   } catch (error) {
//     console.error('Error auto-setting dates:', error)
//   }
// }

const emitDateRangeChange = async () => {
  if (transactionsTableRef.value) {
    await transactionsTableRef.value.refreshTable()
  }
  if (reportsTableRef.value) {
    await reportsTableRef.value.refreshTable()
  }
}

onMounted(async () => {
  // Load reports first and auto-set dates if available
  await loadReportsOnce()
})

// Watch reports data to auto-update dates
watch(() => reportStore.reports, (newReports) => {
  if (newReports.length > 0 && (!dateFrom.value || !dateTo.value)) {
    // autoSetDatesFromReports()
  }
}, { immediate: true })

// Update handleDateRangeChange
const handleDateRangeChange = async () => {
  // ONLY refresh transactions table on date change
  if (transactionsTableRef.value) {
    await transactionsTableRef.value.refreshTable()
  }
  // DO NOT refresh reports table here
}


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