<template>
  <div>
    <h2 class="auth-title">{{ $t("common.Agent Details") }}</h2>
    <p class="auth-subtitle">{{ $t("common.Tell us a bit more about yourself") }}</p>

    <form @submit.prevent="handleNext">
      <!-- Error Box -->
      <FormErrorBox v-if="allErrors.length" :messages="allErrors" @clear="clearErrors" />

      <!-- Full Name -->
      <div class="form-group">
        <label for="fullName" class="form-label">
          {{ $t("common.Full Name") }}
          <span class="text-danger">*</span>
        </label>
        <input
          id="fullName"
          type="text"
          class="form-control"
          :class="{ 'field-error': fieldErrors.fullName }"
          :placeholder="t('common.Enter your full name')"
          v-model="form.fullName"
          autocomplete="off"
        />
      </div>

      <!-- Address -->
      <div class="form-group">
        <label for="address" class="form-label">
          {{ $t("common.Address") }}
          <span class="text-danger">*</span>
        </label>
        <textarea
          id="address"
          class="form-control textarea-control"
          :class="{ 'field-error': fieldErrors.address }"
          :placeholder="t('common.Enter your full address')"
          v-model="form.address"
          rows="3"
          autocomplete="off"
        ></textarea>
      </div>

      <!-- Mobile No. -->
      <div class="form-group">
        <label for="mobile" class="form-label">
          {{ $t("common.Mobile Number") }}
          <span class="text-danger">*</span>
        </label>
        <div class="mobile-row" :class="{ 'is-mobile-error': fieldErrors.mobile }">
          <div class="mobile-country">
            <CountryCodeSelect
              v-model="form.countryCode"
              @change="selectedCountry = $event"
              :disabled="true"
            />
          </div>
          <div class="mobile-number">
            <input
              id="mobile"
              type="tel"
              class="form-control"
              :placeholder="t('common.Enter your mobile number')"
              v-model="form.mobile"
              autocomplete="off"
              maxlength="15"
            />
          </div>
        </div>
      </div>

      <!-- PAN Number -->
      <div class="form-group">
        <label for="panNumber" class="form-label">
          {{ $t("common.PAN Number") }}
          <span class="text-danger">*</span>
        </label>
        <input
          id="panNumber"
          type="text"
          class="form-control"
          :class="{ 'field-error': fieldErrors.panNumber }"
          :placeholder="t('common.Enter your PAN number')"
          v-model="form.panNumber"
          autocomplete="off"
          maxlength="10"
          @input="form.panNumber = form.panNumber.toUpperCase()"
        />
      </div>

      <button type="submit" class="btn btn-primary btn-block" :disabled="isLoading">
        <span v-if="isLoading">{{ $t("common.Please wait") }}...</span>
        <span v-else>{{ $t("common.Continue") }}</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";
import { useRegisterStore } from "@/modules/AuthorizedAgents/stores/registerStore";
import { useAgentValidation } from "@/composables/useAgentValidation";

const {
  validateFullName,
  validateAddress,
  validateMobile,
  validatePAN,
} = useAgentValidation();

const registerStore = useRegisterStore();

const props = defineProps({
  form: { type: Object, required: true },
  isLoading: { type: Boolean, default: false },
  serverErrors: { type: Array, default: () => [] },
});

const emit = defineEmits(["back", "next"]);

const { t } = useI18n();
const localErrors = ref([]);
const selectedCountry = ref(null);

// Per-field red border flags
const fieldErrors = reactive({
  fullName: false,
  address: false,
  mobile: false,
  panNumber: false,
});

const form = reactive({
  fullName: props.form.fullName || "",
  address: props.form.address || "",
  mobile: props.form.mobile || "",
  countryCode: props.form.countryCode || "+91",
  panNumber: props.form.panNumber || "",
});

// Merge local + server errors for FormErrorBox
const allErrors = computed(() => [...localErrors.value, ...props.serverErrors]);

// Clear all errors and red borders
const clearErrors = () => {
  localErrors.value = [];
  fieldErrors.fullName = false;
  fieldErrors.address = false;
  fieldErrors.mobile = false;
  fieldErrors.panNumber = false;
};

// Clear each field's red border as soon as the user starts retyping
watch(
  () => form.fullName,
  () => {
    if (fieldErrors.fullName) fieldErrors.fullName = false;
  }
);
watch(
  () => form.address,
  () => {
    if (fieldErrors.address) fieldErrors.address = false;
  }
);
watch(
  () => form.mobile,
  () => {
    if (fieldErrors.mobile) fieldErrors.mobile = false;
  }
);
watch(
  () => form.panNumber,
  () => {
    if (fieldErrors.panNumber) fieldErrors.panNumber = false;
  }
);

// Scroll to top when server errors arrive
watch(
  () => props.serverErrors,
  (errs) => {
    if (errs.length) window.scrollTo({ top: 0, behavior: "smooth" });
  }
);

const handleNext = () => {
  localErrors.value = [];
  fieldErrors.fullName = false;
  fieldErrors.address = false;
  fieldErrors.mobile = false;
  fieldErrors.panNumber = false;

  const fullNameErr = validateFullName(form.fullName);
  const addressErr = validateAddress(form.address);
  const mobileErr = validateMobile(form.mobile);
  const panNumberErr = validatePAN(form.panNumber);

  if (fullNameErr) fieldErrors.fullName = true;
  if (addressErr) fieldErrors.address = true;
  if (mobileErr) fieldErrors.mobile = true;
  if (panNumberErr) fieldErrors.panNumber = true;

  const errs = [fullNameErr, addressErr, mobileErr, panNumberErr].filter(Boolean);

  if (errs.length) {
    localErrors.value = errs;
    window.scrollTo({ top: 0, behavior: "smooth" });
    return;
  }

  emit("next", { ...form });
};
</script>

<style scoped>
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
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}
.form-control {
  height: 52px;
  border-radius: 8px;
  border: 1px solid #ddd;
  padding: 14px 16px;
  font-size: 15px;
  width: 100%;
  transition: all 0.3s ease;
}
.textarea-control {
  height: auto;
  resize: vertical;
  min-height: 90px;
}
.form-control::placeholder {
  color: #999;
}
.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
  outline: none;
}

/* ── Red border for regular fields ── */
.form-control.field-error {
  border-color: #dc3545;
}
.form-control.field-error:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
}

/* ── Mobile row layout ── */
.mobile-row {
  display: flex;
  flex-direction: row;
  gap: 10px;
  align-items: flex-start;
}
.mobile-country {
  flex: 0 0 130px;
  width: 110px;
}
.mobile-number {
  flex: 1;
  min-width: 0;
}

/* CountryCodeSelect fill its container */
.mobile-country :deep(.country-code-select),
.mobile-country :deep(select),
.mobile-country :deep(.form-select) {
  width: 100%;
  height: 52px;
  border-radius: 8px;
  border: 1px solid #ddd;
  font-size: 15px;
  transition: all 0.3s ease;
}
.mobile-country :deep(.country-code-select:focus),
.mobile-country :deep(select:focus),
.mobile-country :deep(.form-select:focus) {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
  outline: none;
}

/* ── Red border for mobile row ── */
.mobile-row.is-mobile-error .form-control,
.mobile-row.is-mobile-error :deep(.country-code-select),
.mobile-row.is-mobile-error :deep(select),
.mobile-row.is-mobile-error :deep(.form-select) {
  border-color: #dc3545;
}
.mobile-row.is-mobile-error .form-control:focus,
.mobile-row.is-mobile-error :deep(.country-code-select:focus),
.mobile-row.is-mobile-error :deep(select:focus),
.mobile-row.is-mobile-error :deep(.form-select:focus) {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
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

/* ── Responsive ── */
@media (max-width: 767px) {
  .auth-title {
    font-size: 24px;
  }
  .auth-subtitle {
    font-size: 14px;
    margin-bottom: 30px;
  }
  .form-control {
    height: 50px;
    font-size: 16px;
  }
  .btn-primary {
    height: 50px;
    font-size: 15px;
  }
  .mobile-country {
    flex: 0 0 110px;
    width: 95px;
  }
  .mobile-country :deep(.country-code-select),
  .mobile-country :deep(select),
  .mobile-country :deep(.form-select) {
    height: 50px;
    font-size: 16px;
  }
}
@media (max-width: 374px) {
  .auth-title {
    font-size: 22px;
  }
  .form-control {
    height: 48px;
  }
  .btn-primary {
    height: 48px;
  }
  .mobile-country {
    flex: 0 0 100px;
    width: 85px;
  }
  .mobile-country :deep(.country-code-select),
  .mobile-country :deep(select),
  .mobile-country :deep(.form-select) {
    height: 48px;
  }
}
</style>
