import { defineStore } from 'pinia';
import api from '@/config/api';

export const useSubscriptionStore = defineStore('subscription', {
    state: () => ({
        plans: [],
        isSubscriptionExpired: false,
        selectedPlanId: null,
        loading: false,
        error: null,
    }),

    getters: {
        activePlans: (state) => state.plans.filter(plan => plan.active === 1),
        selectedPlan: (state) => state.plans.find(plan => plan.subscription_id === state.selectedPlanId),
    },

    actions: {
        async fetchSubscriptionPlans() {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}subscription-plans`);

                if (data.status === 'success') {
                    this.plans = data.data;
                    this.isSubscriptionExpired = data.isSubscriptionExpired === 1;

                    // Set first plan as default selected
                    if (this.plans.length > 0 && !this.selectedPlanId) {
                        this.selectedPlanId = this.plans[0].subscription_id;
                    }
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch subscription plans';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        selectPlan(planId) {
            this.selectedPlanId = planId;
        },

        clearError() {
            this.error = null;
        },
    },
});
