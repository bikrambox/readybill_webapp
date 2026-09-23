<template>
  <div>
    <h2 class="auth-title">{{ $t("common.Welcome Back") }}</h2>
    <p class="auth-subtitle">{{ $t("common.Sign in to your account to continue") }}</p>

    <form @submit.prevent="handleSubmit">
      <!-- COMMON ERROR BOX (frontend + backend) -->
      <FormErrorBox v-if="allErrors.length" :messages="allErrors" @clear="clearErrors" />

      <div class="form-group">
        <label for="identifier" class="form-label">
          {{ $t("common.Mobile Number") }}
        </label>

        <div style="display: flex; gap: 8px">
          <CountryCodeSelect
            v-model="countryCode"
            :disabled="isFormLocked"
            @change="handleCountryChange"
          />

          <input
            id="identifier"
            type="text"
            class="form-control"
            :placeholder="t('common.Enter your mobile number')"
            v-model="credentials.identifier"
            autocomplete="off"
            :disabled="isFormLocked"
          />
        </div>

        <small v-if="isFormLocked" class="loading-text">
          {{ $t("common.Loading country code") }}...
        </small>
      </div>

      <div class="form-group position-relative">
        <label for="password" class="form-label">{{ $t("common.Password") }}</label>
        <input
          id="password"
          :type="showPassword ? 'text' : 'password'"
          class="form-control"
          :placeholder="t('common.Enter your password')"
          v-model="credentials.password"
          autocomplete="off"
          :disabled="isFormLocked"
        />
        <span
          class="toggle-password"
          :class="{ disabled: isFormLocked }"
          @click="togglePassword"
        >
          <svg
            v-if="showPassword"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
          <svg
            v-else
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
            ></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
          </svg>
        </span>
      </div>

      <div class="form-group form-options">
        <div class="form-check px-0">
          <input
            type="checkbox"
            class="form-check-input"
            id="rememberMe"
            v-model="credentials.remember"
            :disabled="isFormLocked"
          />
          <label class="form-check-label" for="rememberMe">
            {{ $t("common.Remember me") }}
          </label>
        </div>

        <a
          @click.prevent="handleForgotPassword"
          class="forgot-link"
          :class="{ disabled: isFormLocked }"
        >
          {{ $t("common.Forgot Password") }}?
        </a>
      </div>

      <button
        type="submit"
        class="btn btn-primary btn-block"
        :disabled="authStore.isLoading || isFormLocked"
      >
        <span v-if="isFormLocked">{{ $t("common.Please wait") }}...</span>
        <span v-else-if="authStore.isLoading">{{ $t("common.Signing in") }}...</span>
        <span v-else>{{ $t("common.Sign In") }}</span>
      </button>
    </form>

    <div class="auth-footer">
      <p>
        {{ $t("common.dont_have_an_account") }}?
        <router-link :to="getLocalizedPath('register')">
          {{ $t("common.Register Now") }}
        </router-link>
      </p>
      <p class="terms">
        {{ $t("common.By continuing, you agree to") }}
        <a :href="getLocalizedPath('terms-of-use')">
          {{ $t("common.Terms & Conditions") }}
        </a>
      </p>
    </div>

    <ChangePasswordModal
      :show="showChangePasswordModal"
      @close="showChangePasswordModal = false"
      @otp-sent="handleOtpSent"
    />

    <OTPModal
      v-model:show="showOtpModal"
      :mobile="otpMobile"
      :country-code="otpCountryCode"
      purpose="forgot_password"
      @verified="handleOtpVerified"
    />

    <UpdatePasswordModal
      :show="showUpdatePasswordModal"
      :mobile="otpMobile"
      @close="showUpdatePasswordModal = false"
      @success="handlePasswordUpdated"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "../stores/authStore.js";
import { useLocalization } from "@/composables/useLocalization";

import ChangePasswordModal from "../components/ChangePasswordModal.vue";
import OTPModal from "@/modules/GroceryIndia/components/profile/OTPModal.vue";
import UpdatePasswordModal from "./NewPasswordModal.vue";

import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useLocaleStore } from "@/stores/localeStore";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const route = useRoute();
const router = useRouter();

const { getLocalizedPath } = useLocalization();
const authStore = useAuthStore();
const localeStore = useLocaleStore();

const isMobile = ref(false);
const showPassword = ref(false);

const credentials = ref({
  identifier: "",
  password: "",
  remember: false,
});

const showChangePasswordModal = ref(false);
const showOtpModal = ref(false);
const showUpdatePasswordModal = ref(false);
const otpMobile = ref("");
const otpCountryCode = ref("");

const countryCode = ref("");
const selectedCountry = ref(null);
const loginFrom = 2; // web

const countryReady = computed(() => {
  return !!countryCode.value && !!selectedCountry.value?.code;
});

const isFormLocked = computed(() => {
  return !countryReady.value;
});

// Flatten authStore.errors (object) into list for FormErrorBox
const allErrors = computed(() => {
  const list = [];
  const errObj = authStore.errors || {};

  Object.values(errObj).forEach((arr) => {
    if (Array.isArray(arr)) list.push(...arr);
  });

  return list;
});

const handleCountryChange = (country) => {
  selectedCountry.value = country;
};

watch(countryCode, (val) => {
  if (!val) {
    selectedCountry.value = null;
  }
});

const handleSubmit = async () => {
  clearErrors();

  if (isFormLocked.value) {
    authStore.errors = {
      frontend: [t("common.Country code is required to continue") + "."],
    };
    return;
  }

  const frontendErrors = [];

  if (!selectedCountry.value) {
    frontendErrors.push(t("common.Country code is required to continue") + ".");
  }

  if (!countryCode.value) {
    frontendErrors.push(t("common.Country code is required to continue") + ".");
  }

  if (!credentials.value.identifier) {
    frontendErrors.push(t("common.Mobile number is required to continue") + ".");
  }

  if (!credentials.value.password) {
    frontendErrors.push(t("common.Password is required to continue") + ".");
  }

  if (frontendErrors.length) {
    authStore.errors = { frontend: frontendErrors };
    return;
  }

  const result = await authStore.login({
    loginFrom,
    country_code: selectedCountry.value.code,
    mobile: credentials.value.identifier,
    password: credentials.value.password,
    remember: credentials.value.remember,
  });

  if (!result || result.success === false) {
    return;
  }

  await new Promise((resolve) => setTimeout(resolve, 100));

  const country = result.user.country_code || "in";
  const lang = result.user.lang || "en";

  localStorage.setItem("country", country);
  localStorage.setItem("language", lang);

  await router.push(`/${country}/${lang}/grocery/sell`);
};

const clearErrors = () => {
  authStore.errors = {};
};

const togglePassword = () => {
  if (isFormLocked.value) return;
  showPassword.value = !showPassword.value;
};

const handleForgotPassword = () => {
  if (isFormLocked.value) return;
  showChangePasswordModal.value = true;
};

const handleOtpSent = (data) => {
  otpMobile.value = data.mobile;
  otpCountryCode.value = data.countryCode;
  showOtpModal.value = true;
};

const handleOtpVerified = () => {
  showUpdatePasswordModal.value = true;
};

const handlePasswordUpdated = () => {
  console.log("Password updated successfully");
};

const userMobile = ref("");
const handleOtpSuccess = () => {};
const handleOtpError = () => {};
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

.position-relative {
  position: relative;
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

.position-relative .form-control {
  padding-right: 50px;
}

.form-control::placeholder {
  color: #999;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
  outline: none;
}

.form-control:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
  opacity: 0.7;
}

.toggle-password {
  position: absolute;
  right: 16px;
  bottom: 16px;
  cursor: pointer;
  color: #666;
  user-select: none;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
}

.toggle-password:hover {
  color: #333;
}

.toggle-password.disabled {
  pointer-events: none;
  opacity: 0.5;
  cursor: not-allowed;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.form-check {
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-check-input {
  cursor: pointer;
  width: 18px;
  height: 18px;
  margin: 0;
}

.form-check-input:disabled {
  cursor: not-allowed;
}

.form-check-label {
  cursor: pointer;
  font-size: 14px;
  color: #666;
  margin: 0;
  user-select: none;
}

.forgot-link {
  font-size: 14px;
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.forgot-link:hover {
  text-decoration: underline;
  cursor: pointer;
}

.forgot-link.disabled {
  pointer-events: none;
  opacity: 0.5;
  cursor: not-allowed;
  text-decoration: none;
}

.loading-text {
  display: inline-block;
  margin-top: 8px;
  font-size: 13px;
  color: #666;
}

.alert {
  padding: 12px 16px;
  margin-bottom: 20px;
  border-radius: 8px;
  font-size: 14px;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.btn-primary {
  height: 52px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  background-color: #007bff;
  border: none;
  width: 100%;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-primary:active:not(:disabled) {
  transform: translateY(0);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.auth-footer {
  margin-top: 30px;
  text-align: center;
}

.auth-footer p {
  font-size: 14px;
  color: #666;
  margin-bottom: 12px;
}

.auth-footer a {
  color: #007bff;
  text-decoration: none;
  font-weight: 500;
}

.auth-footer a:hover {
  text-decoration: underline;
}

.terms {
  font-size: 12px;
  color: #999;
  margin-top: 16px;
}

/* Mobile Optimizations */
@media (max-width: 767px) {
  .auth-title {
    font-size: 24px;
    margin-bottom: 10px;
  }

  .auth-subtitle {
    font-size: 14px;
    margin-bottom: 30px;
  }

  .form-control {
    height: 50px;
    font-size: 16px;
    padding: 12px 14px;
  }

  .position-relative .form-control {
    padding-right: 48px;
  }

  .toggle-password {
    bottom: 15px;
  }

  .form-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
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

  .form-control {
    height: 48px;
  }

  .btn-primary {
    height: 48px;
  }
}

/* Override browser autofill yellow background */
.form-control:-webkit-autofill,
.form-control:-webkit-autofill:hover,
.form-control:-webkit-autofill:focus,
.form-control:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
  box-shadow: 0 0 0px 1000px #ffffff inset !important;
  -webkit-text-fill-color: #1a1a1a !important;
  transition: background-color 5000s ease-in-out 0s;
}
</style>
