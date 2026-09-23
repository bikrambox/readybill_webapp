<template>
  <div class="container-fluid p-0">
    <!-- Header with Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-4 pt-4">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="sell" class="text-decoration-none">{{ $t("common.Home") }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ $t("common.Profile") }}
            </li>
          </ol>
        </nav>
        <h2 class="mb-0 mt-2 fw-bold">{{ $t("common.Profile") }}</h2>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="px-4 pb-4">
      <div class="row">
        <div class="col-xl-8">
          <!-- Profile Form Card -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
              <ProfileForm
                ref="profileFormRef"
                :user-data="userData"
                :is-loading="isLoading"
                @submit="handleProfileUpdate"
                @change-mobile="handleChangeMobileClick"
              />
            </div>
          </div>

          <!-- Delete Account Card -->
          <div v-if="userData.isAdmin" class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <DeleteAccount
                :user-id="userData.user_id"
                :mobile="userData.mobile"
                :country-code="userData.country_code || 'IN'"
                @show-otp="handleShowDeleteOTP"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Mobile OTP Modal -->
    <OTPModal
      v-model:show="showChangeMobileOTPModal"
      :mobile="newMobileData.mobile"
      :country-code="newMobileData.country_code"
      :user-id="userData.user_id"
      purpose="change_mobile"
      @verified="handleMobileOTPVerified"
    />

    <!-- Delete Account OTP Modal -->
    <OTPModal
      v-model:show="showDeleteOTPModal"
      :mobile="userData.mobile"
      :user-id="userData.user_id"
      purpose="delete_account"
      @verified="handleDeleteOTPVerified"
    />

    <!-- Change Mobile Number Modal -->
    <ChangeMobileNumberModal
      v-model:show="showChangeMobileModal"
      :user-id="userData.user_id"
      @success="handleMobileChange"
    />

    <!-- Result Modal -->
    <ResultModal
      :show="showResultModal"
      :status="resultData.status"
      :message="resultData.message"
      :title="resultData.title"
      @close="closeResultModal"
    />

    <!-- Loading Overlay -->
    <!-- <div v-if="isLoading" class="loading-overlay">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
      </div>
    </div> -->

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useProfileStore } from "../stores/profile.js";

import ProfileForm from "../components/profile/ProfileForm.vue";
import DeleteAccount from "../components/profile/DeleteAccount.vue";
import ChangeMobileNumberModal from "../components/profile/ChangeMobileNumberModal.vue";
import OTPModal from "../components/profile/OTPModal.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

const { t } = useI18n();
const profileStore = useProfileStore();

// Local state
const showChangeMobileModal = ref(false);
const showChangeMobileOTPModal = ref(false);
const showDeleteOTPModal = ref(false);
const newMobileData = ref({
  mobile: "",
  country_code: "IN",
});
const profileFormRef = ref(null);

// Result Modal state
const showResultModal = ref(false);
const resultData = ref({
  status: "success",
  message: "",
  title: "",
});

// Computed from store
const userData = computed(() => profileStore.userData);
const isLoading = computed(() => profileStore.isLoading);
const subscriptionError = computed(() => profileStore.subscriptionError);

const clearError = () => {
  profileStore.clearSubscriptionError();
};

const showResult = (status, message, title = "") => {
  resultData.value = {
    status,
    message,
    title,
  };
  showResultModal.value = true;
};

// const closeResultModal = () => {
//   showResultModal.value = false

//   if (resultData.value.status === 'success') {
//     setTimeout(() => {
//       window.location.reload()
//     }, 300)
//   }
// }

const closeResultModal = async () => {
  const wasSuccess = resultData.value.status === "success";
  showResultModal.value = false;

  if (wasSuccess) {
    // Just refresh the profile data, no page reload needed
    await profileStore.fetchUserProfile();
  }
};

const handleProfileUpdate = async (formData) => {
  const result = await profileStore.updateProfile(formData);

  if (result.success) {
    showResult(
      "success",
      result.message || t("common.Profile updated successfully"),
      "Success!"
    );
  } else {
    if (result.errors && Object.keys(result.errors).length > 0) {
      if (profileFormRef.value) {
        profileFormRef.value.handleBackendErrors(result.errors);
      }
    } else if (result.errorMessages && result.errorMessages.length > 0) {
      showResult("error", result.errorMessages[0], t("common.Validation Error"));
    } else {
      showResult("error", t("common.An error occurred while updating profile"), "Error");
    }
  }
};

const handleChangeMobileClick = () => {
  showChangeMobileModal.value = true;
};

const handleMobileChange = (data) => {
  newMobileData.value = data;
  showChangeMobileModal.value = false;
  showChangeMobileOTPModal.value = true;
};

const handleMobileOTPVerified = async () => {
  showChangeMobileOTPModal.value = false;

  showResult("success", t("Mobile number updated successfully"), "Success!");

  await profileStore.fetchUserProfile();
};

const handleShowDeleteOTP = () => {
  showDeleteOTPModal.value = true;
};

const handleDeleteOTPVerified = () => {
  showDeleteOTPModal.value = false;

  setTimeout(() => {
    window.location.href = window.base_url || "/";
  }, 500);
};

onMounted(() => {
  profileStore.fetchUserProfile();
});
</script>

<style scoped>
/* Clean modern design */
.breadcrumb {
  font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "/";
  color: #6c757d;
}

.breadcrumb-item a {
  color: #6c757d;
}

.breadcrumb-item a:hover {
  color: #0d6efd;
}

.breadcrumb-item.active {
  color: #212529;
}

h2 {
  font-size: 1.75rem;
  color: #212529;
}

.card {
  border-radius: 8px;
  transition: box-shadow 0.3s ease;
}

.card:hover {
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

@media (max-width: 768px) {
  .px-4 {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  h2 {
    font-size: 1.5rem;
  }
}
</style>
