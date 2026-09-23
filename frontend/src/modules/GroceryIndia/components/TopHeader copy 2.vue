<template>
  <header class="top-header">
    <div class="header-left">
      <!-- Logo + Toggle Section: Always visible on both mobile and desktop -->
      <div class="brand-section">
        <button class="menu-toggle" @click="handleToggleSidebar">
          <i class="bi bi-list"></i>
        </button>
        <router-link to="/" class="brand">
          <img :src="logoUrl" alt="ReadyBill" class="logo-img" />
        </router-link>
      </div>
      
      <!-- Desktop: Search Box -->
      <div class="search-box mx-3">
        <i class="bi bi-search"></i>
        <input 
          type="text" 
          class="form-control" 
          placeholder="Search..." 
          v-model="searchQuery"
        />
      </div>
    </div>

    <div class="header-right">

      <!-- Language Dropdown -->
      <LanguageDropdown />

      <button class="icon-btn" title="Help">
        <i class="bi bi-question-circle"></i>
      </button>
      
      <!-- Notification Dropdown -->
      <div class="notification-wrapper dropdown">
        <button 
          class="icon-btn" 
          title="Notifications"
          data-bs-toggle="dropdown"
          aria-expanded="false"
          @click="handleNotificationClick"
        >
          <i class="bi bi-bell"></i>
          <span v-if="unreadCount > 0" class="badge">{{ unreadCount }}</span>
        </button>

        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
          <div class="notification-header">
            <h6 class="mb-0">Notifications</h6>
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
              class="text-center py-4 text-muted"
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

          <div class="notification-footer">
            <router-link 
              to="notifications" 
              class="btn btn-link w-100 text-center"
            >
              View All Notifications
            </router-link>
          </div>
        </div>
      </div>

      <div class="user-menu dropdown">
        <button 
          class="user-btn dropdown-toggle" 
          data-bs-toggle="dropdown" 
          aria-expanded="false"
        >
          <img 
            :src="userAvatar"
            :alt="userName" 
            class="user-avatar"
          />
          <div class="user-info">
            <span class="user-name">{{ userName }}</span>
            <span class="user-role">{{ userRole }}</span>
          </div>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end">
          <li><router-link class="dropdown-item" to="profile">Profile</router-link></li>
          <li><router-link class="dropdown-item" to="setting">Settings</router-link></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" @click.prevent="handleLogout">Logout</a></li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/modules/Authentication/stores/authStore'
import { useNotificationStore } from '@/modules/GroceryIndia/stores/notificationStore'
import NotificationItem from './NotificationItem.vue'
import logoUrl from '@/assets/images/readybill.png'
import LanguageDropdown from '@/modules/Core/components/LanguageDropdown.vue'

const emit = defineEmits(['toggleSidebar'])
const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const searchQuery = ref('')

// User info from auth store
const userName = computed(() => authStore.user?.name || 'Julia Kiran')
const userRole = computed(() => authStore.user?.role || 'Admin')
const userAvatar = computed(() => 
  authStore.user?.avatar || 
  `https://ui-avatars.com/api/?name=${encodeURIComponent(userName.value)}&background=0066cc&color=fff`
)

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

const handleLogout = () => {
  authStore.logout()
  router.push('login')
}

onMounted(() => {
  // Load notifications on mount
  notificationStore.fetchNotifications()
})
</script>

<style scoped>
.top-header {
  height: 70px;
  background-color: #ffffff;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1001;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
}

.brand-section {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.menu-toggle {
  background: transparent;
  border: none;
  font-size: 24px;
  color: #333;
  cursor: pointer;
  padding: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background-color 0.2s;
  min-width: 40px;
  height: 40px;
}

.menu-toggle:hover {
  background-color: #f8f9fa;
  color: #0066cc;
}

.menu-toggle:active {
  transform: scale(0.95);
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}

.logo-img {
  width: 140px;
  border-radius: 6px;
  object-fit: contain;
}

.brand-text {
  font-size: 18px;
  font-weight: 600;
  color: #0066cc;
  white-space: nowrap;
}

.search-box {
  position: relative;
  max-width: 400px;
  flex: 1;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 16px;
}

.search-box input {
  padding-left: 40px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  height: 40px;
  width: 100%;
}

.search-box input:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.icon-btn {
  position: relative;
  background: transparent;
  border: none;
  font-size: 20px;
  color: #6c757d;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: all 0.2s;
}

.icon-btn:hover {
  background-color: #f8f9fa;
  color: #333;
}

.icon-btn .badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background-color: #dc3545;
  color: white;
  font-size: 10px;
  padding: 2px 5px;
  border-radius: 10px;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Notification Dropdown Styles */
.notification-dropdown {
  width: 380px;
  max-height: 600px;
  padding: 0;
  border: none;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  border-radius: 12px;
  overflow: hidden;
}

.notification-header {
  padding: 15px 20px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #fff;
  position: sticky;
  top: 0;
  z-index: 10;
}

.notification-header h6 {
  font-weight: 600;
  color: #212529;
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

.notification-footer {
  border-top: 1px solid #e9ecef;
  background-color: #f8f9fa;
  position: sticky;
  bottom: 0;
}

.notification-footer .btn-link {
  color: #0066cc;
  text-decoration: none;
  font-weight: 500;
  padding: 12px;
  transition: background-color 0.2s;
}

.notification-footer .btn-link:hover {
  background-color: #e9ecef;
  color: #0052a3;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 5px;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.user-btn:hover {
  background-color: #f8f9fa;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  text-align: left;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.user-role {
  font-size: 12px;
  color: #6c757d;
}

@media (max-width: 768px) {
  .top-header {
    padding: 0 15px;
  }
  
  .search-box {
    display: none;
  }
  
  .user-info {
    display: none;
  }

  .notification-dropdown {
    width: 320px;
  }
}

@media (max-width: 480px) {
  .brand-text {
    font-size: 16px;
  }
  
  .logo-img {
    width: 120px;
  }
  
  .icon-btn:first-child {
    display: none;
  }

  .notification-dropdown {
    width: 280px;
  }
}
</style>
