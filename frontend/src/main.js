import { createApp, watch } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import i18n from './i18n'
import ErrorPage from './components/ErrorPage.vue'
import axios from 'axios'
import api, { setApiRouter } from '@/config/api'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { useUserDetailsStore } from '@/modules/Authentication/stores/userDetails'
import { useInputValidation } from "@/composables/useInputValidation";

// Import Bootstrap for Echo setup
import './bootstrap.js';

// Bootstrap CSS and JS
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
// import 'bootstrap/dist/js/bootstrap.bundle.min.js'

// Custom global styles
import './assets/main.css'

// Configure axios
axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v2'
axios.defaults.timeout = 10000

// ============================================
// PREVENT INFINITE REDIRECTS
// ============================================
const MAX_REDIRECTS = 2
const REDIRECT_KEY = 'app_redirect_count'
const REDIRECT_TIMESTAMP = 'app_redirect_time'
const LAST_REDIRECT_URL = 'app_last_redirect'

const getRedirectCount = () => {
    const timestamp = sessionStorage.getItem(REDIRECT_TIMESTAMP)
    const now = Date.now()

    if (timestamp && (now - parseInt(timestamp)) > 5000) {
        sessionStorage.removeItem(REDIRECT_KEY)
        sessionStorage.removeItem(REDIRECT_TIMESTAMP)
        sessionStorage.removeItem(LAST_REDIRECT_URL)
        return 0
    }

    return parseInt(sessionStorage.getItem(REDIRECT_KEY) || '0')
}

const incrementRedirectCount = () => {
    const count = getRedirectCount() + 1
    sessionStorage.setItem(REDIRECT_KEY, count.toString())
    sessionStorage.setItem(REDIRECT_TIMESTAMP, Date.now().toString())
    return count
}

const resetRedirectCount = () => {
    sessionStorage.removeItem(REDIRECT_KEY)
    sessionStorage.removeItem(REDIRECT_TIMESTAMP)
    sessionStorage.removeItem(LAST_REDIRECT_URL)
}

const safeRedirect = (url) => {
    const lastUrl = sessionStorage.getItem(LAST_REDIRECT_URL)

    if (lastUrl === url) {
        console.error('🚫 Circular redirect detected:', url)
        throw new Error(`Circular redirect detected to: ${url}`)
    }

    const count = incrementRedirectCount()
    console.log(`🔄 Redirect attempt ${count}/${MAX_REDIRECTS} to: ${url}`)

    if (count > MAX_REDIRECTS) {
        console.error('🚫 Max redirects exceeded!')
        throw new Error(`Too many redirects (${count}). Stopping to prevent infinite loop.`)
    }

    sessionStorage.setItem(LAST_REDIRECT_URL, url)

    setTimeout(() => {
        window.location.href = url
    }, 200)
}

// ============================================
// TIMEOUT PROTECTION - 10 seconds
// ============================================
let appMounted = false
let timeoutTriggered = false

const showErrorPage = (errorInfo = {}) => {
    if (timeoutTriggered) return
    timeoutTriggered = true

    console.error('🚨 Showing error page:', errorInfo)

    document.body.innerHTML = '<div id="error-app"></div>'

    const errorApp = createApp(ErrorPage, {
        errorCode: errorInfo.type || 'ERROR',
        errorTitle: errorInfo.title || 'Application Error',
        errorMessage: errorInfo.message || 'Something went wrong while loading the application.',
        instructions: Array.isArray(errorInfo.instructions) ? errorInfo.instructions : [],
        showRetry: errorInfo.showRetry ?? true,
        homeUrl: errorInfo.homeUrl || '/',
        onRetry: () => {
            resetRedirectCount()
            window.location.reload()
        }
    })

    errorApp.mount('#error-app')
}

setTimeout(() => {
    if (!appMounted && !timeoutTriggered) {
        console.error('⏰ TIMEOUT: Application failed to initialize within 10 seconds')

        showErrorPage({
            type: 'TIMEOUT',
            title: 'Loading Timeout',
            message: 'The application is taking too long to load.',
            instructions: [
                'Check your internet connection.',
                'Make sure the server is running.',
                'Verify the API endpoint configuration.',
                'Refresh the page and try again.'
            ]
        })
    }
}, 10000)

const app = createApp(App)
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

// Pass app instance to pinia for access in stores
pinia.use(({ store }) => {
    store.$app = app
    store.$i18n = i18n.global
    store.$t = i18n.global.t
    store.$locale = i18n.global.locale
})

app.use(pinia)

import { useLocaleStore } from './stores/localeStore'
import { useLoadingStore } from './stores/loadingStore'

const initApp = async () => {
    if (timeoutTriggered) return

    const loadingStore = useLoadingStore()
    const localeStore = useLocaleStore()

    try {
        console.log('🚀 Initializing application...')
        console.log(`📊 Current redirect count: ${getRedirectCount()}/${MAX_REDIRECTS}`)

        loadingStore.loadingMessage = 'Initializing...'

        const pathParts = window.location.pathname.split('/').filter(Boolean)
        const urlCountry = pathParts[0]?.toLowerCase()
        const urlLang = pathParts[1]?.toLowerCase()

        console.log(`📍 URL params: country="${urlCountry}", language="${urlLang}"`)

        const restOfPath = pathParts.slice(2).join('/')
        const pathSuffix = restOfPath ? `/${restOfPath}` : ''

        // ============================================
        // SCENARIO 1: Both country and language missing
        // ============================================
        if (!urlCountry && !urlLang) {
            console.log('⚠️ Case 1: No country or language in URL')
            loadingStore.loadingMessage = 'Detecting your location...'

            const detected = await localeStore.detectCountryLanguage()
            console.log('✅ API Detection:', detected)

            const redirectUrl = `/${detected.countryCode}/${detected.language}${pathSuffix}`
            safeRedirect(redirectUrl)
            return
        }

        // ============================================
        // SCENARIO 2: Only country provided, language missing
        // ============================================
        if (urlCountry && !urlLang) {
            console.log(`⚠️ Case 2: Country provided (${urlCountry}), language missing`)
            loadingStore.loadingMessage = 'Detecting language...'

            try {
                const response = await api.get('/country-code')
                const apiData = response.data
                console.log('✅ API Response:', apiData)

                const detectedLanguage = apiData.national_language || 'en'
                const redirectUrl = `/${urlCountry}/${detectedLanguage}${pathSuffix}`

                safeRedirect(redirectUrl)
                return
            } catch (error) {
                console.error('❌ Failed to fetch language from API:', error)

                const redirectUrl = `/${urlCountry}/en${pathSuffix}`
                console.log(`⚠️ API failed, using fallback: ${redirectUrl}`)

                safeRedirect(redirectUrl)
                return
            }
        }

        // ============================================
        // SCENARIO 3: Only language provided, country missing
        // ============================================
        if (!urlCountry && urlLang) {
            console.log(`⚠️ Case 3: Language provided (${urlLang}), country missing`)
            loadingStore.loadingMessage = 'Detecting your location...'

            const detected = await localeStore.detectCountryLanguage()
            console.log('✅ API Detection:', detected)

            const redirectUrl = `/${detected.countryCode}/${urlLang}${pathSuffix}`
            safeRedirect(redirectUrl)
            return
        }

        // ============================================
        // SCENARIO 4: Both country and language provided - VALIDATE!
        // ============================================
        console.log(`✅ Case 4: Both country and language provided: ${urlCountry}/${urlLang}`)

        const initialCountry = urlCountry
        const initialLang = urlLang
        const redirectCount = getRedirectCount()

        if (redirectCount > 0) {
            console.log(`⚠️ Already redirected ${redirectCount} time(s), skipping validation`)

            resetRedirectCount()

            localeStore.country = initialCountry
            localeStore.language = initialLang
        } else {
            console.log('🔍 Validating country/language combination...')
            loadingStore.loadingMessage = 'Validating locale...'

            try {
                const validation = await localeStore.validateCountryLanguage(initialCountry, initialLang)
                console.log('✅ Validation result:', validation)

                const isCountryValid = validation.country.is_valid === 1
                const isLanguageValid = validation.language.is_valid === 1

                if (!isCountryValid && !isLanguageValid) {
                    showErrorPage({
                        type: 'INVALID_LOCALE',
                        title: 'Invalid Country and Language',
                        message: `The locale "${initialCountry}/${initialLang}" is not supported.`,
                        instructions: [
                            'Check both the country code and language code in the URL.',
                            'Use a valid locale format such as /in/en.',
                            'Return to the home page and try again.'
                        ]
                    })
                    return
                }

                if (!isCountryValid && isLanguageValid) {
                    showErrorPage({
                        type: 'INVALID_COUNTRY',
                        title: 'Invalid Country Code',
                        message: `The country code "${initialCountry}" is not supported.`,
                        instructions: [
                            'Check the country code in the URL.',
                            'Use a supported country code such as in, us, or de.',
                            'Return to the home page and try again.'
                        ]
                    })
                    return
                }

                if (isCountryValid && !isLanguageValid) {
                    showErrorPage({
                        type: 'INVALID_LANGUAGE',
                        title: 'Invalid Language Code',
                        message: `The language code "${initialLang}" is not supported for country "${initialCountry}".`,
                        instructions: [
                            'Check the language code in the URL.',
                            'Use a supported language for the selected country.',
                            'Try a valid locale such as /in/en.'
                        ]
                    })
                    return
                }

                console.log('✅ Valid country and language combination')
                localeStore.country = initialCountry
                localeStore.language = initialLang
            } catch (error) {
                console.error('❌ Validation failed:', error)

                console.log('⚠️ Validation API error - proceeding anyway')
                localeStore.country = initialCountry
                localeStore.language = initialLang
            }
        }

        // ============================================
        // PROCEED WITH APP INITIALIZATION
        // ============================================
        console.log('✅ Initializing app with locale:', `${localeStore.country}/${localeStore.language}`)

        console.log('📚 Loading translations...')
        loadingStore.loadingMessage = 'Loading translations...'
        const translations = await localeStore.loadTranslations(initialLang)

        if (timeoutTriggered) return

        if (translations && typeof translations === 'object' && Object.keys(translations).length > 0) {
            console.log('✅ Translations loaded successfully')
            i18n.global.setLocaleMessage(initialLang, translations)
            i18n.global.locale.value = initialLang
        } else {
            console.log('⚠️ Using fallback translations')
            const fallback = {
                common: {
                    home: 'Home',
                    about: 'About',
                    contact: 'Contact',
                    settings: 'Settings',
                    welcome: 'Welcome'
                }
            }

            localeStore.translations[initialLang] = fallback
            i18n.global.setLocaleMessage(initialLang, fallback)
            i18n.global.locale.value = initialLang
        }

        localeStore.isInitialized = true

        localStorage.setItem('country', initialCountry)
        localStorage.setItem('language', initialLang)

        console.log('🔧 Setting up plugins...')
        app.use(i18n)
        app.use(router)
        setApiRouter(router)

        console.log('🎯 Mounting app...')
        loadingStore.loadingMessage = 'Starting application...'
        app.mount('#app')

        appMounted = true
        resetRedirectCount()
        console.log('✅ App mounted successfully!')

        setTimeout(() => {
            loadingStore.hideLoading()
            console.log('✅ Initialization complete!')
        }, 300)

    } catch (error) {
        if (timeoutTriggered) return

        console.error('❌ App initialization failed:', error)

        try {
            const loadingStore = useLoadingStore()
            loadingStore.hideLoading()
        } catch (e) {
            console.error('Could not hide loader:', e)
        }

        showErrorPage({
            type: 'INIT_ERROR',
            title: 'Initialization Failed',
            message: error?.message || 'An error occurred while starting the application.',
            instructions: [
                'Refresh the page and try again.',
                'Make sure the backend server is running.',
                'Verify your API URL and environment configuration.',
                'Contact support if the issue continues.'
            ]
        })
    }
}

// Global error handlers
app.config.errorHandler = (err, instance, info) => {
    if (timeoutTriggered) return

    console.error('❌ Vue Error:', err, info)

    showErrorPage({
        type: 'VUE_ERROR',
        title: 'Component Error',
        message: 'A component encountered an error while rendering the page.',
        instructions: [
            'Refresh the page.',
            'Try the same action again.',
            'Clear browser cache if the problem persists.',
            'Contact support if the issue continues.'
        ]
    })
}

window.addEventListener('error', (event) => {
    if (timeoutTriggered) return

    console.error('❌ Global Error:', event.error)

    showErrorPage({
        type: 'GLOBAL_ERROR',
        title: 'Unexpected Error',
        message: 'An unexpected error occurred in the application.',
        instructions: [
            'Refresh the page.',
            'Try again after a moment.',
            'Clear your browser cache.',
            'Contact support if the problem continues.'
        ]
    })

    event.preventDefault()
})

window.addEventListener('unhandledrejection', (event) => {
    if (timeoutTriggered) return

    console.error('❌ Unhandled Rejection:', event.reason)

    showErrorPage({
        type: 'PROMISE_ERROR',
        title: 'Request Failed',
        message: 'A background request failed unexpectedly.',
        instructions: [
            'Check your internet connection.',
            'Refresh the page.',
            'Make sure the server is running properly.',
            'Try again after a few moments.'
        ]
    })

    event.preventDefault()
})

// Initial values (fallbacks)
app.config.globalProperties.$countryCode = '#'
app.config.globalProperties.$dialCode = '#'
app.config.globalProperties.$currency = '#'
app.config.globalProperties.$decimalSeparator = '#'

const userStore = useUserDetailsStore()

watch(
    () => userStore.country_details,
    (country) => {
        if (!country) return

        console.log('country', country.dial_code)

        app.config.globalProperties.$countryCode =
            country.code?.toLowerCase() || 'in'

        app.config.globalProperties.$dialCode =
            country.dial_code || '+91'

        app.config.globalProperties.$currency =
            country.currency_symbol || ''

        app.config.globalProperties.$decimalSeparator =
            country.decimal_separator || '.'

        app.config.globalProperties.$countryName =
            country.name || 'India'
    },
    { immediate: true, deep: true }
)

// ✅ Add global formatCurrency function
app.config.globalProperties.$formatCurrency = function (value) {
    if (value === null || value === undefined || value === '') return '-'

    let number = parseFloat(value)

    if (isNaN(number)) return '-'

    let formatted = number.toFixed(2)

    const decimalSeparator = this.$decimalSeparator || '.'
    const currency = this.$currency || ''

    if (decimalSeparator !== '.') {
        formatted = formatted.replace('.', decimalSeparator)
    }

    return `${currency}${formatted}`
}

// ✅ Add global formatDecimal function
app.config.globalProperties.$formatDecimal = function (value) {
    if (value === null || value === undefined || value === '') return '0.00'

    let number = parseFloat(value)

    if (isNaN(number)) return '0.00'

    return number.toFixed(2)
}

// ============================================
// GLOBAL SUBSCRIPTION ACCESS CHECKER
// ============================================
app.config.globalProperties.$canAccess = function (requiredFeature = null) {
    const userStore = useUserDetailsStore()
    const subscription = userStore.shop_subscription

    if (!subscription || typeof subscription !== 'object') {
        console.warn('⚠️ No subscription data available')
        return false
    }

    if (subscription.isSubscriptionExpired === 1) {
        console.warn('⚠️ Subscription expired')
        return false
    }

    if (subscription.status !== 1) {
        console.warn('⚠️ Subscription not active')
        return false
    }

    if (subscription.payment_status !== 'paid') {
        console.warn('⚠️ Payment not completed')
        return false
    }

    if (requiredFeature) {
        switch (requiredFeature) {
            case 'reports': {
                const yearsAccess = parseInt(subscription.reportDataYearAccess) || 0
                if (yearsAccess < 1) {
                    console.warn('⚠️ No report access')
                    return false
                }
                break
            }

            case 'premium':
                if (subscription.isFreeSubscriptionPlan === 1) {
                    console.warn('⚠️ Free plan - no premium access')
                    return false
                }
                break
        }
    }

    return true
}

// Helper to get subscription details
app.config.globalProperties.$getSubscription = function () {
    const userStore = useUserDetailsStore()
    return {
        isActive: this.$canAccess(),
        isPaid: userStore.shop_subscription?.payment_status === 'paid',
        isExpired: userStore.shop_subscription?.isSubscriptionExpired === 1,
        isFree: userStore.shop_subscription?.isFreeSubscriptionPlan === 1,
        endDate: userStore.shop_subscription?.end_date,
        reportYearsAccess: parseInt(userStore.shop_subscription?.reportDataYearAccess) || 0
    }
}

// Make composable globally available
app.config.globalProperties.$inputValidation = useInputValidation

console.log('🌟 ========================================')
console.log('🌟 Application Starting')
console.log('🌟 ========================================')

initApp()