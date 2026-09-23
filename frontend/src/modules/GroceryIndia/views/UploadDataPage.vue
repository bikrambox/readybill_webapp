<!-- src/views/UploadDataPage.vue -->
<template>
  <div>
    <PageTitle
      :title="$t('upload_data_page.title')"
      :info="$t('upload_data_page.info')"
      :note="$t('upload_data_page.note')"
    />

    <section class="section pb-4" id="uploadDataSection">
      <div class="row">
        <div class="col-xl-8">
          <!-- Subscription Error Box -->
          <FormErrorBox
            :messages="subscriptionErrors"
            @clear="clearSubscriptionErrors"
            class="mb-4"
          />
        </div>

        <div class="col-12">
          <div class="card">
            <div class="card-body pt-3">
              <div class="mb-3">
                <!-- Upload Error Box -->
                <FormErrorBox
                  :messages="uploadErrors"
                  @clear="clearUploadErrors"
                  class="mb-4"
                />

                <FileUploadForm v-if="!showInventoryForm" @upload="handleFileUpload" />
              </div>

              <ProgressSection v-if="showProgress" :progress="progress" />

              <InventoryForm
                v-if="showInventoryForm"
                :job-id="currentJobId"
                :global-errors="globalErrors"
                :is-exporting="isExporting"
                @export="handleExportData"
                @clear-errors="clearGlobalErrors"
                @cell-error="handleCellError"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Result Modal -->
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

<script setup>
import { ref, onMounted } from "vue";
import { useUploadDataStore } from "@/modules/GroceryIndia/stores/uploadDataStore";
import PageTitle from "@/modules/GroceryIndia/components/UploadData/PageTitle.vue";
import FileUploadForm from "@/modules/GroceryIndia/components/UploadData/FileUploadForm.vue";
import ProgressSection from "@/modules/GroceryIndia/components/UploadData/ProgressSection.vue";
import InventoryForm from "@/modules/GroceryIndia/components/UploadData/InventoryForm.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const uploadDataStore = useUploadDataStore();

const subscriptionErrors = ref([]);
const uploadErrors = ref([]);
const globalErrors = ref([]);
const showProgress = ref(false);
const progress = ref({ percentage: 0, message: "" });
const showInventoryForm = ref(false);
const currentJobId = ref(null);
const showResultModal = ref(false);
const isExporting = ref(false);
const resultData = ref({
  status: "success",
  message: "",
  title: "",
});

const clearSubscriptionErrors = () => {
  subscriptionErrors.value = [];
};

const clearUploadErrors = () => {
  uploadErrors.value = [];
};

const clearGlobalErrors = () => {
  globalErrors.value = [];
  uploadDataStore.clearErrors();
};

// ✅ Helper: extract deduplicated user-friendly error messages from any response
const extractErrorMessages = (response) => {
  // Special case: Excel header mismatch
  if (
    response?.errors?.expected_headers &&
    response?.errors?.received_headers &&
    Array.isArray(response.errors.expected_headers) &&
    Array.isArray(response.errors.received_headers)
  ) {
    const expected = response.errors.expected_headers.join(", ");
    const received = response.errors.received_headers.join(", ");

    return [
      response.message || t("common.Excel format does not match the required format"),
      `Expected headers: ${expected}`,
      `Received headers: ${received}`,
    ];
  }

  // ✅ Only use human-readable messages
  if (Array.isArray(response?.errors?.messages) && response.errors.messages.length > 0) {
    return [...new Set(response.errors.messages.filter(Boolean))];
  }

  if (Array.isArray(response?.messages) && response.messages.length > 0) {
    return [...new Set(response.messages.filter(Boolean))];
  }

  if (typeof response?.message === "string" && response.message.trim() !== "") {
    return [response.message];
  }

  return [];
};

const handleFileUpload = async (file) => {
  try {
    uploadErrors.value = [];
    globalErrors.value = [];
    uploadDataStore.clearErrors();
    showProgress.value = true;
    progress.value = { percentage: 0, message: t("common.Initiating upload") + "..." };

    const result = await uploadDataStore.previewExcel(file);

    if (result.status === 1 && result.job_id) {
      currentJobId.value = result.job_id;
      pollProgress(result.job_id);
    } else {
      showProgress.value = false;

      const errors = extractErrorMessages(result);
      uploadErrors.value =
        errors.length > 0 ? errors : [result.message || t("common.Upload failed")];
    }
  } catch (error) {
    showProgress.value = false;

    const errors = extractErrorMessages(error);
    uploadErrors.value =
      errors.length > 0
        ? errors
        : [error.message || t("common.An unexpected error occurred") + "."];
  }
};

const pollProgress = async (jobId) => {
  const interval = setInterval(async () => {
    try {
      const response = await uploadDataStore.checkUploadProgress(jobId);

      progress.value = response.progress;

      if (response.progress.percentage === 100) {
        clearInterval(interval);

        if (response.status === 2) {
          pollFetchProgress(jobId);
        } else {
          showProgress.value = false;
          uploadErrors.value = [response.progress.message];
        }
      }
    } catch (error) {
      clearInterval(interval);
      showProgress.value = false;
      uploadErrors.value = [
        error.message || t("common.An unexpected error occurred") + ".",
      ];
    }
  }, 1000);
};

const pollFetchProgress = async (jobId) => {
  showProgress.value = true;
  progress.value = { percentage: 0, message: t("common.Fetching data") + "..." };

  const interval = setInterval(async () => {
    try {
      const response = await uploadDataStore.fetchExcelDataProgress(jobId);

      progress.value = response.progress;

      if (response.progress.percentage === 100) {
        clearInterval(interval);
        showProgress.value = false;

        if (response.status === 1) {
          showInventoryForm.value = true;

          // ✅ Show validation errors from the fetch response (e.g. duplicate items)
          const errors = extractErrorMessages(response);
          if (errors.length > 0) {
            globalErrors.value = errors;
            // Also store in Pinia so table cells highlight
            if (response.errors) {
              uploadDataStore.errors = response.errors;
            }
          }
        } else {
          uploadErrors.value = [
            response.progress.message || t("common.Failed to fetch data"),
          ];
        }
      }
    } catch (error) {
      clearInterval(interval);
      showProgress.value = false;
      uploadErrors.value = [error.message];
    }
  }, 1000);
};

const handleExportData = async () => {
  try {
    globalErrors.value = [];
    uploadDataStore.clearErrors();
    isExporting.value = true;
    showProgress.value = true;
    progress.value = { percentage: 0, message: t("common.Initiating export") + "..." };

    const result = await uploadDataStore.exportToInventory({ action: 0 });

    if (result.status === 1 && result.job_id) {
      pollExportProgress(result.job_id);
    } else {
      showProgress.value = false;
      isExporting.value = false;
      globalErrors.value = [result.message || t("common.Export failed")];
    }
  } catch (error) {
    showProgress.value = false;
    isExporting.value = false;
    globalErrors.value = [error.message];
  }
};

const pollExportProgress = async (jobId) => {
  const interval = setInterval(async () => {
    try {
      const response = await uploadDataStore.checkExportProgress(jobId);

      progress.value = response.progress;

      if (response.progress.percentage === 100) {
        clearInterval(interval);
        showProgress.value = false;
        isExporting.value = false;

        if (response.status === 1) {
          resultData.value = {
            status: "success",
            message: response.progress.message,
            title: t("common.Export Successful"),
          };
          showResultModal.value = true;

          setTimeout(() => {
            location.reload();
          }, 2000);
        } else {
          // ✅ Extract and show error messages
          const errors = extractErrorMessages(response);
          if (errors.length > 0) {
            globalErrors.value = errors;
          } else if (response.message) {
            globalErrors.value = [response.message];
          } else {
            globalErrors.value = [response.progress.message || t("common.Export failed")];
          }

          // Store errors in Pinia for cell highlighting
          if (response.errors) {
            uploadDataStore.errors = response.errors;
          } else if (response.result?.errors) {
            uploadDataStore.errors = response.result.errors;
          }

          // ✅ Always refresh table on export failure to show error-highlighted cells
          await uploadDataStore.fetchExcelDataSync(currentJobId.value, {
            start: 0,
            length: uploadDataStore.pageSize,
          });
        }
      }
    } catch (error) {
      clearInterval(interval);
      showProgress.value = false;
      isExporting.value = false;
      globalErrors.value = [error.message];
    }
  }, 1000);
};

const closeResultModal = () => {
  showResultModal.value = false;
};

const checkActiveJob = async () => {
  try {
    const response = await uploadDataStore.getActiveJob();

    if (response.status === 1 && response.job_id) {
      showProgress.value = true;
      progress.value = response.progress;
      currentJobId.value = response.job_id;

      if (response.job_type === "upload") {
        pollProgress(response.job_id);
      } else if (response.job_type === "export") {
        showInventoryForm.value = true;
        isExporting.value = true;
        pollExportProgress(response.job_id);
      }
    }
  } catch (error) {
    // console.error("Error checking active job:", error);
  }
};

const handleCellError = (messages) => {
  const cleanMessages = (Array.isArray(messages) ? messages : []).filter(
    (msg) =>
      typeof msg === "string" &&
      msg.trim() !== "" &&
      !msg.startsWith("items.") &&
      !/^\d{2},\d{2}$/.test(msg)
  );

  cleanMessages.forEach((msg) => {
    if (!globalErrors.value.includes(msg)) {
      globalErrors.value.push(msg);
    }
  });

  window.scrollTo({ top: 0, behavior: "smooth" });
};

onMounted(() => {
  checkActiveJob();
});
</script>
