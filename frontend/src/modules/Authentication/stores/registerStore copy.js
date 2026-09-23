import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/config/api'
import { useRouter } from 'vue-router'

export const useRegisterStore = defineStore('register', () => {
    const router = useRouter()

    // Get detected country code from URL
    const getDetectedCountryCode = () => {
        const path = window.location.pathname
        const urlSegments = path.split('/').filter(segment => segment)
        const countryCode = urlSegments[0]?.toUpperCase() || 'IN'
        return countryCode
    }

    // State
    const currentStep = ref(1)
    const isLoading = ref(false)
    const errorMessages = ref([])
    const successMessage = ref('')

    const registrationData = ref({
        mobile: '',
        countryCode: 'IN',
        dialCode: '+91',
        detectedCountryCode: getDetectedCountryCode(),
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
    })

    // Getters
    const progressPercentage = computed(() => {
        return ((currentStep.value - 1) / 3) * 100
    })

    // Actions
    const updateRegistrationField = (field, value) => {
        registrationData.value[field] = value
    }

    const clearErrors = () => {
        errorMessages.value = []
    }

    const clearSuccessMessage = () => {
        successMessage.value = ''
    }

    const handleApiError = (error) => {
        if (error.response?.data?.data?.errors) {
            const errors = error.response.data.data.errors
            errorMessages.value = Object.values(errors).flat()
        } else if (error.response?.data?.message) {
            errorMessages.value = [error.response.data.message]
        } else {
            errorMessages.value = [this.$t('common.An unexpected error occurred') + '.' + this.$t('common.Please try again later') + '.']
        }
    }

    // Step 1: Send OTP
    const sendOtp = async () => {
        clearErrors()
        isLoading.value = true

        try {
            const payload = {
                country_code: registrationData.value.countryCode,
                mobile: registrationData.value.mobile
            }

            const { data } = await api.post(
                `/register/send-otp`,
                payload
            )


            if (data.status === 1) {

                console.log('sendOtp', data, data.data.data.user_id, data.data?.checkUser);

                const dataResponse = data.data.data;

                // Check if user is partially registered (user created but shop not created)
                if (dataResponse?.user_id && dataResponse.user_id != 0 && dataResponse?.checkUser == 0) {
                    // User exists but shop not created - skip to shop details step
                    registrationData.value.userId = dataResponse.user_id
                    currentStep.value = 4
                } else {
                    // Normal flow - proceed to OTP verification
                    currentStep.value = 2
                }

                console.log('sendOtp', currentStep);

            } else {
                errorMessages.value = [data.message || this.$t('common.Failed to send OTP')]
            }
        } catch (error) {
            handleApiError(error)
        } finally {
            isLoading.value = false
        }
    }

    // Step 2: Verify OTP
    const verifyOtp = async () => {
        clearErrors()
        isLoading.value = true

        try {
            const payload = {
                country_code: registrationData.value.countryCode,
                mobile: registrationData.value.mobile,
                otp: registrationData.value.otp
            }

            const { data } = await api.post(
                `/register/verify-otp`,
                payload
            )

            if (data.status === 1) {
                currentStep.value = 3
            } else {
                errorMessages.value = [data.message || this.$t('common.Invalid OTP')]
            }
        } catch (error) {
            handleApiError(error)
        } finally {
            isLoading.value = false
        }
    }

    // Resend OTP
    const resendOtp = async () => {
        await sendOtp()
    }

    // Step 3: Validate Password and Create User
    const validatePassword = async () => {
        clearErrors()
        isLoading.value = true

        try {
            const payload = {
                mobile: registrationData.value.mobile,
                country_code: registrationData.value.countryCode,
                detected_country_code: registrationData.value.detectedCountryCode,
                password: registrationData.value.password,
                password_confirmation: registrationData.value.confirmPassword,
                shop_type: registrationData.value.shopType
            }

            const { data } = await api.post(
                `/register/create-user`,
                payload
            )

            // console.log('validatePassword',data, data.data.user.user);

            if (data.status === 1) {
                // Store user_id from response
                registrationData.value.userId = data.data.user?.user_id || data.data.user?.id
                currentStep.value = 4
            } else {
                errorMessages.value = [data.message || this.$t('common.Failed to create user')]
            }
        } catch (error) {
            handleApiError(error)
        } finally {
            isLoading.value = false
        }
    }

    // Step 4: Complete Registration (Create Shop)
    const completeRegistration = async () => {
        clearErrors()
        isLoading.value = true

        try {
            // Create FormData for file upload support
            const formData = new FormData()

            // Required fields
            formData.append('user_id', registrationData.value.userId)
            formData.append('name', registrationData.value.ownerName)
            formData.append('business_name', registrationData.value.shopName)
            formData.append('address', registrationData.value.address)
            formData.append('terms_n_conditions', registrationData.value.termsAccepted ? 1 : 0)

            // Optional fields
            if (registrationData.value.email && registrationData.value.email.trim() !== '') {
                formData.append('email', registrationData.value.email)
            } else {
                formData.append('email', 'NA')
            }

            if (registrationData.value.gstin && registrationData.value.gstin.trim() !== '') {
                formData.append('gstin', registrationData.value.gstin)
            }

            // Logo file
            if (registrationData.value.logo) {
                formData.append('logo', registrationData.value.logo)
            }

            console.log('Sending create-shop request...')

            const { data } = await api.post(
                `/register/create-shop`,
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                }
            )

            console.log('Create-shop response:', data)

            // if (data.status === 1) {
            //     // Show success message
            //     successMessage.value = `Registration successful!<br>Please login <a href='/login' class='text-primary'>here</a>.`

            //     // Reset form after short delay
            //     setTimeout(() => {
            //         resetRegistration()
            //     }, 3000)
            // } else {
            //     errorMessages.value = [data.message || 'Failed to create shop']
            // }

            if (data.status === 1) {
                // Show success message
                // const message = this.$t('common.Registration Successful') + '!<br>' + this.$t('common.Please login to continue') + '.';
                const message
                // console.log('Setting success message:', message)
                successMessage.value = message
                // console.log('successMessage.value after set:', successMessage.value)
            } else {
                errorMessages.value = [data.message || this.$t('common.Failed to create shop')]
            }

        } catch (error) {
            handleApiError(error)
        } finally {
            isLoading.value = false
        }
    }

    // Navigation
    const nextStep = () => {
        if (currentStep.value < 4) {
            currentStep.value++
        }
    }

    const previousStep = () => {
        if (currentStep.value > 1) {
            currentStep.value--
            clearErrors()
        }
    }

    const resetRegistration = () => {
        currentStep.value = 1
        isLoading.value = false
        errorMessages.value = []
        successMessage.value = ''
        registrationData.value = {
            mobile: '',
            countryCode: 'IN',
            dialCode: '+91',
            detectedCountryCode: getDetectedCountryCode(),
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

    return {
        // State
        currentStep,
        isLoading,
        errorMessages,
        successMessage,
        registrationData,

        // Getters
        progressPercentage,

        // Actions
        updateRegistrationField,
        clearErrors,
        clearSuccessMessage,
        sendOtp,
        verifyOtp,
        resendOtp,
        validatePassword,
        completeRegistration,
        nextStep,
        previousStep,
        resetRegistration
    }
})
