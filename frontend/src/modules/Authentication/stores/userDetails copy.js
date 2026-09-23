// stores/userDetails.js
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
        isSyncing: false, // Track if sync is in progress
    }),

    getters: {
        // Check if data is stale (older than 30 seconds for instant feel)
        isDataStale: (state) => {
            if (!state.lastSyncTime) return true
            const thirtySeconds = 30 * 1000 // Reduced from 5 minutes
            return (Date.now() - state.lastSyncTime) > thirtySeconds
        }
    },

    actions: {
        async fetchUserProfile(showLoading = true) {
            // Prevent duplicate concurrent requests
            if (this.isSyncing && !showLoading) {
                // console.log('⏭️ Sync already in progress, skipping...')
                return false
            }

            this.isSyncing = true
            if (showLoading) this.isLoading = true

            try {
                const token = Cookies.get('auth_token')

                if (!token) {
                    // console.warn('⚠️ No auth token found, skipping profile fetch')
                    return false
                }

                const response = await api.get('/user-detail', {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })

                if (response.data.status === 'success') {
                    const data = response.data.data;
                    const shopSubscription = response.data.shop_subscription || {};
                    const item_categories = response.data.item_categories || {};
                    const currentPlanData = shopSubscription?.current_plan_data || null;

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
                        item_categories: item_categories,
                        currentPlanData: currentPlanData,
                        lastSyncTime: Date.now(),
                        subscriptionError: false
                    });

                    return true;
                } else {
                    this.subscriptionError = true
                    return false
                }
            } catch (error) {
                // console.error('❌ Error fetching profile:', error)
                this.subscriptionError = true
                return false
            } finally {
                this.isSyncing = false
                if (showLoading) this.isLoading = false
            }
        },

        // Initialize instant sync: runs immediately on page load
        initializeSync() {
            const token = Cookies.get('auth_token')
            if (!token) {
                // console.log('⚠️ No auth token, skipping sync initialization')
                return
            }

            // console.log('⚡ Instant sync initialized')

            // Immediate fetch (non-blocking, no await)
            this.fetchUserProfile(false)

            // Start aggressive background sync (every 30 seconds)
            this.startBackgroundSync()
        },

        // Start instant background sync every 30 seconds
        startBackgroundSync() {
            this.stopBackgroundSync()

            const syncIntervalSeconds = 30 // Instant sync every 30 seconds
            const syncIntervalMs = syncIntervalSeconds * 1000

            // console.log(`⚡ Instant background sync enabled (every ${syncIntervalSeconds}s)`)

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

        // async fetchCurrentPlanData() {
        //     try {
        //         const token = Cookies.get('auth_token');

        //         if (!token) {
        //             return null;
        //         }

        //         const response = await api.get('/user-detail', {
        //             headers: {
        //                 Authorization: `Bearer ${token}`,
        //             },
        //         });

        //         if (response.data.status === 'success') {
        //             const shopSubscription = response.data?.shop_subscription || {};

        //             console.log('shopSubscription', shopSubscription);

        //             const currentPlanData = shopSubscription?.current_plan_data || null;

        //             this.shop_subscription = shopSubscription;
        //             this.currentPlanData = currentPlanData;

        //             return currentPlanData;
        //         }

        //         this.currentPlanData = null;
        //         return null;
        //     } catch (error) {
        //         this.currentPlanData = null;
        //         return null;
        //     }
        // },

        async fetchCurrentPlanData(forceRefresh = false) {
            try {
                const token = Cookies.get('auth_token');

                if (!token) {
                    this.currentPlanData = null;
                    this.shop_subscription = {};
                    return null;
                }

                const hasShopSubscription =
                    this.shop_subscription &&
                    Object.keys(this.shop_subscription).length > 0;

                if (forceRefresh || !hasShopSubscription) {
                    await this.fetchUserProfile(false);
                }

                const shopSubscription = this.shop_subscription || {};
                const currentPlanData = shopSubscription?.current_plan_data || null;

                this.currentPlanData = currentPlanData;

                return {
                    planName: currentPlanData?.plan_name || null,
                    expiryDate: shopSubscription?.end_date || null,
                    currentPlanData,
                    shopSubscription,
                };
            } catch (error) {
                this.currentPlanData = null;
                return null;
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

                return this.item_categories || []
            } catch (error) {
                this.item_categories = []
                return []
            }
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
        // ⚡ Instant sync on reload - no await, runs immediately
        afterRestore: (context) => {
            // console.log('⚡ Persisted data restored, triggering instant sync...')
            const store = context.store

            // Non-blocking instant sync
            store.initializeSync()
        }
    }
})
