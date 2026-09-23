<template>
  <div>
    <!-- <button class="back-btn" type="button" @click="emit('back')">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
      {{ $t('common.Back') }}
    </button> -->

    <h2 class="auth-title">{{ $t("common.Document Uploads") }}</h2>
    <p class="auth-subtitle">{{ $t("common.document_upload_note") }}</p>

    <!-- ✅ Add this single-line instruction -->
    <!-- ✅ Updated instruction design -->
    <div class="upload-instruction-box">
      <svg
        class="instruction-icon"
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
      >
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <p class="instruction-text">
        {{ $t("common.upload_instruction") }}
      </p>
    </div>

    <form @submit.prevent="handleSubmit">
      <!-- Server errors from store -->
      <FormErrorBox v-if="allErrors.length" :messages="allErrors" @clear="clearErrors" />

      <!-- Upload Photo -->
      <div class="form-group">
        <label class="form-label">
          {{ $t("common.Upload Photo") }}
          <!-- <span class="required-star">*</span> -->
          <span class="text-danger">*</span>
        </label>
        <FileUploadBox
          ref="photoRef"
          :label="t('common.Click or drag to upload your photo')"
          :hint="t('common.upload_format_n_size_restriction')"
          accept="image/jpeg,image/png"
          icon="photo"
          :preview-url="previews.photo"
          @change="handleFile('photo', $event)"
          @remove="removeFile('photo')"
        />
      </div>

      <!-- Upload Aadhaar Card -->
      <div class="form-group">
        <label class="form-label">
          {{ $t("common.Upload Aadhaar Card") }}
          <!-- <span class="required-star">*</span> -->
          <span class="text-danger">*</span>
        </label>
        <FileUploadBox
          ref="aadharRef"
          :label="t('common.Click or drag to upload Aadhaar card')"
          :hint="t('common.upload_format_n_size_restriction')"
          accept="image/jpeg,image/png,application/pdf"
          icon="document"
          :preview-url="previews.aadharCard"
          @change="handleFile('aadharCard', $event)"
          @remove="removeFile('aadharCard')"
        />
      </div>

      <!-- Upload UPI QR Code -->
      <div class="form-group">
        <label class="form-label">
          {{ $t("common.upload_upi_qr_code") }}
          <!-- <span class="required-star">*</span> -->
          <span class="text-danger">*</span>
        </label>
        <FileUploadBox
          ref="upiRef"
          :label="t('common.Click or drag to upload UPI QR code')"
          :hint="t('common.upload_format_n_size_restriction')"
          accept="image/jpeg,image/png"
          icon="qr"
          :preview-url="previews.upiQrCode"
          @change="handleFile('upiQrCode', $event)"
          @remove="removeFile('upiQrCode')"
        />
      </div>

      <button type="submit" class="btn btn-primary btn-block" :disabled="isLoading">
        <span v-if="isLoading">{{ $t("common.Submitting") }}...</span>
        <span v-else>{{ $t("common.Submit") }}</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import FileUploadBox from "@/modules/Core/components/FileUploadBox.vue";
import { useRegisterStore } from "@/modules/AuthorizedAgents/stores/registerStore";

const photoRef = ref(null);
const aadharRef = ref(null);
const upiRef = ref(null);

// Map field names to their refs
const fieldRefs = {
  photo: photoRef,
  aadharCard: aadharRef,
  upiQrCode: upiRef,
};

const registerStore = useRegisterStore();
const props = defineProps({
  isLoading: { type: Boolean, default: false },
  serverErrors: { type: Array, default: () => [] },
});

const emit = defineEmits(["back", "submit"]);

const { t } = useI18n();

const localErrors = ref([]);

// Merge local + server errors
const allErrors = computed(() => [...localErrors.value, ...props.serverErrors]);

// Clear local errors only (server errors cleared via store in parent)
const clearErrors = () => {
  localErrors.value = [];
};

// Watch server errors — if new ones arrive, scroll to top of form
watch(
  () => props.serverErrors,
  (errs) => {
    if (errs.length) window.scrollTo({ top: 0, behavior: "smooth" });
  }
);

const files = reactive({
  photo: null,
  aadharCard: null,
  upiQrCode: null,
});

const previews = reactive({
  photo: null,
  aadharCard: null,
  upiQrCode: null,
});

const handleFile = (field, file) => {
  files[field] = file;
  if (file?.type.startsWith("image/")) {
    if (previews[field]) URL.revokeObjectURL(previews[field]);
    previews[field] = URL.createObjectURL(file);
  } else {
    previews[field] = null;
  }
};

// const removeFile = (field) => {
//   files[field] = null;
//   if (previews[field]) {
//     URL.revokeObjectURL(previews[field]);
//     previews[field] = null;
//   }
// };

const removeFile = (field) => {
  files[field] = null;
  if (previews[field]) {
    URL.revokeObjectURL(previews[field]);
    previews[field] = null;
  }
  // ✅ Tell the child to clear its internal fileName
  fieldRefs[field].value?.clearFileName();
};

const handleSubmit = () => {
  localErrors.value = [];
  const errs = [];

  if (!files.photo) errs.push(t("common.Please upload your photo") + ".");
  if (!files.aadharCard) errs.push(t("common.Please upload your Aadhaar card") + ".");
  if (!files.upiQrCode) errs.push(t("common.Please upload your UPI QR code") + ".");

  if (errs.length) {
    localErrors.value = errs;
    return;
  }

  emit("submit", { ...files });
};
</script>

<style scoped>
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  color: #007bff;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  margin-bottom: 20px;
  transition: color 0.2s;
}
.back-btn:hover {
  color: #0056b3;
}

.auth-title {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 12px;
  color: #1a1a1a;
  line-height: 1.3;
}
.auth-subtitle {
  font-size: 15px;
  color: #666;
  margin-bottom: 35px;
  line-height: 1.5;
}
.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}
.required-star {
  color: #dc3545;
  font-size: 14px;
  line-height: 1;
}
.btn-primary {
  height: 52px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  background-color: #007bff;
  border: none;
  width: 100%;
  margin-top: 8px;
  transition: all 0.3s ease;
}
.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 767px) {
  .auth-title {
    font-size: 24px;
  }
  .auth-subtitle {
    font-size: 14px;
    margin-bottom: 30px;
  }
  .btn-primary {
    height: 50px;
    font-size: 15px;
  }
}
@media (max-width: 374px) {
  .auth-title {
    font-size: 22px;
  }
  .btn-primary {
    height: 48px;
  }
}

.upload-instruction-box {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background-color: #fff8e1;
  border: 1px solid #ffe082;
  border-left: 4px solid #f59e0b;
  border-radius: 8px;
  padding: 12px 14px;
  margin-bottom: 16px; /* ⬅ reduced from 24px */
  margin-top: -20px; /* ⬅ pulls it closer to the subtitle above */
}

.instruction-icon {
  color: #f59e0b;
  flex-shrink: 0;
  margin-top: 1px;
}

.instruction-text {
  font-size: 13.5px;
  color: #7a5f00;
  line-height: 1.55;
  margin: 0;
}

@media (max-width: 767px) {
  .upload-instruction-box {
    padding: 10px 12px;
  }
  .instruction-text {
    font-size: 13px;
  }
}
</style>
