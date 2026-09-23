import { defineStore } from 'pinia'
import api from '@/config/api'

export const useRegisterStore = defineStore('register', {

    state: () => ({
        currentStep: 1,
        isLoading: false,
        errorMessages: [],
        successMessage: '',
        registrationData: {
            mobile: '',
            countryCode: 'IN',
            dialCode: '+91',
            detectedCountryCode: (() => {
                const path = window.location.pathname
                const urlSegments = path.split('/').filter(segment => segment)
                return urlSegments[0]?.toUpperCase() || 'IN'
            })(),
            otp: '',
            password: '',
            confirmPassword: '',
            shopName: '',
            ownerName: '',
            email: '',
            address: '',
            gstin: '',
            logo: null,
            shopType: 'grocery',
            termsAccepted: false,
            userId: null
        }
    }),

    getters: {
        progressPercentage: (state) => {
            return ((state.currentStep - 1) / 3) * 100
        }
    },

    actions: {

        getDetectedCountryCode() {
            const path = window.location.pathname
            const urlSegments = path.split('/').filter(segment => segment)
            return urlSegments[0]?.toUpperCase() || 'IN'
        },

        updateRegistrationField(field, value) {
            this.registrationData[field] = value
        },

        clearErrors() {
            this.errorMessages = []
        },

        clearSuccessMessage() {
            this.successMessage = ''
        },

        handleApiError(error) {
            if (error.response?.data?.data?.errors) {
                const errors = error.response.data.data.errors
                this.errorMessages = Object.values(errors).flat()
            } else if (error.response?.data?.message) {
                this.errorMessages = [error.response.data.message]
            } else {
                this.errorMessages = [
                    this.$t('common.An unexpected error occurred') + '. ' +
                    this.$t('common.Please try again later') + '.'
                ]
            }
        },

        // Step 1: Send OTP
        async sendOtp() {
            this.clearErrors()
            this.isLoading = true

            try {
                const payload = {
                    country_code: this.registrationData.countryCode,
                    mobile: this.registrationData.mobile
                }

                const { data } = await api.post(`/register/send-otp`, payload)

                if (data.status === 1) {
                    const dataResponse = data.data.data

                    if (dataResponse?.user_id && dataResponse.user_id != 0 && dataResponse?.checkUser == 0) {
                        this.registrationData.userId = dataResponse.user_id
                        this.currentStep = 4
                    } else {
                        this.currentStep = 2
                    }
                } else {
                    this.errorMessages = [data.message || this.$t('common.Failed to send OTP')]
                }
            } catch (error) {
                this.handleApiError(error)
            } finally {
                this.isLoading = false
            }
        },

        // Step 2: Verify OTP
        async verifyOtp() {
            this.clearErrors()
            this.isLoading = true

            try {
                const payload = {
                    country_code: this.registrationData.countryCode,
                    mobile: this.registrationData.mobile,
                    otp: this.registrationData.otp
                }

                const { data } = await api.post(`/register/verify-otp`, payload)

                if (data.status === 1) {
                    this.currentStep = 3
                } else {
                    this.errorMessages = [data.message || this.$t('common.Invalid OTP')]
                }
            } catch (error) {
                this.handleApiError(error)
            } finally {
                this.isLoading = false
            }
        },

        // Resend OTP
        async resendOtp() {
            await this.sendOtp()
        },

        // Step 3: Validate Password and Create User
        async validatePassword() {
            this.clearErrors()
            this.isLoading = true

            try {
                const payload = {
                    mobile: this.registrationData.mobile,
                    country_code: this.registrationData.countryCode,
                    detected_country_code: this.registrationData.detectedCountryCode,
                    password: this.registrationData.password,
                    password_confirmation: this.registrationData.confirmPassword,
                    shop_type: this.registrationData.shopType
                }

                const { data } = await api.post(`/register/create-user`, payload)

                if (data.status === 1) {
                    this.registrationData.userId = data.data.user?.user_id || data.data.user?.id
                    this.currentStep = 4
                } else {
                    this.errorMessages = [data.message || this.$t('common.Failed to create user')]
                }
            } catch (error) {
                this.handleApiError(error)
            } finally {
                this.isLoading = false
            }
        },

        // Step 4: Complete Registration (Create Shop)
        // async completeRegistration() {
        //     this.clearErrors()
        //     this.isLoading = true

        //     try {
        //         const formData = new FormData()

        //         formData.append('user_id', this.registrationData.userId)
        //         formData.append('name', this.registrationData.ownerName)
        //         formData.append('business_name', this.registrationData.shopName)
        //         formData.append('address', this.registrationData.address)
        //         formData.append('terms_n_conditions', this.registrationData.termsAccepted ? 1 : 0)

        //         if (this.registrationData.email && this.registrationData.email.trim() !== '') {
        //             formData.append('email', this.registrationData.email)
        //         } else {
        //             formData.append('email', 'NA')
        //         }

        //         if (this.registrationData.gstin && this.registrationData.gstin.trim() !== '') {
        //             formData.append('gstin', this.registrationData.gstin)
        //         }

        //         if (this.registrationData.logo) {
        //             formData.append('logo', this.registrationData.logo)
        //         }

        //         const { data } = await api.post(
        //             `/register/create-shop`,
        //             formData,
        //             { headers: { 'Content-Type': 'multipart/form-data' } }
        //         )

        //         if (data.status === 1) {
        //             const message = this.$t('common.registration_successful') + '!<br>' +
        //                 this.$t('common.Please login to continue') + '.'
        //             this.successMessage = message
        //         } else {
        //             this.errorMessages = [data.message || this.$t('common.Failed to create shop')]
        //         }

        //     } catch (error) {
        //         this.handleApiError(error)
        //     } finally {
        //         this.isLoading = false
        //     }
        // },


        async completeRegistration() {
            this.isLoading = true;
            this.clearErrors();

            try {
                const formData = new FormData();
                formData.append("ownerName", this.registrationData.ownerName || "");
                formData.append("shopName", this.registrationData.shopName || "");
                formData.append("email", this.registrationData.email || "");
                formData.append("address", this.registrationData.address || "");
                formData.append("gstin", this.registrationData.gstin || "");

                if (this.registrationData.logo) {
                    formData.append("logo", this.registrationData.logo);
                }

                const response = await api.post("/complete-registration", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });

                return response.data;
            } catch (error) {
                const responseData = error?.response?.data;
                const status = error?.response?.status;

                if (responseData?.data?.errors && Array.isArray(responseData.data.errors) && responseData.data.errors.length) {
                    this.errorMessages = responseData.data.errors;
                } else if (responseData?.message) {
                    this.errorMessages = [responseData.message];
                } else if (status === 500) {
                    this.errorMessages = ["Sorry, we are unable to process your request. Please try again after some time."];
                } else {
                    this.errorMessages = ["Something went wrong. Please try again."];
                }

                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        // Navigation
        nextStep() {
            if (this.currentStep < 4) this.currentStep++
        },

        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--
                this.clearErrors()
            }
        },

        resetRegistration() {
            this.currentStep = 1
            this.isLoading = false
            this.errorMessages = []
            this.successMessage = ''
            this.registrationData = {
                mobile: '',
                countryCode: 'IN',
                dialCode: '+91',
                detectedCountryCode: this.getDetectedCountryCode(),
                otp: '',
                password: '',
                confirmPassword: '',
                shopName: '',
                ownerName: '',
                email: '',
                address: '',
                gstin: '',
                logo: null,
                shopType: 'grocery',
                termsAccepted: false,
                userId: null
            }
        }
    }
})
