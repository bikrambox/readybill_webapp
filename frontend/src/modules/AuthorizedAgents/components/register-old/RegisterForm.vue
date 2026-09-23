<template>
  <div>
    <h2 class="auth-title">{{ $t("common.Create Account") }}</h2>
    <p class="auth-subtitle">{{ $t("common.Enter your credentials to get started") }}</p>

    <form @submit.prevent="handleNext">
      <FormErrorBox
        v-if="allErrors.length"
        :messages="allErrors"
        @clear="registerStore.clearErrors"
      />

      <!-- Email -->
      <div class="form-group">
        <label for="email" class="form-label">{{ $t("common.Email") }}</label>
        <input
          id="email"
          type="email"
          class="form-control"
          :placeholder="t('common.Enter your email address')"
          v-model="form.email"
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
          :placeholder="t('common.Enter your password')"
          v-model="form.password"
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

      <!-- Confirm Password -->
      <div class="form-group position-relative">
        <label for="confirmPassword" class="form-label">{{
          $t("common.Confirm Password")
        }}</label>
        <input
          id="confirmPassword"
          :type="showConfirmPassword ? 'text' : 'password'"
          class="form-control"
          :placeholder="t('common.Re-enter your password')"
          v-model="form.confirmPassword"
          autocomplete="off"
        />
        <span class="toggle-password" @click="showConfirmPassword = !showConfirmPassword">
          <svg
            v-if="showConfirmPassword"
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

      <button type="submit" class="btn btn-primary btn-block" :disabled="isLoading">
        <span v-if="isLoading">{{ $t("common.Please wait") }}...</span>
        <span v-else>{{ $t("common.Continue") }}</span>
      </button>
    </form>

    <div class="auth-footer">
      <p>
        {{ $t("common.Already have an account") }}?
        <router-link :to="getLocalizedPath('authorized-agents/login')">{{
          $t("common.Sign In")
        }}</router-link>
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
import { useI18n } from "vue-i18n";
import { useLocalization } from "@/composables/useLocalization";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useRegisterStore } from "@/modules/AuthorizedAgents/stores/registerStore";

const registerStore = useRegisterStore();

const props = defineProps({
  form: { type: Object, required: true },
  isLoading: { type: Boolean, default: false },
  serverErrors: { type: Array, default: () => [] },
});

const emit = defineEmits(["next"]);

const { t } = useI18n();
const { getLocalizedPath } = useLocalization();

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const localErrors = ref([]);

const form = reactive({
  email: props.form.email || "",
  password: props.form.password || "",
  confirmPassword: props.form.confirmPassword || "",
});

// Merge local validation errors + server errors
const allErrors = computed(() => [...localErrors.value, ...props.serverErrors]);

// Clear only local errors; server errors are cleared via store in parent
const clearErrors = () => {
  localErrors.value = [];
};

// Auto-scroll to top when new server errors arrive
watch(
  () => props.serverErrors,
  (errs) => {
    if (errs.length) window.scrollTo({ top: 0, behavior: "smooth" });
  }
);

const handleNext = () => {
  localErrors.value = [];
  const errs = [];

  if (!form.email) {
    errs.push(t("common.Email is required to continue") + ".");
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errs.push(t("common.Please enter a valid email address") + ".");
  }

  if (!form.password) {
    errs.push(t("common.Password is required to continue") + ".");
  } else if (form.password.length < 8) {
    errs.push(t("common.Password must be at least 8 characters") + ".");
  }

  if (!form.confirmPassword) {
    errs.push(t("common.Please confirm your password") + ".");
  } else if (form.password !== form.confirmPassword) {
    errs.push(t("common.Passwords do not match") + ".");
  }

  if (errs.length) {
    localErrors.value = errs;
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
.toggle-password {
  position: absolute;
  right: 16px;
  bottom: 16px;
  cursor: pointer;
  color: #666;
  user-select: none;
  display: flex;
  align-items: center;
  padding: 5px;
}
.toggle-password:hover {
  color: #333;
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
