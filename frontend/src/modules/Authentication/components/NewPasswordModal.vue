<template>
  <teleport to="body">
    <transition name="modal-fade">
      <div
        v-if="show"
        class="modal d-block"
        tabindex="-1"
        role="dialog"
        @click.self="handleClose"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg">
            <!-- Close Button -->
            <button
              type="button"
              class="btn-close position-absolute top-0 end-0 m-3"
              @click="handleClose"
              aria-label="Close"
              :disabled="isLoading"
            ></button>

            <!-- Modal Body -->
            <div class="modal-body text-center p-4 p-md-5">
              <h4 class="modal-title fw-bold mb-3">
                {{ $t("common.Set New Password") }}
              </h4>

              <p class="text-muted mb-4">
                {{ $t("common.Enter your new password") }}
              </p>

              <!-- SUCCESS MESSAGE -->
              <div
                v-if="successMessage"
                class="alert alert-success alert-dismissible fade show text-start mb-3"
              >
                <svg
                  width="20"
                  height="20"
                  class="me-2"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  style="display: inline-block; vertical-align: middle"
                >
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                {{ successMessage }}
              </div>

              <!-- ERROR BOX -->
              <FormErrorBox
                v-if="allErrors.length && !successMessage"
                :messages="allErrors"
                @clear="clearErrors"
              />

              <!-- Form fields - hide when showing success -->
              <div v-if="!successMessage">
                <!-- New Password -->
                <div class="form-group position-relative mb-3">
                  <label for="newPassword" class="form-label text-start w-100">
                    {{ $t("common.New Password") }}
                  </label>
                  <input
                    id="newPassword"
                    :type="showNewPassword ? 'text' : 'password'"
                    class="form-control"
                    :placeholder="t('common.Enter new password')"
                    v-model="password"
                    :disabled="isLoading"
                  />
                  <span
                    class="toggle-password"
                    @click="showNewPassword = !showNewPassword"
                  >
                    <svg
                      v-if="showNewPassword"
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
                <div class="form-group position-relative mb-4">
                  <label for="confirmPassword" class="form-label text-start w-100">
                    {{ $t("common.Confirm Password") }}
                  </label>
                  <input
                    id="confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="form-control"
                    :placeholder="t('common.Confirm new password')"
                    v-model="passwordConfirmation"
                    :disabled="isLoading"
                  />
                  <span
                    class="toggle-password"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
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

                <!-- Action Buttons -->
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                  <button
                    type="button"
                    class="btn btn-success px-4"
                    :disabled="isLoading"
                    @click="handleUpdatePassword"
                  >
                    <span
                      v-if="isLoading"
                      class="spinner-border spinner-border-sm me-2"
                    ></span>
                    {{ $t("common.Update Password") }}
                  </button>

                  <button
                    type="button"
                    class="btn btn-danger px-4"
                    :disabled="isLoading"
                    @click="handleClose"
                  >
                    {{ $t("common.Cancel") }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Backdrop -->
    <transition name="backdrop-fade">
      <div v-if="show" class="modal-backdrop" @click="handleClose"></div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed } from "vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useForgotPasswordStore } from "../stores/forgotPasswordStore.js";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  mobile: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["close", "success"]);

const forgotPasswordStore = useForgotPasswordStore();

const password = ref("");
const passwordConfirmation = ref("");
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const isLoading = ref(false);
const successMessage = ref(""); // ✅ Add success message state

// Flatten errors into array for FormErrorBox
const allErrors = computed(() => {
  const list = [];
  const errObj = forgotPasswordStore.errors || {};
  Object.values(errObj).forEach((arr) => {
    if (Array.isArray(arr)) list.push(...arr);
  });
  return list;
});

const clearErrors = () => {
  forgotPasswordStore.clearErrors();
};

const handleClose = () => {
  if (!isLoading.value) {
    clearErrors();
    password.value = "";
    passwordConfirmation.value = "";
    showNewPassword.value = false;
    showConfirmPassword.value = false;
    successMessage.value = ""; // ✅ Clear success message
    emit("close");
  }
};

const handleUpdatePassword = async () => {
  clearErrors();
  successMessage.value = ""; // ✅ Clear previous success message

  // Frontend validation
  const frontendErrors = [];

  if (!password.value) {
    frontendErrors.push(t("common.Password is required to continue") + ".");
  }

  if (!passwordConfirmation.value) {
    frontendErrors.push(t("common.Password confirmation is required to continue") + ".");
  }

  if (
    password.value &&
    passwordConfirmation.value &&
    password.value !== passwordConfirmation.value
  ) {
    frontendErrors.push(t("common.Passwords do not match") + ".");
  }

  if (password.value && password.value.length < 6) {
    frontendErrors.push(t("common.Password must be at least 6 characters") + ".");
  }

  if (frontendErrors.length) {
    forgotPasswordStore.errors = { frontend: frontendErrors };
    return;
  }

  isLoading.value = true;

  const result = await forgotPasswordStore.updatePassword(
    props.mobile,
    password.value,
    passwordConfirmation.value
  );

  isLoading.value = false;

  if (result.success) {
    // ✅ Show success message
    successMessage.value =
      result.message || t("common.Password updated successfully") + "!";

    // ✅ Wait 2 seconds, then close modal and emit success
    setTimeout(() => {
      emit("success", result.message);
      handleClose();
    }, 2000);
  }
};
</script>

<style scoped>
/* Same styles as before */
.modal {
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 1070;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1065;
}

.modal-content {
  border-radius: 12px;
  overflow: hidden;
}

.modal-title {
  color: #212529;
  font-size: 1.5rem;
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
  justify-content: center;
  padding: 5px;
}

.toggle-password:hover {
  color: #333;
}

.btn {
  min-width: 120px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-success {
  background-color: #198754;
  border-color: #198754;
}

.btn-success:hover:not(:disabled) {
  background-color: #157347;
  border-color: #146c43;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-danger:hover:not(:disabled) {
  background-color: #bb2d3b;
  border-color: #b02a37;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-close {
  z-index: 10;
  opacity: 0.5;
  transition: opacity 0.2s;
}

.btn-close:hover {
  opacity: 1;
}

/* ✅ Success alert styling */
.alert-success {
  background-color: #d1e7dd;
  color: #0f5132;
  border: 1px solid #badbcc;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 15px;
  font-weight: 500;
}

/* Modal Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal-dialog,
.modal-fade-leave-to .modal-dialog {
  transform: scale(0.9) translateY(-20px);
}

.modal-fade-enter-to .modal-dialog,
.modal-fade-leave-from .modal-dialog {
  transform: scale(1) translateY(0);
}

.modal-dialog {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Backdrop Animations */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
  opacity: 0;
}

/* Mobile Responsive */
@media (max-width: 576px) {
  .modal-body {
    padding: 2rem 1.5rem !important;
  }

  .modal-title {
    font-size: 1.25rem;
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

  .btn {
    min-width: 100px;
    font-size: 0.875rem;
  }

  .d-flex.gap-3 {
    gap: 0.75rem !important;
  }
}

@media (max-width: 400px) {
  .d-flex.gap-3 {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>
