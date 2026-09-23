import { defineStore } from 'pinia'
import api from '@/config/api'

export const useUserPreferencesStore = defineStore('userPreferences', {
    state: () => ({
        preferences: {
            preference_mrp: true,
            preference_quantity: false,
            preference_hsn: false,
            preference_mrp_invoice: false,
            preference_hsn_invoice: false,
            preference_invoice_format: 0, // 0=A4, 1=80mm, 2=50mm
            preference_transaction_mark_as_paid: false,
            preference_transaction_mark_as_unpaid: true
        },
        loading: false,
        errors: [],
        saveSuccess: false
    }),

    getters: {
        isLoading: (state) => state.loading,
        hasErrors: (state) => state.errors.length > 0,
        getPreferences: (state) => state.preferences
    },

    actions: {

        async fetchUserPreferences() {
            this.loading = true
            this.errors = []

            try {
                const response = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}user-preferences`)


                const data = response.data.data;

                // const data = response.data;

                // this.preferences = {
                //     preference_mrp: data.preference_mrp ?? true,
                //     preference_quantity: data.preference_quantity ?? false,
                //     preference_hsn: data.preference_hsn ?? false,
                //     preference_mrp_invoice: data.preference_mrp_invoice ?? false,
                //     preference_hsn_invoice: data.preference_hsn_invoice ?? false,
                //     preference_invoice_format: data.preference_invoice_format ?? 0,
                //     preference_transaction_mark_as_paid: data.preference_transaction_mark_as_paid ?? false,
                //     preference_transaction_mark_as_unpaid: data.preference_transaction_mark_as_unpaid ?? true
                // }


                this.preferences = {
                    preference_mrp: data.preference_mrp == 1,
                    preference_quantity: data.preference_quantity == 1,
                    preference_hsn: data.preference_hsn == 1,
                    preference_mrp_invoice: data.preference_mrp_invoice == 1,
                    preference_hsn_invoice: data.preference_hsn_invoice == 1,
                    preference_invoice_format: data.preference_invoice_format ?? 0,
                    preference_transaction_mark_as_paid: data.preference_transaction_mark_as_paid == 1,
                    preference_transaction_mark_as_unpaid: data.preference_transaction_mark_as_unpaid == 1
                };


            } catch (error) {
                const errorMessage = error.response?.data?.message || 'Failed to fetch preferences'

                // Handle Laravel validation errors
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat()
                } else {
                    this.errors = [errorMessage]
                }

                console.error('Error fetching preferences:', error)
            } finally {
                this.loading = false
            }
        },

        async updatePreferences(preferences) {
            this.loading = true
            this.errors = []
            this.saveSuccess = false

            try {
                const payload = {
                    preference_mrp: preferences.preference_mrp ? 1 : 0,
                    preference_quantity: preferences.preference_quantity ? 1 : 0,
                    preference_hsn: preferences.preference_hsn ? 1 : 0,
                    preference_mrp_invoice: preferences.preference_mrp_invoice ? 1 : 0,
                    preference_hsn_invoice: preferences.preference_hsn_invoice ? 1 : 0,
                    preference_invoice_format: preferences.preference_invoice_format,
                    preference_transaction_mark_as_paid: preferences.preference_transaction_mark_as_paid ? 1 : 0,
                    preference_transaction_mark_as_unpaid: preferences.preference_transaction_mark_as_unpaid ? 1 : 0
                }

                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}preference`, payload)

                this.preferences = preferences
                this.saveSuccess = true

                return { success: true, data }
            } catch (error) {
                const errorMessage = error.response?.data?.message || 'Failed to update preferences'

                // Handle Laravel validation errors
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat()
                } else {
                    this.errors = [errorMessage]
                }

                console.error('Error updating preferences:', error)
                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        clearErrors() {
            this.errors = []
        },

        resetSaveSuccess() {
            this.saveSuccess = false
        }
    }
})
