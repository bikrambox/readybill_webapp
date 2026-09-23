import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import Cookies from 'js-cookie'
import apiAgent from '@/config/apiAgent'


const API_KEY = 'agent_api_key'

const authHeader = () => {
    const token = Cookies.get('agent_token')
    const apiKey = Cookies.get(API_KEY)
    return {
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
        ...(apiKey ? { 'auth-key': apiKey } : {}),
    }
}

export const useAssignSubscriptionStore = defineStore('assignSubscription', () => {

    // ─── State ───────────────────────────────────────────────────────────────
    const searchResults = ref([])
    const selectedShop = ref(null)
    const plans = ref([])
    const earnings = ref(null)
    const isSearching = ref(false)
    const isSubmitting = ref(false)
    const isLoadingPlans = ref(false)
    const isLoadingEarnings = ref(false)
    const searchError = ref(null)
    const submitError = ref(null)
    const plansError = ref(null)
    const earningsError = ref(null)
    const submitSuccess = ref(false)

    // ─── Getters ─────────────────────────────────────────────────────────────
    const hasResults = computed(() => searchResults.value.length > 0)
    const hasSelected = computed(() => selectedShop.value !== null)

    // ─── Mappers ─────────────────────────────────────────────────────────────
    const mapShop = (raw) => ({
        entityId: raw.entity_id ?? '—',
        shopId: raw.shop_id,
        shopName: raw.name || raw.shop_name || '(No Name)',
        businessName: raw.business_name ?? '—',
        email: raw.email ?? '—',
        mobile: raw.mobile ?? '—',
        address: raw.address ?? '—',
        gstin: raw.gstin ?? '—',
        logo: raw.logo ?? null,
        registeredOn: raw.created_at,
        subscriptionStatus: raw.subscription_status ?? 'inactive',
        subscriptionPlan: raw.subscription_plan ?? null,
        subscriptionExpiry: raw.subscription_expiry ?? null,
        _connection: raw._connection ?? null,
    })

    // const mapPlan = (raw) => ({
    //     id: raw.subscription_id,
    //     name: raw.name ?? 'Plan',
    //     price: parseFloat(raw.price ?? raw.amount ?? 0),
    //     period: raw.period ?? 'mo',
    //     icon: raw.icon ?? 'bi bi-box',
    //     popular: Boolean(raw.popular ?? false),
    //     features: (() => {
    //         if (!raw.features) return []
    //         if (Array.isArray(raw.features)) return raw.features
    //         try { return JSON.parse(raw.features) } catch { return [] }
    //     })(),
    // })

    const mapPlan = (raw) => ({
        id: raw.subscription_id,
        name: raw.plan_name ?? 'Plan',
        heading: raw.heading ?? null,
        subheading: raw.subheading ?? null,
        description: raw.description ?? null,
        price: parseFloat(raw.price ?? 0),
        months: parseInt(raw.months ?? 1),
        isBestValue: parseInt(raw.is_best_value ?? 0) === 1,
        period: raw.period ?? 'mo',
        icon: raw.icon ?? 'bi bi-box',
        popular: Boolean(raw.popular ?? false),
        features: (() => {
            if (!raw.features) return []
            if (Array.isArray(raw.features)) return raw.features
            try { return JSON.parse(raw.features) } catch { return [] }
        })(),
    })

    // ─── Actions ─────────────────────────────────────────────────────────────

    const fetchPlans = async () => {
        plansError.value = null
        isLoadingPlans.value = true

        try {
            const response = await apiAgent.get(
                '/authorized-agent/subscription-plans',
                { headers: { ...authHeader() } }
            )
            plans.value = (response.data?.data ?? []).map(mapPlan)

        } catch (err) {
            plansError.value = err.response?.data?.message ?? 'Failed to load plans.'
            plans.value = []
        } finally {
            isLoadingPlans.value = false
        }
    }

    const searchShop = async (query) => {
        searchError.value = null
        searchResults.value = []
        isSearching.value = true

        try {
            const isEntityId = /^\d{9,}$/.test(query)

            const response = await apiAgent.post(
                '/authorized-agent/shop-search',
                {
                    shop_name: !isEntityId ? query : null,
                    entity_id: isEntityId ? query : null,
                },
                { headers: { ...authHeader() } }
            )
            searchResults.value = (response.data?.data ?? []).map(mapShop)

        } catch (err) {
            searchError.value = err.response?.data?.message ?? 'Search failed. Please try again.'
            searchResults.value = []
        } finally {
            isSearching.value = false
        }
    }

    const assignSubscription = async ({ shop_module, shop_id, subscription_id, payment_mode, notes }) => {
        if (!selectedShop.value) return

        submitError.value = null
        submitSuccess.value = false
        isSubmitting.value = true

        try {
            const response = await apiAgent.post(
                '/authorized-agent/assign-subscription',
                {
                    shop_module,      // ← DB connection e.g. 'grocery_india'
                    shop_id,          // ← numeric
                    subscription_id,  // ← numeric plan id
                    payment_mode,     // ← 'cash' | 'online'
                    notes,
                },
                { headers: { ...authHeader() } }
            )

            selectedShop.value = {
                ...selectedShop.value,
                subscriptionStatus: 'active',
            }

            submitSuccess.value = true
            return response.data

        } catch (err) {
            submitError.value = err.response?.data?.message ?? 'Failed to activate subscription.'
            throw err  // ← re-throw so component can catch 401 and redirect
        } finally {
            isSubmitting.value = false
        }
    }

    const fetchEarnings = async () => {
        earningsError.value = null
        isLoadingEarnings.value = true

        try {
            const response = await apiAgent.post(
                '/authorized-agent/earnings',
                {},
                { headers: { ...authHeader() } }
            )
            earnings.value = response.data?.data ?? response.data

        } catch (err) {
            earningsError.value = err.response?.data?.message ?? 'Failed to load earnings.'
        } finally {
            isLoadingEarnings.value = false
        }
    }

    const selectShop = (shop) => {
        selectedShop.value = shop
        submitSuccess.value = false
        submitError.value = null
    }

    const clearSelectedShop = () => {
        selectedShop.value = null
        submitSuccess.value = false
        submitError.value = null
    }

    const reset = () => {
        searchResults.value = []
        selectedShop.value = null
        earnings.value = null
        plans.value = []
        searchError.value = null
        submitError.value = null
        plansError.value = null
        earningsError.value = null
        submitSuccess.value = false
        isSearching.value = false
        isSubmitting.value = false
        isLoadingPlans.value = false
        isLoadingEarnings.value = false
    }

    return {
        // state
        searchResults, selectedShop, plans, earnings,
        isSearching, isSubmitting, isLoadingPlans, isLoadingEarnings,
        searchError, submitError, plansError, earningsError, submitSuccess,
        // getters
        hasResults, hasSelected,
        // actions
        fetchPlans,
        searchShop,
        assignSubscription,
        fetchEarnings, selectShop, clearSelectedShop, reset,
    }
})