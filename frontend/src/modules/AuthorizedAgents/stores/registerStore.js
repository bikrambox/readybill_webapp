// stores/registerStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiAgent from '@/config/apiAgent'

export const useRegisterStore = defineStore('register', () => {

    const isLoading = ref(false)
    const errors = ref({})
    const successMessage = ref('')

    // Persisted between step calls
    const userId = ref(null)
    const agentDetailsId = ref(null)

    // ── Step 1: Register Email ────────────────────────────
    const registerEmail = async (formData) => {
        isLoading.value = true
        errors.value = {}

        try {
            const payload = new FormData()
            payload.append('email', formData.email)
            payload.append('password', formData.password)
            payload.append('password_confirmation', formData.confirmPassword)
            payload.append('detected_country_code', 'IN')

            const response = await apiAgent.post(
                '/authorized-agent/register/register-email',
                payload,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )

            const dataResponse = response.data.data.data
            // Expected shape:
            // {
            //   user_id: 142,
            //   checkAgent: 0,
            //   checkAgentDetails: 0,
            //   checkAgentDocuments: 0,
            //   agent_details_id: 5  ← present only when details already exist
            // }

            userId.value = dataResponse.user_id ?? null

            // Hydrate agentDetailsId if API returns it (resume-to-step-3 case)
            if (dataResponse.agent_details_id) {
                agentDetailsId.value = dataResponse.agent_details_id
            }

            return { success: true, data: response.data, flags: dataResponse }

        } catch (err) {
            _handleError(err)
            return { success: false }

        } finally {
            isLoading.value = false
        }
    }

    // ── Step 2: Agent Details ─────────────────────────────
    const registerAgentDetails = async (formData) => {
        isLoading.value = true
        errors.value = {}

        console.log('registerAgentDetails',formData);

        try {
            const payload = new FormData()
            payload.append('user_id', formData.user_id ?? userId.value)
            payload.append('name', formData.fullName)
            payload.append('address', formData.address)
            payload.append('country_code', 'IN')
            payload.append('mobile', formData.mobile)
            payload.append('pan_number', formData.panNumber)

            const response = await apiAgent.post(
                '/authorized-agent/register/agent-details',
                payload,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )

            const dataResponse = response.data.data.data

            agentDetailsId.value = dataResponse.id ?? null

            return { success: true, data: response.data }

        } catch (err) {
            _handleError(err)
            return { success: false }

        } finally {
            isLoading.value = false
        }
    }

    // ── Step 3: Document Upload ───────────────────────────
    const registerDocuments = async (formData) => {
        isLoading.value = true
        errors.value = {}

        try {
            const payload = new FormData()
            payload.append('agent_details_id', agentDetailsId.value)
            if (formData.photo) payload.append('photo', formData.photo)
            if (formData.aadharCard) payload.append('aadhar_card', formData.aadharCard)
            if (formData.upiQrCode) payload.append('qr_code', formData.upiQrCode)

            const response = await apiAgent.post(
                '/authorized-agent/register/agent-document-upload',
                payload,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )

            successMessage.value = response.data?.message || ''

            return { success: true, data: response.data }

        } catch (err) {
            _handleError(err)
            return { success: false }

        } finally {
            isLoading.value = false
        }
    }

    // ── Shared error handler ──────────────────────────────
    const _handleError = (err) => {
        const response = err?.response?.data
        const serverErrors = response?.data?.errors || {}
        const message = response?.message || ''

        if (Object.keys(serverErrors).length) {
            errors.value = serverErrors
        } else if (message) {
            errors.value = { general: [message] }
        } else {
            errors.value = { general: ['An unexpected error occurred.'] }
        }
    }

    // ── Utilities ─────────────────────────────────────────
    const clearErrors = () => {
        errors.value = {}
    }

    const clearSuccessMessage = () => {
        successMessage.value = ''
    }

    const resetRegistration = () => {
        isLoading.value = false
        errors.value = {}
        successMessage.value = ''
        userId.value = null
        agentDetailsId.value = null
    }

    return {
        isLoading,
        errors,
        successMessage,
        userId,      
        agentDetailsId, 
        registerEmail,
        registerAgentDetails,
        registerDocuments,
        clearErrors,
        clearSuccessMessage,
        resetRegistration,
    }
})