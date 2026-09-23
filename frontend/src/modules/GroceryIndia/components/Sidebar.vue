<template>
  <div>
    <!-- Sidebar -->
    <transition
      :name="isMobile ? 'sidebar-mobile' : 'sidebar-desktop'"
      @before-enter="onBeforeEnter"
      @enter="onEnter"
      @after-enter="onAfterEnter"
      @before-leave="onBeforeLeave"
      @leave="onLeave"
      @after-leave="onAfterLeave"
    >
      <div
        v-if="isOpen || !isMobile"
        class="sidebar"
        :class="{
          collapsed: !isOpen && !isMobile,
          'mobile-sidebar': isMobile,
        }"
      >
        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
          <a
            v-if="!isSubscriptionExpired"
            class="nav-item"
            :class="{ active: isActiveRoute('sell') }"
            @click.prevent="handleNavClick('sell')"
            title="Quick Sell"
          >
            <i class="bi bi-lightning"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Quick Sell") }}</span>
            </transition>
          </a>

          <a
            v-if="!isSubscriptionExpired"
            class="nav-item"
            @click.prevent="handleNavClick('refund')"
            :class="{ active: isActiveRoute('refund') }"
            title="Refund"
          >
            <i class="bi bi-arrow-counterclockwise"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Refund") }}</span>
            </transition>
          </a>

          <!-- <a
            v-if="isAdmin && !isSubscriptionExpired"
            class="nav-item"
            :class="{ active: isActiveRoute('add-inventory') }"
            @click.prevent="handleNavClick('add-inventory')"
            title="Add Inventory"
          >
            <i class="bi bi-plus-circle"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{
                $t("common.Add Inventory")
              }}</span>
            </transition>
          </a> -->

          <a
            v-if="!isSubscriptionExpired"
            class="nav-item"
            :class="{
              active: isActiveRoute('inventory') || isActiveRoute('add-inventory'),
            }"
            @click.prevent="handleNavClick('inventory')"
            title="Inventory"
          >
            <i class="bi bi-pencil-square"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Inventory") }}</span>
            </transition>
          </a>

          <a
            v-if="!isSubscriptionExpired"
            class="nav-item"
            :class="{
              active: isActiveRoute('transactions') || isActiveRoute('generate-report'),
            }"
            @click.prevent="handleNavClick('transactions')"
            title="Transaction"
          >
            <i class="bi bi-arrow-left-right"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Transaction") }}</span>
            </transition>
          </a>

          <a
            v-if="isAdmin && !isSubscriptionExpired"
            class="nav-item"
            :class="{
              active: isActiveRoute('add-employee') || isActiveRoute('employees'),
            }"
            @click.prevent="handleNavClick('employees')"
            title="Employees"
          >
            <i class="bi bi-people"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Employees") }}</span>
            </transition>
          </a>

          <a
            v-if="isAdmin && !isSubscriptionExpired"
            class="nav-item"
            :class="{ active: isActiveRoute('scan-product') }"
            @click.prevent="handleNavClick('scan-product')"
            title="Settings"
          >
            <i class="bi bi-upc-scan"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{
                $t("common.Scan Product")
              }}</span>
            </transition>
          </a>

          <a
            v-if="isAdmin && !isSubscriptionExpired"
            class="nav-item"
            :class="{ active: isActiveRoute('setting') }"
            @click.prevent="handleNavClick('setting')"
            title="Settings"
          >
            <i class="bi bi-gear"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Settings") }}</span>
            </transition>
          </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
          <a
            class="nav-item"
            :class="{ active: isActiveRoute('profile') }"
            @click.prevent="handleNavClick('profile')"
            title="Account"
          >
            <i class="bi bi-person-circle"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Account") }}</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('change-password') }"
            @click.prevent="handleChangePasswordClick"
            title="Change Password"
          >
            <i class="bi bi-key"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{
                $t("common.Change Password")
              }}</span>
            </transition>
          </a>

          <a
            v-if="isAdmin"
            class="nav-item"
            :class="{ active: isActiveRoute('subscription') }"
            @click.prevent="handleNavClick('subscription')"
            title="Subscription"
          >
            <i class="bi bi-arrow-repeat"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{
                $t("common.Subscription")
              }}</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('support') }"
            @click.prevent="handleNavClick('support')"
            title="Support"
          >
            <i class="bi bi-headset"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Support") }}</span>
            </transition>
          </a>

          <a
            v-if="isAdmin && !isSubscriptionExpired"
            class="nav-item"
            :class="{ active: isActiveRoute('dataset') }"
            @click.prevent="handleNavClick('dataset')"
            title="Dataset"
          >
            <i class="bi bi-database"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Dataset") }}</span>
            </transition>
          </a>

          <button
            :class="{ active: isActiveRoute('sign-out') }"
            @click="handleSignOutClick"
            class="nav-item sign-out"
            title="Sign Out"
          >
            <i class="bi bi-box-arrow-right"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">{{ $t("common.Sign Out") }}</span>
            </transition>
          </button>
        </div>
      </div>
    </transition>

    <!-- Mobile Overlay -->
    <transition name="fade-smooth">
      <div v-if="isOpen && isMobile" class="sidebar-overlay" @click="handleToggle"></div>
    </transition>

    <!-- Send OTP Modal -->
    <SendOtpModal
      :show="showOtpModal"
      :mobile-number="userMobile"
      :country-code="userCountryCode"
      @close="showOtpModal = false"
      @success="handleOtpSuccess"
      @error="handleOtpError"
    />

    <!-- Sign Out Confirmation Modal -->
    <SignOutConfirmationModal ref="signOutModal" @confirm="handleSignOutConfirm" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/modules/Authentication/stores/authStore";
import { useProfileStore } from "@/modules/GroceryIndia/stores/profile.js";
import SendOtpModal from "@/modules/Authentication/components/SendOtpModal.vue";
import SignOutConfirmationModal from "@/modules/Core/components/modals/SignOutConfirmationModal.vue";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["toggle"]);
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const profileStore = useProfileStore();

const isMobile = ref(false);
const showOtpModal = ref(false);

const signOutModal = ref(null);

// console.log('profileStore isAdmin',authStore.user.isAdmin);

// Check if user is admin
const isAdmin = computed(() => authStore.user?.isAdmin === 1);

const userDetailsStore = useUserDetailsStore();

onMounted(() => {
  userDetailsStore.fetchUserProfile();
});

// console.log('fetchUserProfile',userDetailsStore.shop_subscription?.end_date);

// ✅ NEW: Check if subscription is expired
const isSubscriptionExpired = computed(() => {
  // const endDate = userDetailsStore.shop_subscription.end_date
  // if (!endDate) return true
  // const expiryDate = new Date(endDate)
  // const currentDate = new Date()
  // return currentDate > expiryDate
});

// console.log('authStore shop_subscription',authStore.shop_subscription);

// Get user mobile from auth store
// const userMobile = computed(() => authStore.user?.mobile || "");
const userMobile = computed(() => userDetailsStore.userData?.mobile || "");
const userCountryCode = computed(() => userDetailsStore.userData?.country_code || "");

// console.log('authStore', userDetailsStore.userData);

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

const handleToggle = () => {
  emit("toggle");
};

// Check if a route is active
const isActiveRoute = (routePath) => {
  const currentPath = route.path.split("/").pop() || route.path;
  const checkPath = routePath.startsWith("/") ? routePath.slice(1) : routePath;

  return currentPath === checkPath || route.path.includes(`/${checkPath}`);
};

// Updated to accept a route and navigate
const handleNavClick = (route) => {
  if (isMobile.value) {
    emit("toggle");
  }
  if (route) {
    router.push(route);
  }
};

// Handle change password click - show modal
const handleChangePasswordClick = () => {
  if (isMobile.value) {
    emit("toggle");
  }
  showOtpModal.value = true;
};

// Handle OTP success
const handleOtpSuccess = (response) => {
  console.log("OTP sent successfully:", response);
  // Modal will close and navigate automatically
};

// Handle OTP error
const handleOtpError = (error) => {
  console.error("OTP error:", error);
  // alert(error || "Failed to send OTP. Please try again.");
};

// Handle sign out button click - show modal
const handleSignOutClick = () => {
  if (isMobile.value) {
    emit("toggle");
  }
  signOutModal.value?.show();
};

// Handle sign out confirmation
const handleSignOutConfirm = () => {
  console.log("Sign out confirmed");
  authStore.logout();
  router.push({ name: "Login" });
};

// const handleSignOut = () => {
//   // Add sign-out logic here
//   console.log("Sign out clicked");
//   authStore.logout();
//   router.push({ name: 'Home' });
// };

// Animation hooks for smooth synchronized transitions
const onBeforeEnter = (el) => {
  if (isMobile.value) {
    el.style.transform = "translateX(-100%)";
    el.style.opacity = "0";
  }
};

const onEnter = (el, done) => {
  if (isMobile.value) {
    el.offsetHeight;
    el.style.transition =
      "transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
    el.style.transform = "translateX(0)";
    el.style.opacity = "1";

    setTimeout(() => {
      done();
    }, 400);
  } else {
    done();
  }
};

const onAfterEnter = (el) => {
  if (isMobile.value) {
    el.style.transition = "";
  }
};

const onBeforeLeave = (el) => {
  if (isMobile.value) {
    el.style.transform = "translateX(0)";
    el.style.opacity = "1";
  }
};

const onLeave = (el, done) => {
  if (isMobile.value) {
    el.offsetHeight;
    el.style.transition =
      "transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
    el.style.transform = "translateX(-100%)";
    el.style.opacity = "0";

    setTimeout(() => {
      done();
    }, 400);
  } else {
    done();
  }
};

const onAfterLeave = (el) => {
  if (isMobile.value) {
    el.style.transition = "";
  }
};

onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
});
</script>

<style scoped>
.sidebar {
  position: fixed;
  left: 0;
  top: 70px;
  width: 240px;
  height: calc(100vh - 70px);
  background-color: #ffffff;
  box-shadow: 2px 0 15px rgba(0, 0, 0, 0.08);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
  overflow-y: auto;
  will-change: transform, opacity;
  backface-visibility: hidden;
  -webkit-font-smoothing: antialiased;
  transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar.mobile-sidebar {
  top: 70px;
  height: calc(100vh - 70px);
}

.sidebar-nav {
  padding: 15px 12px;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  margin-bottom: 4px;
  color: #6c757d;
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  gap: 12px;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-size: 14px;
  border-radius: 8px;
  position: relative;
}

.sidebar.collapsed .nav-item {
  justify-content: center;
}

.nav-item i {
  font-size: 18px;
  min-width: 20px;
  flex-shrink: 0;
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-item:hover i {
  transform: scale(1.1);
}

.nav-text {
  white-space: nowrap;
  overflow: hidden;
}

.nav-item:hover {
  background-color: #f8f9fa;
  color: #0066cc;
}

.sidebar:not(.collapsed) .nav-item:hover {
  transform: translateX(3px);
}

.nav-item.active {
  background-color: #e7f1ff;
  color: #0066cc;
  font-weight: 500;
}

/* Text fade transition */
.fade-text-enter-active,
.fade-text-leave-active {
  transition: opacity 0.2s ease;
}

.fade-text-enter-from,
.fade-text-leave-to {
  opacity: 0;
}

.sidebar-footer {
  border-top: 1px solid #e9ecef;
  padding: 15px 12px;
  flex-shrink: 0;
}

.sign-out {
  color: #dc3545;
}

.sign-out:hover {
  background-color: #fff5f5;
  color: #dc3545;
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
  backdrop-filter: blur(2px);
}

/* Mobile Sidebar Transitions */
.sidebar-mobile-enter-active,
.sidebar-mobile-leave-active {
  /* Animation handled by JavaScript hooks */
}

/* Desktop Sidebar Transitions */
.sidebar-desktop-enter-active,
.sidebar-desktop-leave-active {
  transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Overlay Fade Transitions */
.fade-smooth-enter-active {
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-smooth-leave-active {
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-smooth-enter-from,
.fade-smooth-leave-to {
  opacity: 0;
}

.fade-smooth-enter-to,
.fade-smooth-leave-from {
  opacity: 1;
}

/* Desktop view */
@media (min-width: 769px) {
  .sidebar {
    left: 0;
  }
}

/* Scrollbar styling */
.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}

@media (max-width: 480px) {
  .logo-img {
    width: 28px;
    height: 28px;
  }
}
</style>
