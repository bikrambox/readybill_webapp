<template>
  <div class="cp-card">
    <div class="cp-card-header">
      <div class="cp-icon-wrap">
        <i class="bi bi-lock-fill"></i>
      </div>
      <div>
        <h6 class="cp-card-title">New Password</h6>
        <p class="cp-card-sub">
          Enter your new password. An OTP will be sent to verify before changing.
        </p>
      </div>
    </div>

    <div class="cp-divider"></div>

    <div class="cp-field">
      <label class="cp-label">New Password</label>
      <div class="cp-input-wrap" :class="{ 'cp-input-error': errors.password }">
        <input
          :type="showNew ? 'text' : 'password'"
          class="cp-input"
          v-model="newPassword"
          placeholder="Enter new password"
          :disabled="store.isLoading"
        />
        <button type="button" class="cp-eye" @click="showNew = !showNew" tabindex="-1">
          <i :class="showNew ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
        </button>
      </div>
      <p class="cp-error-msg" v-if="errors.password">{{ errors.password }}</p>
    </div>

    <div class="cp-field">
      <label class="cp-label">Confirm Password</label>
      <div class="cp-input-wrap" :class="{ 'cp-input-error': errors.confirm }">
        <input
          :type="showConfirm ? 'text' : 'password'"
          class="cp-input"
          v-model="confirmPassword"
          placeholder="Re-enter new password"
          @keyup.enter="submit"
          :disabled="store.isLoading"
        />
        <button
          type="button"
          class="cp-eye"
          @click="showConfirm = !showConfirm"
          tabindex="-1"
        >
          <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
        </button>
      </div>
      <p class="cp-error-msg" v-if="errors.confirm">{{ errors.confirm }}</p>
      <p class="cp-match-msg" v-else-if="confirmPassword">
        <i
          :class="
            newPassword === confirmPassword
              ? 'bi bi-check-circle-fill text-success'
              : 'bi bi-x-circle-fill text-danger'
          "
        ></i>
        {{
          newPassword === confirmPassword ? "Passwords match" : "Passwords do not match"
        }}
      </p>
    </div>

    <div class="cp-rules">
      <div
        v-for="r in rules"
        :key="r.label"
        class="cp-rule"
        :class="{ met: r.check(newPassword) }"
      >
        <i :class="r.check(newPassword) ? 'bi bi-check-circle-fill' : 'bi bi-circle'"></i>
        {{ r.label }}
      </div>
    </div>

    <div class="cp-alert cp-alert-danger" v-if="store.error">
      <i class="bi bi-exclamation-circle"></i>
      <span>{{ store.error }}</span>
    </div>

    <button class="cp-btn cp-btn-primary" @click="submit" :disabled="store.isLoading">
      <span v-if="store.isLoading" class="spinner-border spinner-border-sm me-2"></span>
      <i v-else class="bi bi-send me-2"></i>
      {{ store.isLoading ? "Sending OTP..." : "Send OTP" }}
    </button>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from "vue";
import { useChangePasswordStore } from "@/modules/AuthorizedAgents/stores/changePassword";

const emit = defineEmits(["otpSent"]);
const store = useChangePasswordStore();

const newPassword = ref("");
const confirmPassword = ref("");
const showNew = ref(false);
const showConfirm = ref(false);
const errors = ref({});

const rules = [
  { label: "At least 8 characters", check: (p) => p.length >= 8 },
  { label: "One uppercase letter (A–Z)", check: (p) => /[A-Z]/.test(p) },
  { label: "One number (0–9)", check: (p) => /[0-9]/.test(p) },
  { label: "One special character (!@#…)", check: (p) => /[^A-Za-z0-9]/.test(p) },
];

const validate = () => {
  const e = {};
  if (!newPassword.value) e.password = "Password is required.";
  else if (newPassword.value.length < 8) e.password = "Minimum 8 characters.";
  if (newPassword.value !== confirmPassword.value) e.confirm = "Passwords do not match.";
  errors.value = e;
  return !Object.keys(e).length;
};

const submit = async () => {
  store.error = null;
  if (!validate()) return;
  const res = await store.sendOTP(newPassword.value, confirmPassword.value);
  if (res.success) {
    emit("otpSent", {
      password: newPassword.value,
      passwordConfirmation: confirmPassword.value,
      cooldown: res.cooldown ?? 60,
    });
  }
};

onUnmounted(() => {
  store.error = null;
});
</script>

<style scoped>
.cp-card {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}
.cp-card-header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}
.cp-icon-wrap {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #e8f0fe;
  color: #0d6efd;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.cp-card-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0 0 3px;
}
.cp-card-sub {
  font-size: 0.8rem;
  color: #6c757d;
  margin: 0;
  line-height: 1.5;
}
.cp-divider {
  height: 1px;
  background: #f1f3f5;
  margin: 20px 0;
}
.cp-field {
  margin-bottom: 18px;
}
.cp-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 6px;
}
.cp-input-wrap {
  display: flex;
  align-items: center;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.cp-input-wrap:focus-within {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}
.cp-input-wrap.cp-input-error {
  border-color: #dc3545;
}
.cp-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 9px 12px;
  font-size: 0.875rem;
  color: #212529;
  background: transparent;
}
.cp-input::placeholder {
  color: #adb5bd;
}
.cp-input:disabled {
  background: #f8f9fa;
  color: #adb5bd;
  cursor: not-allowed;
}
.cp-eye {
  background: none;
  border: none;
  padding: 0 12px;
  color: #adb5bd;
  cursor: pointer;
  font-size: 0.9rem;
  flex-shrink: 0;
  transition: color 0.15s;
}
.cp-eye:hover {
  color: #0d6efd;
}
.cp-error-msg {
  font-size: 0.775rem;
  color: #dc3545;
  margin: 5px 0 0;
}
.cp-match-msg {
  font-size: 0.775rem;
  margin: 5px 0 0;
  display: flex;
  align-items: center;
  gap: 5px;
}
.cp-rules {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 7px;
  margin-bottom: 20px;
}
.cp-rule {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.78rem;
  color: #adb5bd;
  transition: color 0.2s;
}
.cp-rule.met {
  color: #198754;
}
.cp-alert {
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.8rem;
  margin-bottom: 16px;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  line-height: 1.5;
}
.cp-alert-danger {
  background: #fff5f5;
  border: 1px solid #f5c6cb;
  color: #842029;
}
.cp-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.18s, opacity 0.18s;
}
.cp-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}
.cp-btn-primary {
  background: #0d6efd;
  color: #fff;
}
.cp-btn-primary:hover:not(:disabled) {
  background: #0b5ed7;
  color: #fff;
}
@media (max-width: 480px) {
  .cp-card {
    padding: 20px 16px;
    border-radius: 10px;
  }
}
</style>
