// stores/profile.js
import { defineStore } from 'pinia'
import api from '@/config/api'
import Cookies from 'js-cookie'

export const useProfileStore = defineStore('profile', {
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
        preferences: {},
        otpConfig: {
            expiry: import.meta.env.VITE_OTP_EXPIRY || 5,
            maxAttempts: import.meta.env.VITE_OTP_COUNT || 4,
            countdown: import.meta.env.VITE_COUNTDOWN || 60
        }
    }),

    actions: {
        async fetchUserProfile() {
            this.isLoading = true
            try {
                const token = Cookies.get('auth_token')
                const response = await api.get('/user-detail', {
                    headers: {
                        Authorization: `Bearer ${token}`
                    }
                })

                if (response.data.status === 'success') {
                    const data = response.data.data

                    console.log('fetchUserProfile', response.data.isPhoto);

                    this.userData = {
                        user_id: data.user_id || '',
                        mobile: data.mobile || '',
                        name: data.details?.name || '',
                        email: data.details?.email && data.details.email !== 'NA'
                            ? data.details.email
                            : '',
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
                        isPhoto: response.data.isLogo || 0
                    }

                    this.subscription_expiry_date = data.subscription_expiry_date || ''
                    this.subscription_current_plan = data.subscription_current_plan || ''
                    this.isSubscriptionExpired = data.isSubscriptionExpired || 0
                    this.subscription_id = data.subscription_id || 0
                    this.subscriptionExpiredMessage = data.subscriptionExpiredMessage || null
                    this.all_languages = data.all_languages || []
                    this.preferences = data.preferences || {}

                } else {
                    this.subscriptionError = true
                }
            } catch (error) {
                console.error('Error fetching profile:', error)
                this.subscriptionError = true
            } finally {
                this.isLoading = false
            }
        },

        async updateProfile(formData) {
            this.isLoading = true
            try {
                const token = Cookies.get('auth_token')
                const response = await api.post('/update-profile', formData, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                })

                if (response.data.status === 1 || response.data.status === 'success') {
                    // const data = response.data.data

                    // this.userData = {
                    //     ...this.userData,
                    //     name: data.name || this.userData.name,
                    //     business_name: data.business_name || this.userData.business_name,
                    //     email: data.email || this.userData.email,
                    //     address: data.address || this.userData.address,
                    //     gstin: data.gstin || this.userData.gstin,
                    //     logo: data.logo || this.userData.logo,
                    //     logo_url: data.logo_url || this.userData.logo_url,
                    //     api_key: data.api_key || this.userData.api_key,
                    //     shop_type: data.shop_type || this.userData.shop_type
                    // }


                    // After successful update, fetch fresh data from server
                    await this.fetchUserProfile()

                    return {
                        success: true,
                        message: response.data.message || this.$t('common.Profile updated successfully')
                    }
                } else {
                    const errors = response.data.data || {}
                    const errorMessages = []

                    Object.keys(errors).forEach(field => {
                        if (Array.isArray(errors[field])) {
                            errorMessages.push(...errors[field])
                        } else {
                            errorMessages.push(errors[field])
                        }
                    })

                    return {
                        success: false,
                        errors: errors,
                        errorMessages: errorMessages
                    }
                }
            } catch (error) {
                console.error('Error updating profile:', error)

                let errorMessages = [this.$t('common.An error occurred while updating profile')]
                let errors = {}

                if (error.response?.data) {
                    const responseData = error.response.data

                    if (responseData.data && typeof responseData.data === 'object') {
                        errors = responseData.data
                        errorMessages = []
                        Object.keys(errors).forEach(field => {
                            if (Array.isArray(errors[field])) {
                                errorMessages.push(...errors[field])
                            } else {
                                errorMessages.push(errors[field])
                            }
                        })
                    } else if (responseData.message) {
                        errorMessages = [responseData.message]
                    }
                }

                return {
                    success: false,
                    errors: errors,
                    errorMessages: errorMessages
                }
            } finally {
                this.isLoading = false
            }
        },

        async sendOTP(userId, mobile, countryCode) {
            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('user_id', userId)
                formData.append('mobile', mobile)
                formData.append('type', 'send-otp')
                formData.append('sms_type', 'change_mobile_number')
                formData.append('country_code', countryCode)

                const response = await api.post(
                    `generate-verify-otp`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                if (response.data.status === 1) {
                    return {
                        success: true,
                        message: response.data.message || this.$t('common.OTP sent successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message
                    }
                }
            } catch (error) {
                console.error('Send OTP error:', error)
                return {
                    success: false,
                    errors: error.response?.data?.data?.errors || {},
                    message: error.response?.data?.message || this.$t('common.Failed to send OTP')
                }
            }
        },

        async resendOTP(mobile) {
            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('mobile', mobile)
                formData.append('sms_type', 'change_mobile_number')

                const response = await api.post(
                    `resend-otp`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                if (response.data.status === 1) {
                    return {
                        success: true,
                        message: response.data.message || this.$t('common.OTP resent successfully')
                    }
                } else {
                    return {
                        success: false,
                        message: response.data.message || this.$t('common.Failed to resend OTP')
                    }
                }
            } catch (error) {
                console.error('Resend OTP error:', error)
                return {
                    success: false,
                    message: error.response?.data?.message || this.$t('common.Failed to resend OTP')
                }
            }
        },

        async verifyAndUpdateMobile(userId, mobile, countryCode, otp) {
            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('mobile', mobile)
                formData.append('user_id', userId)
                formData.append('country_code', countryCode)
                formData.append('otp', otp)

                const response = await api.post(
                    `update-mobile-number`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                if (response.data.status === 1) {
                    // Update mobile in state
                    this.userData.mobile = mobile

                    return {
                        success: true,
                        message: response.data.message || this.$t('common.Mobile number updated successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message || this.$t('common.Invalid OTP')
                    }
                }
            } catch (error) {
                console.error('Verify OTP error:', error)
                return {
                    success: false,
                    errors: error.response?.data?.data?.errors || {},
                    message: error.response?.data?.message || this.$t('common.Failed to verify OTP')
                }
            }
        },

        async sendDeleteAccountOTP(mobile, countryCode = 'IN') {
            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('mobile', mobile)
                formData.append('type', 'send-otp')
                formData.append('sms_type', 'delete_account')
                formData.append('country_code', countryCode)

                const response = await api.post(
                    `generate-verify-otp`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                if (response.data.status === 1) {
                    return {
                        success: true,
                        message: response.data.message || this.$t('common.OTP sent successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message || this.$t('common.Failed to send OTP')
                    }
                }
            } catch (error) {
                console.error('Send Delete OTP error:', error)

                let errors = {}
                let message = this.$t('common.Failed to send OTP')

                if (error.response?.data) {
                    const responseData = error.response.data

                    if (responseData.data?.errors) {
                        errors = responseData.data.errors
                    }

                    if (responseData.message) {
                        message = responseData.message
                    }
                }

                return {
                    success: false,
                    errors: errors,
                    message: message
                }
            }
        },

        // Keep the existing deleteAccount method as is
        async deleteAccount(userId, otp) {
            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('user_id', userId)
                formData.append('otp', otp)

                const response = await api.post(
                    `delete-account`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                if (response.data.status === 1) {
                    return {
                        success: true,
                        message: response.data.message || this.$t('common.Account deleted successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message || this.$t('common.Failed to delete account')
                    }
                }
            } catch (error) {
                console.error('Delete Account error:', error)
                return {
                    success: false,
                    errors: error.response?.data?.data?.errors || {},
                    message: error.response?.data?.message || this.$t('common.Failed to delete account')
                }
            }
        },

        clearSubscriptionError() {
            this.subscriptionError = false
        },
    }
})
