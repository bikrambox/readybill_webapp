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
          <!-- <span class="brand-text">ReadyBill</span> -->
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
      <button class="icon-btn" title="Help">
        <i class="bi bi-question-circle"></i>
      </button>
      
      <button class="icon-btn" title="Notifications">
        <i class="bi bi-bell"></i>
        <span class="badge">3</span>
      </button>

      <div class="user-menu dropdown">
        <button 
          class="user-btn dropdown-toggle" 
          data-bs-toggle="dropdown" 
          aria-expanded="false"
        >
          <img 
            src="https://ui-avatars.com/api/?name=Julia+Kiran&background=0066cc&color=fff" 
            alt="Julia Kiran" 
            class="user-avatar"
          />
          <div class="user-info">
            <span class="user-name">Julia Kiran</span>
            <span class="user-role">Admin</span>
          </div>
        </button>
        
        <ul class="dropdown-menu dropdown-menu-end">
          <li><router-link class="dropdown-item" to="/account">Profile</router-link></li>
          <li><router-link class="dropdown-item" to="/settings">Settings</router-link></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="#" @click.prevent="handleLogout">Logout</a></li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import logoUrl from '@/assets/images/readybill.png';

const emit = defineEmits(['toggleSidebar']);
const router = useRouter();

const searchQuery = ref('');

const handleToggleSidebar = () => {
  emit('toggleSidebar');
};

const handleLogout = () => {
  router.push('/login');
};
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
  /* height: 32px; */
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
}

@media (max-width: 480px) {
  .brand-text {
    font-size: 16px;
  }
  
  .logo-img {
    width: 120px;
    /* height: 28px; */
  }
  
  .icon-btn:first-child {
    display: none;
  }
}
</style>
