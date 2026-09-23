<template>
  <div class="register-page">
    <div class="progress-bar" v-if="currentStep > 1">
      <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
    </div>

    <AuthLayout>
      <RegisterForm
        v-if="currentStep === 1"
        :form="formData"
        :is-loading="registerStore.isLoading"
        :server-errors="serverErrors"
        @next="handleStepOne"
      />
      <AgentDetails
        v-if="currentStep === 2"
        :form="formData"
        :is-loading="registerStore.isLoading"
        :server-errors="serverErrors"
        @back="currentStep = 1"
        @next="handleStepTwo"
      />
      <AgentUploads
        v-if="currentStep === 3"
        :is-loading="registerStore.isLoading"
        :server-errors="serverErrors"
        @back="currentStep = 2"
        @submit="handleSubmit"
      />
    </AuthLayout>

    <ResultModal
      :show="showResultModal"
      :status="resultData.status"
      :message="resultData.message"
      :title="resultData.title"
      @close="closeResultModal"
    />
  </div>
</template>

<script setup>
// RegisterPage.vue <script setup>

import { ref, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useLocalization } from "@/composables/useLocalization";
import { useRegisterStore } from "../stores/registerStore";

import AuthLayout from "@/modules/AuthorizedAgents/components/AuthLayout.vue";
import RegisterForm from "@/modules/AuthorizedAgents/components/register/RegisterForm.vue";
import AgentDetails from "@/modules/AuthorizedAgents/components/register/AgentDetails.vue";
import AgentUploads from "@/modules/AuthorizedAgents/components/register/AgentUploads.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";

const { t } = useI18n();
const router = useRouter();
const { getLocalizedPath } = useLocalization();
const registerStore = useRegisterStore();

// ── Step state ────────────────────────────────────────
const currentStep = ref(1);
const totalSteps = 3;

const progressPercentage = computed(() =>
  Math.round(((currentStep.value - 1) / (totalSteps - 1)) * 100)
);

// ── Shared form data ──────────────────────────────────
const formData = ref({
  email: "",
  password: "",
  confirmPassword: "",
  fullName: "",
  address: "",
  mobile: "",
  panNumber: "",
  photo: null,
  aadharCard: null,
  upiQrCode: null,
});

// ── Server errors ─────────────────────────────────────
const serverErrors = computed(() => {
  const list = [];
  Object.values(registerStore.errors || {}).forEach((arr) => {
    if (Array.isArray(arr)) list.push(...arr);
  });
  return list;
});

// ── Result modal state ────────────────────────────────
const showResultModal = ref(false);
const resultData = ref({ status: "success", message: "", title: "" });

watch(
  () => registerStore.successMessage,
  (msg) => {
    if (msg) {
      showResult(
        "success",
        t("common.registration_activation_email_message"),
        t("common.registration_successful") + "!"
      );
    }
  }
);

// ── Step 1: Register email → decide which step to resume ──
const handleStepOne = async (data) => {
  formData.value = { ...formData.value, ...data };
  registerStore.clearErrors();

  const result = await registerStore.registerEmail(formData.value);

  if (!result.success) return;

  const flags = result.flags;
  // flags = { user_id, checkAgent, checkAgentDetails, checkAgentDocuments, agent_details_id? }

  // ── Case 1: Email exists + details filled + documents MISSING → Step 3
  if (
    flags.user_id != 0 &&
    flags.checkAgentDetails == 1 &&
    flags.checkAgentDocuments == 0
  ) {
    currentStep.value = 3;
    return;
  }

  // ── Case 2: Email exists but agent details NOT filled → Step 2
  if (flags.user_id != 0 && (flags.checkAgent == 0 || flags.checkAgentDetails == 0)) {
    currentStep.value = 2;
    return;
  }

  // ── Case 3: Everything complete (already fully registered)
  if (
    flags.user_id != 0 &&
    flags.checkAgentDetails == 1 &&
    flags.checkAgentDocuments == 1
  ) {
    showResult(
      "error",
      t("common.registration_already_complete"),
      t("common.Already Registered")
    );
    return;
  }

  // ── Case 4: Fresh registration (user_id == 0 → new account created) → Step 2
  currentStep.value = 2;
};

// ── Step 2: Save agent details via API, then advance ──
const handleStepTwo = async (data) => {
  formData.value = { ...formData.value, ...data };
  registerStore.clearErrors();

  const result = await registerStore.registerAgentDetails(formData.value);
  if (!result.success) return;

  currentStep.value = 3;
};

// ── Step 3: Upload documents via API ──────────────────
const handleSubmit = async (uploadData) => {
  formData.value = { ...formData.value, ...uploadData };
  registerStore.clearErrors();

  await registerStore.registerDocuments(formData.value);
};

// ── Modal helpers ─────────────────────────────────────
const showResult = (status, message, title = "") => {
  resultData.value = { status, message, title };
  showResultModal.value = true;
};

const closeResultModal = () => {
  showResultModal.value = false;
  registerStore.clearSuccessMessage();

  if (resultData.value.status === "success") {
    setTimeout(() => {
      registerStore.resetRegistration();
      currentStep.value = 1;
      formData.value = {
        email: "",
        password: "",
        confirmPassword: "",
        fullName: "",
        address: "",
        mobile: "",
        panNumber: "",
        photo: null,
        aadharCard: null,
        upiQrCode: null,
      };
      // router.push(getLocalizedPath("/authorized-agents/login"));
      router.push({
        name: "AuthorizeAgentsLogin",
      });
    }, 300);
  }
};
</script>

<style scoped>
.register-page {
  position: relative;
  min-height: 100vh;
}

.progress-bar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 4px;
  background-color: #e9ecef;
  z-index: 9999;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
}

@media (max-width: 767px) {
  .progress-bar {
    height: 3px;
  }
}
</style>
