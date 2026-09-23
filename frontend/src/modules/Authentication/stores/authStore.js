// authStore.js
import { defineStore } from 'pinia'
import api from '@/config/api'
import Cookies from 'js-cookie'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: null,
        isLoading: false,
        errors: {},
        shop_subscription: null,
    }),

    getters: {
        isAuthenticated: (state) => {

            console.log('isAuthenticated login', !!state.token || !!Cookies.get('auth_token'));
            // Check both state and cookie
            // return !!state.token || !!Cookies.get('auth_token')
            return !!state.token
        }
    },

    actions: {

        async login(payload) {
            this.isLoading = true
            this.errors = {}

            try {
                const { data } = await api.post('/login', payload)

                if (data.status === 0) {
                    this.errors = data.data?.errors || {}
                    throw new Error(this.$t('common.Validation failed'))
                }

                const subscription = data.data?.[0].shop_subscription || null;
                const authData = data.data?.[0] || null
                const token = authData?.token || null
                const x_api_key = authData?.api_key || null
                const user = authData?.user || null
                const countryDetails = data.data?.[0].user.country_details || null

                // console.log('✅ Login successful - data:', data);

                this.user = user
                this.token = token
                this.shop_subscription = subscription

                // console.log('subscription', this.shop_subscription);

                // const minutes = payload.remember ? 2 : 1;
                // const cookieExpiration = minutes / 1440;

                const cookieExpiration = payload.remember ? 30 : 7 // 30 days or 7 days

                if (token) {
                    Cookies.set('auth_token', token, {
                        expires: cookieExpiration,
                        secure: false,
                        sameSite: 'Lax'
                    })
                }

                if (x_api_key) {
                    Cookies.set('x_api_key', x_api_key, {
                        expires: cookieExpiration,
                        secure: false,
                        sameSite: 'Lax'
                    })
                }

                // console.log('countryDetails', countryDetails, countryDetails.currency_symbol, countryDetails.decimal_separator);

                // // ✅ Set global variables after successful login
                // if (countryDetails && this.$app) {
                //     this.$app.config.globalProperties.$countryCode = countryDetails.code?.toLowerCase() || 'in'
                //     this.$app.config.globalProperties.$currency = countryDetails.currency_symbol || ''
                //     this.$app.config.globalProperties.$decimalSeparator = countryDetails.decimal_separator || '.'

                //     console.log('✅ Global variables set:', {
                //         countryCode: this.$app.config.globalProperties.$countryCode,
                //         currency: this.$app.config.globalProperties.$currency,
                //         decimalSeparator: this.$app.config.globalProperties.$decimalSeparator
                //     })
                // }


                // ✅ Return success indicator
                return { success: true, user, token }

                // } catch (e) {
                //     console.error('Login error:', e);
                //     console.log('Error response:', e.response);

                //     if (e.response) {
                //         const responseData = e.response.data;

                //         if (responseData.status === 0) {
                //             if (responseData.data?.errors && Array.isArray(responseData.data.errors) && responseData.data.errors.length > 0) {
                //                 this.errors = { general: responseData.data.errors };
                //             } else if (responseData.message) {
                //                 this.errors = { general: [responseData.message] };
                //             } else {
                //                 this.errors = { general: ['An error occurred. Please try again.'] };
                //             }
                //         }
                //         else if (e.response.status === 422 && responseData.errors) {
                //             this.errors = responseData.errors;
                //         }
                //         else if (e.response.status === 401 && responseData.message) {
                //             this.errors = { general: [responseData.message] };
                //         }
                //         else if (responseData.message) {
                //             this.errors = { general: [responseData.message] };
                //         }
                //         else {
                //             this.errors = {
                //                 general: [`Error ${e.response.status}: ${e.response.statusText || 'Something went wrong'}`]
                //             };
                //         }
                //     }
                //     else if (e.request) {
                //         this.errors = {
                //             general: ['Unable to connect to server. Please check your internet connection.']
                //         };
                //     }
                //     else {
                //         this.errors = {
                //             general: [e.message || 'An unexpected error occurred. Please try again.']
                //         };
                //     }

                //     // ✅ Return failure instead of throwing
                //     return { success: false, error: e };

                // } finally {
                //     this.isLoading = false;
                // }

            } catch (e) {
                // console.error('Login error:', e);
                // console.log('Error response:', e.response);

                if (e.response) {
                    const responseData = e.response.data;

                    // Case 1: Your custom API format with status: 0
                    if (responseData.status === 0) {
                        // Sub-case A: Errors in data.errors object (field-specific errors)
                        // Format: { status: 0, data: { errors: { country_code: ["Invalid credentials."] } } }
                        if (responseData.data?.errors && typeof responseData.data.errors === 'object' && !Array.isArray(responseData.data.errors)) {
                            // Check if errors object has any fields with messages
                            const errorFields = Object.keys(responseData.data.errors);
                            if (errorFields.length > 0) {
                                this.errors = responseData.data.errors; // Use field-specific errors
                            } else if (responseData.message) {
                                this.errors = { general: [responseData.message] };
                            } else {
                                this.errors = { general: [this.$t('common.An error occurred') + '.' + this.$t('common.Please try again') + '.'] };
                            }
                        }
                        // Sub-case B: Errors in data.errors array
                        // Format: { status: 0, data: { errors: [] } }
                        else if (responseData.data?.errors && Array.isArray(responseData.data.errors) && responseData.data.errors.length > 0) {
                            this.errors = { general: responseData.data.errors };
                        }
                        // Sub-case C: No errors, just message
                        else if (responseData.message) {
                            this.errors = { general: [responseData.message] };
                        }
                        // Sub-case D: Fallback
                        else {
                            this.errors = { general: [this.$t('common.An error occurred') + '.' + this.$t('common.Please try again') + '.'] };
                        }
                    }
                    // Case 2: Laravel validation errors (422 status)
                    else if (e.response.status === 422 && responseData.errors) {
                        this.errors = responseData.errors;
                    }
                    // Case 3: 401 Unauthorized with message
                    else if (e.response.status === 401 && responseData.message) {
                        this.errors = { general: [responseData.message] };
                    }
                    // Case 4: 400 Bad Request with errors
                    else if (e.response.status === 400 && responseData.data?.errors) {
                        this.errors = responseData.data.errors;
                    }
                    // Case 5: Generic error message from backend
                    else if (responseData.message) {
                        this.errors = { general: [responseData.message] };
                    }
                    // Case 6: Other HTTP errors
                    else {
                        this.errors = {
                            general: [`Error ${e.response.status}: ${e.response.statusText || this.$t('common.Something went wrong')}`]
                        };
                    }
                }
                // Network error (no response from server)
                else if (e.request) {
                    this.errors = {
                        general: [this.$t('common.Unable to connect to server') + '.' + this.$t('Please check your internet connection.')]
                    };
                }
                // Something else went wrong
                else {
                    this.errors = {
                        general: [e.message || this.$t('common.An unexpected error occurred. Please try again.')]
                    };
                }

                return { success: false, error: e };

            } finally {
                this.isLoading = false;
            }


        },

        logout() {
            this.user = null
            this.token = null
            this.errors = {} // ✅ Clear errors
            Cookies.remove('x_api_key')
            Cookies.remove('auth_token')
            Cookies.remove('remember_preference')
            localStorage.removeItem('auth')
        },

        // Check if user is authenticated
        checkAuth() {
            const token = Cookies.get('auth_token')
            if (token && !this.token) {
                this.token = token
            }
            return !!token
        },

        clearAuth() {
            this.user = null
            this.token = null
            Cookies.remove('auth_token')     
            Cookies.remove('x_api_key')       
            // localStorage.removeItem('auth')
        },

        // Initialize auth state from persisted data
        initAuth() {
            // Plugin handles restoration automatically
            // Just verify token is still valid
            if (this.token) {
                return true
            }
            return false
        }

    },

    // ✅ Enable persistence with custom config
    persist: {
        key: 'auth',
        storage: localStorage, // or sessionStorage for tab-only persistence
        paths: ['user', 'token'], // Only persist these fields
    }


})
