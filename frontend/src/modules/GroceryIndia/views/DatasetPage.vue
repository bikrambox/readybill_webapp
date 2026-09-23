<script setup>
import { ref, onMounted, computed } from "vue";
import { storeToRefs } from "pinia";
import { useDatasetStore } from "@/modules/GroceryIndia/stores/datasetStore";
import { useConfigStore } from "@/stores/config";
import DatasetToolbar from "../components/dataset/DatasetToolbar.vue";
import DatasetTable from "../components/dataset/DatasetTable.vue";
import DatasetMobileList from "../components/dataset/DatasetMobileList.vue";
import ExportModal from "@/modules/Core/components/modals/DatasetExportModal.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const datasetStore = useDatasetStore();
const configStore = useConfigStore();

// Get units from config store
const { unitsArray, taxesArray, loading: configLoading } = storeToRefs(configStore);

// console.log('unitsArray',unitsArray.value);

const showExportModal = ref(false);
const showResultModal = ref(false);
const resultData = ref({
  status: "success",
  message: "",
  title: "",
});
const isDownloading = ref(false);

// Confirm modal reference and data
const confirmModal = ref(null);
const confirmData = ref({
  modalMessage: "",
  modalTitle: "",
  action: null, // 'delete' or 'reset'
});

// Additional error messages
const cellUpdateErrors = ref([]);
const exportErrors = ref([]);
const deleteErrors = ref([]);
const resetErrors = ref([]);
const addRowErrors = ref([]);
const infoMessages = ref([]);
const isInformationDismissed = ref(false);

// Pagination state
const currentPage = ref(0);
// const pageLength = ref(10);

// Combined error messages
// const errorMessages = computed(() => {
//   const errors = [];

//   if (datasetStore.hasErrors && datasetStore.errorDetails.messages) {
//     errors.push(...datasetStore.errorDetails.messages);
//   }

//   if (cellUpdateErrors.value.length > 0) {
//     errors.push(...cellUpdateErrors.value);
//   }

//   if (exportErrors.value.length > 0) {
//     errors.push(...exportErrors.value);
//   }

//   if (deleteErrors.value.length > 0) {
//     errors.push(...deleteErrors.value);
//   }

//   if (resetErrors.value.length > 0) {
//     errors.push(...resetErrors.value);
//   }

//   if (addRowErrors.value.length > 0) {
//     errors.push(...addRowErrors.value);
//   }

//   return errors;
// });

const errorMessages = computed(() => {
  const errors = [];

  const duplicateInventoryMessage = "Item(s) is already present in your inventory";

  if (datasetStore.hasErrors && datasetStore.errorDetails?.messages) {
    const hasItemNameError = datasetStore.errorDetails.coordinates?.some((coord) =>
      coord?.includes("item_name")
    );

    datasetStore.errorDetails.messages.forEach((msg) => {
      const isDuplicateItemNameInfo =
        msg === duplicateInventoryMessage && hasItemNameError;

      if (!isDuplicateItemNameInfo) {
        errors.push(msg);
      }
    });
  }

  if (cellUpdateErrors.value.length > 0) {
    errors.push(...cellUpdateErrors.value);
  }

  if (exportErrors.value.length > 0) {
    errors.push(...exportErrors.value);
  }

  if (deleteErrors.value.length > 0) {
    errors.push(...deleteErrors.value);
  }

  if (resetErrors.value.length > 0) {
    errors.push(...resetErrors.value);
  }

  if (addRowErrors.value.length > 0) {
    errors.push(...addRowErrors.value);
  }

  return errors;
});

const informationMessages = computed(() => {
  if (isInformationDismissed.value) return [];

  const infos = [];
  const duplicateInventoryMessage = "Item(s) is already present in your inventory";

  if (datasetStore.hasErrors && datasetStore.errorDetails?.messages) {
    const hasItemNameError = datasetStore.errorDetails.coordinates?.some((coord) =>
      coord?.includes("item_name")
    );

    datasetStore.errorDetails.messages.forEach((msg) => {
      if (msg === duplicateInventoryMessage && hasItemNameError) {
        infos.push(msg);
      }
    });
  }

  if (infoMessages.value.length > 0) {
    infos.push(...infoMessages.value);
  }

  return [...new Set(infos)];
});

onMounted(() => {
  isInformationDismissed.value = false;
  configStore.fetchConfigData();
  datasetStore.fetchDatasets();
});

const handleSearch = (query) => {
  datasetStore.setSearchQuery(query);
  currentPage.value = 0;
};

const handleDeleteSelected = () => {
  const count = datasetStore.selectedCount;

  if (count === 0) {
    resultData.value = {
      status: "error",
      message: t("common.Please select items to delete"),
      title: t("common.No Selection"),
    };
    showResultModal.value = true;
    return;
  }

  deleteErrors.value = [];

  confirmData.value = {
    // modalMessage: `Are you sure you want to delete ${count} item${count > 1 ? 's' : ''}? This action cannot be undone.`,
    modalMessage: t("common.delete_items_confirm", { count }),
    modalTitle: t("common.Confirm Delete"),
    action: "delete",
  };

  confirmModal.value?.show();
};

const handleResetDataset = () => {
  resetErrors.value = [];

  confirmData.value = {
    modalMessage: t("common.reset_dataset"),
    modalTitle: t("common.Confirm Reset"),
    action: "reset",
  };

  confirmModal.value?.show();
};

// const handleDownloadDataset = async () => {
//   if (isDownloading.value) return;
//   isDownloading.value = true;
//   try {
//     const result = await datasetStore.downloadDataset();
//     if (!result.success) {
//       // toast.error(inventoryStore.errors.join(', ') || 'Export failed')
//     }
//   } catch (error) {
//     console.error("Download error:", error);
//   } finally {
//     isDownloading.value = false;
//   }
// };

// Download dataset file only
const handleDownloadDataset = async () => {
  if (isDownloading.value) return;

  isDownloading.value = true;
  try {
    await datasetStore.downloadPreDataset();
  } catch (error) {
    console.error("Download error:", error);
    const errorMessage = error.message || t("common.Failed to download dataset");
    exportErrors.value = [errorMessage];
    window.scrollTo({ top: 0, behavior: "smooth" });
  } finally {
    isDownloading.value = false;
  }
};

const handleDelete = async () => {
  const action = confirmData.value.action;

  if (action === "delete") {
    try {
      const result = await datasetStore.deleteSelected();

      confirmModal.value?.hide();

      confirmData.value = {
        modalMessage: "",
        modalTitle: "",
        action: null,
      };

      resultData.value = {
        status: "success",
        message: result.message || t("common.Items deleted successfully"),
        title: t("common.Success"),
      };
      showResultModal.value = true;
    } catch (error) {
      console.error("Delete error:", error);

      confirmModal.value?.hide();

      confirmData.value = {
        modalMessage: "",
        modalTitle: "",
        action: null,
      };

      const errorMessage = error.message || t("common.Failed to delete items");
      deleteErrors.value = [errorMessage];

      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  } else if (action === "reset") {
    try {
      const result = await datasetStore.resetDataset();

      confirmModal.value?.hide();

      confirmData.value = {
        modalMessage: "",
        modalTitle: "",
        action: null,
      };

      resultData.value = {
        status: "success",
        message: result.message || t("common.Dataset reset successfully"),
        title: t("common.Success"),
      };
      showResultModal.value = true;
    } catch (error) {
      console.error("Reset error:", error);

      confirmModal.value?.hide();

      confirmData.value = {
        modalMessage: "",
        modalTitle: "",
        action: null,
      };

      // const errorMessage = error.message || t("common.Failed to reset dataset");
      const errorMessage = t("common.Failed to reset dataset");
      resetErrors.value = [errorMessage];

      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }
};

const handleAddRow = async () => {
  try {
    addRowErrors.value = [];

    console.log("Adding new row...");

    await datasetStore.addRow();

    console.log("New row added successfully");
  } catch (error) {
    console.error("Add row error:", error);

    // const errorMessage = error.message || t('common.Failed to add new row');
    // addRowErrors.value = [errorMessage];

    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const handleExport = () => {
  exportErrors.value = [];
  showExportModal.value = true;
};

const handleExportConfirm = async (action) => {
  try {
    exportErrors.value = [];

    const result = await datasetStore.exportData(action);

    showExportModal.value = false;

    resultData.value = {
      status: "success",
      message: result.message || t("common.Data exported successfully"),
      title: "Export Success",
    };
    showResultModal.value = true;
  } catch (error) {
    console.error("Export error:", error);

    showExportModal.value = false;

    // Refresh dataset table when export fails
    console.log("Export failed - refreshing dataset table...");
    await datasetStore.fetchDatasets();

    // Show validation errors from store if available
    if (
      datasetStore.hasErrors &&
      datasetStore.errorDetails.messages &&
      datasetStore.errorDetails.messages.length > 0
    ) {
      console.log("Validation errors from store:", datasetStore.errorDetails.messages);
    } else {
      console.log("Export failed without specific validation errors");
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const closeResultModal = () => {
  showResultModal.value = false;

  if (resultData.value.status === "success") {
    datasetStore.fetchDatasets();
  }
};

// const handlePageChange = async (newPage) => {
//   currentPage.value = newPage;
//   try {
//     await datasetStore.fetchDatasets({
//       start: newPage * pageLength.value,
//       length: pageLength.value,
//     });
//   } catch (error) {
//     console.error("Page change error:", error);
//   }
// };

// const handlePageLengthChange = async (newLength) => {
//   pageLength.value = newLength;
//   currentPage.value = 0;
//   try {
//     await datasetStore.fetchDatasets({
//       start: 0,
//       length: newLength,
//     });
//   } catch (error) {
//     console.error("Length change error:", error);
//   }
// };

const handlePageChange = (newPage) => {
  const currentStart = datasetStore.pagination.start;
  const length = datasetStore.pagination.length;
  const newStart = newPage * length;

  if (newStart !== currentStart) {
    datasetStore.pagination.start = newStart;
    datasetStore.fetchDatasets();
  }
};

const handlePageLengthChange = (newLength) => {
  datasetStore.setRowsPerPage(newLength);
};

// const clearErrors = () => {
//   datasetStore.clearErrors();
//   cellUpdateErrors.value = [];
//   exportErrors.value = [];
//   deleteErrors.value = [];
//   resetErrors.value = [];
//   addRowErrors.value = [];
// };

const clearErrors = () => {
  datasetStore.clearErrors();
  cellUpdateErrors.value = [];
  exportErrors.value = [];
  deleteErrors.value = [];
  resetErrors.value = [];
  addRowErrors.value = [];
  infoMessages.value = [];
  isInformationDismissed.value = false;
};

// const clearInformationMessages = () => {
//   infoMessages.value = [];

//   if (datasetStore.errorDetails?.messages?.length) {
//     const duplicateInventoryMessage = "Item(s) is already present in your inventory";
//     const hasItemNameError = datasetStore.errorDetails.coordinates?.some((coord) =>
//       coord?.includes("item_name")
//     );

//     if (hasItemNameError) {
//       datasetStore.errorDetails = {
//         ...datasetStore.errorDetails,
//         messages: datasetStore.errorDetails.messages.filter(
//           (msg) => msg !== duplicateInventoryMessage
//         ),
//       };

//       if (
//         datasetStore.errorDetails.messages.length === 0 &&
//         (!datasetStore.errorDetails.gridCoordinates ||
//           datasetStore.errorDetails.gridCoordinates.length === 0)
//       ) {
//         datasetStore.errorDetails = null;
//       }
//     }
//   }
// };

const clearInformationMessages = () => {
  infoMessages.value = [];
  isInformationDismissed.value = true;
};

const handleCellUpdated = (message) => {
  console.log("Cell updated:", message);
  cellUpdateErrors.value = [];
};

// const handleCellError = (message) => {
//   console.error("Cell update error:", message);
//   if (!datasetStore.hasErrors) {
//     cellUpdateErrors.value = [message];
//   }
// };

const handleCellError = (message) => {
  console.error("Cell update error:", message);

  const duplicateItemMessage = "Item(s) is already present in your inventory";

  if (message === duplicateItemMessage) {
    resultData.value = {
      status: "info",
      title: t("common.Information"),
      message,
    };
    showResultModal.value = true;
    return;
  }

  if (!datasetStore.hasErrors) {
    cellUpdateErrors.value = [message];
  }
};
</script>

<template>
  <div class="min-vh-100 bg-light">
    <div class="container-fluid p-3 pb-4">
      <!-- Page Header -->
      <div class="mb-3 mb-lg-4">
        <h4 class="fw-bold mb-2">{{ $t("dataset_page.title") }}</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item">
              <a href="sell" class="text-decoration-none">{{ $t("common.Home") }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ $t("common.Dataset") }}
            </li>
          </ol>
        </nav>

        <p class="dataset-subtitle">
          {{ $t("common.dataset_subtitle") }}
        </p>
      </div>

      <!-- Error Box -->
      <FormErrorBox :messages="errorMessages" @clear="clearErrors" class="mb-3" />

      <div
        v-if="informationMessages.length > 0"
        class="alert alert-info d-flex justify-content-between align-items-center mb-3 information-alert"
        role="alert"
      >
        <div class="d-flex align-items-center flex-nowrap information-alert-content">
          <i class="bi bi-info-circle-fill information-alert-icon"></i>

          <div class="small mb-0">
            <div
              v-for="(message, index) in informationMessages"
              :key="`info-${index}`"
              class="mb-0"
            >
              {{ message }}
            </div>
          </div>
        </div>

        <button
          type="button"
          class="btn-close ms-3 flex-shrink-0"
          aria-label="Close"
          @click="clearInformationMessages"
        ></button>
      </div>

      <!-- Toolbar -->
      <DatasetToolbar
        :search-query="datasetStore.searchQuery"
        :selected-count="datasetStore.selectedCount"
        :has-selection="datasetStore.hasSelection"
        @update:search-query="handleSearch"
        @deselect="datasetStore.deselectAll"
        @delete-selected="handleDeleteSelected"
        @reset-dataset="handleResetDataset"
        @download-dataset="handleDownloadDataset"
        @add-row="handleAddRow"
      />

      <!-- Total Items Count -->
      <!-- <div class="mb-3">
        <h5 class="fw-semibold mb-0">
          {{ $t("common.Total Items") }}:
          {{ datasetStore.pagination.recordsFiltered }}
        </h5>
      </div> -->

      <div class="col-12 my-3 text-center">
        <h5 class="text-primary mb-0 fw-bold">
          <i class="bi bi-box-seam me-2"></i>
          <span class="d-none d-sm-inline">{{ $t("common.Total Items") }}:</span>
          <span class="badge bg-primary">{{
            datasetStore.pagination.recordsFiltered
          }}</span>
        </h5>
      </div>

      <!-- Desktop Table -->
      <div class="d-none d-md-block">
        <!-- <DatasetTable
          :datasets="datasetStore.filteredDatasets"
          :is-loading="datasetStore.loading"
          :current-page="currentPage"
          :page-length="pageLength"
          :total-records="datasetStore.pagination.recordsTotal"
          :filtered-records="datasetStore.pagination.recordsFiltered"
          :units="unitsArray"
          @toggle-item="datasetStore.toggleSelection"
          @toggle-all="datasetStore.toggleAll"
          @page-change="handlePageChange"
          @page-length-change="handlePageLengthChange"
          @cell-updated="handleCellUpdated"
          @cell-error="handleCellError"
        /> -->

        <DatasetTable
          :datasets="datasetStore.filteredDatasets"
          :is-loading="datasetStore.loading"
          :current-page="datasetStore.currentPage - 1"
          :page-length="datasetStore.pagination.length"
          :total-records="datasetStore.pagination.recordsTotal"
          :filtered-records="datasetStore.pagination.recordsFiltered"
          :units="unitsArray"
          @toggle-item="datasetStore.toggleSelection"
          @toggle-all="datasetStore.toggleAll"
          @page-change="handlePageChange"
          @page-length-change="handlePageLengthChange"
          @cell-updated="handleCellUpdated"
          @cell-error="handleCellError"
        />

        <!-- Desktop Export Button -->
        <div class="mt-3">
          <button
            class="btn btn-success px-4 fw-semibold"
            @click="handleExport"
            :disabled="datasetStore.loading"
          >
            <i class="bi bi-download me-2"></i>{{ $t("common.Export Data") }}
          </button>
        </div>
      </div>

      <!-- Mobile List -->
      <div class="d-md-none">
        <DatasetMobileList
          :datasets="datasetStore.filteredDatasets"
          :is-loading="datasetStore.loading"
          :units="unitsArray"
          @toggle-item="datasetStore.toggleSelection"
          @cell-updated="handleCellUpdated"
          @cell-error="handleCellError"
        />

        <!-- Mobile Pagination -->
        <div class="mt-3">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <span class="small">Rows:</span>
              <select
                :value="datasetStore.pagination.length"
                @change="handlePageLengthChange(parseInt($event.target.value))"
                class="form-select form-select-sm"
                style="width: 65px"
                :disabled="datasetStore.loading"
              >
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </div>

            <div class="d-flex align-items-center gap-2">
              <span class="small text-muted text-nowrap">{{
                datasetStore.displayRange
              }}</span>
              <div class="btn-group" role="group">
                <button
                  class="btn btn-sm btn-outline-secondary"
                  @click="handlePageChange(datasetStore.currentPage - 2)"
                  :disabled="datasetStore.currentPage <= 1 || datasetStore.loading"
                >
                  <i class="bi bi-chevron-left"></i>
                </button>
                <button
                  class="btn btn-sm btn-outline-secondary"
                  @click="handlePageChange(datasetStore.currentPage)"
                  :disabled="
                    datasetStore.currentPage >= datasetStore.totalPages ||
                    datasetStore.loading
                  "
                >
                  <i class="bi bi-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>

          <button
            class="btn btn-success fw-semibold w-100"
            @click="handleExport"
            :disabled="datasetStore.loading"
          >
            <i class="bi bi-download me-2"></i>{{ $t("common.Export Data") }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Modal -->
    <ConfirmModal
      ref="confirmModal"
      :message="confirmData.modalMessage"
      :title="confirmData.modalTitle"
      @confirm="handleDelete"
      confirmText="Confirm"
    />

    <!-- Export Modal -->
    <ExportModal
      :show="showExportModal"
      @confirm="handleExportConfirm"
      @cancel="showExportModal = false"
    />

    <!-- Result Modal (Only for Success) -->
    <ResultModal
      :show="showResultModal"
      :status="resultData.status"
      :message="resultData.message"
      :title="resultData.title"
      @close="closeResultModal"
    />

    <!-- Footer -->
    <Footer />
  </div>
</template>

<style scoped>
.dataset-subtitle {
  font-size: 1rem;
  font-weight: 400;
  /* color: #888; */
  margin-top: 0.1rem;
}

.information-alert {
  padding: 0.75rem 1rem;
}

.information-alert-content {
  min-width: 0;
  gap: 0.5rem;
}

.information-alert-icon {
  font-size: 1rem;
  line-height: 1;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  color: #0c5460;
}

.information-alert .small {
  margin-bottom: 0;
  line-height: 1.4;
}

@media (max-width: 576px) {
  .dataset-subtitle {
    font-size: 0.9rem;
  }
}
</style>
