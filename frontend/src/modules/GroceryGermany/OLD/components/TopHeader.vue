<template>
  <header class="top-header bg-white border-bottom fixed-top">
    <div class="d-flex align-items-center justify-content-between w-100 px-3 px-md-4 h-100">
      
      <!-- Left Section: Logo + Toggle + Search -->
      <div class="d-flex align-items-center gap-2 gap-md-3 flex-grow-1 me-3">
        
        <!-- Brand Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <button 
            class="btn btn-link text-dark p-2 menu-toggle" 
            @click="handleToggleSidebar"
          >
            <i class="bi bi-list fs-4"></i>
          </button>
          
          <router-link to="/" class="d-flex align-items-center text-decoration-none">
            <img :src="logoUrl" alt="ReadyBill" class="logo-img" />
          </router-link>
        </div>
        
        <!-- Desktop Search Box -->
        <div class="d-none d-lg-flex position-relative flex-grow-1 me-3" style="max-width: 400px;">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
          <input 
            type="text" 
            class="form-control ps-5 rounded-pill border" 
            placeholder="Search..." 
            v-model="searchQuery"
          />
        </div>
      </div>

      <!-- Right Section: Language + Help + Notifications + User -->
      <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">

        <!-- Language Dropdown -->
        <LanguageDropdown />

        <!-- Help Button - Hidden on small screens -->
        <button class="btn btn-link text-muted p-2 d-none d-md-inline-flex" title="Help">
          <i class="bi bi-question-circle fs-5"></i>
        </button>
        
        <!-- Notification Dropdown -->
        <div class="dropdown">
          <button 
            class="btn btn-link text-muted p-2 position-relative" 
            title="Notifications"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            @click="handleNotificationClick"
          >
            <i class="bi bi-bell fs-5"></i>
            <span 
              v-if="unreadCount > 0" 
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="font-size: 10px; padding: 3px 6px;"
            >
              {{ unreadCount }}
            </span>
          </button>

          <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow border-0">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
              <h6 class="mb-0 fw-semibold">Notifications</h6>
              <button 
                v-if="unreadCount > 0"
                class="btn btn-link btn-sm text-primary p-0"
                @click="markAllAsRead"
              >
                Mark all as read
              </button>
            </div>

            <div class="notification-list">
              <div 
                v-if="notifications.length === 0"
                class="text-center py-5 text-muted"
              >
                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                <p class="mb-0">No notifications</p>
              </div>

              <NotificationItem
                v-for="notification in recentNotifications"
                :key="notification.id"
                :notification="notification"
                @click="handleNotificationItemClick(notification)"
              />
            </div>

            <div class="border-top bg-light">
              <router-link 
                to="notifications" 
                class="btn btn-link w-100 text-center text-decoration-none py-2 fw-medium"
              >
                View All Notifications
              </router-link>
            </div>
          </div>
        </div>

        <!-- User Dropdown -->
        <div class="dropdown">
          <button 
            class="btn btn-link text-decoration-none d-flex align-items-center gap-2 p-1 p-md-2" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
          >
            <img 
              :src="userAvatar"
              :alt="userName" 
              class="rounded-circle"
              style="width: 36px; height: 36px; object-fit: cover;"
            />
            <div class="d-none d-md-flex flex-column align-items-start text-start lh-sm">
              <span class="text-dark fw-medium small">{{ userName }}</span>
              <span class="text-muted" style="font-size: 0.75rem;">{{ userRole }}</span>
            </div>
          </button>
          
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
            <li>
              <router-link class="dropdown-item" to="profile">
                <i class="bi bi-person me-2"></i>Profile
              </router-link>
            </li>
            <li>
              <router-link class="dropdown-item" to="setting">
                <i class="bi bi-gear me-2"></i>Settings
              </router-link>
            </li>
            <li><hr class="dropdown-divider"></li>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/Authentication/stores/authStore'
import { useNotificationStore } from '@/modules/GroceryGermany/stores/notificationStore'
import NotificationItem from './NotificationItem.vue'
import logoUrl from '@/assets/images/readybill.png'
import LanguageDropdown from '@/modules/Core/components/LanguageDropdown.vue'


const emit = defineEmits(['toggleSidebar'])
const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const searchQuery = ref('')

if(authStore.isAuthenticated && authStore.user){
    authStore.initAuth()
    // console.log('authStore',authStore.user); 
}


// User info from auth store
const userName = computed(() =>
  authStore.user?.isAdmin == 1
    ? authStore.user?.shop?.name ?? 'NA'
    : authStore.user?.staff?.name ?? 'NA'
);

const userRole = computed(() => (authStore.user?.isAdmin == 1) ? 'Owner' :'Staff' )

const userAvatar = computed(() => {
  // If user is null, return default avatar
  if (!authStore.user) {
    return `https://ui-avatars.com/api/?name=Guest&background=0066cc&color=fff`
  }
  
  // If is_logo is 1, show uploaded photo
  if (authStore.user.is_logo === 1 && authStore.user.photo_url) {
    return authStore.user?.photo_url
  }
  
  // Otherwise show generated avatar
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=0066cc&color=fff`
})

// Notifications
const notifications = computed(() => notificationStore.notifications)
const unreadCount = computed(() => notificationStore.unreadCount)
const recentNotifications = computed(() => notifications.value.slice(0, 5))

const handleToggleSidebar = () => {
  emit('toggleSidebar')
}

const handleNotificationClick = () => {
  // Fetch notifications if not already loaded
  if (notifications.value.length === 0) {
    notificationStore.fetchNotifications()
  }
}

const handleNotificationItemClick = (notification) => {
  notificationStore.markAsRead(notification.id)
  
  // Navigate to notification link if available
  if (notification.link) {
    router.push(notification.link)
  }
}

const markAllAsRead = () => {
  notificationStore.markAllAsRead()
}

const handleLogout = async  () => {
  await authStore.logout()
  router.push({ name: 'Login' }) // Use named route for better reliability
}

onMounted(() => {
  // Load notifications on mount
  // notificationStore.fetchNotifications()

   // Only fetch notifications if user is authenticated
  if (authStore.isAuthenticated) {
    notificationStore.fetchNotifications()
  }

})
</script>

<style scoped>
/* Minimal custom CSS - Bootstrap handles most styling */
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
  width: 140px;
  height: auto;
  object-fit: contain;
}

.notification-dropdown {
  width: 380px;
  max-height: 600px;
  border-radius: 12px;
}

.notification-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-list::-webkit-scrollbar {
  width: 6px;
}

.notification-list::-webkit-scrollbar-track {
  background: #f8f9fa;
}

.notification-list::-webkit-scrollbar-thumb {
  background: #dee2e6;
  border-radius: 3px;
}

.notification-list::-webkit-scrollbar-thumb:hover {
  background: #adb5bd;
}

/* Responsive adjustments */
@media (max-width: 576px) {
  .logo-img {
    width: 110px;
  }
  
  .notification-dropdown {
    width: 90vw;
    max-width: 320px;
  }
}
</style>
