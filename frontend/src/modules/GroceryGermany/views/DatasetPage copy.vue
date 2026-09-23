<script setup>
import { ref, onMounted, computed } from "vue";
import { useDatasetStore } from "@/modules/GroceryIndia/stores/datasetStore";
import DatasetToolbar from "../components/dataset/DatasetToolbar.vue";
import DatasetTable from "../components/dataset/DatasetTable.vue";
import ExportModal from "@/modules/Core/components/modals/DatasetExportModal.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";

const datasetStore = useDatasetStore();
const showExportModal = ref(false);
const exportLoading = ref(false);
const showResultModal = ref(false);
const resultData = ref({
  status: "success",
  message: "",
  title: "",
});

onMounted(() => {
  datasetStore.fetchDatasets();
});

const handleSearch = (query) => {
  datasetStore.setSearchQuery(query);
};

const handleDeleteSelected = async () => {
  if (confirm(`Delete ${datasetStore.selectedCount} item(s)?`)) {
    try {
      await datasetStore.deleteSelected();
    } catch (error) {
      alert(error.message || "Failed to delete items");
    }
  }
};

const handleResetDataset = async () => {
  if (confirm("Reset dataset? This will reload all data.")) {
    try {
      await datasetStore.resetDataset();
      alert("Dataset reset successfully");
    } catch (error) {
      alert(error.message || "Failed to reset dataset");
    }
  }
};

const handleAddRow = () => {
  datasetStore.addRow();
};

const handleExport = () => {
  showExportModal.value = true;
};

// const handleExportConfirm = async (action) => {
//   try {
//     const result = await datasetStore.exportData(action)
//     alert(result.message)
//     showExportModal.value = false
//   } catch (error) {
//     alert(error.message || 'Failed to export data')
//   }
// }

const showSuccessNotification = (message) => {
  // You can use a toast library or custom notification
  // For now, using simple alert
  alert(message);

  // Or use Bootstrap Toast if available
  // const toastEl = document.getElementById('successToast')
  // const toast = new bootstrap.Toast(toastEl)
  // toast.show()
};

const handleExportConfirm = async (action) => {
  // Modal stays open during processing - loader shows inside it
  try {
    const result = await datasetStore.exportData(action)
    
    // Close modal after success/failure (handled by parent)
    showExportModal.value = false
    
    // Show result modal
    resultData.value = {
      status: 'success',
      message: result.message,
      title: 'Success'
    }
    showResultModal.value = true
  } catch (error) {
    // Close modal after error
    showExportModal.value = false
    
    resultData.value = {
      status: 'error',
      message: error.message || 'Export failed',
      title: 'Error'
    }
    showResultModal.value = true
  }
};


const closeResultModal = () => {
  showResultModal.value = false;
  // If success, optionally refresh the data
  if (resultData.value.status === "success") {
    datasetStore.fetchDatasets();
  }
};

const handleRowsPerPageChange = (value) => {
  datasetStore.setRowsPerPage(value);
};

const dismissErrors = () => {
  datasetStore.clearErrors();
};
</script>

<template>
  <div class="dataset-layout">
    <div class="main-content">
      <!-- Page Title -->
      <div
        class="mb-3 mt-3 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2 px-3"
      >
        <div>
          <h4 class="fw-bold mb-1">Sample Dataset</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">
                Dataset
              </li>
            </ol>
          </nav>
        </div>
      </div>

      <!-- Error Alert -->
      <div
        v-if="datasetStore.hasErrors"
        class="alert alert-danger mx-3 d-flex align-items-center justify-content-between"
        role="alert"
      >
        <div>
          <i class="bi bi-exclamation-circle-fill me-2"></i>
          <span
            v-for="(message, index) in datasetStore.errorDetails.messages"
            :key="index"
          >
            {{ message }}
          </span>
        </div>
        <button
          type="button"
          class="btn-close"
          @click="dismissErrors"
          aria-label="Close"
        ></button>
      </div>

      <DatasetToolbar
        :search-query="datasetStore.searchQuery"
        :selected-count="datasetStore.selectedCount"
        :has-selection="datasetStore.hasSelection"
        @update:search-query="handleSearch"
        @deselect="datasetStore.deselectAll"
        @delete-selected="handleDeleteSelected"
        @reset-dataset="handleResetDataset"
        @add-row="handleAddRow"
      />

      <!-- Total Items Count -->
      <div class="px-3 mb-3">
        <h5 class="fw-semibold">
          Total Items: {{ datasetStore.pagination.recordsFiltered }}
        </h5>
      </div>

      <!-- Loading State -->
      <div v-if="datasetStore.loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Error State -->
      <!-- <div v-else-if="datasetStore.error" class="alert alert-danger mx-3" role="alert">
        {{ datasetStore.error }}
      </div> -->

      <!-- Table/cards -->
      <DatasetTable
        v-else
        :datasets="datasetStore.filteredDatasets"
        @toggle-item="datasetStore.toggleSelection"
        @toggle-all="datasetStore.toggleAll"
      />

      <!-- DESKTOP: Pagination and Export in a single row -->
      <div
        class="d-none d-md-flex justify-content-between align-items-center mt-3 px-2 flex-wrap"
      >
        <button class="btn btn-success px-4 fw-semibold" @click="handleExport">
          Export Data
        </button>
        <div class="d-flex align-items-center gap-3 ms-auto">
          <span class="small text-nowrap">Rows per page:</span>
          <select
            :value="datasetStore.pagination.length"
            @change="handleRowsPerPageChange($event.target.value)"
            class="form-select form-select-sm borderless-select paginate-size"
            style="width: 68px"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
          <span class="small text-nowrap">{{ datasetStore.displayRange }}</span>
          <button
            class="btn btn-sm btn-outline-secondary"
            @click="datasetStore.previousPage"
            :disabled="datasetStore.pagination.start === 0"
          >
            <i class="bi bi-chevron-left"></i>
          </button>
          <button
            class="btn btn-sm btn-outline-secondary"
            @click="datasetStore.nextPage"
            :disabled="
              datasetStore.pagination.start + datasetStore.pagination.length >=
              datasetStore.pagination.recordsFiltered
            "
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- MOBILE: Pagination first, then Export button (full width) -->
      <div class="d-md-none">
        <div
          class="d-flex align-items-center mt-2 px-2 gap-2 justify-content-between"
        >
          <div class="d-flex align-items-center">
            <span class="small me-2">Rows per page:</span>
            <select
              :value="datasetStore.pagination.length"
              @change="handleRowsPerPageChange($event.target.value)"
              class="form-select form-select-sm borderless-select"
              style="width: 60px"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="100">100</option>
            </select>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="small text-nowrap">{{
              datasetStore.displayRange
            }}</span>
            <button
              class="btn btn-sm btn-outline-secondary"
              @click="datasetStore.previousPage"
              :disabled="datasetStore.pagination.start === 0"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              class="btn btn-sm btn-outline-secondary"
              @click="datasetStore.nextPage"
              :disabled="
                datasetStore.pagination.start +
                  datasetStore.pagination.length >=
                datasetStore.pagination.recordsFiltered
              "
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
        <button
          class="btn btn-success fw-semibold w-100 mt-3"
          @click="handleExport"
        >
          Export Data
        </button>
      </div>
    </div>

    <!-- Export Modal -->
    <ExportModal
      :show="showExportModal"
      @confirm="handleExportConfirm"
      @cancel="showExportModal = false"
    />

    <!-- Result Modal -->
    <ResultModal
      v-if="showResultModal"
      :show="showResultModal"
      :status="resultData.status"
      :message="resultData.message"
      :title="resultData.title"
      @close="closeResultModal"
    />
    
  </div>
</template>

<style scoped>
.dataset-layout {
  display: flex;
  min-height: 100vh;
  background: #f8f9fa;
}
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: #f8f9fa;
  padding-bottom: 1rem;
}
.borderless-select {
  border: 1px solid #dee2e6;
  background: #fff;
  box-shadow: none;
  padding-right: 1.25rem;
  background-position: right 0.5rem center;
}
.borderless-select:focus {
  border-color: #dee2e6;
  box-shadow: none;
}
.paginate-size {
  height: 33px;
}
</style>
