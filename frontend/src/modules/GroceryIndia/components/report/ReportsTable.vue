<template>
  <div class="reports-section mb-4">
    <h5 class="mb-3">Generated Reports</h5>

    <div
      v-if="reportStore.loadingReports && reportStore.reports.length === 0"
      class="text-center py-3"
    >
      <div class="spinner-border spinner-border-sm text-primary" role="status">
        <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
      </div>
    </div>

    <div v-else-if="reportStore.reports.length === 0" class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i>
      {{ $t("common.no_report_generated") }}
    </div>

    <!-- Error Alert -->
    <div
      v-if="errorMessage"
      class="alert alert-danger alert-dismissible fade show mt-3"
      role="alert"
    >
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      {{ errorMessage }}
      <button type="button" class="btn-close" @click="errorMessage = ''"></button>
    </div>

    <!-- Success Alert -->
    <div
      v-if="successMessage"
      class="alert alert-success alert-dismissible fade show mt-3"
      role="alert"
    >
      <i class="bi bi-check-circle-fill me-2"></i>
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''"></button>
    </div>

    <div v-if="reportStore.reports.length > 0" class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead class="table-light">
          <tr>
            <th>{{ $t("common.Report Type") }}</th>
            <th>{{ $t("common.Date Range") }}</th>
            <th>{{ $t("common.Status") }}</th>
            <th>{{ $t("common.Requested At") }}</th>
            <th>{{ $t("common.Actions") }}</th>
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
              <!-- Status 0: Queue -->
              <span v-if="report.status === 0" class="text-muted small">
                <i class="bi bi-clock-history"></i> {{ $t("common.Queued") }}
              </span>

              <!-- Status 1: Processing -->
              <span v-if="report.status === 1" class="text-primary small">
                <div class="spinner-border spinner-border-sm me-1" role="status">
                  <span class="visually-hidden">{{ $t("common.Processing") }}...</span>
                </div>
                {{ $t("common.Processing") }}...
              </span>

              <!-- Status 2: Ready for Download -->
              <button
                v-if="report.status === 2"
                class="btn btn-sm btn-primary me-1"
                @click="handleDownload(report.id)"
                :disabled="downloading === report.id"
                title="View/Download Report"
              >
                <span
                  v-if="downloading === report.id"
                  class="spinner-border spinner-border-sm me-1"
                  role="status"
                ></span>
                <i v-else class="bi bi-box-arrow-up-right"></i>
                {{
                  downloading === report.id
                    ? $t("common.Opening") + "..."
                    : $t("common.View")
                }}
              </button>

              <!-- Status 3: Failed - Show Retry -->
              <button
                v-if="report.status === 3"
                class="btn btn-sm btn-warning"
                @click="handleRetry(report.id)"
                :disabled="retrying === report.id"
                title="Retry Generation"
              >
                <span
                  v-if="retrying === report.id"
                  class="spinner-border spinner-border-sm me-1"
                  role="status"
                ></span>
                <i v-else class="bi bi-arrow-clockwise"></i>
                {{ retrying === report.id ? "Retrying..." : "Retry" }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination Info -->
      <div v-if="reportStore.reports.length > 0" class="text-muted small mt-2">
        Showing {{ reportStore.reports.length }} of {{ reportStore.totalReports }} reports
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { useReportStore } from "@/modules/GroceryIndia/stores/report.js";

const props = defineProps({
  dateFrom: String,
  dateTo: String,
});

const reportStore = useReportStore();
const currentPage = ref(0);
const pageSize = ref(10);
const downloading = ref(null);
const retrying = ref(null);
const errorMessage = ref("");
const successMessage = ref("");
const autoRefreshInterval = ref(null);

// ✅ UPDATED: Status text mapping
const getStatusText = (status) => {
  const statusNum = typeof status === "number" ? status : parseInt(status) || 0;
  const statusMap = {
    0: "Queue",
    1: "Processing",
    2: "Ready for download",
    3: "Failed",
  };
  return statusMap[statusNum] || "Unknown";
};

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
    errorMessage.value = "Failed to load reports. Please try again.";
  }
};

// ✅ Download Handler (opens in new tab)
const handleDownload = async (reportId) => {
  downloading.value = reportId;
  errorMessage.value = "";
  successMessage.value = "";

  try {
    console.log("📥 Opening report:", reportId);

    const result = await reportStore.downloadReport(reportId);

    successMessage.value = `Report opened in new tab! You can download it from there.`;
    console.log("✅ Report opened:", result);

    // Auto-hide success message after 4 seconds
    setTimeout(() => {
      successMessage.value = "";
    }, 4000);
  } catch (error) {
    console.error("❌ Error opening report:", error);
    errorMessage.value = error.message || "Failed to open report. Please try again.";
  } finally {
    downloading.value = null;
  }
};

// ✅ NEW: Retry Handler
const handleRetry = async (reportId) => {
  retrying.value = reportId;
  errorMessage.value = "";
  successMessage.value = "";

  try {
    console.log("🔄 Retrying report generation:", reportId);

    const result = await reportStore.retryReport(reportId);

    successMessage.value = "Report generation retry initiated successfully!";
    console.log("✅ Retry successful:", result);

    // Refresh the reports table after 2 seconds
    setTimeout(async () => {
      await refreshTable();
      successMessage.value = "";
    }, 2000);
  } catch (error) {
    console.error("❌ Retry error:", error);
    errorMessage.value =
      error.message || "Failed to retry report generation. Please try again.";
  } finally {
    retrying.value = null;
  }
};

const getReportIcon = (type) => {
  const typeStr = String(type || "").toLowerCase();
  const icons = {
    pdf: "bi bi-file-pdf text-danger",
    excel: "bi bi-file-excel text-success",
    csv: "bi bi-file-text text-secondary",
  };
  return icons[typeStr] || "bi bi-file";
};

// ✅ UPDATED: Status badge mapping
const getStatusBadge = (status) => {
  const statusNum = typeof status === "number" ? status : parseInt(status) || 0;
  const badges = {
    0: "badge rounded-pill bg-secondary", // Queue
    1: "badge rounded-pill bg-primary", // Processing
    2: "badge rounded-pill bg-success", // Ready for download
    3: "badge rounded-pill bg-danger", // Failed
  };
  return badges[statusNum] || "badge bg-secondary";
};

const refreshTable = async () => {
  currentPage.value = 0;
  await fetchReports();
};

// const startAutoRefresh = () => {
//   stopAutoRefresh();

//   autoRefreshInterval.value = setInterval(() => {
//     fetchReports();
//   }, 5000); // refresh every 5 seconds
// };

const startAutoRefresh = () => {
  stopAutoRefresh();

  autoRefreshInterval.value = setInterval(async () => {
    await fetchReports();
  }, 5000);
};

const stopAutoRefresh = () => {
  if (autoRefreshInterval.value) {
    clearInterval(autoRefreshInterval.value);
    autoRefreshInterval.value = null;
  }
};

watch(
  () => [props.dateFrom, props.dateTo],
  async () => {
    await refreshTable();
  }
);

onMounted(async () => {
  await fetchReports();
  startAutoRefresh();
});

onBeforeUnmount(() => {
  stopAutoRefresh();
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

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.btn-sm i {
  font-size: 0.875rem;
}

.spinner-border-sm {
  width: 0.875rem;
  height: 0.875rem;
  border-width: 0.1rem;
}

.alert {
  font-size: 0.9rem;
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Animation for processing spinner */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
