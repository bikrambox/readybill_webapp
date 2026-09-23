// stores/notificationStore.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/config/api'

const PREFIX = import.meta.env.VITE_GROCERY_INDIA_PREFIX

// ─── Route Map by Notification Type ──────────────────────────────────────────
const routeMap = {
    subscription_expiry: { name: 'Subscription' },
    stock_alert: { name: 'InventoryList' },
}


export const useNotificationStore = defineStore('notification', () => {

    // State
    const notifications = ref([])
    const isLoading = ref(false)
    const unreadCount = ref(0)   // used for navbar badge (from API count)

    // Computed — live count derived from local notifications array
    // stays in sync after markAsRead / markAllAsRead / delete operations
    const computedUnreadCount = computed(() =>
        notifications.value.filter(n => !n.is_read).length
    )

    // ─── Fetch All (NotificationPage + bell dropdown) ─────────────────────────
    const fetchNotifications = async () => {
        try {
            isLoading.value = true
            const response = await api.get(`${PREFIX}notifications/all`)
            if (response.data.status) {
                notifications.value = response.data.data
                // Sync badge count with actual data
                unreadCount.value = notifications.value.filter(n => !n.is_read).length
            }
        } catch (error) {
            console.error('Error fetching notifications:', error)
        } finally {
            isLoading.value = false
        }
    }

    // ─── Fetch Unread Count Only (Navbar badge on mount) ──────────────────────
    const fetchUnreadCount = async () => {
        try {
            const response = await api.get(`${PREFIX}notifications/unread-count`)
            if (response.data.status) {
                unreadCount.value = response.data.data[0].unread_count
            }
        } catch (error) {
            console.error('Error fetching unread count:', error)
        }
    }

    // ─── Mark Single as Read ──────────────────────────────────────────────────
    const markAsRead = async (id) => {
        try {
            const response = await api.put(`${PREFIX}notifications/${id}/read`)
            if (response.data.status) {
                const notification = notifications.value.find(n => n.id === id)
                if (notification && !notification.is_read) {
                    notification.is_read = true
                    if (unreadCount.value > 0) unreadCount.value--
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error)
        }
    }

    // ─── Mark All as Read ─────────────────────────────────────────────────────
    const markAllAsRead = async () => {
        try {
            const response = await api.put(`${PREFIX}notifications/mark-all-read`)
            if (response.data.status) {
                notifications.value.forEach(n => (n.is_read = true))
                unreadCount.value = 0
            }
        } catch (error) {
            console.error('Error marking all as read:', error)
        }
    }

    // ─── Delete Single ────────────────────────────────────────────────────────
    const deleteNotification = async (id) => {
        try {
            const response = await api.delete(`${PREFIX}notifications/${id}`)
            if (response.data.status) {
                const deleted = notifications.value.find(n => n.id === id)
                if (deleted && !deleted.is_read && unreadCount.value > 0) {
                    unreadCount.value--
                }
                notifications.value = notifications.value.filter(n => n.id !== id)
            }
        } catch (error) {
            console.error('Error deleting notification:', error)
        }
    }

    // ─── Clear All ────────────────────────────────────────────────────────────
    const clearAll = async () => {
        try {
            const response = await api.delete(`${PREFIX}notifications/clear-all`)
            if (response.data.status) {
                notifications.value = []
                unreadCount.value = 0
            }
        } catch (error) {
            console.error('Error clearing notifications:', error)
        }
    }

    return {
        notifications,
        isLoading,
        unreadCount,            // raw ref — used for initial badge from API
        computedUnreadCount,    // computed — live synced after local mutations
        routeMap,
        fetchNotifications,
        fetchUnreadCount,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        clearAll,
    }
})
