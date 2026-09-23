import { defineStore } from 'pinia'
import api from '@/config/api'
import Cookies from 'js-cookie'

export const useUserDetailsStore = defineStore('userDetails', {
    state: () => ({
        userData: {
            user_id: '',
            name: '',
            email: '',
            mobile: '',
            address: '',
            business_name: '',
            gstin: '',
            logo: '',
            logo_url: '',
            api_key: '',
            isAdmin: 0,
            countryCode: '+91',
            shop_type: '',
            entity_id: '',
            staffPhoto: '',
            isLogo: 0,
            isPhoto: 0
        },
        isLoading: false,
        subscriptionError: false,
        subscription_expiry_date: '',
        subscription_current_plan: '',
        isSubscriptionExpired: 0,
        subscriptionExpiredMessage: null,
        subscription_id: 0,
        all_languages: [],
        shop_subscription: {},
        currentPlanData: null,
        preferences: {},
        country_details: {},
        item_categories: [],
        otpConfig: {
            expiry: import.meta.env.VITE_OTP_EXPIRY || 5,
            maxAttempts: import.meta.env.VITE_OTP_COUNT || 4,
            countdown: import.meta.env.VITE_COUNTDOWN || 60
        },
        syncInterval: null,
        lastSyncTime: null,
        isSyncing: false,
    }),

    getters: {
        isDataStale: (state) => {
            if (!state.lastSyncTime) return true
            const thirtySeconds = 30 * 1000
            return (Date.now() - state.lastSyncTime) > thirtySeconds
        },

        level1Categories: (state) => {
            return (state.item_categories || []).filter(
                (category) => category.parent_id === null
            )
        },

        hasCategories: (state) => {
            return Array.isArray(state.item_categories) && state.item_categories.length > 0
        }
    },

    actions: {
        async fetchUserProfile(showLoading = true) {
            if (this.isSyncing && !showLoading) {
                return false
            }

            this.isSyncing = true
            if (showLoading) this.isLoading = true

            try {
                const token = Cookies.get('auth_token')

                if (!token) {
                    return false
                }

                const response = await api.get('/user-detail', {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })

                if (response.data.status === 'success') {
                    const data = response.data.data
                    const shopSubscription = response.data.shop_subscription || {}
                    const item_categories = response.data.item_categories || []
                    const currentPlanData = shopSubscription?.current_plan_data || null

                    this.$patch({
                        userData: {
                            user_id: data.user_id || '',
                            mobile: data.mobile || '',
                            name: data.details?.name || '',
                            email: data.details?.email || '',
                            business_name: data.details?.business_name || '',
                            address: data.details?.address || '',
                            gstin: data.details?.gstin || '',
                            logo: data.details?.logo || '',
                            logo_url: response.data.logo || '',
                            api_key: data.api_key || '',
                            isAdmin: data.isAdmin || 0,
                            countryCode: data.country_details?.dial_code || '',
                            country_code: data.country_details?.code || '',
                            shop_type: data.shop_type || '',
                            entity_id: response.data.entity_id || '',
                            staffPhoto: response.data.staffPhoto || '',
                            isLogo: response.data.isLogo || 0,
                            isPhoto: response.data.isPhoto || 0
                        },
                        country_details: data.country_details || {},
                        subscription_expiry_date: response.data.subscription_expiry_date || '',
                        subscription_current_plan: response.data.subscription_current_plan || '',
                        isSubscriptionExpired: response.data.isSubscriptionExpired || 0,
                        subscription_id: response.data.subscription_id || 0,
                        subscriptionExpiredMessage: response.data.subscriptionExpiredMessage || null,
                        all_languages: response.data.all_languages || [],
                        preferences: response.data.preferences || {},
                        shop_subscription: shopSubscription,
                        item_categories: Array.isArray(item_categories) ? item_categories : [],
                        currentPlanData: currentPlanData,
                        lastSyncTime: Date.now(),
                        subscriptionError: false
                    })

                    return true
                } else {
                    this.subscriptionError = true
                    return false
                }
            } catch (error) {
                this.subscriptionError = true
                return false
            } finally {
                this.isSyncing = false
                if (showLoading) this.isLoading = false
            }
        },

        initializeSync() {
            const token = Cookies.get('auth_token')
            if (!token) {
                return
            }

            this.fetchUserProfile(false)
            this.startBackgroundSync()
        },

        startBackgroundSync() {
            this.stopBackgroundSync()

            const syncIntervalSeconds = 30
            const syncIntervalMs = syncIntervalSeconds * 1000

            this.syncInterval = setInterval(() => {
                const token = Cookies.get('auth_token')
                if (token && this.isDataStale) {
                    console.log('⚡ Instant background sync triggered')
                    this.fetchUserProfile(false)
                } else if (!token) {
                    console.log('⚠️ No auth token, stopping background sync')
                    this.stopBackgroundSync()
                }
            }, syncIntervalMs)
        },

        stopBackgroundSync() {
            if (this.syncInterval) {
                clearInterval(this.syncInterval)
                this.syncInterval = null
                console.log('⏹️ Background sync stopped')
            }
        },

        clearProfile() {
            this.stopBackgroundSync()
            this.$reset()
        },

        async refreshProfile() {
            console.log('🔄 Manual instant refresh triggered')
            return await this.fetchUserProfile(true)
        },

        async fetchCurrentPlanData(forceRefresh = false) {
            try {
                const token = Cookies.get('auth_token')

                if (!token) {
                    this.currentPlanData = null
                    this.shop_subscription = {}
                    return null
                }

                const hasShopSubscription =
                    this.shop_subscription &&
                    Object.keys(this.shop_subscription).length > 0

                if (forceRefresh || !hasShopSubscription) {
                    await this.fetchUserProfile(false)
                }

                const shopSubscription = this.shop_subscription || {}
                const currentPlanData = shopSubscription?.current_plan_data || null

                this.currentPlanData = currentPlanData

                return {
                    planName: currentPlanData?.plan_name || null,
                    expiryDate: shopSubscription?.end_date || null,
                    currentPlanData,
                    shopSubscription,
                }
            } catch (error) {
                this.currentPlanData = null
                return null
            }
        },

        async fetchCategories(forceRefresh = false) {
            try {
                const token = Cookies.get('auth_token')

                if (!token) {
                    this.item_categories = []
                    return []
                }

                const hasCategories =
                    Array.isArray(this.item_categories) && this.item_categories.length > 0

                if (forceRefresh || !hasCategories) {
                    await this.fetchUserProfile(false)
                }

                return Array.isArray(this.item_categories) ? this.item_categories : []
            } catch (error) {
                this.item_categories = []
                return []
            }
        },

        getLevel1Categories() {
            return (this.item_categories || []).filter(
                (category) => category.parent_id === null
            )
        },

        getSubCategories(parentId) {
            if (!parentId) return []

            return (this.item_categories || []).filter(
                (category) => Number(category.parent_id) === Number(parentId)
            )
        },

        getCategoryById(categoryId) {
            if (!categoryId) return null

            return (this.item_categories || []).find(
                (category) => Number(category.id) === Number(categoryId)
            ) || null
        },

        setItemCategories(categories = []) {
            this.item_categories = Array.isArray(categories) ? categories : []
        }
    },

    persist: {
        key: 'userDetails',
        storage: localStorage,
        paths: [
            'userData',
            'subscription_expiry_date',
            'subscription_current_plan',
            'isSubscriptionExpired',
            'subscription_id',
            'all_languages',
            'shop_subscription',
            'preferences',
            'lastSyncTime',
            'item_categories'
        ],
        afterRestore: (context) => {
            const store = context.store
            store.initializeSync()
        }
    }
})