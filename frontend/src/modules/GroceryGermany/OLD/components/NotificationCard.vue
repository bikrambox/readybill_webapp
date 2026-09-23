<template>
  <div 
    class="notification-card"
    :class="{ unread: !notification.is_read }"
  >
    <div class="notification-icon-wrapper">
      <div class="notification-icon" :class="`icon-${notification.type}`">
        <i :class="iconClass"></i>
      </div>
    </div>

    <div 
      class="notification-body"
      @click="emit('click')"
    >
      <div class="d-flex justify-content-between align-items-start mb-2">
        <h6 class="notification-title mb-0">{{ notification.title }}</h6>
        <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
      </div>

      <p class="notification-message mb-2">{{ notification.message }}</p>

      <div class="d-flex align-items-center gap-3">
        <span 
          v-if="!notification.is_read"
          class="badge bg-primary"
        >
          New
        </span>
        <span class="text-muted small">
          <i class="bi bi-clock me-1"></i>
          {{ formatFullDate(notification.created_at) }}
        </span>
      </div>
    </div>

    <div class="notification-actions">
      <button 
        class="btn btn-sm btn-outline-danger"
        @click.stop="emit('delete')"
        title="Delete"
      >
        <i class="bi bi-trash"></i>
      </button>
    </div>
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

const emit = defineEmits(['click', 'delete'])

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

const formatFullDate = (timestamp) => {
  if (!timestamp) return ''
  
  const date = new Date(timestamp)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.notification-card {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 15px;
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 12px;
  transition: all 0.2s;
}

.notification-card:hover {
  border-color: #0066cc;
  box-shadow: 0 2px 8px rgba(0, 102, 204, 0.1);
}

.notification-card.unread {
  background-color: #f0f8ff;
  border-color: #b3d9ff;
}

.notification-icon-wrapper {
  flex-shrink: 0;
}

.notification-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
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

.notification-body {
  flex: 1;
  cursor: pointer;
  min-width: 0;
}

.notification-title {
  font-size: 15px;
  font-weight: 600;
  color: #212529;
}

.notification-message {
  font-size: 14px;
  color: #6c757d;
  line-height: 1.5;
}

.notification-time {
  font-size: 12px;
  color: #adb5bd;
  white-space: nowrap;
}

.notification-actions {
  flex-shrink: 0;
}

.notification-actions .btn {
  opacity: 0;
  transition: opacity 0.2s;
}

.notification-card:hover .notification-actions .btn {
  opacity: 1;
}

/* Mobile Responsive */
@media (max-width: 576px) {
  .notification-card {
    gap: 12px;
    padding: 12px;
  }

  .notification-icon {
    width: 40px;
    height: 40px;
    font-size: 18px;
  }

  .notification-title {
    font-size: 14px;
  }

  .notification-message {
    font-size: 13px;
  }

  .notification-actions .btn {
    opacity: 1;
  }
}
</style>
