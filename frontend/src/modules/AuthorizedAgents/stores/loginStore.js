import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import apiAgent from '@/config/apiAgent'
import Cookies from 'js-cookie'
import { AUTH_TOKEN, API_KEY, COOKIE_EXPIRY } from '@/config/authKeys'

export const useLoginStore = defineStore('login', () => {

    const isLoading = ref(false)
    const errors = ref({})
    const agent = ref(null)

    const token = ref(Cookies.get(AUTH_TOKEN) || null)
    const isAuthenticated = computed(() => !!token.value)

    const resumeFlags = ref(null)

    const login = async (credentials) => {
        isLoading.value = true
        errors.value = {}
        resumeFlags.value = null

        try {
            const payload = new FormData()
            payload.append('email', credentials.identifier)
            payload.append('password', credentials.password)

            const response = await apiAgent.post(
                '/authorized-agent/login',
                payload,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            )

            // ── Normalize response.data.data (array or object) ──────────────────
            const data = Array.isArray(response.data?.data)
                ? response.data.data[0]
                : response.data?.data || {}

            // ── Incomplete registration check ────────────────────────────────────
            if (
                data?.user_id &&
                typeof data.checkAgentDetails !== 'undefined' &&
                (data.checkAgentDetails == 0 || data.checkAgentDocuments == 0)
            ) {
                resumeFlags.value = data
                return { success: true, incomplete: true, flags: data }
            }

            // ── Full login — token present ───────────────────────────────────────
            token.value = data.token ?? null
            agent.value = data.user ?? null

            const cookieOptions = {
                expires: credentials.remember ? COOKIE_EXPIRY : undefined,
                secure: import.meta.env.PROD,
                sameSite: 'Lax',
            }

            if (token.value) {
                Cookies.set(AUTH_TOKEN, token.value, cookieOptions)
                apiAgent.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
            }

            if (data.api_key) {
                Cookies.set(API_KEY, data.api_key, cookieOptions)
                apiAgent.defaults.headers.common['auth-key'] = data.api_key
            }

            return { success: true, incomplete: false, data }

        } catch (err) {
            const status = err?.response?.status

            if (status === 403) {
                _handleError(err)
                return { success: false, unapproved: true }
            }

            if (status === 401) {
                _handleError(err)
                return { success: false, unauthorized: true }
            }

            _handleError(err)
            return { success: false }

        } finally {
            isLoading.value = false
        }
    }

    const logout = () => {
        token.value = null
        agent.value = null
        errors.value = {}
        resumeFlags.value = null

        Cookies.remove(AUTH_TOKEN)
        Cookies.remove(API_KEY)

        delete apiAgent.defaults.headers.common['Authorization']
        delete apiAgent.defaults.headers.common['auth-key']

        localStorage.removeItem('agent_auth')
    }

    const restoreSession = () => {
        const savedToken = Cookies.get(AUTH_TOKEN)
        const savedApiKey = Cookies.get(API_KEY)

        if (savedToken) {
            token.value = savedToken
            apiAgent.defaults.headers.common['Authorization'] = `Bearer ${savedToken}`
        }

        if (savedApiKey) {
            apiAgent.defaults.headers.common['auth-key'] = savedApiKey
        }
    }

    const _handleError = (err) => {
        const response = err?.response?.data
        const status = err?.response?.status
        const serverErrors = response?.data?.errors || {}
        const message = response?.message || ''

        // ── Clear stale token on any auth failure ─────────────────────────────
        // This prevents isAuthenticated staying true after a failed login
        // which would cause the router agentGuest guard to redirect away
        if (status === 401 || status === 403) {
            token.value = null
            agent.value = null
            Cookies.remove(AUTH_TOKEN)
            Cookies.remove(API_KEY)
            delete apiAgent.defaults.headers.common['Authorization']
            delete apiAgent.defaults.headers.common['auth-key']
        }

        // ── 403 Not Activated / Not Approved ──────────────────────────────────
        if (status === 403) {
            errors.value = {
                general: [message || 'Your account is currently not activated. Kindly contact the administrator.']
            }
            return
        }

        // ── 401 Invalid Credentials ───────────────────────────────────────────
        if (status === 401) {
            errors.value = {
                general: [message || 'Invalid email or password. Please try again.']
            }
            return
        }

        if (Object.keys(serverErrors).length) {
            errors.value = serverErrors
        } else if (message) {
            errors.value = { general: [message] }
        } else {
            errors.value = { general: ['An unexpected error occurred.'] }
        }
    }

    const clearErrors = () => { errors.value = {} }

    return {
        isLoading,
        errors,
        agent,
        token,
        isAuthenticated,
        resumeFlags,
        login,
        logout,
        restoreSession,
        clearErrors,
    }

}, {
    persist: {
        key: 'agent_auth',
        storage: localStorage,
        paths: ['agent'],
    }
})