<template>
  <div class="cp-card">
    <div class="cp-card-header">
      <div class="cp-icon-wrap cp-icon-otp">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <div>
        <h6 class="cp-card-title">Verify OTP</h6>
        <p class="cp-card-sub">
          Enter the OTP sent to your registered email. Valid for
          <strong>{{ otpExpiry }} minutes</strong>.
        </p>
      </div>
    </div>

    <div class="cp-divider"></div>

    <div class="cp-field">
      <label class="cp-label">One-Time Password</label>
      <input
        type="text"
        class="cp-input cp-otp-input"
        :class="{ 'cp-otp-error': errors.otp }"
        v-model="otpInput"
        placeholder="e.g. A1B2C3"
        maxlength="6"
        @keyup.enter="submit"
        :disabled="store.isLoading || isLockedOut"
        autocomplete="one-time-code"
      />
      <p class="cp-error-msg" v-if="errors.otp">{{ errors.otp }}</p>
    </div>

    <!-- Attempts warning -->
    <div
      class="cp-alert cp-alert-warning"
      v-if="attemptsRemaining !== null && attemptsRemaining <= 2 && !isLockedOut"
    >
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ attemptsRemaining }} attempt(s) remaining before lockout.</span>
    </div>

    <!-- Lockout / API error -->
    <div class="cp-alert cp-alert-danger" v-if="store.error">
      <i class="bi bi-slash-circle" v-if="isLockedOut"></i>
      <i class="bi bi-exclamation-circle" v-else></i>
      <span>{{ store.error }}</span>
    </div>

    <!-- Info -->
    <div class="cp-alert cp-alert-info" v-if="!isLockedOut">
      <i class="bi bi-info-circle"></i>
      <span>Your password will be updated once the OTP is verified.</span>
    </div>

    <button
      class="cp-btn cp-btn-primary"
      @click="submit"
      :disabled="store.isLoading || isLockedOut"
    >
      <span v-if="store.isLoading" class="spinner-border spinner-border-sm me-2"></span>
      <i v-else class="bi bi-check2-circle me-2"></i>
      {{ store.isLoading ? "Verifying..." : "Verify & Update Password" }}
    </button>

    <!-- Resend with cooldown -->
    <button
      class="cp-btn cp-btn-ghost mt-2"
      @click="resendOtp"
      :disabled="cooldownLeft > 0 || store.isLoading || isLockedOut"
    >
      <span
        v-if="store.isLoading && isResending"
        class="spinner-border spinner-border-sm me-2"
      ></span>
      <i v-else class="bi bi-arrow-clockwise me-2"></i>
      {{ cooldownLeft > 0 ? `Resend OTP in ${cooldownLeft}s` : "Resend OTP" }}
    </button>

    <button
      class="cp-btn cp-btn-link mt-1"
      @click="emit('back')"
      :disabled="store.isLoading"
    >
      <i class="bi bi-arrow-left me-2"></i>Back
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useChangePasswordStore } from "@/modules/AuthorizedAgents/stores/changePassword";

const props = defineProps({
  password: { type: String, required: true },
  passwordConfirmation: { type: String, required: true },
  initialCooldown: { type: Number, default: 60 },
});
const emit = defineEmits(["verified", "back", "resend"]);
const store = useChangePasswordStore();

const otpInput = ref("");
const errors = ref({});
const cooldownLeft = ref(props.initialCooldown);
const attemptsRemaining = ref(null);
const isLockedOut = ref(false);
const isResending = ref(false);
const otpExpiry = import.meta.env.VITE_OTP_EXPIRY_MINUTES ?? 10;

let cooldownTimer = null;

const startCooldown = (seconds) => {
  cooldownLeft.value = seconds;
  clearInterval(cooldownTimer);
  cooldownTimer = setInterval(() => {
    if (cooldownLeft.value <= 0) {
      clearInterval(cooldownTimer);
      return;
    }
    cooldownLeft.value--;
  }, 1000);
};

onMounted(() => {
  if (props.initialCooldown > 0) startCooldown(props.initialCooldown);
});

onUnmounted(() => {
  clearInterval(cooldownTimer);
  store.error = null;
});

const submit = async () => {
  errors.value = {};
  store.error = null;

  if (!otpInput.value || otpInput.value.trim().length < 6) {
    errors.value.otp = "Please enter the 6-character OTP.";
    return;
  }

  const res = await store.updatePassword(
    props.password,
    props.passwordConfirmation,
    otpInput.value.trim()
  );

  if (res.success) {
    emit("verified");
    return;
  }

  if (res.lockout) {
    isLockedOut.value = true;
    attemptsRemaining.value = null;
    return;
  }

  // Parse remaining attempts from backend message
  const match = res.message?.match(/(\d+) attempt/);
  if (match) attemptsRemaining.value = parseInt(match[1]);

  otpInput.value = ""; // clear on wrong attempt
};

const resendOtp = async () => {
  store.error = null;
  errors.value = {};
  otpInput.value = "";
  attemptsRemaining.value = null;
  isLockedOut.value = false;
  isResending.value = true;

  const res = await store.sendOTP(props.password, props.passwordConfirmation);
  isResending.value = false;

  if (res.success) {
    startCooldown(res.cooldown ?? 60);
  }
};
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
.cp-icon-otp {
  background: #e6f9f0;
  color: #198754;
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
.cp-input {
  display: block;
  width: 100%;
  padding: 10px 14px;
  font-size: 1.2rem;
  font-weight: 700;
  letter-spacing: 0.4em;
  text-transform: uppercase;
  text-align: center;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  outline: none;
  color: #212529;
  background: #fff;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.cp-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}
.cp-input.cp-otp-error {
  border-color: #dc3545;
}
.cp-input:disabled {
  background: #f8f9fa;
  color: #adb5bd;
  cursor: not-allowed;
}
.cp-input::placeholder {
  color: #ced4da;
  letter-spacing: 0.15em;
  font-weight: 400;
  font-size: 0.875rem;
}
.cp-error-msg {
  font-size: 0.775rem;
  color: #dc3545;
  margin: 5px 0 0;
}
.cp-alert {
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 0.8rem;
  margin-bottom: 12px;
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
.cp-alert-warning {
  background: #fffbea;
  border: 1px solid #fde68a;
  color: #92400e;
}
.cp-alert-info {
  background: #f0f4ff;
  border: 1px solid #d0d9f7;
  color: #3730a3;
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
  transition: background 0.18s, opacity 0.18s, color 0.18s;
}
.cp-btn:disabled {
  opacity: 0.55;
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
.cp-btn-ghost {
  background: transparent;
  color: #495057;
  border: 1.5px solid #dee2e6;
}
.cp-btn-ghost:hover:not(:disabled) {
  background: #f8f9fa;
}
.cp-btn-link {
  background: transparent;
  color: #6c757d;
  border: none;
  font-weight: 500;
}
.cp-btn-link:hover:not(:disabled) {
  color: #343a40;
}
.mt-1 {
  margin-top: 6px;
}
.mt-2 {
  margin-top: 8px;
}
@media (max-width: 480px) {
  .cp-card {
    padding: 20px 16px;
    border-radius: 10px;
  }
}
</style>
