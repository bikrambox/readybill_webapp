<template>
  <div>
    <!-- Sidebar -->
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
          class="nav-item"
          :class="{
            active: isActiveRoute('make-subscription') || isActiveRoute('dashboard'),
          }"
          @click.prevent="handleNavClick('make-subscription')"
          title="Make Subscription"
        >
          <i class="bi bi-patch-check"></i>
          <span v-show="isOpen" class="nav-text">Make Subscription</span>
        </a>

        <a
          class="nav-item"
          :class="{ active: isActiveRoute('earnings') }"
          @click.prevent="handleNavClick('earnings')"
          title="My Earning"
        >
          <i class="bi bi-wallet2"></i>
          <span v-show="isOpen" class="nav-text">My Earning</span>
        </a>

        <a
          class="nav-item"
          :class="{ active: isActiveRoute('profile') }"
          @click.prevent="handleNavClick('profile')"
          title="Profile"
        >
          <i class="bi bi-person-circle"></i>
          <span v-show="isOpen" class="nav-text">Profile</span>
        </a>

        <a
          class="nav-item"
          :class="{ active: isActiveRoute('change-password') }"
          @click.prevent="handleNavClick('change-password')"
          title="Change Password"
        >
          <i class="bi bi-shield-lock"></i>
          <span v-show="isOpen" class="nav-text">Change Password</span>
        </a>

        <!-- <a class="nav-item" :class="{ active: isActiveRoute('support') }" @click.prevent="handleNavClick('support')" title="Support">
          <i class="bi bi-headset"></i>
          <span v-show="isOpen" class="nav-text">Support</span>
        </a> -->
      </nav>

      <!-- Sidebar Footer -->
      <div class="sidebar-footer">
        <!-- <a class="nav-item" :class="{ active: isActiveRoute('profile') }" @click.prevent="handleNavClick('profile')" title="Profile">
          <i class="bi bi-person-circle"></i>
          <span v-show="isOpen" class="nav-text">Profile</span>
        </a>

        <a class="nav-item" :class="{ active: isActiveRoute('change-password') }" @click.prevent title="Change Password">
          <i class="bi bi-shield-lock"></i>
          <span v-show="isOpen" class="nav-text">Change Password</span>
        </a>

        <a class="nav-item" :class="{ active: isActiveRoute('support') }" @click.prevent="handleNavClick('support')" title="Support">
          <i class="bi bi-headset"></i>
          <span v-show="isOpen" class="nav-text">Support</span>
        </a> -->

        <button class="nav-item sign-out" @click="handleSignOut" title="Sign Out">
          <i class="bi bi-box-arrow-right"></i>
          <span v-show="isOpen" class="nav-text">Sign Out</span>
        </button>
      </div>
    </div>

    <!-- Mobile Overlay -->
    <div v-if="isOpen && isMobile" class="sidebar-overlay" @click="handleToggle"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useLoginStore } from "@/modules/AuthorizedAgents/stores/loginStore.js";

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["toggle"]);
const router = useRouter();
const route = useRoute();
const authStore = useLoginStore();
const isMobile = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

const handleToggle = () => {
  emit("toggle");
};

const isActiveRoute = (routePath) => {
  const currentPath = route.path.split("/").pop() || route.path;
  const checkPath = routePath.startsWith("/") ? routePath.slice(1) : routePath;
  return currentPath === checkPath || route.path.includes(`/${checkPath}`);
};

const handleNavClick = (path) => {
  if (isMobile.value) emit("toggle");
  if (path) router.push(path);
};

const handleSignOut = () => {
  authStore.logout();
  router.push({ name: "AuthorizeAgents" });
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
  justify-content: space-between; /* ✅ pins footer to bottom */
  overflow-x: hidden;
  overflow-y: auto;
  transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar-nav {
  padding: 15px 12px;
  flex: 1; /* ✅ takes only needed space */
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
}

.sidebar.collapsed .nav-item {
  justify-content: center;
}

.nav-item i {
  font-size: 18px;
  min-width: 20px;
  flex-shrink: 0;
}

.nav-text {
  white-space: nowrap;
  overflow: hidden;
}

.nav-item:hover {
  background-color: #f8f9fa;
  color: #0066cc;
}

.nav-item.active {
  background-color: #e7f1ff;
  color: #0066cc;
  font-weight: 500;
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

@media (max-width: 768px) {
  .sidebar {
    top: 70px;
    height: calc(100vh - 70px);
  }
}
</style>
