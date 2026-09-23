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

const showErrorPage = (errorInfo) => {
    if (timeoutTriggered) return
    timeoutTriggered = true

    console.error('🚨 Showing error page:', errorInfo)

    document.body.innerHTML = '<div id="error-app"></div>'

    const errorApp = createApp(ErrorPage, {
        errorCode: errorInfo.type || 'ERROR',
        errorTitle: errorInfo.title || 'Application Error',
        errorMessage: errorInfo.message || 'Something went wrong while loading the application.',
        errorDetails: errorInfo.details || null,
        showRetry: true,
        homeUrl: '/',
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
            message: 'The application is taking too long to load. This usually indicates a network issue or the server is not responding.',
            details: `Timeout after 10 seconds\nTimestamp: ${new Date().toISOString()}\n\nPossible causes:\n• Server not responding\n• Network connection issues\n• API endpoint not configured\n• CORS issues\n\nPlease check:\n1. Your internet connection\n2. Server is running (php artisan serve)\n3. .env file is configured correctly`
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


// pinia.use(({ store }) => {
//     store.$i18n = i18n.global
//     store.$t = i18n.global.t
//     store.$locale = i18n.global.locale
// })

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
            // VALIDATE on first load
            console.log('🔍 Validating country/language combination...')
            loadingStore.loadingMessage = 'Validating locale...'

            try {
                const validation = await localeStore.validateCountryLanguage(initialCountry, initialLang)
                console.log('✅ Validation result:', validation)

                // ============================================
                // CHECK IF COUNTRY OR LANGUAGE IS INVALID
                // ============================================
                const isCountryValid = validation.country.is_valid === 1
                const isLanguageValid = validation.language.is_valid === 1

                // Both invalid
                if (!isCountryValid && !isLanguageValid) {
                    console.error('❌ Both country and language are invalid!')
                    throw new Error('INVALID_LOCALE')
                }

                // Only country invalid
                if (!isCountryValid && isLanguageValid) {
                    console.error('❌ Invalid country code:', initialCountry)

                    showErrorPage({
                        type: 'INVALID_COUNTRY',
                        title: 'Invalid Country Code',
                        message: `The country code "${initialCountry}" is not supported or invalid.`,
                        details: `Country Code: ${initialCountry}\nLanguage Code: ${initialLang}\n\nValidation Response:\n${JSON.stringify(validation.country, null, 2)}\n\nSupported countries:\n• in (India)\n• us (United States)\n• de (Germany)\n• And more...\n\nPlease use a valid country code.`
                    })
                    return
                }

                // Only language invalid
                if (isCountryValid && !isLanguageValid) {
                    console.error('❌ Invalid language code:', initialLang)

                    showErrorPage({
                        type: 'INVALID_LANGUAGE',
                        title: 'Invalid Language Code',
                        message: `The language code "${initialLang}" is not supported for country "${initialCountry}".`,
                        details: `Country Code: ${initialCountry}\nLanguage Code: ${initialLang}\n\nValidation Response:\n${JSON.stringify(validation.language, null, 2)}\n\nSupported languages:\n• en (English)\n• hi (Hindi)\n• de (German)\n• And more...\n\nPlease use a valid language code for this country.`
                    })
                    return
                }

                // Both valid - proceed!
                console.log('✅ Valid country and language combination')
                localeStore.country = initialCountry
                localeStore.language = initialLang

            } catch (error) {
                console.error('❌ Validation failed:', error)

                // Special handling for invalid locale
                if (error.message === 'INVALID_LOCALE') {
                    showErrorPage({
                        type: 'INVALID_LOCALE',
                        title: 'Invalid Country and Language',
                        message: `Both country code "${initialCountry}" and language code "${initialLang}" are invalid or not supported.`,
                        details: `Country Code: ${initialCountry}\nLanguage Code: ${initialLang}\n\nBoth codes are invalid.\n\nPlease use valid codes:\n\nCountry codes: in, us, de, etc.\nLanguage codes: en, hi, de, etc.\n\nExample: /in/en (India, English)`
                    })
                    return
                }

                // Network or other validation errors - proceed with caution
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
            loadingStore.hideLoading()
        } catch (e) {
            console.error('Could not hide loader:', e)
        }

        showErrorPage({
            type: 'INIT_ERROR',
            title: 'Initialization Failed',
            message: error.message || 'An error occurred while starting the application.',
            details: `Error: ${error.message}\n\nType: ${error.name}\n\nStack Trace:\n${error.stack}\n\nTimestamp: ${new Date().toISOString()}\n\nRedirect Count: ${getRedirectCount()}`
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
        message: 'A component encountered an error. Please refresh the page.',
        details: `${err.message}\n\nComponent: ${instance?.$options?.name || 'Unknown'}\nInfo: ${info}\n\nStack:\n${err.stack}`
    })
}

window.addEventListener('error', (event) => {
    console.error('❌ Global Error:', event.error)
    event.preventDefault()
})

window.addEventListener('unhandledrejection', (event) => {
    console.error('❌ Unhandled Rejection:', event.reason)
    event.preventDefault()
})


// ✅ Initialize global variables
// app.config.globalProperties.$countryCode = 'in'
// app.config.globalProperties.$currency = '₹'
// app.config.globalProperties.$decimalSeparator = ','

// Initial values (fallbacks)
app.config.globalProperties.$countryCode = '#'
app.config.globalProperties.$dialCode = '#'
app.config.globalProperties.$currency = '#'
app.config.globalProperties.$decimalSeparator = '#'

const userStore = useUserDetailsStore()


// 🔥 Watch store and keep globals in sync
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

        // console.log('🌍 Globals updated from store:', {
        //     countryCode: app.config.globalProperties.$countryCode,
        //     currency: app.config.globalProperties.$currency,
        //     decimalSeparator: app.config.globalProperties.$decimalSeparator
        // })
    },
    { immediate: true, deep: true }
)


// ✅ Add global formatCurrency function
app.config.globalProperties.$formatCurrency = function (value) {
    if (value === null || value === undefined || value === '') return '-'

    let number = parseFloat(value)

    if (isNaN(number)) return '-'

    // Format to 2 decimals
    let formatted = number.toFixed(2)

    // Get current decimal separator and currency from global properties
    const decimalSeparator = this.$decimalSeparator || '.'
    const currency = this.$currency || ''

    // Replace decimal separator dynamically
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

// // fetch item categories 
// app.config.globalProperties.$itemCategories = function (value) {
//     userStore.fetchCategories();
// }
// // fetch item categories 

// ============================================
// GLOBAL SUBSCRIPTION ACCESS CHECKER
// ============================================
app.config.globalProperties.$canAccess = function (requiredFeature = null) {
    const userStore = useUserDetailsStore()
    const subscription = userStore.shop_subscription

    // Check if subscription data exists
    if (!subscription || typeof subscription !== 'object') {
        console.warn('⚠️ No subscription data available')
        return false
    }

    // Check if subscription is expired
    if (subscription.isSubscriptionExpired === 1) {
        console.warn('⚠️ Subscription expired')
        return false
    }

    // Check if subscription is active
    if (subscription.status !== 1) {
        console.warn('⚠️ Subscription not active')
        return false
    }

    // Check payment status
    if (subscription.payment_status !== 'paid') {
        console.warn('⚠️ Payment not completed')
        return false
    }

    // Feature-specific checks (optional)
    if (requiredFeature) {
        switch (requiredFeature) {
            case 'reports':
                const yearsAccess = parseInt(subscription.reportDataYearAccess) || 0
                if (yearsAccess < 1) {
                    console.warn('⚠️ No report access')
                    return false
                }
                break
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


// Make composable globally available via provide/inject or app.config.globalProperties
app.config.globalProperties.$inputValidation = useInputValidation;


console.log('🌟 ========================================')
console.log('🌟 Application Starting')
console.log('🌟 ========================================')
initApp()
