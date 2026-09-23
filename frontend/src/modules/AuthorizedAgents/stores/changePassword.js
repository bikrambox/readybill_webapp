import { defineStore } from 'pinia'
import { ref } from 'vue'
import Cookies from 'js-cookie'
import apiAgent from '@/config/apiAgent'

const API_KEY = 'agent_api_key'

export const useChangePasswordStore = defineStore('changePassword', () => {

    const isLoading = ref(false)
    const error = ref(null)

    const authHeader = () => {
        const token = Cookies.get('agent_token')
        const apiKey = Cookies.get(API_KEY)
        return {
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(apiKey ? { 'auth-key': apiKey } : {}),
        }
    }

    async function sendOTP(password, passwordConfirmation) {
        isLoading.value = true
        error.value = null
        try {
            const response = await apiAgent.post(
                '/authorized-agent/change-password/send-otp',
                { password, password_confirmation: passwordConfirmation },
                { headers: { ...authHeader() } }
            )
            return {
                success: true,
                message: response.data.message,
                cooldown: response.data.cooldown ?? 60,
            }
        } catch (err) {
            const { msg, status, lockout } = _extractError(err)
            error.value = msg
            return { success: false, message: msg, status, lockout }
        } finally {
            isLoading.value = false
        }
    }

    async function updatePassword(password, passwordConfirmation, otp) {
        isLoading.value = true
        error.value = null
        try {
            const response = await apiAgent.post(
                '/authorized-agent/change-password/update-password',
                { password, password_confirmation: passwordConfirmation, otp },
                { headers: { ...authHeader() } }
            )
            return { success: true, message: response.data.message }
        } catch (err) {
            const { msg, status, lockout } = _extractError(err)
            error.value = msg
            return { success: false, message: msg, status, lockout }
        } finally {
            isLoading.value = false
        }
    }

    function _extractError(err) {
        const status = err.response?.status
        const d = err.response?.data
        const lockout = status === 429
        if (d?.data) {
            if (typeof d.data === 'object') return { msg: Object.values(d.data).flat().join(' '), status, lockout }
            return { msg: d.data, status, lockout }
        }
        return { msg: d?.message ?? 'Something went wrong. Please try again.', status, lockout }
    }

    return { isLoading, error, sendOTP, updatePassword }
})