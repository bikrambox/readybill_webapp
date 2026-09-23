<template>
  <div class="change-password-page">
    <div class="page-header mb-4">
      <h4 class="page-title mb-1"><i class="bi bi-key-fill me-2"></i>Change Password</h4>
      <p class="text-muted small mb-0">Set a new password with OTP verification.</p>
    </div>

    <div class="row">
      <div class="col-xl-5 col-lg-6 col-md-8 col-12">
        <div class="step-tracker mb-4">
          <template v-for="(step, i) in steps" :key="i">
            <div
              class="step-item"
              :class="{ active: currentStep === i + 1, completed: currentStep > i + 1 }"
            >
              <div class="step-bubble">
                <i v-if="currentStep > i + 1" class="bi bi-check-lg"></i>
                <span v-else>{{ i + 1 }}</span>
              </div>
              <span class="step-label">{{ step }}</span>
            </div>
            <div
              v-if="i < steps.length - 1"
              class="step-line"
              :class="{ done: currentStep > i + 1 }"
            ></div>
          </template>
        </div>

        <transition name="slide-fade" mode="out-in">
          <StepRequestOtp v-if="currentStep === 1" key="1" @otpSent="onOtpSent" />
          <StepVerifyOtp
            v-else-if="currentStep === 2"
            key="2"
            :password="password"
            :password-confirmation="passwordConfirmation"
            :initial-cooldown="cooldown"
            @verified="onVerified"
            @back="currentStep = 1"
            @resend="onResend"
          />
          <StepSuccess v-else-if="currentStep === 3" key="3" />
        </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import StepRequestOtp from "@/modules/AuthorizedAgents/components/change-password/StepRequestOtp.vue";
import StepVerifyOtp from "@/modules/AuthorizedAgents/components/change-password/StepVerifyOtp.vue";
import StepSuccess from "@/modules/AuthorizedAgents/components/change-password/StepSuccess.vue";

const currentStep = ref(1);
const password = ref("");
const passwordConfirmation = ref("");
const cooldown = ref(0);
const steps = ["New Password", "Verify OTP", "Done"];

const onOtpSent = ({ password: p, passwordConfirmation: pc, cooldown: cd }) => {
  password.value = p;
  passwordConfirmation.value = pc;
  cooldown.value = cd ?? 60;
  currentStep.value = 2;
};
const onResend = () => {
  currentStep.value = 1;
};
const onVerified = () => {
  currentStep.value = 3;
};
</script>

<style scoped>
.page-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a1a2e;
}
.step-tracker {
  display: flex;
  align-items: center;
}
.step-item {
  display: flex;
  align-items: center;
  gap: 7px;
  flex-shrink: 0;
}
.step-bubble {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #e9ecef;
  color: #adb5bd;
  font-size: 0.72rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.25s;
}
.step-item.active .step-bubble {
  background: #0d6efd;
  color: #fff;
}
.step-item.completed .step-bubble {
  background: #198754;
  color: #fff;
}
.step-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #adb5bd;
  white-space: nowrap;
  transition: color 0.25s;
}
.step-item.active .step-label {
  color: #0d6efd;
}
.step-item.completed .step-label {
  color: #198754;
}
.step-line {
  flex: 1;
  height: 2px;
  background: #e9ecef;
  margin: 0 8px;
  min-width: 20px;
  transition: background 0.25s;
}
.step-line.done {
  background: #198754;
}
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(12px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-12px);
}
@media (max-width: 480px) {
  .step-label {
    display: none;
  }
  .step-line {
    min-width: 12px;
  }
}
</style>
