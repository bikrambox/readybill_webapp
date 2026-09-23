<template>
  <div class="register-form">
    <div class="form-wrapper">
      <div class="form-header">
        <h1 class="form-title">{{ $t("common.Register your account") }}</h1>
        <p class="form-description">{{ $t("common.Join us and grow your business") }}!</p>
      </div>

      <form @submit.prevent="handleSubmit" class="registration-form">
        <FormErrorBox
          v-if="registerStore.errorMessages.length"
          :messages="registerStore.errorMessages"
          @clear="registerStore.clearErrors"
        />

        <!-- Mobile Number -->
        <div class="input-group-wrapper mb-3">
          <label class="form-label fw-semibold">
            {{ $t("common.Mobile Number") }}
          </label>

          <div class="mobile-input-group">
            <CountryCodeSelect
              v-model="registerStore.registrationData.dialCode"
              @change="handleCountryChange"
            />

            <input
              type="tel"
              inputmode="numeric"
              class="form-input"
              :class="{ 'border-danger': mobileError }"
              :value="registerStore.registrationData.mobile"
              :placeholder="$t('profile_page.Enter your mobile number')"
              maxlength="12"
              @input="validateMobile"
              @paste="handleMobilePaste"
              required
              autocomplete="tel"
            />
          </div>

          <span v-if="mobileError" class="input-error">{{ mobileError }}</span>
        </div>

        <div class="form-group form-options">
          <div class="form-check">
            <input
              type="checkbox"
              class="form-check-input"
              id="agreeCondition"
              v-model="registerStore.registrationData.termsAccepted"
            />
            <label class="form-check-label" for="agreeCondition">
              {{ $t("common.I agree to all") }}
              <a :href="getLocalizedPath('terms-of-use')">
                {{ $t("common.Terms & Conditions") }}
              </a>
            </label>
          </div>
        </div>

        <button
          type="submit"
          class="submit-button"
          :disabled="
            registerStore.isLoading ||
            !isValidMobile ||
            !registerStore.registrationData.termsAccepted
          "
        >
          {{
            registerStore.isLoading
              ? $t("common.Sending OTP") + "..."
              : $t("common.Continue")
          }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRegisterStore } from "../stores/registerStore";
import { useLocalization } from "@/composables/useLocalization";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const { getLocalizedPath } = useLocalization();

const registerStore = useRegisterStore();
const mobileError = ref("");
const selectedCountry = ref(null);

const MIN_MOBILE_LENGTH = 10;
const MAX_MOBILE_LENGTH = 12;

const sanitizeMobile = (value) => {
  return String(value || "")
    .replace(/\D/g, "")
    .slice(0, MAX_MOBILE_LENGTH);
};

const setMobileError = (cleanedValue, originalValue = "") => {
  const originalDigitCount = String(originalValue || "").replace(/\D/g, "").length;

  if (originalDigitCount > MAX_MOBILE_LENGTH) {
    mobileError.value = t("common.Maximum 12 digits allowed");
    return;
  }

  if (cleanedValue.length > 0 && cleanedValue.length < MIN_MOBILE_LENGTH) {
    mobileError.value = t("common.Mobile number must be at least 10 digits");
    return;
  }

  if (
    cleanedValue.length >= MIN_MOBILE_LENGTH &&
    cleanedValue.length <= MAX_MOBILE_LENGTH
  ) {
    mobileError.value = "";
    return;
  }

  mobileError.value = "";
};

const isValidMobile = computed(() => {
  const mobile = registerStore.registrationData.mobile;
  return /^\d{10,12}$/.test(mobile);
});

const handleCountryChange = (country) => {
  selectedCountry.value = country;

  // Update dial code for display (e.g., +91, +44)
  registerStore.updateRegistrationField("dialCode", country?.dialCode || "+91");

  // Update country code to send to API (e.g., IN, GB)
  registerStore.updateRegistrationField("countryCode", country?.code || "IN");
};

const validateMobile = (event) => {
  const rawValue = event.target.value;
  const cleanedValue = sanitizeMobile(rawValue);

  registerStore.updateRegistrationField("mobile", cleanedValue);
  event.target.value = cleanedValue;

  setMobileError(cleanedValue, rawValue);
};

const handleMobilePaste = (event) => {
  event.preventDefault();

  const pastedText = event.clipboardData?.getData("text") || "";
  const cleanedValue = sanitizeMobile(pastedText);

  registerStore.updateRegistrationField("mobile", cleanedValue);
  event.target.value = cleanedValue;

  setMobileError(cleanedValue, pastedText);
};

const handleSubmit = async () => {
  const mobile = registerStore.registrationData.mobile;

  if (!/^\d{10,12}$/.test(mobile)) {
    mobileError.value = t("common.Please enter a valid mobile number");
    return;
  }

  if (!registerStore.registrationData.termsAccepted) {
    return;
  }

  try {
    await registerStore.sendOtp();
  } catch (error) {
    console.error("Failed to send OTP:", error);
  }
};
</script>

<style scoped>
.register-form {
  width: 100%;
  display: flex;
  flex-direction: column;
}

.form-wrapper {
  flex: 1;
}

.form-header {
  margin-bottom: 36px;
}

.form-title {
  font-size: 32px;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 8px 0;
  line-height: 1.2;
  text-align: left;
}

.form-description {
  font-size: 16px;
  color: #6c757d;
  margin: 0;
  line-height: 1.5;
  text-align: left;
}

.registration-form {
  margin-bottom: 0;
}

.input-group-wrapper {
  margin-bottom: 24px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  color: #6c757d;
}

.mobile-input-group {
  display: flex;
  gap: 8px;
  align-items: stretch;
}

.mobile-input-group :deep(.country-code-select) {
  flex-shrink: 0;
}

.form-input {
  flex: 1;
  height: 54px;
  padding: 0 18px;
  font-size: 16px;
  color: #1a1a1a;
  background-color: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  transition: all 0.2s ease;
  font-family: inherit;
}

.form-input::placeholder {
  color: #adb5bd;
}

.form-input:focus {
  outline: none;
  border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.form-input.border-danger {
  border-color: #dc3545;
}

.input-error {
  display: block;
  color: #dc3545;
  font-size: 13px;
  margin-top: 8px;
  font-weight: 500;
}

.form-group {
  margin-bottom: 20px;
}

.form-check {
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-check-input {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.form-check-label {
  font-size: 14px;
  color: #6c757d;
  cursor: pointer;
}

.form-check-label a {
  color: #0d6efd;
  text-decoration: none;
}

.form-check-label a:hover {
  text-decoration: underline;
}

.submit-button {
  width: 100%;
  height: 54px;
  padding: 0 24px;
  font-size: 17px;
  font-weight: 600;
  color: #fff;
  background-color: #0d6efd;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  margin-top: 12px;
}

.submit-button:hover:not(:disabled) {
  background-color: #0b5ed7;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.submit-button:active:not(:disabled) {
  transform: translateY(0);
}

.submit-button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
  opacity: 0.65;
}

/* Mobile Responsive - Center Text Alignment */
@media (max-width: 767px) {
  .form-header {
    margin-bottom: 28px;
    text-align: center;
  }

  .form-title {
    font-size: 24px;
    text-align: center;
  }

  .form-description {
    font-size: 14px;
    text-align: center;
  }

  .mobile-input-group {
    flex-direction: column;
    gap: 12px;
  }

  .form-input {
    height: 50px;
    padding: 0 16px;
    font-size: 16px;
    border-radius: 8px;
  }

  .submit-button {
    height: 50px;
    font-size: 16px;
    border-radius: 8px;
  }
}

@media (max-width: 374px) {
  .form-title {
    font-size: 22px;
  }

  .form-description {
    font-size: 13px;
  }

  .form-input {
    height: 48px;
    font-size: 15px;
  }

  .submit-button {
    height: 48px;
    font-size: 15px;
  }
}
</style>
