<template>
  <div class="register-page">
    <div class="progress-bar" v-if="registerStore.currentStep > 1">
      <div
        class="progress-fill"
        :style="{ width: registerStore.progressPercentage + '%' }"
      ></div>
    </div>

    <AuthLayout>
      <RegisterForm v-if="registerStore.currentStep === 1" />
      <OtpVerificationForm v-if="registerStore.currentStep === 2" />
      <PasswordCreationForm v-if="registerStore.currentStep === 3" />
      <ShopDetailsForm v-if="registerStore.currentStep === 4" />

      <div class="auth-footer">
        <p>
          {{ $t("common.already_have_an_account") }}?
          <router-link :to="getLocalizedPath('login')">{{
            $t("common.Login")
          }}</router-link>
        </p>
      </div>
    </AuthLayout>

    <!-- Result Modal -->
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
import { ref, watch, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useRegisterStore } from "../stores/registerStore";
import { useLocalization } from "@/composables/useLocalization";
import AuthLayout from "../components/AuthLayout.vue";
import RegisterForm from "../components/RegisterForm.vue";
import OtpVerificationForm from "../components/OtpVerificationForm.vue";
import PasswordCreationForm from "../components/PasswordCreationForm.vue";
import ShopDetailsForm from "../components/ShopDetailsForm.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const router = useRouter();
const { getLocalizedPath } = useLocalization();
const registerStore = useRegisterStore();

// Result Modal state
const showResultModal = ref(false);
const resultData = ref({
  status: "success",
  message: "",
  title: "",
});

// Watch for success message from store
watch(
  () => registerStore.successMessage,

  (newMessage) => {
    console.log("newMessage", newMessage);
    if (newMessage) {
      showResult("success", newMessage, t("common.registration_successful") + "!");
    }
  }
);

const showResult = (status, message, title = "") => {
  resultData.value = {
    status,
    message,
    title,
  };
  showResultModal.value = true;
};

const closeResultModal = () => {
  showResultModal.value = false;
  registerStore.clearSuccessMessage();

  if (resultData.value.status === "success") {
    setTimeout(() => {
      registerStore.resetRegistration();
      router.push(getLocalizedPath("login"));
    }, 300);
  }
};

// Reset registration state when leaving page
onUnmounted(() => {
  // registerStore.resetRegistration()
});
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
  background-color: #007bff;
  background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
  transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
}

/* Mobile-specific styling */
@media (max-width: 767px) {
  .progress-bar {
    height: 3px;
  }
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
</style>
