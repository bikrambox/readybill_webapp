<template>
  <div class="shop-form">
    <div class="form-wrapper">
      <div class="form-header">
        <h1 class="form-title">{{ $t("common.Enter Shop Details") }}</h1>
        <p class="form-description">
          {{ $t("common.Complete your shop profile to get started") }}
        </p>
      </div>

      <form @submit.prevent="handleSubmit" class="details-form">
        <FormErrorBox
          v-if="registerStore.errorMessages.length"
          :messages="registerStore.errorMessages"
          @clear="registerStore.clearErrors"
        />

        <!-- Owner Name (Required) -->
        <div class="input-group">
          <input
            type="text"
            class="form-input"
            :placeholder="$t('common.Shop Name') + ' *'"
            v-model="registerStore.registrationData.ownerName"
            required
            autocomplete="name"
            maxlength="250"
          />
        </div>

        <!-- Business Name (Required) -->
        <div class="input-group">
          <input
            type="text"
            class="form-input"
            :placeholder="$t('common.Business Name') + ' *'"
            v-model="registerStore.registrationData.shopName"
            required
            autocomplete="organization"
            maxlength="250"
          />
          <!-- <small class="form-text text-muted">Only letters, numbers, and spaces allowed</small> -->
        </div>

        <!-- Email Address (Optional) -->
        <div class="input-group">
          <input
            type="email"
            class="form-input"
            :placeholder="$t('common.Email Address') + ' (' + $t('common.Optional') + ')'"
            v-model="registerStore.registrationData.email"
            @input="validateEmail"
            autocomplete="email"
            maxlength="250"
          />
          <span v-if="emailError" class="input-error">{{ emailError }}</span>
          <!-- <small class="form-text text-muted"
            >Leave empty or enter 'NA' if not available</small
          > -->
        </div>

        <!-- Shop Address (Required) -->
        <div class="input-group">
          <textarea
            class="form-textarea"
            :placeholder="$t('common.Shop Address') + ' *'"
            v-model="registerStore.registrationData.address"
            rows="3"
            required
            autocomplete="street-address"
          ></textarea>
        </div>

        <!-- GSTIN (Optional) -->
        <div class="input-group">
          <input
            type="text"
            class="form-input"
            :placeholder="$t('common.GSTIN') + '(' + $t('common.Optional') + ')'"
            v-model="registerStore.registrationData.gstin"
            maxlength="15"
            @input="validateGstin"
          />
          <span v-if="gstinError" class="input-error">{{ gstinError }}</span>
        </div>

        <!-- Logo Upload (Optional) -->
        <div class="row mb-3">
          <label class="col-12 col-form-label fw-semibold">
            {{ $t("profile_page.Logo") }} ({{ $t("common.Optional") }})
          </label>
          <div class="col-12">
            <ImagePreview
              :preview="logoPreview"
              :error="logoError"
              @file-selected="handleLogoSelected"
              @delete="handleLogoDelete"
            />
            <!-- <small class="form-text text-muted d-block mt-2">
              {{ $t('common.upload_photo_instruction') }}
            </small> -->
          </div>
        </div>

        <button
          type="submit"
          class="submit-button"
          :disabled="registerStore.isLoading || !isFormValid"
        >
          {{
            registerStore.isLoading
              ? $t("common.Completing") + "..."
              : $t("common.Complete Registration")
          }}
        </button>
      </form>
    </div>

    <div class="form-links">
      <!-- <button
        type="button"
        class="link-back"
        @click="registerStore.previousStep()"
      >
        ← Back
      </button> -->

      <p class="link-terms">
        {{ $t("common.By continuing, you agree to") }}
        <a :href="getLocalizedPath('terms-of-use')" class="link-secondary">{{
          $t("common.Terms & Conditions")
        }}</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRegisterStore } from "../stores/registerStore";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import ImagePreview from "@/modules/GroceryIndia/components/profile/ImagePreview.vue";
import { useI18n } from "vue-i18n";
import { useLocalization } from "@/composables/useLocalization";

const { getLocalizedPath } = useLocalization();

// const { t: $t } = useI18n();
const { t } = useI18n();

const registerStore = useRegisterStore();
const emailError = ref("");
const gstinError = ref("");
const logoError = ref("");
const logoPreview = ref("");

const isFormValid = computed(() => {
  const data = registerStore.registrationData;
  const hasValidBusinessName = /^[a-zA-Z0-9\s]+$/.test(data.shopName);

  return (
    data.shopName &&
    hasValidBusinessName &&
    data.ownerName &&
    data.address &&
    !emailError.value &&
    !gstinError.value &&
    !logoError.value
  );
});

const validateEmail = () => {
  const email = registerStore.registrationData.email;

  if (!email || email.trim() === "" || email.toUpperCase() === "NA") {
    emailError.value = "";
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!emailRegex.test(email)) {
    emailError.value = t("common.Please enter a valid email address");
  } else {
    emailError.value = "";
  }
};

const validateGstin = (event) => {
  const value = event.target.value.toUpperCase();
  registerStore.updateRegistrationField("gstin", value);

  if (value && value.length > 0 && value.length !== 15) {
    gstinError.value = t("common.GSTIN must be exactly 15 characters");
  } else {
    gstinError.value = "";
  }
};

const handleLogoSelected = (file) => {
  logoError.value = "";

  if (!file) {
    return;
  }

  // Validate file type
  const allowedTypes = [
    "image/jpeg",
    "image/png",
    "image/gif",
    "image/jpg",
    "image/heic",
  ];
  if (!allowedTypes.includes(file.type)) {
    logoError.value = t("common.photo_validation");
    return;
  }

  // Validate file size (5MB = 5120KB)
  const maxSize = 5 * 1024 * 1024; // 5MB in bytes
  if (file.size > maxSize) {
    logoError.value = t("common.photo_upload_max_size");
    return;
  }

  // Store file and create preview
  registerStore.updateRegistrationField("logo", file);

  const reader = new FileReader();
  reader.onload = (e) => {
    logoPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const handleLogoDelete = () => {
  registerStore.updateRegistrationField("logo", null);
  logoPreview.value = "";
  logoError.value = "";
};

// const handleSubmit = async () => {
//   // Validate business name format
//   if (!/^[a-zA-Z0-9\s]+$/.test(registerStore.registrationData.shopName)) {
//     return;
//   }

//   if (!isFormValid.value) return;

//   try {
//     await registerStore.completeRegistration();
//   } catch (error) {
//     console.error("Failed to complete registration:", error);
//   }
// };

const handleSubmit = async () => {
  if (!/^[a-zA-Z0-9\s]+$/.test(registerStore.registrationData.shopName)) {
    return;
  }

  if (!isFormValid.value) return;

  try {
    await registerStore.completeRegistration();
  } catch (error) {
    const message =
      error?.response?.data?.message ||
      "Sorry, we are unable to process your request. Please try again after some time.";

    if (!registerStore.errorMessages.length) {
      registerStore.errorMessages = [message];
    }

    console.error("Failed to complete registration:", error);
  }
};
</script>

<style scoped>
.shop-form {
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

.details-form {
  margin-bottom: 0;
}

.input-group {
  margin-bottom: 18px;
}

.row {
  display: flex;
  flex-wrap: wrap;
  margin-right: -15px;
  margin-left: -15px;
}

.col-md-3,
.col-md-9 {
  position: relative;
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
}

.col-form-label {
  padding-top: calc(0.375rem + 1px);
  padding-bottom: calc(0.375rem + 1px);
  margin-bottom: 0;
  font-size: inherit;
  line-height: 1.5;
}

.fw-semibold {
  font-weight: 600 !important;
}

.mb-3 {
  margin-bottom: 1rem !important;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}

@media (min-width: 768px) {
  .col-md-3 {
    flex: 0 0 auto;
    width: 25%;
  }

  .col-md-9 {
    flex: 0 0 auto;
    width: 75%;
  }
}

.form-input {
  width: 100%;
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

.form-textarea {
  width: 100%;
  min-height: 100px;
  padding: 16px 18px;
  font-size: 16px;
  color: #1a1a1a;
  background-color: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  transition: all 0.2s ease;
  resize: vertical;
  line-height: 1.5;
  font-family: inherit;
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: #adb5bd;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.form-text {
  display: block;
  margin-top: 6px;
  font-size: 12px;
}

.d-block {
  display: block !important;
}

.mt-2 {
  margin-top: 0.5rem !important;
}

.input-error {
  display: block;
  color: #dc3545;
  font-size: 13px;
  margin-top: 8px;
  font-weight: 500;
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

.form-links {
  margin-top: 28px;
  text-align: center;
}

.link-back {
  background: none;
  border: none;
  color: #0d6efd;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  margin-bottom: 16px;
  font-family: inherit;
}

.link-back:hover {
  text-decoration: underline;
}

.link-terms {
  font-size: 13px;
  color: #adb5bd;
  margin: 0;
}

.link-secondary {
  color: #0d6efd;
  text-decoration: none;
  font-weight: 500;
}

.link-secondary:hover {
  text-decoration: underline;
}

/* Mobile Responsive */
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

  .col-md-3,
  .col-md-9 {
    width: 100%;
  }

  .col-form-label {
    text-align: left;
    margin-bottom: 8px;
  }

  .form-input {
    height: 50px;
    padding: 0 16px;
    font-size: 16px;
    border-radius: 8px;
  }

  .form-textarea {
    min-height: 90px;
    padding: 14px 16px;
    font-size: 16px;
    border-radius: 8px;
  }

  .submit-button {
    height: 50px;
    font-size: 16px;
    border-radius: 8px;
  }

  .link-back {
    font-size: 14px;
  }

  .form-links {
    margin-top: 20px;
  }
}

@media (max-width: 374px) {
  .form-title {
    font-size: 22px;
  }

  .form-description {
    font-size: 14px;
  }

  .form-input {
    height: 48px;
    font-size: 15px;
  }

  .form-textarea {
    min-height: 80px;
    font-size: 15px;
  }

  .submit-button {
    height: 48px;
    font-size: 15px;
  }
}
</style>
