import axios from 'axios'
import Cookies from 'js-cookie'
import { useAuthStore } from "@/modules/Authentication/stores/authStore"

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v2'
const API_SECRET_KEY = import.meta.env.VITE_API_SECRET_KEY

const api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 60000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-API-Secret': API_SECRET_KEY
    }
})


const AUTH_EXCLUDED_ROUTES = [
    '/login',
]

// ✅ Router is injected from outside (main.js or router/index.js)
// This avoids circular dependency issues
let _router = null
export const setApiRouter = (router) => {
    _router = router
}

// ✅ Central redirect logic — used only once, avoids duplicate redirects
let isRedirectingToLogin = false

const redirectToLogin = () => {
    if (isRedirectingToLogin) return
    isRedirectingToLogin = true

    // Clear all auth cookies
    Cookies.remove('auth_token')
    Cookies.remove('x_api_key')

    // ✅ Hard redirect - forces full page reload, clears all state
    const loginPath = _router
        ? _router.resolve({ name: 'Login' }).href  // get the correct path from router
        : '/'

    window.location.href = loginPath
}


// Request interceptor
api.interceptors.request.use(
    (config) => {
        const apiKey = Cookies.get('x_api_key')
        if (apiKey) {
            config.headers['auth-key'] = apiKey
        }

        const token = Cookies.get('auth_token')
        if (token) {
            config.headers.Authorization = `Bearer ${token}`
        }
        return config
    },
    (error) => {
        console.error('API Request Error:', error)
        return Promise.reject(error)
    }
)

// Response interceptor
api.interceptors.response.use(
    (response) => response,
    (error) => {
        console.error('API Response Error:', error.config?.url, error)

        if (error.response) {
            const status = error.response.status
            const authStore = useAuthStore()

            // ✅ DEFINE IT HERE
            const requestUrl = error.config?.url || ''
            
            console.error('Response status:', status)
            console.error('Response data:', error.response.data)

            switch (status) {
                case 401:

                    if (AUTH_EXCLUDED_ROUTES.some(route => requestUrl.includes(route))) {
                        return Promise.reject(error)
                    }
                    // ✅ Token expired or unauthorized — redirect to login centrally
                    // console.warn('🔒 Token expired or unauthorized. Redirecting to login...')
                    authStore.logout()
                    redirectToLogin()
                    break

                case 403:
                    // // console.error('Access forbidden')
                    // authStore.logout()
                    // redirectToLogin()
                    break

                case 500:
                    console.error('Server error')
                    break
            }
        } else if (error.request) {
            console.error('No response received:', error.request)
        }

        return Promise.reject(error)
    }
)

export default api
