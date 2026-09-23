import { defineStore } from 'pinia'
import api from '@/config/api'
import Cookies from 'js-cookie'

export const useChangePasswordStore = defineStore('changePassword', {
    state: () => ({
        user: null,
        isLoading: false,
        error: null,
        // Store mobile and country code for password change flow
        passwordChangeData: {
            mobile: '',
            countryCode: 'IN'
        }
    }),

    getters: {
        userMobile: (state) => state.user?.mobile || '',
        userCountryCode: (state) => state.user?.country_code || 'IN',
        isAuthenticated: (state) => !!state.user,
        hasPasswordChangeData: (state) => !!state.passwordChangeData.mobile
    },

    actions: {
        // Set password change data (mobile and country code)
        setPasswordChangeData(mobile, countryCode = 'IN') {
            this.passwordChangeData = {
                mobile,
                countryCode
            }
        },

        // Clear password change data
        clearPasswordChangeData() {
            this.passwordChangeData = {
                mobile: '',
                countryCode: 'IN'
            }
        },

        // Get password change mobile
        getPasswordChangeMobile() {
            return this.passwordChangeData.mobile
        },

        // Get password change country code
        getPasswordChangeCountryCode() {
            return this.passwordChangeData.countryCode
        },

        // Send OTP for password change
        async sendPasswordChangeOTP(mobile, countryCode = 'IN') {
            this.isLoading = true
            this.error = null

            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('mobile', mobile)
                formData.append('type', 'send-otp')
                formData.append('sms_type', 'change_password')
                formData.append('country_code', countryCode)


                console.log('sendPasswordChangeOTP', formData);

                const response = await api.post(
                    `generate-verify-otp`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                this.isLoading = false

                if (response.data.status === 1) {
                    return {
                        success: true,
                        message: response.data.message || this.$t('common.OTP sent successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message || this.$t('common.Failed to send OTP'),
                        code: response.data.code,
                        data: response.data.data
                    }
                }
            } catch (error) {
                this.isLoading = false
                console.error('Send OTP error:', error)

                const responseData = error.response?.data

                return {
                    success: false,
                    errors: responseData?.data?.errors || {},
                    message: responseData?.message || this.$t('common.Failed to send OTP'),
                    code: responseData?.code || error.response?.status,
                    data: responseData?.data
                }
            }
        },

        // Verify OTP
        async verifyOTP(mobile, otp, smsType = '') {
            this.isLoading = true
            this.error = null

            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('mobile', mobile)
                formData.append('type', 'verify-otp')
                formData.append('sms_type', smsType)
                formData.append('otp', otp)

                const response = await api.post(
                    `generate-verify-otp`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                this.isLoading = false

                if (response.data.status === 1) {
                    return {
                        success: true,
                        message: response.data.message || this.$t('common.OTP verified successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message || this.$t('common.Invalid OTP'),
                        code: response.data.code,
                        data: response.data.data
                    }
                }
            } catch (error) {
                this.isLoading = false
                console.error('Verify OTP error:', error)

                const responseData = error.response?.data

                return {
                    success: false,
                    errors: responseData?.data?.errors || {},
                    message: responseData?.message || this.$t('common.Failed to verify OTP'),
                    code: responseData?.code || error.response?.status,
                    data: responseData?.data
                }
            }
        },

        // Update Password
        async updatePassword(mobile, password, passwordConfirmation) {
            this.isLoading = true
            this.error = null

            try {
                const token = Cookies.get('auth_token')
                const formData = new FormData()

                formData.append('mobile', mobile)
                formData.append('password', password)
                formData.append('password_confirmation', passwordConfirmation)

                const response = await api.post(
                    `update-password`,
                    formData,
                    {
                        headers: {
                            Authorization: `Bearer ${token}`
                        }
                    }
                )

                this.isLoading = false

                if (response.data.status === 1) {
                    // Clear password change data after successful password update
                    this.clearPasswordChangeData()

                    return {
                        success: true,
                        message: response.data.message || this.$t('common.Password updated successfully')
                    }
                } else {
                    return {
                        success: false,
                        errors: response.data.data?.errors || {},
                        message: response.data.message || this.$t('common.Failed to update password')
                    }
                }
            } catch (error) {
                this.isLoading = false
                console.error('Update password error:', error)

                return {
                    success: false,
                    errors: error.response?.data?.data?.errors || {},
                    message: error.response?.data?.message || this.$t('common.Failed to update password')
                }
            }
        },

        // Set user data
        setUser(userData) {
            this.user = userData
        },

        // Clear user data
        clearUser() {
            this.user = null
        }
    }
})
