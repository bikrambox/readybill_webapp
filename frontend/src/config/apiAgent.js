import axios from 'axios'
import Cookies from 'js-cookie'
import { AUTH_TOKEN, API_KEY } from '@/config/authKeys'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v2'
const API_SECRET_KEY = import.meta.env.VITE_API_SECRET_KEY

const apiAgent = axios.create({
    baseURL: API_BASE_URL,
    timeout: 60000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-API-Secret': API_SECRET_KEY,
    },
})


const AUTH_EXCLUDED_ROUTES = [
    '/login',
]

// ✅ Router injected from outside (main.js) to avoid circular dependency
let _router = null
export const setApiRouter = (router) => { _router = router }

// ✅ Guard flag — safe because window.location.href triggers full page reload
let isRedirectingToLogin = false

const redirectToLogin = () => {
    if (isRedirectingToLogin) return
    isRedirectingToLogin = true

    // ✅ Fix #2: use shared constants — was 'auth_token' / 'x_api_key' (stale keys)
    Cookies.remove(AUTH_TOKEN)
    Cookies.remove(API_KEY)

    const loginPath = _router
        ? _router.resolve({ name: 'Login' }).href
        : '/'

    window.location.href = loginPath
}


// ── Request interceptor ──────────────────────────────────────────────────────
apiAgent.interceptors.request.use(
    (config) => {
        // ✅ Fix #2: reads the same keys that loginStore writes
        const token = Cookies.get(AUTH_TOKEN)
        const apiKey = Cookies.get(API_KEY)

        if (token) config.headers['Authorization'] = `Bearer ${token}`
        if (apiKey) config.headers['auth-key'] = apiKey

        return config
    },
    (error) => {
        console.error('API Request Error:', error)
        return Promise.reject(error)
    }
)


// ── Response interceptor ─────────────────────────────────────────────────────
apiAgent.interceptors.response.use(
    (response) => response,
    async (error) => {
        console.error('API Response Error:', error.config?.url, error)

        if (error.response) {
            const status = error.response.status


            const requestUrl = error.config?.url || ''

            console.error('Response status:', status)
            console.error('Response data:', error.response.data)

            switch (status) {
                case 401:
                    {

                        if (AUTH_EXCLUDED_ROUTES.some(route => requestUrl.includes(route))) {
                            return Promise.reject(error)
                        }

                        // ✅ Fix #3: lazy import breaks the circular dependency chain
                        //    (stores import apiAgent → apiAgent must NOT import stores at
                        //     module load time, only lazily inside callbacks)
                        const { useLoginStore } = await import(
                            '@/modules/AuthorizedAgents/stores/loginStore'
                        )
                        const loginStore = useLoginStore()
                        loginStore.logout()
                        redirectToLogin()
                        break
                    }

                case 403:
                    break

                case 500:
                    console.error('Internal server error')
                    break
            }
        } else if (error.request) {
            console.error('No response received:', error.request)
        }

        return Promise.reject(error)
    }
)


export default apiAgent