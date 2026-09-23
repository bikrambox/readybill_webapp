<script setup>
import { ref } from "vue";
import SettingsContent from "@/modules/GroceryGermany/components/setting/SettingsContent.vue";
import Footer from "@/modules/GroceryGermany/components/Footer.vue";
// import SettingsSidebar from '../components/Settings/SettingsSidebar.vue';
// import TopHeader from '../components/TopHeader.vue';

const isSidebarOpen = ref(false);

const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value;
};
</script>

<template>
  <div>
    <div class="d-flex">
      <!-- <SettingsSidebar :isOpen="isSidebarOpen" @close="toggleSidebar" /> -->

      <div class="flex-grow-1 main-content">
        <!-- <TopHeader @toggleSidebar="toggleSidebar" /> -->
        <SettingsContent />
      </div>
    </div>

    <!-- Footer -->
    <Footer />
  </div>
</template>

<style scoped>
.main-content {
  min-height: 100vh;
  background-color: #f8f9fa;
}

@media (max-width: 991.98px) {
  .main-content {
    margin-left: 0;
  }
}
</style>
