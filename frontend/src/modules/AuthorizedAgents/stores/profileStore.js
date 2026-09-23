// stores/profileStore.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiAgent from '@/config/apiAgent'
import Cookies from 'js-cookie'

const API_KEY = 'agent_api_key'

export const useProfileStore = defineStore('profile', () => {

    const isLoading = ref(false)
    const isUpdating = ref(false)
    const errors = ref({})
    const profile = ref(null)

    // ── Auth header helper ────────────────────────────────
    const authHeader = () => {
        const token = Cookies.get('agent_token')
        const apiKey = Cookies.get(API_KEY)
        return {
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(apiKey ? { 'auth-key': apiKey } : {}),
        }
    }

    // ── Fetch profile ─────────────────────────────────────
    const fetchProfile = async () => {
        isLoading.value = true
        errors.value = {}

        try {
            const response = await apiAgent.get('/authorized-agent/profile-details', {
                headers: { ...authHeader() },
            })

            profile.value = response.data?.data ?? null

            if (profile.value?.api_key) {
                Cookies.set(API_KEY, profile.value.api_key, {
                    secure: true,
                    sameSite: 'Strict',
                })
                apiAgent.defaults.headers.common['auth-key'] = profile.value.api_key
            }

            return { success: true, data: response.data }

        } catch (err) {
            _handleError(err)
            return { success: false }

        } finally {
            isLoading.value = false
        }
    }

    // ── Silent re-fetch (no isLoading flag) ───────────────
    // Used internally after update to refresh full profile data
    const _refreshProfile = async () => {
        try {
            const response = await apiAgent.get('/authorized-agent/profile-details', {
                headers: { ...authHeader() },
            })
            profile.value = response.data?.data ?? null
        } catch (_) {
            // silent — best effort refresh
        }
    }

    // ── Update profile ────────────────────────────────────
    // const updateProfile = async (formData) => {
    //     isUpdating.value = true
    //     errors.value = {}

    //     try {
    //         const payload = new FormData()

    //         payload.append('agent_details_id', profile.value?.agentDetails?.id ?? '')
    //         payload.append('email', formData.email ?? '')
    //         payload.append('name', formData.fullName ?? '')
    //         payload.append('address', formData.address ?? '')
    //         payload.append('country_code', 'IN')
    //         payload.append('mobile', formData.mobile ?? '')
    //         payload.append('pan_number', formData.pan ?? '')

    //         if (formData.photo instanceof File) payload.append('photo', formData.photo)
    //         if (formData.aadhar instanceof File) payload.append('aadhar_card', formData.aadhar)
    //         if (formData.upiQr instanceof File) payload.append('qr_code', formData.upiQr)

    //         if (formData.password) {
    //             payload.append('password', formData.password)
    //             payload.append('password_confirmation', formData.confirmPassword)
    //         }

    //         await apiAgent.post(
    //             '/authorized-agent/update-profile',
    //             payload,
    //             {
    //                 headers: {
    //                     'Content-Type': 'multipart/form-data',
    //                     ...authHeader(),
    //                 },
    //             }
    //         )

    //         // ✅ Always re-fetch full profile after update
    //         // This guarantees profile.value has complete, fresh data
    //         // including updated URLs, country_details, and all fields
    //         await _refreshProfile()

    //         return { success: true }

    //     } catch (err) {
    //         _handleError(err)
    //         return { success: false }

    //     } finally {
    //         isUpdating.value = false
    //     }
    // }


    const updateProfile = async (formData) => {
        isUpdating.value = true
        errors.value = {}

        try {
            const payload = new FormData()

            payload.append('agent_details_id', profile.value?.agentDetails?.id ?? '')
            payload.append('email', formData.email ?? '')
            payload.append('name', formData.fullName ?? '')
            payload.append('address', formData.address ?? '')
            payload.append('country_code', 'IN')
            payload.append('mobile', formData.mobile ?? '')
            payload.append('pan_number', formData.pan ?? '')

            if (formData.photo instanceof File) payload.append('photo', formData.photo)
            if (formData.aadhar instanceof File) payload.append('aadhar_card', formData.aadhar)
            if (formData.upiQr instanceof File) payload.append('qr_code', formData.upiQr)

            const response = await apiAgent.post(
                '/authorized-agent/update-profile',
                payload,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                        ...authHeader(),
                    },
                }
            )

            await _refreshProfile()

            return {
                success: true,
                data: response.data?.data ?? {},
                message: response.data?.message ?? '',
            }

        } catch (err) {
            _handleError(err)
            return { success: false }

        } finally {
            isUpdating.value = false
        }
    };

    // ── Error handler ─────────────────────────────────────
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

    return {
        isLoading,
        isUpdating,
        errors,
        profile,
        fetchProfile,
        updateProfile,
        clearErrors,
    }
})