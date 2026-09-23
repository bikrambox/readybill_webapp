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
        activePlans: (state) => {
            if (!Array.isArray(state.plans)) return [];
            return state.plans.filter((plan) => Number(plan.active) === 1);
        },

        selectedPlan: (state) => {
            if (!Array.isArray(state.plans)) return null;
            return (
                state.plans.find(
                    (plan) => Number(plan.subscription_id) === Number(state.selectedPlanId)
                ) || null
            );
        },
    },

    actions: {
        async fetchSubscriptionPlans() {
            this.loading = true;
            this.error = null;

            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}subscription-plans`
                );

                if (data.status === 'success') {
                    let fetchedPlans = [];

                    if (Array.isArray(data.data)) {
                        fetchedPlans = data.data;
                    } else if (Array.isArray(data.data?.plans)) {
                        fetchedPlans = data.data.plans;
                    }

                    this.plans = fetchedPlans;
                    this.isSubscriptionExpired = Number(data.isSubscriptionExpired) === 1;

                    const selectedStillExists = this.plans.some(
                        (plan) =>
                            Number(plan.subscription_id) === Number(this.selectedPlanId)
                    );

                    if (!selectedStillExists) {
                        this.selectedPlanId = this.plans.length
                            ? this.plans[0].subscription_id
                            : null;
                    }
                } else {
                    this.plans = [];
                    this.selectedPlanId = null;
                    this.error = data.message || 'No subscription plans found';
                }
            } catch (error) {
                this.plans = [];
                this.selectedPlanId = null;
                this.error =
                    error.response?.data?.message || 'Failed to fetch subscription plans';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        selectPlan(planId) {
            if (!Array.isArray(this.plans) || this.plans.length === 0) {
                this.selectedPlanId = null;
                return;
            }

            const exists = this.plans.some(
                (plan) => Number(plan.subscription_id) === Number(planId)
            );

            this.selectedPlanId = exists ? planId : null;
        },

        removePlan(planId) {
            if (!Array.isArray(this.plans)) {
                this.plans = [];
                this.selectedPlanId = null;
                return;
            }

            this.plans = this.plans.filter(
                (plan) => Number(plan.subscription_id) !== Number(planId)
            );

            if (Number(this.selectedPlanId) === Number(planId)) {
                this.selectedPlanId = this.plans.length
                    ? this.plans[0].subscription_id
                    : null;
            }
        },

        clearPlans() {
            this.plans = [];
            this.selectedPlanId = null;
        },

        clearError() {
            this.error = null;
        },
    },
});