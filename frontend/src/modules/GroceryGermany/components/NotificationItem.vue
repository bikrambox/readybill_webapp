<template>
  <div 
    class="notification-item"
    :class="{ unread: !notification.is_read }"
    @click="handleClick"
  >
    <div class="notification-icon" :class="`icon-${notification.type}`">
      <i :class="iconClass"></i>
    </div>

    <div class="notification-content">
      <h6 class="notification-title">{{ notification.title }}</h6>
      <p class="notification-message">{{ notification.message }}</p>
      <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
    </div>

    <div v-if="!notification.is_read" class="unread-indicator"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  notification: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['click'])

const iconClass = computed(() => {
  const iconMap = {
    success: 'bi bi-check-circle-fill',
    warning: 'bi bi-exclamation-triangle-fill',
    error: 'bi bi-x-circle-fill',
    info: 'bi bi-info-circle-fill',
    order: 'bi bi-cart-fill',
    inventory: 'bi bi-box-seam-fill',
    payment: 'bi bi-credit-card-fill',
    user: 'bi bi-person-fill',
    system: 'bi bi-gear-fill'
  }
  return iconMap[props.notification.type] || 'bi bi-bell-fill'
})

const formatTime = (timestamp) => {
  if (!timestamp) return ''
  
  const date = new Date(timestamp)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffHours < 24) return `${diffHours}h ago`
  if (diffDays < 7) return `${diffDays}d ago`
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric' 
  })
}

const handleClick = () => {
  emit('click')
}
</script>

<style scoped>
.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 20px;
  cursor: pointer;
  transition: background-color 0.2s;
  position: relative;
  border-bottom: 1px solid #f8f9fa;
}

.notification-item:hover {
  background-color: #f8f9fa;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item.unread {
  background-color: #f0f8ff;
}

.notification-item.unread:hover {
  background-color: #e7f3ff;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 18px;
}

.icon-success {
  background-color: #d1e7dd;
  color: #0f5132;
}

.icon-warning {
  background-color: #fff3cd;
  color: #664d03;
}

.icon-error {
  background-color: #f8d7da;
  color: #842029;
}

.icon-info {
  background-color: #cfe2ff;
  color: #084298;
}

.icon-order {
  background-color: #e7f1ff;
  color: #0066cc;
}

.icon-inventory {
  background-color: #e0e7ff;
  color: #4338ca;
}

.icon-payment {
  background-color: #dcfce7;
  color: #166534;
}

.icon-user {
  background-color: #fce7f3;
  color: #9f1239;
}

.icon-system {
  background-color: #f3f4f6;
  color: #374151;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-size: 14px;
  font-weight: 600;
  color: #212529;
  margin: 0 0 4px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.notification-message {
  font-size: 13px;
  color: #6c757d;
  margin: 0 0 4px 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.notification-time {
  font-size: 12px;
  color: #adb5bd;
}

.unread-indicator {
  width: 8px;
  height: 8px;
  background-color: #0066cc;
  border-radius: 50%;
  position: absolute;
  top: 20px;
  right: 20px;
}
</style>
