<template>
  <AuthLayout>
    <!-- Step 0: Login form -->
    <LoginForm v-if="currentView === 'login'" @incomplete="handleIncomplete" />

    <!-- Step 2: Agent Details (resume) -->
    <AgentDetails
      v-else-if="currentView === 'agentDetails'"
      :form="formData"
      :is-loading="registerStore.isLoading"
      :server-errors="serverErrors"
      @next="handleAgentDetailsNext"
    />

    <!-- Step 3: Document Upload (resume) -->
    <AgentUploads
      v-else-if="currentView === 'agentUploads'"
      :is-loading="registerStore.isLoading"
      :server-errors="serverErrors"
      @submit="handleDocumentSubmit"
    />
  </AuthLayout>

  <ResultModal
    :show="showResultModal"
    :status="resultData.status"
    :message="resultData.message"
    :title="resultData.title"
    @close="closeResultModal"
  />
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useLocalization } from "@/composables/useLocalization";
import { useRegisterStore } from "@/modules/AuthorizedAgents/stores/registerStore";
import { useLoginStore } from "@/modules/AuthorizedAgents/stores/loginStore";

import AuthLayout from "@/modules/AuthorizedAgents/components/AuthLayout.vue";
import LoginForm from "@/modules/AuthorizedAgents/components/login/LoginForm.vue";
import AgentDetails from "@/modules/AuthorizedAgents/components/register/AgentDetails.vue";
import AgentUploads from "@/modules/AuthorizedAgents/components/register/AgentUploads.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";

const { t } = useI18n();
const router = useRouter();
const { getLocalizedPath } = useLocalization();
const registerStore = useRegisterStore();
const loginStore = useLoginStore();

// ── View state ────────────────────────────────────────────────────────────────
// 'login' | 'agentDetails' | 'agentUploads'
const currentView = ref("login");

// ── Shared form data ──────────────────────────────────────────────────────────
const formData = ref({
  fullName: "",
  address: "",
  mobile: "",
  countryCode: "+91",
  panNumber: "",
  photo: null,
  aadharCard: null,
  upiQrCode: null,
});

// ── Server errors from registerStore ─────────────────────────────────────────
const serverErrors = computed(() => {
  const list = [];
  Object.values(registerStore.errors || {}).forEach((arr) => {
    if (Array.isArray(arr)) list.push(...arr);
  });
  return list;
});

// ── Result modal ──────────────────────────────────────────────────────────────
const showResultModal = ref(false);
const resultData = ref({ status: "success", message: "", title: "" });

const showResult = (status, message, title = "") => {
  resultData.value = { status, message, title };
  showResultModal.value = true;
};

// ── Called by LoginForm when login returns incomplete ─────────────────────────
// flags = { user_id, agent_details_id?, checkAgentDetails, checkAgentDocuments }
const handleIncomplete = (flags) => {
  // Hydrate registerStore IDs so step APIs work correctly
  // ✅ Fix: store values are plain refs in store, not .value wrapped again
  registerStore.$patch({
    userId: flags.user_id ?? null,
    agentDetailsId: flags.agent_details_id ?? null,
  });

  if (flags.checkAgentDetails == 0) {
    currentView.value = "agentDetails";
    return;
  }

  if (flags.checkAgentDocuments == 0) {
    currentView.value = "agentUploads";
    return;
  }
};

// ── Step 2: Agent Details submitted ──────────────────────────────────────────
const handleAgentDetailsNext = async (data) => {
  // ✅ Fix: registerStore.userId is a ref — access .value correctly
  formData.value = {
    ...formData.value,
    ...data,
    user_id: registerStore.userId,
  };

  registerStore.clearErrors();

  const result = await registerStore.registerAgentDetails(formData.value);
  if (!result.success) return;

  currentView.value = "agentUploads";
};

// ── Step 3: Documents submitted ───────────────────────────────────────────────
const handleDocumentSubmit = async (uploadData) => {
  formData.value = { ...formData.value, ...uploadData };
  registerStore.clearErrors();

  const result = await registerStore.registerDocuments(formData.value);
  if (!result.success) return;

  showResult(
    "success",
    t("common.registration_activation_email_message"),
    t("common.registration_successful") + "!"
  );
};

// ── Modal close ───────────────────────────────────────────────────────────────
const closeResultModal = () => {
  showResultModal.value = false;

  if (resultData.value.status === "success") {
    setTimeout(() => {
      registerStore.resetRegistration();
      loginStore.clearErrors();
      currentView.value = "login";
      router.push(getLocalizedPath("authorized-agents/login"));
    }, 300);
  }
};
</script>
