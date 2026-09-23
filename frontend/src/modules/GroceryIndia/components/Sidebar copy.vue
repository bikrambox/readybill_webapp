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
            class="nav-item"
            :class="{ active: isActiveRoute('sell') }"
            @click.prevent="handleNavClick('sell')"
            title="Quick Sell"
          >
            <i class="bi bi-lightning"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Quick Sell</span>
            </transition>
          </a>

          <a
            class="nav-item"
            @click.prevent="handleNavClick('refund')"
            :class="{ active: isActiveRoute('refund') }"
            title="Refund"
          >
            <i class="bi bi-arrow-counterclockwise"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Refund</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('add-inventory') }"
            @click.prevent="handleNavClick('add-inventory')"
            title="Add Inventory"
          >
            <i class="bi bi-plus-circle"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Add Inventory</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('inventory') }"
            @click.prevent="handleNavClick('inventory')"
            title="Inventory"
          >
            <i class="bi bi-pencil-square"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Inventory</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('transactions') }"
            @click.prevent="handleNavClick('transactions')"
            title="Transaction"
          >
            <i class="bi bi-arrow-left-right"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Transaction</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('add-employee') }"
            @click.prevent="handleNavClick('add-employee')"
            title="Add Employee"
          >
            <i class="bi bi-people"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Employees</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('setting') }"
            @click.prevent="handleNavClick('setting')"
            title="Settings"
          >
            <i class="bi bi-gear"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Settings</span>
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
              <span v-show="isOpen" class="nav-text">Account</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('change-password') }"
            @click.prevent="handleNavClick('change-password')"
            title="Change Password"
          >
            <i class="bi bi-key"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Change Password</span>
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
              <span v-show="isOpen" class="nav-text">Support</span>
            </transition>
          </a>

          <a
            class="nav-item"
            :class="{ active: isActiveRoute('dataset') }"
            @click.prevent="handleNavClick('dataset')"
            title="Dataset"
          >
            <i class="bi bi-database"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Dataset</span>
            </transition>
          </a>

          <!-- <a class="nav-item" @click.prevent="handleNavClick('subscriptions')" title="Subscriptions">
            <i class="bi bi-credit-card"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Subscriptions</span>
            </transition>
          </a> -->

          <button
            :class="{ active: isActiveRoute('sign-out') }"
            @click="handleSignOut"
            class="nav-item sign-out"
            title="Sign Out"
          >
            <i class="bi bi-box-arrow-right"></i>
            <transition name="fade-text">
              <span v-show="isOpen" class="nav-text">Sign Out</span>
            </transition>
          </button>
        </div>
      </div>
    </transition>

    <!-- Mobile Overlay -->
    <transition name="fade-smooth">
      <div
        v-if="isOpen && isMobile"
        class="sidebar-overlay"
        @click="handleToggle"
      ></div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRouter, useRoute } from "vue-router";

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["toggle"]);
const router = useRouter();
const route = useRoute();

const isMobile = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

const handleToggle = () => {
  emit("toggle");
};

// Check if a route is active
const isActiveRoute = (routePath) => {
  // Remove leading slash if present for comparison
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

const handleSignOut = () => {
  // Add sign-out logic here
  console.log("Sign out clicked");
  // For example:
  // router.push('/login');
};

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
  /* flex: 1;
  overflow-y: auto;
  overflow-x: hidden; */
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
