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
            preference_barcode: false,
            preference_invoice_gst_complaint: false, 
            preference_purchase_price: false, 
            // preference_category: false, 
            preference_sku: false, 
            preference_l1_category: false, 
            preference_l2_category: false, 
            gstin: '',                               
            signature: null,                        
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
                const response = await api.get(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}user-preferences`)

                const data = response.data.data   // preferences row
                const shop = response.data.shop   // shop row (gstin, signature)

                this.preferences = {
                    // ── Boolean preference fields — cast from 0/1 ──────────────
                    preference_mrp: data.preference_mrp == 1,
                    preference_quantity: data.preference_quantity == 1,
                    preference_hsn: data.preference_hsn == 1,
                    preference_mrp_invoice: data.preference_mrp_invoice == 1,
                    preference_hsn_invoice: data.preference_hsn_invoice == 1,
                    preference_transaction_mark_as_paid: data.preference_transaction_mark_as_paid == 1,
                    preference_barcode: data.preference_barcode == 1,

                    // ── Non-boolean preference field ───────────────────────────
                    preference_invoice_format: data.preference_invoice_format ?? 0,

                    // ── GST master toggle ──────────────────────────────────────
                    preference_invoice_gst_complaint: data.preference_invoice_gst_complaint == 1,

                    
                    // ── From shop row ──────────────────────────────────────────
                    gstin: data?.gstin ?? '',   // e.g. "07ABCDE1234F1Z5"
                    signature: shop?.signature ?? null, // e.g. "1776485385_images.png"
                    preference_purchase_price: data.preference_purchase_price == 1,
                    // preference_category: data.preference_category == 1,
                    preference_sku: data.preference_sku == 1,
                    preference_l1_category: data.preference_l1_category == 1,
                    preference_l2_category: data.preference_l2_category == 1,
                }

            } catch (error) {
                const errorMessage = error.response?.data?.message || this.$t('Failed to fetch preferences')

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
                // Use FormData because signature may be a File object
                const formData = new FormData()

                // ── Boolean fields → 0/1 integers ──────────────────────────
                formData.append('preference_mrp', preferences.preference_mrp ? 1 : 0)
                formData.append('preference_quantity', preferences.preference_quantity ? 1 : 0)
                formData.append('preference_hsn', preferences.preference_hsn ? 1 : 0)
                formData.append('preference_mrp_invoice', preferences.preference_mrp_invoice ? 1 : 0)
                formData.append('preference_hsn_invoice', preferences.preference_hsn_invoice ? 1 : 0)
                formData.append('preference_transaction_mark_as_paid', preferences.preference_transaction_mark_as_paid ? 1 : 0)
                formData.append('preference_barcode', preferences.preference_barcode ? 1 : 0)

                // ── Non-boolean fields ──────────────────────────────────────
                formData.append('preference_invoice_format', preferences.preference_invoice_format)

                // ── New fields ──────────────────────────────────────────────
                formData.append('preference_invoice_gst_complaint', preferences.preference_invoice_gst_complaint ? 1 : 0)
                formData.append('preference_purchase_price', preferences.preference_purchase_price ? 1 : 0)
                // formData.append('preference_category', preferences.preference_category ? 1 : 0)
                formData.append('preference_sku', preferences.preference_sku ? 1 : 0)
                formData.append('preference_l1_category', preferences.preference_l1_category ? 1 : 0)
                formData.append('preference_l2_category', preferences.preference_l2_category ? 1 : 0)
                formData.append('gstin', preferences.gstin ?? '')

                // Only append signature if a new File was selected
                // If it's a string (existing URL) or null, skip — server keeps the existing one
                if (preferences.signature instanceof File) {
                    formData.append('signature', preferences.signature)
                }

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}preference`,
                    formData,
                    { headers: { 'Content-Type': 'multipart/form-data' } }
                )

                // Sync store state — preserve server-returned signature URL if provided
                this.preferences = {
                    ...preferences,
                    preference_invoice_gst_complaint: preferences.preference_invoice_gst_complaint,
                    preference_purchase_price: preferences.preference_purchase_price,
                    gstin: preferences.gstin,
                    signature: data.data?.signature ?? this.preferences.signature,
                }

                this.saveSuccess = true

                return { success: true, data, message: data.message }

            } catch (error) {
                const errorMessage = error.response?.data?.message || this.$t('Failed to update preferences')

                if (error.response?.data?.data) {
                    this.errors = Object.values(error.response.data.data).flat()
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