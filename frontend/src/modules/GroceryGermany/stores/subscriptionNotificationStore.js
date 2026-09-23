// stores/subscriptionNotificationStore.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/config/api'

export const useSubscriptionNotificationStore = defineStore('subscriptionNotification', () => {
    const notifications = ref([])
    const isSubscriptionExpired = ref(0)
    const loading = ref(false)
    const error = ref(null)

    // Computed property to extract only messages
    const notificationMessages = computed(() => {
        return notifications.value.map(notification => notification.message)
    })

    const fetchNotifications = async () => {
        loading.value = true
        error.value = null

        try {
            const { data } = await api.get(`notifications`)


            console.log('fetchNotifications', data);

            if (data.status === 'success') {
                notifications.value = data.notifications || []
                isSubscriptionExpired.value = data.isSubscriptionExpired || 0
            }

            return data
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch notifications'
            console.error('Error fetching notifications:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const clearNotifications = () => {
        notifications.value = []
        isSubscriptionExpired.value = 0
        error.value = null
    }

    return {
        notifications,
        notificationMessages,
        isSubscriptionExpired,
        loading,
        error,
        fetchNotifications,
        clearNotifications
    }
})
