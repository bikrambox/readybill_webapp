<template>
  <nav
    class="readybill-navbar navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm"
  >
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="/">
        <img
          src="@/assets/images/readybill.png"
          alt="ReadyBill Logo"
          height="50"
          class="me-2"
        />
      </a>

      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a
              class="nav-link rb-nav-link"
              :href="getLocalizedPath('features')"
              >{{ $t("common.Features") }}</a
            >
          </li>
          <li class="nav-item">
            <a class="nav-link rb-nav-link" :href="getLocalizedPath('about')">{{
              $t("common.About")
            }}</a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link rb-nav-link"
              :href="getLocalizedPath('contact')"
              >{{ $t("common.Contact") }}</a
            >
          </li>
        </ul>

        <div class="d-flex align-items-center gap-2">
          <LanguageDropdown />

          <!-- UPDATED: Login/Sign Up matching screenshot -->
          <template v-if="!isAuthenticated">
            <!-- <router-link
              :to="getLocalizedPath('login')"
              class="btn btn-link text-decoration-none fw-semibold p-0 me-2 login-link"
            >
              {{ $t("common.Login") }}
            </router-link>
            <router-link
              :to="getLocalizedPath('register')"
              class="btn btn-primary px-4 py-2 fw-semibold rounded-pill shadow-sm"
            >
              {{ $t("common.Sign Up Free") }}
            </router-link> -->

            <a
              :href="getLocalizedPath('login')"
              class="btn btn-link text-decoration-none fw-semibold p-0 me-2 login-link"
            >
              {{ $t("common.Login") }}
            </a>

            <a
              :href="getLocalizedPath('register')"
              class="btn btn-primary px-4 py-2 fw-semibold rounded-pill shadow-sm"
            >
              {{ $t("common.Sign Up Free") }}
            </a>
          </template>

          <!-- Logged In -->
          <div v-else class="dropdown">
            <a
              class="d-flex align-items-center text-reset text-decoration-none p-2 rounded hover-card"
              href="#"
              data-bs-toggle="dropdown"
            >
              <img
                :src="userAvatar"
                :alt="userName"
                class="avatar-img me-2 rounded-circle"
                @error="handleAvatarError"
              />
              <!-- <div class="d-none d-md-block">
                <div class="fw-semibold text-dark">{{ userName }}</div>
                <small class="text-muted">{{ userRole }}</small>
              </div> -->

              <div
                class="d-none d-md-flex flex-column align-items-start text-start lh-sm"
              >
                <span class="text-dark fw-medium small">{{ userName }}</span>
                <span class="text-muted" style="font-size: 0.75rem">{{
                  userRole
                }}</span>
              </div>
            </a>

            <ul class="dropdown-menu shadow-lg border-0 mt-2">
              <li class="px-3 py-2 border-bottom bg-light rounded-top">
                <img
                  :src="userAvatar"
                  :alt="userName"
                  class="dropdown-avatar rounded-circle me-2"
                  @error="handleAvatarError"
                />
                <!-- <div>
                  <div class="fw-bold">{{ userName }}</div>
                  <small class="text-muted">{{ userRole }}</small>
                </div> -->

                <div
                  class="d-none d-md-flex flex-column align-items-start text-start lh-sm"
                >
                  <span class="text-dark fw-medium small">{{ userName }}</span>
                  <span class="text-muted" style="font-size: 0.75rem">{{
                    userRole
                  }}</span>
                </div>
              </li>
              <li>
                <router-link
                  class="dropdown-item fw-medium px-3 py-2"
                  :to="{ name: 'Profile' }"
                  ><i class="bi bi-person me-2 text-primary"></i
                  >Profile</router-link
                >
              </li>
              <li>
                <router-link
                  class="dropdown-item fw-medium px-3 py-2"
                  :to="{ name: 'Setting' }"
                  ><i class="bi bi-gear me-2 text-primary"></i
                  >{{ $t("common.Settings") }}</router-link
                >
              </li>
              <li><hr class="my-1 mx-2" /></li>
              <li>
                <button
                  class="dropdown-item text-danger fw-semibold px-3 py-2"
                  @click="logout"
                >
                  <i class="bi bi-box-arrow-right me-2"></i
                  >{{ $t("common.Logout") }}
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useAuthStore } from "@/modules/Authentication/stores/authStore";
import { useLocalization } from "@/composables/useLocalization";
import LanguageDropdown from "./LanguageDropdown.vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const { getLocalizedPath } = useLocalization();
const authStore = useAuthStore();

const isAuthenticated = computed(() => authStore.isAuthenticated);

console.log("isAuthenticated navbar", isAuthenticated.value);

// Your exact userName logic
const userName = computed(() =>
  authStore.user?.isAdmin == 1
    ? authStore.user?.shop?.name ?? "NA"
    : authStore.user?.staff?.name ?? "NA"
);

const userRole = computed(() =>
  authStore.user?.isAdmin == 1 ? "Owner" : "Staff"
);

const userAvatar = computed(() => {
  if (!authStore.user) {
    return `https://ui-avatars.com/api/?name=Guest&background=0066cc&color=fff&size=128`;
  }

  if (authStore.user.is_logo === 1 && authStore.user.photo_url) {
    return authStore.user.photo_url;
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(
    userName.value
  )}&background=0066cc&color=fff&size=128`;
});

const handleAvatarError = (event) => {
  // Fallback to generic avatar
  event.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(
    userName.value || "User"
  )}&background=6c757d&color=fff&size=128`;
};

onMounted(() => {
  if (authStore.isAuthenticated && authStore.user) {
    authStore.initAuth();
  }

  // console.log(
  //   'onMounted',
  //   authStore.isAuthenticated,
  //   authStore.user
  // );
});

const logout = async () => {
  await authStore.logout();
};
</script>

<style scoped>
.login-link:hover {
  color: #0d6efd !important;
}
.hover-lift:hover,
.hover-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.avatar-img,
.dropdown-avatar {
  width: 36px;
  height: 36px;
  object-fit: cover;
  border: 2px solid #fff;
}

.hover-lift,
.hover-card {
  transition: all 0.2s ease;
}

@media (max-width: 991px) {
  .gap-2 {
    flex-direction: column;
    width: 100%;
    padding: 1rem;
  }
  .btn {
    width: 100%;
    margin-bottom: 0.5rem;
  }
}
</style>
