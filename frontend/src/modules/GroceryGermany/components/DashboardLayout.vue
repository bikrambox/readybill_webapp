<template>
  <div class="dashboard-wrapper">
    <!-- Top Header - Fixed with Logo and Toggle -->
    <TopHeader @toggleSidebar="toggleSidebar" />

    <!-- Sidebar - Below Header -->
    <Sidebar :isOpen="sidebarOpen" @toggle="toggleSidebar" />

    <!-- Main Content - Adjusts width based on sidebar state -->
    <div
      class="main-content"
      :class="{
        'sidebar-expanded': sidebarOpen && !isMobile,
        'sidebar-collapsed': !sidebarOpen && !isMobile,
        'mobile-content': isMobile,
      }"
    >
      <div class="content-area">
        <!-- Error/Notification Box - Shows subscription expiry OR notifications -->
        <FormErrorBox
          v-if="displayMessages.length > 0"
          :title="boxTitle"
          :messages="displayMessages"
          @clear="clearErrors"
          class="mb-4"
        />

        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRouter } from "vue-router";
import Sidebar from "../components/Sidebar.vue";
import TopHeader from "../components/TopHeader.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useSubscriptionNotificationStore } from "@/modules/GroceryGermany/stores/subscriptionNotificationStore";
import { useAuthStore } from "@/modules/Authentication/stores/authStore";
import { useLocaleStore } from "@/stores/localeStore";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";

const sidebarOpen = ref(true);
const isMobile = ref(false);
const subscriptionStore = useSubscriptionNotificationStore();
const authStore = useAuthStore();
const localeStore = useLocaleStore();
const router = useRouter();

const userDetailsStore = useUserDetailsStore();

onMounted(() => {
  userDetailsStore.fetchUserProfile();
});

// const userDetailsStore = useUserDetailsStore()

console.log('userDetailsStore',userDetailsStore.country_details);

// Check if subscription is expired
const isSubscriptionExpired = computed(() => {
  const endDate = userDetailsStore.shop_subscription?.end_date;
  if (!endDate) return true;

  const expiryDate = new Date(endDate);
  const currentDate = new Date();

  return currentDate > expiryDate;
});

// Get subscription expiry message with clickable link
const subscriptionMessage = computed(() => {
  if (!isSubscriptionExpired.value) return null;

  const endDate = userDetailsStore.shop_subscription?.end_date;
  const formattedDate = endDate
    ? new Date(endDate).toLocaleDateString("en-IN", {
        year: "numeric",
        month: "long",
        day: "numeric",
      })
    : "N/A";

  const country = localeStore.country || "in";
  const lang = localeStore.language || "en";

  return `Your subscription expired on ${formattedDate}. Please <a href="/${country}/${lang}/grocery/subscription" class="alert-link">renew your subscription</a> to continue using all features.`;
});

// Display messages - show only subscription message if expired, otherwise show notifications
const displayMessages = computed(() => {
  // If subscription is expired, show ONLY the subscription message
  if (isSubscriptionExpired.value && subscriptionMessage.value) {
    return [subscriptionMessage.value];
  }

  // Otherwise, show notification messages
  return subscriptionStore.notificationMessages;
});

// Dynamic title based on content
const boxTitle = computed(() => {
  if (isSubscriptionExpired.value) {
    return "Subscription Expired";
  }
  return "Subscription Alert";
});

const checkMobile = () => {
  const wasMobile = isMobile.value;
  isMobile.value = window.innerWidth <= 768;

  if (isMobile.value && !wasMobile) {
    sidebarOpen.value = false;
  } else if (!isMobile.value && wasMobile) {
    sidebarOpen.value = true;
  }
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
  loadNotifications();

  // Add click event listener for subscription links
  document.addEventListener("click", handleLinkClick);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
  document.removeEventListener("click", handleLinkClick);
});

// Handle clicks on subscription renewal links
const handleLinkClick = (event) => {
  const target = event.target;

  // Check if clicked element is the subscription link
  if (target.classList.contains("alert-link") && target.getAttribute("href")) {
    event.preventDefault();
    const href = target.getAttribute("href");
    router.push(href);
  }
};

const clearErrors = () => {
  // Only clear notifications, subscription message persists until renewed
  if (!isSubscriptionExpired.value) {
    subscriptionStore.clearNotifications();
  }
};

const loadNotifications = async () => {
  try {
    await subscriptionStore.fetchNotifications();
  } catch (error) {
    console.error("Failed to load notifications:", error);
  }
};
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

/* Desktop - Sidebar Expanded */
.main-content.sidebar-expanded {
  margin-left: 240px;
}

/* Desktop - Sidebar Collapsed */
.main-content.sidebar-collapsed {
  margin-left: 70px;
}

/* Mobile */
.main-content.mobile-content {
  margin-left: 0;
}

.content-area {
  padding: 20px;
  flex: 1;
}

/* Style for alert links inside FormErrorBox */
:deep(.alert-link) {
  color: inherit;
  font-weight: 600;
  text-decoration: underline;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

:deep(.alert-link:hover) {
  opacity: 0.8;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0 !important;
  }

  .content-area {
    padding: 15px;
  }
}

@media (max-width: 480px) {
  .content-area {
    padding: 10px;
  }
}

</style>
