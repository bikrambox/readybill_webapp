import { defineStore } from 'pinia'
import api from '@/config/api'

export const useForgotPasswordStore = defineStore('forgotPassword', {
    state: () => ({
        isLoading: false,
        errors: {},
        otpConfig: {
            expiry: 5,
            maxAttempts: 3,
            countdown: 60
        },
        verifiedMobile: null,
    }),

    actions: {
        async sendOTP(mobile, countryCode) {
            this.isLoading = true
            this.errors = {}

            try {
                const formData = new FormData()
                formData.append('mobile', mobile)
                formData.append('type', 'send-otp')
                formData.append('sms_type', 'forgot_password')
                formData.append('country_code', countryCode)

                const { data } = await api.post(
                    `generate-verify-otp`,
                    formData
                )

                if (data.status === 0) {
                    if (data.data?.errors) {
                        this.errors = data.data.errors
                    } else if (data.data?.retry_after) {
                        this.errors = {
                            general: [`${data.message} ${this.$t('common.Please try again after')} ${data.data.retry_after}.`]
                        }
                    } else {
                        this.errors = {
                            general: [data.message || this.$t('common.Failed to send OTP')]
                        }
                    }

                    return { success: false, errors: this.errors, message: data.message }
                }

                return {
                    success: true,
                    message: data.message || this.$t('common.OTP sent successfully')
                }

            } catch (e) {
                const responseData = e.response?.data

                if (responseData?.data?.errors) {
                    this.errors = responseData.data.errors
                } else if (responseData?.data?.retry_after) {
                    this.errors = {
                        general: [`${responseData.message} ${this.$t('common.Please try again after')} ${responseData.data.retry_after}.`]
                    }
                } else {
                    this.errors = {
                        general: [
                            this.$t('common.Failed to send OTP') + '. ' + this.$t('common.Please try again') + '.'
                        ]
                    }
                }

                return { success: false, errors: this.errors, message: responseData?.message }
            } finally {
                this.isLoading = false
            }
        },

        async verifyOTP(mobile, otp) {
            this.isLoading = true
            this.errors = {}

            try {
                const formData = new FormData()
                formData.append('mobile', mobile)
                formData.append('type', 'verify-otp')
                formData.append('sms_type', 'forgot_password')
                formData.append('otp', otp)

                const { data } = await api.post(
                    `generate-verify-otp`,
                    formData
                )

                if (data.status === 0) {
                    if (data.data?.errors) {
                        this.errors = data.data.errors
                    } else if (data.data?.retry_after) {
                        this.errors = {
                            general: [`${data.message} ${this.$t('common.Please try again after')} ${data.data.retry_after}.`]
                        }
                    } else {
                        this.errors = {
                            general: [data.message || this.$t('common.Failed to verify OTP')]
                        }
                    }

                    return { success: false, errors: this.errors, message: data.message }
                }

                this.verifiedMobile = data.data?.mobile
                return {
                    success: true,
                    message: data.message || this.$t('common.OTP verified successfully')
                }

            } catch (e) {
                const responseData = e.response?.data

                if (responseData?.data?.errors) {
                    this.errors = responseData.data.errors
                } else if (responseData?.data?.retry_after) {
                    this.errors = {
                        general: [`${responseData.message} ${this.$t('common.Please try again after')} ${responseData.data.retry_after}.`]
                    }
                } else {
                    this.errors = {
                        general: [
                            this.$t('common.Failed to verify OTP') + '. ' + this.$t('common.Please try again') + '.'
                        ]
                    }
                }

                return { success: false, errors: this.errors, message: responseData?.message }
            } finally {
                this.isLoading = false
            }
        },

        async updatePassword(mobile, password, passwordConfirmation) {
            this.isLoading = true
            this.errors = {}

            try {
                const formData = new FormData()
                formData.append('mobile', mobile)
                formData.append('password', password)
                formData.append('password_confirmation', passwordConfirmation)

                const { data } = await api.post(
                    `update-password`,
                    formData
                )

                if (data.status === 0) {
                    if (data.data?.errors) {
                        this.errors = data.data.errors
                    } else if (data.data?.retry_after) {
                        this.errors = {
                            general: [`${data.message} ${this.$t('common.Please try again after')} ${data.data.retry_after}.`]
                        }
                    } else {
                        this.errors = {
                            general: [data.message || this.$t('common.Failed to update password')]
                        }
                    }

                    return { success: false, errors: this.errors, message: data.message }
                }

                this.verifiedMobile = null
                return {
                    success: true,
                    message: data.message || this.$t('common.Password updated successfully')
                }

            } catch (e) {
                const responseData = e.response?.data

                if (responseData?.data?.errors) {
                    this.errors = responseData.data.errors
                } else if (responseData?.data?.retry_after) {
                    this.errors = {
                        general: [`${responseData.message} ${this.$t('common.Please try again after')} ${responseData.data.retry_after}.`]
                    }
                } else {
                    this.errors = {
                        general: [
                            this.$t('common.Failed to update password') + '. ' + this.$t('common.Please try again') + '.'
                        ]
                    }
                }

                return { success: false, errors: this.errors, message: responseData?.message }
            } finally {
                this.isLoading = false
            }
        },

        clearErrors() {
            this.errors = {}
        }
    }
})