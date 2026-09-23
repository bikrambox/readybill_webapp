import { defineStore } from 'pinia'
import api from '@/config/api'

export const useContactStore = defineStore('contact', {
    state: () => ({
        isSubmitting: false,
        errors: [],
        successMessage: ''
    }),

    actions: {
        async submitContactForm(formData) {
            this.isSubmitting = true
            this.errors = []
            this.successMessage = ''

            try {
                const { data } = await api.post('/contact-submit', {
                    full_name: formData.fullName,
                    contact_no: formData.contactNo,
                    email: formData.email,
                    message: formData.message
                })

                this.successMessage = data.message || this.$t('common.Form submitted successfully') + '!'
                return { success: true, data }
            } catch (error) {
                // Handle different types of errors
                if (error.response) {
                    // Server responded with error status
                    if (error.response.status === 422) {
                        // Laravel validation errors
                        const validationErrors = error.response.data.errors
                        this.errors = Object.values(validationErrors).flat()
                    } else if (error.response.status === 500) {
                        // Server error
                        this.errors = [this.$t('common.Internal server error') + '.' + this.$t('common.Please try again later') + '.']
                    } else if (error.response.status === 404) {
                        // Not found
                        this.errors = [this.$t('common.API endpoint not found') + '.' + this.$t('common.Please contact support') + '.']
                    } else if (error.response.status === 403) {
                        // Forbidden
                        this.errors = [this.$t('common.Access denied') + '.' + this.$t('common.You do not have permission to perform this action') + '.']
                    } else if (error.response.status === 401) {
                        // Unauthorized
                        this.errors = [this.$t('common.Session expired') + '.' + this.$t('common.Please login again') + '.']
                    } else if (error.response.data && error.response.data.message) {
                        // Generic server error with message
                        this.errors = [error.response.data.message]
                    } else {
                        // Other server errors
                        this.errors = [`${this.$t('common.Server error')} (${error.response.status}). ${this.$t('common.Please try again')}.`]
                    }
                } else if (error.request) {
                    // Request was made but no response received (network error)
                    this.errors = [`${this.$t('common.Network error')}. ${this.$t('common.Please check your internet connection and try again')}.`]
                } else {
                    // Something else happened
                    this.errors = [error.message || this.$t('common.An unexpected error occurred. Please try again.')]
                }

                return { success: false, error }
            } finally {
                this.isSubmitting = false
            }
        },

        clearErrors() {
            this.errors = []
        },

        clearSuccess() {
            this.successMessage = ''
        }
    }
})
