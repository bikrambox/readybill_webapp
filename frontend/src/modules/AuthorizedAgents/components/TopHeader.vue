<template>
  <header class="top-header bg-white border-bottom fixed-top">
    <div
      class="d-flex align-items-center justify-content-between w-100 px-3 px-md-4 h-100"
    >
      <!-- Left Section: Logo + Toggle + Search -->
      <div class="d-flex align-items-center gap-2 gap-md-3 flex-grow-1 me-3">
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <button
            class="btn btn-link text-dark p-2 menu-toggle"
            @click="handleToggleSidebar"
            type="button"
          >
            <i class="bi bi-list fs-4"></i>
          </button>

          <a
            class="d-flex align-items-center gap-2 text-decoration-none logo-group"
            href="/"
          >
            <img :src="logoUrl" alt="ReadyBill" class="logo-img" />
            <span class="logo-pipe">|</span>
            <span class="agents-badge">Agents</span>
          </a>
        </div>

        <!-- Desktop Search -->
        <div
          class="d-none d-lg-flex position-relative flex-grow-1 me-3"
          style="max-width: 400px"
        >
          <i
            class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
          ></i>
          <input
            v-model="searchQuery"
            type="text"
            class="form-control ps-5 rounded-pill border"
            placeholder="Search..."
          />
        </div>
      </div>

      <!-- Right Section -->
      <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
        <!-- Help Button -->
        <button
          class="btn btn-link text-muted p-2 d-none d-md-inline-flex"
          title="Help"
          type="button"
        >
          <i class="bi bi-question-circle fs-5"></i>
        </button>

        <!-- Notification Bell -->
        <button
          class="btn btn-link text-muted p-0 position-relative"
          title="Notifications"
          type="button"
        >
          <i class="bi bi-bell fs-5"></i>
        </button>

        <!-- User Dropdown -->
        <div class="dropdown">
          <button
            class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-1 p-md-2"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            type="button"
          >
            <img
              :src="userAvatar"
              alt="User"
              class="rounded-circle"
              style="width: 36px; height: 36px; object-fit: cover"
            />
            <div class="d-none d-md-flex flex-column align-items-start text-start lh-sm">
              <span class="text-dark fw-medium small">{{ userName }}</span>
              <span class="text-muted" style="font-size: 0.75rem">{{ userRole }}</span>
            </div>
          </button>

          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
            <li>
              <router-link class="dropdown-item" to="profile">
                <i class="bi bi-person me-2"></i>Profile
              </router-link>
            </li>
            <li><hr class="dropdown-divider" /></li>
            <li>
              <a class="dropdown-item text-danger" href="#" @click.prevent="handleLogout">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useLoginStore } from "@/modules/AuthorizedAgents/stores/loginStore.js";
import { useProfileStore } from "@/modules/AuthorizedAgents/stores/profileStore.js";
import logoUrl from "@/assets/images/readybill.png";

const emit = defineEmits(["toggleSidebar"]);

const router = useRouter();
const authStore = useLoginStore();
const profileStore = useProfileStore();

const searchQuery = ref("");

onMounted(async () => {
  try {
    await profileStore.fetchProfile();
  } catch (error) {
    console.error("Failed to fetch profile:", error);
  }
});

const handleToggleSidebar = () => {
  emit("toggleSidebar");
};

const userName = computed(() => {
  return (
    profileStore.profile?.name ||
    authStore.user?.name ||
    authStore.user?.username ||
    "Admin"
  );
});

const userRole = computed(() => {
  return profileStore.profile?.role || authStore.user?.role || "Owner";
});

const userAvatar = computed(() => {
  if (profileStore.profile?.photo_url) {
    return profileStore.profile.photo_url;
  }

  if (authStore.user?.photo_url) {
    return authStore.user.photo_url;
  }

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(
    userName.value
  )}&background=0066cc&color=fff`;
});

const handleLogout = async () => {
  try {
    await authStore.logout();
  } catch (error) {
    console.error("Logout failed:", error);
  } finally {
    router.push({ name: "AuthorizeAgents" });
  }
};
</script>

<style scoped>
.top-header {
  height: 70px;
  z-index: 1001;
}

.menu-toggle {
  min-width: 40px;
  height: 40px;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.menu-toggle:hover {
  background-color: #f8f9fa !important;
  color: #0066cc !important;
}

.logo-img {
  width: 130px;
  height: auto;
  object-fit: contain;
}

/* Logo + Agents group */
.logo-group {
  align-items: center;
}

.logo-pipe {
  color: #d0d5dd;
  font-size: 22px;
  font-weight: 200;
  line-height: 1;
  user-select: none;
  margin-top: -1px;
}

.agents-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 10px;
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  border: 1px solid #c7d2fe;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: #4338ca;
  line-height: 1.5;
  box-shadow: 0 1px 4px rgba(99, 102, 241, 0.15);
  transition: box-shadow 0.2s ease;
  white-space: nowrap;
}

.agents-badge:hover {
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.28);
}

/* Mobile: hide agents badge on very small screens to save space */
@media (max-width: 400px) {
  .logo-img {
    width: 100px;
  }

  .logo-pipe,
  .agents-badge {
    display: none;
  }
}

@media (max-width: 576px) {
  .logo-img {
    width: 110px;
  }

  .agents-badge {
    font-size: 10px;
    padding: 2px 8px;
    letter-spacing: 0.05em;
  }
}
</style>
