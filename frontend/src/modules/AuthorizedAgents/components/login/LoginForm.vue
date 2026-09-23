<template>
  <div>
    <h2 class="auth-title">{{ $t("common.Welcome Back") }}</h2>
    <p class="auth-subtitle">{{ $t("common.Sign in to your account to continue") }}</p>

    <form @submit.prevent="handleSubmit">
      <FormErrorBox v-if="allErrors.length" :messages="allErrors" @clear="clearErrors" />

      <!-- Email -->
      <div class="form-group">
        <label for="identifier" class="form-label">
          {{ $t("common.Email") }}
        </label>
        <input
          id="identifier"
          type="text"
          class="form-control"
          :class="{ 'field-error': fieldErrors.identifier }"
          :placeholder="t('common.Enter your email address')"
          v-model="credentials.identifier"
          autocomplete="off"
        />
      </div>

      <!-- Password -->
      <div class="form-group position-relative">
        <label for="password" class="form-label">{{ $t("common.Password") }}</label>
        <input
          id="password"
          :type="showPassword ? 'text' : 'password'"
          class="form-control"
          :class="{ 'field-error': fieldErrors.password }"
          :placeholder="t('common.Enter your password')"
          v-model="credentials.password"
          autocomplete="off"
        />
        <span class="toggle-password" @click="showPassword = !showPassword">
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

      <!-- Remember Me + Forgot Password -->
      <div class="form-group form-options">
        <div class="form-check px-0">
          <input
            type="checkbox"
            class="form-check-input"
            id="rememberMe"
            v-model="credentials.remember"
          />
          <label class="form-check-label" for="rememberMe">
            {{ $t("common.Remember me") }}
          </label>
        </div>
        <a @click.prevent="handleChangePasswordClick" class="forgot-link">
          {{ $t("common.Forgot Password") }}?
        </a>
      </div>

      <button
        type="submit"
        class="btn btn-primary btn-block"
        :disabled="loginStore.isLoading"
      >
        <span v-if="loginStore.isLoading">{{ $t("common.Signing in") }}...</span>
        <span v-else>{{ $t("common.Sign In") }}</span>
      </button>
    </form>

    <div class="auth-footer">
      <p>
        {{ $t("common.dont_have_an_account") }}?
        <router-link :to="getLocalizedPath('authorized-agents/register')">
          {{ $t("common.Register Now") }}
        </router-link>
      </p>
      <p class="terms">
        {{ $t("common.By continuing, you agree to") }}
        <a :href="getLocalizedPath('terms-of-use')">{{
          $t("common.Terms & Conditions")
        }}</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useLocalization } from "@/composables/useLocalization";
import { useLoginStore } from "@/modules/AuthorizedAgents/stores/loginStore";
import { useRegisterStore } from "@/modules/AuthorizedAgents/stores/registerStore";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useAgentValidation } from "@/composables/useAgentValidation";

const { validateEmail, validatePassword } = useAgentValidation();

const { t } = useI18n();
const router = useRouter();
const { getLocalizedPath } = useLocalization();
const loginStore = useLoginStore();
const registerStore = useRegisterStore();

const showPassword = ref(false);

const credentials = ref({
  identifier: "",
  password: "",
  remember: false,
});

const localErrors = ref([]);

// Per-field red border flags
const fieldErrors = reactive({
  identifier: false,
  password: false,
});

const emit = defineEmits(["incomplete"]);

// Merge local + server errors for FormErrorBox
const allErrors = computed(() => {
  const serverList = [];
  Object.values(loginStore.errors || {}).forEach((arr) => {
    if (Array.isArray(arr)) serverList.push(...arr);
  });
  return [...localErrors.value, ...serverList];
});

// Clear all errors and red borders
const clearErrors = () => {
  localErrors.value = [];
  loginStore.clearErrors();
  fieldErrors.identifier = false;
  fieldErrors.password = false;
};

// Clear each field's red border as soon as the user starts retyping
watch(
  () => credentials.value.identifier,
  () => {
    if (fieldErrors.identifier) fieldErrors.identifier = false;
  }
);
watch(
  () => credentials.value.password,
  () => {
    if (fieldErrors.password) fieldErrors.password = false;
  }
);

// Scroll to top when server errors arrive (e.g. 403 not activated)
watch(
  () => loginStore.errors,
  (errs) => {
    if (errs && Object.keys(errs).length) {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  }
);

const handleSubmit = async () => {
  // Reset everything before re-validating
  localErrors.value = [];
  loginStore.clearErrors();
  fieldErrors.identifier = false;
  fieldErrors.password = false;

  const identifierErr = validateEmail(credentials.value.identifier);
  const passwordErr = validatePassword(credentials.value.password);

  if (identifierErr) fieldErrors.identifier = true;
  if (passwordErr) fieldErrors.password = true;

  const errs = [identifierErr, passwordErr].filter(Boolean);

  if (errs.length) {
    localErrors.value = errs;
    window.scrollTo({ top: 0, behavior: "smooth" });
    // return;
  }

  const result = await loginStore.login(credentials.value);

  // ── Any failure (403 not activated, wrong credentials, etc.) ──────────────
  // loginStore.errors is already populated by _handleError in the store
  // FormErrorBox picks it up via allErrors computed — just scroll and stay
  if (!result.success) {
    window.scrollTo({ top: 0, behavior: "smooth" });
    return;
  }

  // ── Incomplete registration → emit flags to LoginPage ─────────────────────
  if (result.incomplete) {
    emit("incomplete", result.flags);
    return;
  }

  // ── Fully registered → Dashboard ──────────────────────────────────────────
  router.push(getLocalizedPath("authorized-agents/dashboard"));
};

const handleChangePasswordClick = () => {
  // TODO: open forgot password modal when ready
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

/* ── Red border only ── */
.form-control.field-error {
  border-color: #dc3545;
}
.form-control.field-error:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.15);
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
  cursor: pointer;
}
.forgot-link:hover {
  text-decoration: underline;
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
.form-control:-webkit-autofill,
.form-control:-webkit-autofill:hover,
.form-control:-webkit-autofill:focus,
.form-control:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0px 1000px #ffffff inset !important;
  box-shadow: 0 0 0px 1000px #ffffff inset !important;
  -webkit-text-fill-color: #1a1a1a !important;
  transition: background-color 5000s ease-in-out 0s;
}
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
</style>
