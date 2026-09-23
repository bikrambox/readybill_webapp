<template>
  <div>
    <FormErrorBox :messages="globalErrors" @clear="$emit('clear-errors')" class="mb-3" />

    <form @submit.prevent="handleExport">
      <div class="table-container">
        <div class="toolbar-section mb-3 p-3 bg-light rounded">
          <div class="row align-items-center g-2">
            <div class="col-md-4 mb-2 mb-md-0">
              <button
                type="button"
                class="btn btn-danger"
                :disabled="selectedIds.length === 0 || isDeleting"
                @click="handleDeleteSelected"
              >
                <i class="bi bi-trash me-2"></i>
                <span class="d-none d-sm-inline">{{ $t("common.Delete Selected") }}</span>
                <span class="d-inline d-sm-none">Delete</span>
                <span
                  v-if="selectedIds.length > 0"
                  class="badge bg-white text-danger ms-2"
                >
                  {{ selectedIds.length }}
                </span>
                <span
                  v-if="isDeleting"
                  class="spinner-border spinner-border-sm ms-2"
                  role="status"
                ></span>
              </button>
            </div>

            <div class="col-md-4 mb-2 mb-md-0 text-center">
              <h5 class="text-primary mb-0 fw-bold">
                <i class="bi bi-box-seam me-2"></i>
                <span class="d-none d-sm-inline">{{ $t("common.Total Items") }}:</span>
                <span class="badge bg-primary">{{ totalRecords }}</span>
              </h5>
            </div>

            <div class="col-md-4 text-md-end">
              <div class="d-flex gap-2 align-items-center justify-content-md-end">
                <InfoTooltip
                  :title="$t('common.upload_add_row_title')"
                  :message="$t('common.uplpoad_add_row_message')"
                  placement="top"
                  trigger="hover"
                >
                  <button
                    type="button"
                    class="btn btn-success"
                    @click="handleAddRow"
                    :disabled="isExporting || isAddingRow"
                  >
                    <span
                      v-if="isAddingRow"
                      class="spinner-border spinner-border-sm me-2"
                      role="status"
                    ></span>
                    <i v-else class="bi bi-plus-circle me-2"></i>
                    <span class="d-none d-sm-inline">
                      {{
                        isAddingRow ? $t("common.Adding") + "..." : $t("common.Add Row")
                      }}
                    </span>
                    <span class="d-inline d-sm-none">
                      {{ isAddingRow ? "..." : "Add" }}
                    </span>
                  </button>
                </InfoTooltip>
              </div>
            </div>
          </div>
        </div>

        <div class="d-none d-md-block">
          <DataTable
            ref="dataTableRef"
            :job-id="jobId"
            :page-size="pageSize"
            :columns="datasetColumns"
            @selection-change="handleSelectionChange"
            @refresh="refreshData"
            @cell-error="handleCellError"
          />
        </div>

        <div class="d-md-none">
          <MobileList
            :items="tableData"
            :columns="datasetColumns"
            :loading="loading"
            :unit-list="unitList"
            :current-page="currentPage"
            :page-size="mobilePageSize"
            :total-records="totalRecords"
            @selection-change="handleSelectionChange"
            @page-change="handleMobilePageChange"
            @page-size-change="handleMobilePageSizeChange"
            @validation-error="handleMobileValidationError"
          />
        </div>
      </div>

      <div class="mt-4">
        <button
          type="submit"
          class="btn btn-export"
          :disabled="isExporting || totalRecords === 0"
        >
          <span
            v-if="isExporting"
            class="spinner-border spinner-border-sm me-2"
            role="status"
            aria-hidden="true"
          ></span>
          <i v-else class="bi bi-download me-2"></i>
          <span v-if="isExporting">{{ $t("common.Exporting") }}...</span>
          <span v-else>{{ $t("common.Export Data") }}</span>
        </button>

        <button type="button" class="btn btn-cancel" @click="handleCancel">
          {{ $t("common.Cancel") }}
        </button>
      </div>
    </form>
  </div>

  <ConfirmModal
    ref="confirmModal"
    :message="confirmData.modalMessage"
    :title="confirmData.modalTitle"
    @confirm="handleReload"
    confirmText="Confirm"
  />
</template>

<script setup>
import { ref, computed } from "vue";
import { storeToRefs } from "pinia";
import { useUploadDataStore } from "@/modules/GroceryIndia/stores/uploadDataStore";
import DataTable from "./DataTable.vue";
import MobileList from "./MobileList.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import ConfirmModal from "@/modules/Core/components/modals/ConfirmModal.vue";
import InfoTooltip from "@/modules/core/components/InfoTooltip.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  jobId: {
    type: String,
    required: true,
  },
  globalErrors: {
    type: Array,
    default: () => [],
  },
  isExporting: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["export", "clear-errors", "cell-error"]);

const uploadDataStore = useUploadDataStore();
const { excelData, totalRecords, unitList, datasetColumns } = storeToRefs(
  uploadDataStore
);

const dataTableRef = ref(null);
const selectedIds = ref([]);
const isDeleting = ref(false);
const isAddingRow = ref(false);
const pageSize = ref(uploadDataStore.pageSize || 100);
const mobilePageSize = ref(10);
const currentPage = ref(1);
const loading = ref(false);
const confirmModal = ref(null);

const tableData = computed(() => excelData.value);

const confirmData = ref({
  modalMessage: "",
  modalTitle: "",
  action: null,
});

const isUsefulMessage = (msg) => {
  if (typeof msg !== "string") return false;

  const value = msg.trim();
  if (!value) return false;

  if (value.startsWith("items.")) return false;
  if (/^\d{2},\d{2}$/.test(value)) return false;
  if (value.startsWith("validation.")) return false;

  return true;
};

const normalizeErrorMessages = (payload) => {
  if (Array.isArray(payload) && payload.length) {
    return payload;
  }

  if (Array.isArray(payload?.errors?.messages) && payload.errors.messages.length) {
    return payload.errors.messages;
  }

  if (Array.isArray(payload?.messages) && payload.messages.length) {
    return payload.messages;
  }

  if (typeof payload?.message === "string" && payload.message.trim()) {
    return [payload.message];
  }

  return [t("common.Something went wrong")];
};

const emitCleanErrors = (payload) => {
  const messages = normalizeErrorMessages(payload);
  emit("cell-error", messages);
};

const handleSelectionChange = (ids) => {
  selectedIds.value = ids;
};

const handleDeleteSelected = async () => {
  if (
    !confirm(
      `Are you sure you want to delete ${selectedIds.value.length} selected items?`
    )
  ) {
    return;
  }

  try {
    isDeleting.value = true;
    await uploadDataStore.deleteSelectedItems(selectedIds.value);
    selectedIds.value = [];
    refreshData();
  } catch (error) {
    emitCleanErrors(error);
    window.scrollTo({ top: 0, behavior: "smooth" });
  } finally {
    isDeleting.value = false;
  }
};

const handleAddRow = async () => {
  if (isAddingRow.value) return;

  try {
    isAddingRow.value = true;
    await uploadDataStore.addNewRowToDb(props.jobId);
    window.scrollTo({ top: 0, behavior: "smooth" });
  } catch (error) {
    emitCleanErrors(error);
    window.scrollTo({ top: 0, behavior: "smooth" });
  } finally {
    isAddingRow.value = false;
  }
};

const handleMobilePageChange = async (page) => {
  currentPage.value = page;
  loading.value = true;

  try {
    const start = (page - 1) * mobilePageSize.value;
    await uploadDataStore.fetchExcelDataSync(props.jobId, {
      start,
      length: mobilePageSize.value,
      draw: page,
    });
  } catch (error) {
    emitCleanErrors(error);
  } finally {
    loading.value = false;
  }
};

const handleMobilePageSizeChange = async (size) => {
  mobilePageSize.value = size;
  uploadDataStore.setPageSize(size);
  currentPage.value = 1;
  loading.value = true;

  try {
    await uploadDataStore.fetchExcelDataSync(props.jobId, {
      start: 0,
      length: size,
      draw: 1,
    });
  } catch (error) {
    emitCleanErrors(error);
  } finally {
    loading.value = false;
  }
};

const handleMobileValidationError = (payload) => {
  window.scrollTo({ top: 0, behavior: "smooth" });
  emit("cell-error", normalizeErrorMessages(payload));
};

const handleCellError = (payload) => {
  window.scrollTo({ top: 0, behavior: "smooth" });
  emit("cell-error", normalizeErrorMessages(payload));
};

const handleExport = () => {
  if (!props.isExporting) {
    emit("export");
  }
};

const refreshData = async () => {
  try {
    loading.value = true;
    await uploadDataStore.fetchExcelDataSync(props.jobId, {
      start: 0,
      length: pageSize.value,
      draw: 1,
    });
  } catch (error) {
    emitCleanErrors(error);
  } finally {
    loading.value = false;
  }
};

const handleCancel = () => {
  confirmData.value = {
    modalMessage: t("common.reset_upload"),
    modalTitle: t("common.Confirm Reset"),
    action: "reset",
  };

  confirmModal.value?.show();
};

const handleReload = async () => {
  window.location.reload();
};
</script>

<style scoped>
.toolbar-section {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #e3e6ea;
}

.btn-export {
  background-color: #198754;
  border: none;
  color: white;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
}

.btn-export:hover:not(:disabled) {
  background-color: #157347;
  box-shadow: 0 4px 8px rgba(25, 135, 84, 0.4);
  transform: translateY(-1px);
}

.btn-export:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
}

.btn-export:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-export i {
  font-size: 1.1rem;
}

.btn-cancel {
  background-color: transparent;
  border: 1.5px solid #6c757d;
  color: #6c757d;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  border-radius: 6px;
  margin-left: 12px;
  transition: all 0.2s ease;
}

.btn-cancel:hover:not(:disabled) {
  background-color: #6c757d;
  color: #fff;
  box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
  transform: translateY(-1px);
}

.btn-cancel:active:not(:disabled) {
  background-color: #5c636a;
  border-color: #5c636a;
  color: #fff;
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

.btn-cancel:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 767px) {
  .btn {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }

  .btn-export {
    width: 100%;
    padding: 12px 24px;
  }

  .btn-cancel {
    width: 100%;
    margin-left: 0;
    margin-top: 10px;
    padding: 12px 24px;
  }

  h5 {
    font-size: 1rem;
  }
}
</style>
