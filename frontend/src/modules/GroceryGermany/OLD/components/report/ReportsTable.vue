<template>
  <div class="reports-section mb-4">
    <h5 class="mb-3">Generated Reports</h5>

    <div
      v-if="reportStore.loading && reportStore.reports.length === 0"
      class="text-center py-3"
    >
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="reportStore.reports.length === 0" class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>
      No reports generated yet. Click on the buttons above to request a report.
    </div>

    <div v-else class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead class="table-light">
          <tr>
            <th>Report Type</th>
            <th>Date Range</th>
            <th>Status</th>
            <th>Requested At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="report in reportStore.reports" :key="report.id">
            <td>
              <i :class="getReportIcon(report.report_type)" class="me-2"></i>
              {{ report.report_type?.toUpperCase() }}
            </td>
            <td>{{ report.date_range }}</td>
            <td>
              <span :class="getStatusBadge(report.status)">
                {{ report.statusMessage || getStatusText(report.status) }}
              </span>
            </td>
            <td>{{ report.created_at }}</td>
            <td>
              <button
                v-if="report.status === 2"
                class="btn btn-sm btn-success me-1"
                @click="handleDownload(report.id)"
                title="Download Report"
              >
                <i class="bi bi-download"></i>
              </button>

              <button
                v-if="report.status === 3"
                class="btn btn-sm btn-warning"
                @click="handleRetry(report.id)"
                title="Retry Generation"
              >
                <i class="bi bi-arrow-clockwise"></i>
              </button>

              <span v-if="report.status === 'processing'" class="text-muted">
                <i class="bi bi-hourglass-split"></i> Processing...
              </span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Info -->
      <div v-if="reportStore.reports.length > 0" class="text-muted small mt-2">
        Showing {{ reportStore.reports.length }} of
        {{ reportStore.totalReports }} reports
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { useReportStore } from "@/modules/GroceryGermany/stores/report.js";

const props = defineProps({
  dateFrom: String,
  dateTo: String,
});


const getStatusText = (status) => {
  const statusNum = typeof status === 'number' ? status : parseInt(status) || 0
  const statusMap = {
    0: 'Failed',
    1: 'Processing',
    2: 'Ready for download'
  }
  return statusMap[statusNum] || 'Unknown'
}
const emit = defineEmits(["download-report", "retry-report"]);

const reportStore = useReportStore();
const currentPage = ref(0);
const pageSize = ref(10);

const formatDateForAPI = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-GB");
};

const fetchReports = async () => {
  try {
    await reportStore.fetchReports({
      start: currentPage.value,
      length: pageSize.value,
      date_from: formatDateForAPI(props.dateFrom),
      date_to: formatDateForAPI(props.dateTo),
    });
  } catch (error) {
    console.error("Error fetching reports:", error);
  }
};

const handleDownload = (reportId) => {
  emit("download-report", reportId);
};

const handleRetry = (reportId) => {
  emit("retry-report", reportId);
};

const getReportIcon = (type) => {
  // Also make report_type safe
  const typeStr = String(type || '').toLowerCase()
  const icons = {
    'pdf': 'bi bi-file-pdf text-danger',
    'excel': 'bi bi-file-excel text-success',
    'csv': 'bi bi-file-text text-secondary'
  }
  return icons[typeStr] || 'bi bi-file'
}

const getStatusBadge = (status) => {
  // Convert status to string first, or use number mapping
  const statusNum = typeof status === 'number' ? status : parseInt(status) || 0
  const badges = {
    0: 'badge bg-danger',      // Failed
    1: 'badge bg-warning text-dark',  // Processing
    2: 'badge bg-success'      // Ready for download
  }
  return badges[statusNum] || 'badge bg-secondary'
}

const formatDateTime = (datetime) => {
  if (!datetime) return "";
  return new Date(datetime).toLocaleString("en-IN", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const formatDateRange = (fromDate, toDate) => {
  if (!fromDate || !toDate) return "N/A";
  return `${fromDate} - ${toDate}`;
};

const refreshTable = async () => {
  currentPage.value = 0;
  await fetchReports();
};

watch([() => props.dateFrom, () => props.dateTo], () => {
  refreshTable();
});

onMounted(() => {
  fetchReports();
});

defineExpose({
  refreshTable,
});
</script>

<style scoped>
.reports-section {
  background-color: #f8f9fa;
  padding: 1rem;
  border-radius: 0.375rem;
}

.table {
  font-size: 14px;
  margin-bottom: 0;
}
</style>
