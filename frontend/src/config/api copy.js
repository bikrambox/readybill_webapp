import axios from 'axios'
import Cookies from 'js-cookie'
import { useAuthStore } from "@/modules/Authentication/stores/authStore";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v2'
const API_SECRET_KEY = import.meta.env.VITE_API_SECRET_KEY

// console.log('API Base URL:', API_BASE_URL)

const api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-API-Secret': API_SECRET_KEY
    }
})

// Request interceptor
api.interceptors.request.use(
    (config) => {
        // console.log('API Request:', config.method?.toUpperCase(), config.url)

        // const token = localStorage.getItem('auth_token')
        // if (token) {
        //     config.headers.Authorization = `Bearer ${token}`
        // }


        // Add X-API-Secret from cookies or env
        const apiKey = Cookies.get('x_api_key');

        // console.log('x_api_key', apiKey);

        if (apiKey) {
            config.headers['auth-key'] = apiKey
        }

        // Add Bearer token from cookies
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
    (response) => {
        // console.log('API Response:', response.config.url, '- Status:', response.status)
        return response
    },
    (error) => {
        console.error('API Response Error:', error.config?.url, error)

        if (error.response) {

            const authStore = useAuthStore();

            console.error('Response status:', error.response.status)
            console.error('Response data:', error.response.data)

            switch (error.response.status) {
                case 401:
                    // Clear auth cookies
                    Cookies.remove('auth_token')
                    Cookies.remove('x_api_key')
                    // window.location.href = '/in/en/login'
                    break
                case 403:
                    console.error('Access forbidden')

                    // COMMENTED DUE TO FREE SUBSCRIPTION PLAN RESTRICTED ROUTE
                    authStore.logout();
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
