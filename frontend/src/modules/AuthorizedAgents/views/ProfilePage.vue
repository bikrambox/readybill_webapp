<template>
  <div class="profile-page">
    <!-- Page Header -->
    <div class="page-header mb-4">
      <div class="d-flex align-items-center gap-3">
        <div class="header-avatar">
          <img v-if="avatarPreview" :src="avatarPreview" alt="avatar" />
          <span v-else>{{ initials }}</span>
        </div>
        <div>
          <h4 class="page-title mb-0">{{ form.fullName || "Your Profile" }}</h4>
          <p class="text-muted mb-0 small">Manage your account and agent details</p>
        </div>
      </div>
    </div>

    <!-- Fetch loader -->
    <div v-if="profileStore.isLoading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <form v-else @submit.prevent="handleSubmit" novalidate>
      <div class="row g-4">
        <!-- Left: Account Info -->
        <div class="col-lg-5">
          <ProfileAccountSection
            :form="form"
            :errors="errors"
            @update:form="updateForm"
          />
        </div>

        <!-- Right: Agent Details -->
        <div class="col-lg-7">
          <ProfileAgentSection
            :form="form"
            :errors="errors"
            :photo-preview-url="photoPreviewUrl"
            :aadhar-preview-url="aadharPreviewUrl"
            :upi-preview-url="upiPreviewUrl"
            @update:form="updateForm"
            @avatarChanged="(url) => (avatarPreview = url)"
          />
        </div>
      </div>

      <!-- Save Bar -->
      <div class="save-bar mt-4">
        <div class="d-flex align-items-center justify-content-between">
          <p class="text-muted small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            All changes are saved to your account immediately.
          </p>
          <div class="d-flex gap-2">
            <button
              type="button"
              class="btn btn-outline-secondary px-4"
              :disabled="profileStore.isUpdating"
              @click="resetForm"
            >
              <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
            <button
              type="submit"
              class="btn btn-primary px-5"
              :disabled="profileStore.isUpdating"
            >
              <span
                v-if="profileStore.isUpdating"
                class="spinner-border spinner-border-sm me-2"
                role="status"
              ></span>
              <i v-else class="bi bi-check2-circle me-2"></i>
              {{ profileStore.isUpdating ? "Saving..." : "Save Changes" }}
            </button>
          </div>
        </div>
      </div>

      <!-- Server errors -->
      <div v-if="serverErrors.length" class="alert alert-danger mt-3 rounded-3">
        <ul class="mb-0 ps-3">
          <li v-for="(err, i) in serverErrors" :key="i">{{ err }}</li>
        </ul>
      </div>
    </form>

    <!-- Success Toast -->
    <transition name="toast-pop">
      <div v-if="showSuccess" class="global-toast success-toast">
        <i class="bi bi-check-circle-fill me-2"></i> Profile updated successfully!
      </div>
    </transition>

    <transition name="modal-fade">
      <div
        v-if="showEmailVerifyModal"
        class="rb-modal-backdrop"
        @click.self="showEmailVerifyModal = false"
      >
        <div class="rb-modal-card">
          <div class="rb-modal-icon">
            <i class="bi bi-envelope-check-fill"></i>
          </div>

          <h5 class="rb-modal-title">Verify your new email</h5>

          <p class="rb-modal-text">
            {{ emailVerifyMessage }}
          </p>

          <p v-if="pendingNewEmail" class="rb-modal-email">
            <strong>{{ pendingNewEmail }}</strong>
          </p>

          <div class="rb-modal-actions">
            <button
              type="button"
              class="btn btn-primary px-4"
              @click="showEmailVerifyModal = false"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useProfileStore } from "../stores/profileStore";
import ProfileAccountSection from "../components/profile/ProfileAccountSection.vue";
import ProfileAgentSection from "../components/profile/ProfileAgentSection.vue";

const profileStore = useProfileStore();

// ── Form state ────────────────────────────────────────
const form = ref({
  email: "",
  fullName: "",
  address: "",
  countryCode: "IN",
  dialCode: "+91",
  mobile: "",
  pan: "",
  photo: null,
  aadhar: null,
  upiQr: null,
});

const errors = ref({});
const showSuccess = ref(false);
const avatarPreview = ref(null);
const photoPreviewUrl = ref(null);
const aadharPreviewUrl = ref(null);
const upiPreviewUrl = ref(null);
const originalForm = ref(null);
const showEmailVerifyModal = ref(false);
const emailVerifyMessage = ref("");
const pendingNewEmail = ref("");

// ── Server errors ─────────────────────────────────────
const serverErrors = computed(() => {
  const list = [];
  Object.values(profileStore.errors || {}).forEach((arr) => {
    if (Array.isArray(arr)) list.push(...arr);
  });
  return list;
});

// ── Initials ──────────────────────────────────────────
const initials = computed(() =>
  (form.value.fullName || "U")
    .split(" ")
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase()
);

// ── Map API response → form ───────────────────────────
const mapProfileToForm = (data) => {
  let countryDetails = {};
  try {
    countryDetails = JSON.parse(data.user?.country_details || "{}");
  } catch (_) {}

  return {
    email: data.user?.email ?? "",
    fullName: data.agentDetails?.name ?? "",
    address: data.agentDetails?.address ?? "",
    countryCode: (data.user?.country_code ?? "IN").toUpperCase(),
    dialCode: countryDetails?.dial_code ?? "+91",
    mobile: data.user?.mobile ?? "",
    pan: data.agentDetails?.pan_number ?? "",
    photo: null,
    aadhar: null,
    upiQr: null,
  };
};

// ── Sync preview URLs from profile ───────────────────
const syncPreviewUrls = (p) => {
  photoPreviewUrl.value = p?.photo_url ?? null;
  aadharPreviewUrl.value = p?.aadhar_card_url ?? null;
  upiPreviewUrl.value = p?.qr_code_url ?? null;
  avatarPreview.value = p?.photo_url ?? null;
};

// ── Apply full profile to form + previews ─────────────
const applyProfile = (p) => {
  const mapped = mapProfileToForm(p);
  form.value = { ...mapped };
  originalForm.value = { ...mapped };
  syncPreviewUrls(p);
};

// ── Fetch on mount ────────────────────────────────────
onMounted(async () => {
  const result = await profileStore.fetchProfile();
  if (result.success && profileStore.profile) {
    applyProfile(profileStore.profile);
  }
});

// ── Update form from child emits ──────────────────────
const updateForm = (patch) => {
  form.value = { ...form.value, ...patch };
};

// ── Frontend validation ───────────────────────────────
const validate = () => {
  const e = {};

  if (!form.value.email) {
    e.email = "Email is required.";
  } else if (!/\S+@\S+\.\S+/.test(form.value.email)) {
    e.email = "Invalid email address.";
  }

  if (!form.value.fullName) {
    e.fullName = "Full name is required.";
  }

  if (form.value.pan && !/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(form.value.pan.toUpperCase())) {
    e.pan = "Enter a valid PAN number (e.g. ABCDE1234F).";
  }

  errors.value = e;
  return Object.keys(e).length === 0;
};

// ── Submit ────────────────────────────────────────────
// const handleSubmit = async () => {
//   profileStore.clearErrors();
//   if (!validate()) return;

//   const result = await profileStore.updateProfile(form.value);

//   if (!result.success) {
//     window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
//     return;
//   }

//   // ✅ profileStore.profile is now fully re-fetched inside updateProfile
//   // Just apply the fresh data directly — no merging needed
//   if (profileStore.profile) {
//     applyProfile(profileStore.profile);
//   }

//   showSuccess.value = true;
//   setTimeout(() => (showSuccess.value = false), 3500);
// };

const handleSubmit = async () => {
  profileStore.clearErrors();
  if (!validate()) return;

  const result = await profileStore.updateProfile(form.value);

  if (!result.success) {
    window.scrollTo({ top: document.body.scrollHeight, behavior: "smooth" });
    return;
  }

  if (profileStore.profile) {
    applyProfile(profileStore.profile);
  }

  if (result.data?.email_verification_required) {
    pendingNewEmail.value = result.data?.new_email || form.value.email;
    emailVerifyMessage.value =
      result.message ||
      "Activation link has been sent to your new email address. Please verify it to activate your updated login email.";
    showEmailVerifyModal.value = true;
    return;
  }

  showSuccess.value = true;
  setTimeout(() => (showSuccess.value = false), 3500);
};

// ── Reset ─────────────────────────────────────────────
const resetForm = () => {
  if (originalForm.value) {
    form.value = { ...originalForm.value };
  }
  errors.value = {};
  profileStore.clearErrors();
  syncPreviewUrls(profileStore.profile);
};
</script>

<style scoped>
.profile-page {
  padding: 4px 0;
}
.page-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1a1a2e;
}
.header-avatar {
  width: 58px;
  height: 58px;
  border-radius: 14px;
  background: linear-gradient(135deg, #0d6efd, #6610f2);
  color: #fff;
  font-size: 1.3rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}
.header-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.save-bar {
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 14px;
  padding: 18px 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}
.global-toast {
  position: fixed;
  bottom: 28px;
  right: 28px;
  z-index: 9999;
  padding: 14px 22px;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 500;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}
.success-toast {
  background: #065f46;
  color: #fff;
}
.toast-pop-enter-active,
.toast-pop-leave-active {
  transition: all 0.3s ease;
}
.toast-pop-enter-from,
.toast-pop-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

.rb-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 10000;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.rb-modal-card {
  width: 100%;
  max-width: 460px;
  background: #fff;
  border-radius: 18px;
  padding: 28px 24px 22px;
  box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18);
  text-align: center;
}

.rb-modal-icon {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  background: #ecfdf3;
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  font-size: 1.7rem;
}

.rb-modal-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 10px;
}

.rb-modal-text {
  font-size: 0.95rem;
  line-height: 1.7;
  color: #5b6475;
  margin-bottom: 10px;
}

.rb-modal-email {
  font-size: 0.95rem;
  color: #0f172a;
  background: #f8fafc;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 10px 12px;
  margin: 0 0 18px;
  word-break: break-word;
}

.rb-modal-actions {
  display: flex;
  justify-content: center;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .rb-modal-card,
.modal-fade-leave-to .rb-modal-card {
  transform: translateY(12px) scale(0.98);
}
</style>
