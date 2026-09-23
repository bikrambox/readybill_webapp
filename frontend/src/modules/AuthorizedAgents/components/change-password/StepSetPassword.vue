<template>
  <div class="card border-0 shadow-sm rounded-3 p-4">
    <h6 class="fw-bold mb-1">Enter OTP</h6>
    <p class="text-muted small mb-4">
      An OTP was sent to your registered email. It expires in
      <strong>{{ expiry }} minutes</strong>.
    </p>

    <div class="mb-3">
      <label class="form-label small fw-semibold text-secondary">One-Time Password</label>
      <input
        type="text"
        class="form-control text-center fw-bold fs-5 letter-spacing-wide"
        :class="{ 'is-invalid': error }"
        v-model="otpInput"
        placeholder="• • • • • •"
        maxlength="6"
        @keyup.enter="submit"
        :disabled="store.isLoading"
      />
      <div class="invalid-feedback">{{ error }}</div>
    </div>

    <!-- API Error -->
    <div v-if="store.error" class="alert alert-danger py-2 small mb-3">
      <i class="bi bi-exclamation-circle me-2"></i>{{ store.error }}
    </div>

    <button
      class="btn btn-primary w-100 mb-2"
      @click="submit"
      :disabled="store.isLoading"
    >
      <span v-if="store.isLoading" class="spinner-border spinner-border-sm me-2"></span>
      <i v-else class="bi bi-check2 me-2"></i>
      {{ store.isLoading ? "Verifying..." : "Verify & Update Password" }}
    </button>

    <button class="btn btn-link btn-sm w-100 text-muted" @click="emit('back')">
      <i class="bi bi-arrow-left me-1"></i>Back
    </button>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useChangePasswordStore } from "@/modules/AuthorizedAgents/stores/changePassword";

const props = defineProps({
  password: String,
  passwordConfirmation: String,
});
const emit = defineEmits(["verified", "back"]);
const store = useChangePasswordStore();

const otpInput = ref("");
const error = ref("");
const expiry = import.meta.env.VITE_OTP_EXPIRY_MINUTES ?? 10;

const submit = async () => {
  error.value = "";
  if (!otpInput.value || otpInput.value.length < 6) {
    error.value = "Please enter the 6-character OTP.";
    return;
  }

  const res = await store.updatePassword(
    props.password,
    props.passwordConfirmation,
    otpInput.value
  );
  if (res.success) {
    emit("verified");
  }
};
</script>

<style scoped>
.letter-spacing-wide {
  letter-spacing: 0.4em;
}
</style>
