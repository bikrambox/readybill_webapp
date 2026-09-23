<template>
  <div class="container-fluid p-0">
    <!-- Header with Breadcrumb -->
    <div
      class="d-flex justify-content-between align-items-center mb-4 px-4 pt-4"
    >
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="sell" class="text-decoration-none">{{
                $t("common.Home")
              }}</a>
            </li>
            <!-- <li class="breadcrumb-item">
              <a href="/profile" class="text-decoration-none">{{ $t('common.Profile') }}</a>
            </li> -->
            <li class="breadcrumb-item active" aria-current="page">
              {{ $t("common.Change Password") }}
            </li>
          </ol>
        </nav>
        <h2 class="mb-0 mt-2 fw-bold">{{ $t("common.Change Password") }}</h2>
      </div>
    </div>

    <!-- Content -->
    <div class="px-4 pb-4">
      <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
              <!-- Step 1: OTP Verification -->
              <VerifyOtpSection
                v-if="!isOtpVerified"
                :mobile="mobile"
                :country-code="countryCode"
                @verified="handleOtpVerified"
              />

              <!-- Step 2: Change Password Form -->
              <ChangePasswordForm
                v-else
                :mobile="mobile"
                @success="handlePasswordChanged"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useChangePasswordStore } from "@/modules/Authentication/stores/changePassword";
import VerifyOtpSection from "../components/changePassword/VerifyOtpSection.vue";
import ChangePasswordForm from "../components/changePassword/ChangePasswordForm.vue";
import Footer from "@/modules/GroceryIndia/components/Footer.vue";

const { t } = useI18n();
const router = useRouter();
const changePasswordStore = useChangePasswordStore();

const mobile = ref("");
const countryCode = ref("IN");
const isOtpVerified = ref(false);

onMounted(() => {
  // Get mobile and country code from store
  mobile.value = changePasswordStore.getPasswordChangeMobile();
  countryCode.value = changePasswordStore.getPasswordChangeCountryCode();

  // If no mobile data in store, redirect back to profile
  if (!mobile.value) {
    router.push({ name: "Profile" });
  }
});

const handleOtpVerified = () => {
  isOtpVerified.value = true;
};

const handlePasswordChanged = () => {
  // Clear password change data
  changePasswordStore.clearPasswordChangeData();

  // Redirect back to profile
  router.push({
    name: "Profile",
  });
};
</script>

<style scoped>
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
