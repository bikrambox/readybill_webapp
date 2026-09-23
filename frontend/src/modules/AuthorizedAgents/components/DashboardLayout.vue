<template>
  <div class="dashboard-wrapper">
    <!-- Top Header -->
    <TopHeader @toggleSidebar="toggleSidebar" />

    <!-- Sidebar -->
    <Sidebar :isOpen="sidebarOpen" @toggle="toggleSidebar" />

    <!-- Main Content -->
    <div
      class="main-content"
      :class="{
        'sidebar-expanded': sidebarOpen && !isMobile,
        'sidebar-collapsed': !sidebarOpen && !isMobile,
        'mobile-content': isMobile,
      }"
    >
      <div class="content-area">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import Sidebar from "../components/Sidebar.vue";
import TopHeader from "../components/TopHeader.vue";

const sidebarOpen = ref(true);
const isMobile = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const checkMobile = () => {
  const wasMobile = isMobile.value;
  isMobile.value = window.innerWidth <= 768;

  if (isMobile.value && !wasMobile) {
    sidebarOpen.value = false;
  } else if (!isMobile.value && wasMobile) {
    sidebarOpen.value = true;
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
.dashboard-wrapper {
  min-height: 100vh;
  background-color: #f8f9fa;
  display: flex;
  flex-direction: column;
}

.main-content {
  margin-left: 0;
  margin-top: 70px;
  flex: 1;
  transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}

.main-content.sidebar-expanded { margin-left: 240px; }
.main-content.sidebar-collapsed { margin-left: 70px; }
.main-content.mobile-content    { margin-left: 0; }

.content-area {
  padding: 20px;
  flex: 1;
}

@media (max-width: 768px) {
  .main-content { margin-left: 0 !important; }
  .content-area { padding: 15px; }
}

@media (max-width: 480px) {
  .content-area { padding: 10px; }
}
</style>