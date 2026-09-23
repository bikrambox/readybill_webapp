import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
    // State
    const notifications = ref([])
    const isLoading = ref(false)

    // Computed
    const unreadCount = computed(() => {
        return notifications.value.filter(n => !n.is_read).length
    })

    // Actions
    const fetchNotifications = async () => {
        try {
            isLoading.value = true

            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 500))

            // Load mock data
            notifications.value = getMockNotifications()
        } catch (error) {
            console.error('Error fetching notifications:', error)
        } finally {
            isLoading.value = false
        }
    }

    const markAsRead = async (id) => {
        try {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 200))

            const notification = notifications.value.find(n => n.id === id)
            if (notification) {
                notification.is_read = true
            }
        } catch (error) {
            console.error('Error marking notification as read:', error)
        }
    }

    const markAllAsRead = async () => {
        try {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 300))

            notifications.value.forEach(n => {
                n.is_read = true
            })
        } catch (error) {
            console.error('Error marking all as read:', error)
        }
    }

    const deleteNotification = async (id) => {
        try {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 200))

            notifications.value = notifications.value.filter(n => n.id !== id)
        } catch (error) {
            console.error('Error deleting notification:', error)
        }
    }

    const clearAll = async () => {
        try {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 300))

            notifications.value = []
        } catch (error) {
            console.error('Error clearing notifications:', error)
        }
    }

    return {
        notifications,
        isLoading,
        unreadCount,
        fetchNotifications,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        clearAll
    }
})

// Mock data - 25 diverse notifications
function getMockNotifications() {
    const now = Date.now()

    return [
        // Recent - Unread (Last hour)
        {
            id: 1,
            type: 'order',
            title: 'New Order Received',
            message: 'You have received a new order #12345 worth ₹2,450 from Rajesh Kumar',
            link: '/transactions',
            is_read: false,
            created_at: new Date(now - 5 * 60000).toISOString() // 5 mins ago
        },
        {
            id: 2,
            type: 'payment',
            title: 'Payment Received',
            message: 'Payment of ₹3,200 received for order #12340 via UPI',
            link: '/transactions',
            is_read: false,
            created_at: new Date(now - 15 * 60000).toISOString() // 15 mins ago
        },
        {
            id: 3,
            type: 'inventory',
            title: 'Critical Stock Alert',
            message: 'Basmati Rice (5kg) is critically low. Only 5 units remaining. Restock immediately!',
            link: '/inventory',
            is_read: false,
            created_at: new Date(now - 30 * 60000).toISOString() // 30 mins ago
        },
        {
            id: 4,
            type: 'order',
            title: 'Order Completed',
            message: 'Order #12338 has been successfully completed and delivered',
            link: '/transactions',
            is_read: false,
            created_at: new Date(now - 45 * 60000).toISOString() // 45 mins ago
        },

        // Today - Some Read, Some Unread
        {
            id: 5,
            type: 'inventory',
            title: 'Low Stock Alert',
            message: 'Toor Dal (1kg) is running low. Only 12 units remaining',
            link: '/inventory',
            is_read: false,
            created_at: new Date(now - 2 * 3600000).toISOString() // 2 hours ago
        },
        {
            id: 6,
            type: 'user',
            title: 'New Employee Added',
            message: 'Priya Sharma has been added as a cashier to your team',
            link: '/add-employee',
            is_read: true,
            created_at: new Date(now - 3 * 3600000).toISOString() // 3 hours ago
        },
        {
            id: 7,
            type: 'success',
            title: 'Inventory Updated Successfully',
            message: '45 items have been successfully added to your inventory',
            link: '/inventory',
            is_read: true,
            created_at: new Date(now - 4 * 3600000).toISOString() // 4 hours ago
        },
        {
            id: 8,
            type: 'warning',
            title: 'Multiple Failed Login Attempts',
            message: 'There were 3 failed login attempts from IP 192.168.1.105',
            link: '/settings',
            is_read: false,
            created_at: new Date(now - 5 * 3600000).toISOString() // 5 hours ago
        },
        {
            id: 9,
            type: 'payment',
            title: 'Refund Processed',
            message: 'Refund of ₹850 has been processed for order #12335',
            link: '/refund',
            is_read: true,
            created_at: new Date(now - 6 * 3600000).toISOString() // 6 hours ago
        },

        // Yesterday
        {
            id: 10,
            type: 'order',
            title: 'Bulk Order Received',
            message: 'Large order #12330 worth ₹15,750 received from Corporate Client',
            link: '/transactions',
            is_read: true,
            created_at: new Date(now - 24 * 3600000).toISOString() // 1 day ago
        },
        {
            id: 11,
            type: 'system',
            title: 'System Update Available',
            message: 'A new version (v2.5.0) is available with bug fixes and improvements',
            link: '/settings',
            is_read: false,
            created_at: new Date(now - 25 * 3600000).toISOString()
        },
        {
            id: 12,
            type: 'inventory',
            title: 'Stock Updated',
            message: 'Wheat Flour (10kg) stock has been replenished. New count: 50 units',
            link: '/inventory',
            is_read: true,
            created_at: new Date(now - 26 * 3600000).toISOString()
        },
        {
            id: 13,
            type: 'payment',
            title: 'Daily Sales Report',
            message: 'Yesterday\'s total sales: ₹28,450 from 47 transactions',
            link: '/transactions',
            is_read: true,
            created_at: new Date(now - 30 * 3600000).toISOString()
        },

        // 2-3 Days Ago
        {
            id: 14,
            type: 'warning',
            title: 'Subscription Expiring Soon',
            message: 'Your ReadyBill subscription will expire in 7 days. Renew now to continue service',
            link: '/settings',
            is_read: false,
            created_at: new Date(now - 48 * 3600000).toISOString() // 2 days ago
        },
        {
            id: 15,
            type: 'order',
            title: 'Order Cancelled',
            message: 'Order #12325 has been cancelled by customer. Refund initiated',
            link: '/transactions',
            is_read: true,
            created_at: new Date(now - 50 * 3600000).toISOString()
        },
        {
            id: 16,
            type: 'user',
            title: 'Employee Shift Updated',
            message: 'Amit Verma\'s shift has been changed to morning (9 AM - 5 PM)',
            link: '/add-employee',
            is_read: true,
            created_at: new Date(now - 60 * 3600000).toISOString()
        },
        {
            id: 17,
            type: 'success',
            title: 'Data Backup Completed',
            message: 'Your store data has been successfully backed up to cloud storage',
            link: '/dataset',
            is_read: true,
            created_at: new Date(now - 72 * 3600000).toISOString() // 3 days ago
        },

        // Last Week
        {
            id: 18,
            type: 'inventory',
            title: 'Product Added',
            message: 'New product "Organic Honey (500g)" has been added to inventory',
            link: '/inventory',
            is_read: true,
            created_at: new Date(now - 96 * 3600000).toISOString() // 4 days ago
        },
        {
            id: 19,
            type: 'payment',
            title: 'Weekly Sales Summary',
            message: 'This week\'s total sales: ₹1,85,500 from 312 transactions',
            link: '/transactions',
            is_read: true,
            created_at: new Date(now - 120 * 3600000).toISOString() // 5 days ago
        },
        {
            id: 20,
            type: 'error',
            title: 'Payment Gateway Error',
            message: 'Transaction #TXN45678 failed due to payment gateway timeout. Please retry',
            link: '/transactions',
            is_read: true,
            created_at: new Date(now - 144 * 3600000).toISOString() // 6 days ago
        },
        {
            id: 21,
            type: 'info',
            title: 'New Feature Available',
            message: 'Check out our new WhatsApp integration for order notifications!',
            link: '/settings',
            is_read: false,
            created_at: new Date(now - 156 * 3600000).toISOString() // 6.5 days ago
        },

        // Older Notifications
        {
            id: 22,
            type: 'system',
            title: 'Maintenance Scheduled',
            message: 'System maintenance scheduled for Sunday 2 AM - 4 AM. Services may be temporarily unavailable',
            link: '/settings',
            is_read: true,
            created_at: new Date(now - 192 * 3600000).toISOString() // 8 days ago
        },
        {
            id: 23,
            type: 'order',
            title: 'Order Milestone Reached',
            message: 'Congratulations! You have completed 1000 orders on ReadyBill',
            link: '/transactions',
            is_read: true,
            created_at: new Date(now - 240 * 3600000).toISOString() // 10 days ago
        },
        {
            id: 24,
            type: 'inventory',
            title: 'Inventory Audit Reminder',
            message: 'It\'s time for your monthly inventory audit. Last audit was 30 days ago',
            link: '/inventory',
            is_read: false,
            created_at: new Date(now - 288 * 3600000).toISOString() // 12 days ago
        },
        {
            id: 25,
            type: 'success',
            title: 'GST Return Filed',
            message: 'Your GST return for last month has been successfully filed',
            link: '/dataset',
            is_read: true,
            created_at: new Date(now - 336 * 3600000).toISOString() // 14 days ago
        }
    ]
}
