import { defineStore } from 'pinia';
import api from '@/config/api';

export const useSupportStore = defineStore('support', {
    state: () => ({
        queries: [],
        isSubmitting: false,
        errors: {},
        successMessage: '',
    }),

    actions: {
        async createQuery(formData) {
            this.isSubmitting = true;
            this.errors = {};
            this.successMessage = '';

            try {
                const { data } = await api.post('/create-query', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                this.successMessage = data.message || 'Query submitted successfully';

                // Optionally store the created query
                if (data.query) {
                    this.queries.unshift(data.query);
                }

                return { success: true, data };
            } catch (error) {
                if (error.response?.status === 422) {
                    // Laravel validation errors
                    this.errors = error.response.data.errors || {};
                } else {
                    this.errors = {
                        general: error.response?.data?.message || 'An error occurred while submitting your query',
                    };
                }
                return { success: false, errors: this.errors };
            } finally {
                this.isSubmitting = false;
            }
        },

        clearErrors() {
            this.errors = {};
            this.successMessage = '';
        },
    },
});
