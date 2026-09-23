// src/router/guards/shopGuard.js
import Cookies from 'js-cookie'
import { useLocaleStore } from '@/stores/localeStore'
import { useAuthStore } from '@/modules/Authentication/stores/authStore'
import { useUserDetailsStore } from '@/modules/Authentication/stores/userDetails'
import { isAuthTokenExpired } from '@/utils/tokenUtils'

/**
 * Handles all shop / employee route protection.
 * switchGroceryRoutes is passed in from index.js since it
 * needs access to the router instance.
 */
export async function shopGuard(to, from, next, { country, lang, switchGroceryRoutes, currentGroceryCountry }) {

    const localeStore = useLocaleStore()
    const authStore = useAuthStore()
    const token = Cookies.get('auth_token')

    // ── Shop token expiry ──────────────────────────────────────
    if (token && isAuthTokenExpired()) {
        console.log('🔐 [ShopGuard] Shop token expired — clearing')
        Cookies.remove('auth_token')
        Cookies.remove('x_api_key')
        authStore.clearAuth()
        if (to.meta.requiresAuth) {
            next(`/${country}/${lang}/login`)
            return
        }
    }

    const isAuthenticated = authStore.isAuthenticated
    console.log('🔐 [ShopGuard] token exists:', !!token)
    console.log('🔐 [ShopGuard] isAuthenticated:', isAuthenticated)

    // ── Redirect authenticated shop users away from Home/Login/Register ──
    if (to.name === 'Home' && isAuthenticated) {
        console.log('🏠 [ShopGuard] Home + authenticated → /grocery/sell')
        next(`/${country}/${lang}/grocery/sell`)
        return
    }

    if (to.name === 'Login' && isAuthenticated) {
        console.log('🔐 [ShopGuard] Login + authenticated → /grocery/sell')
        next(`/${country}/${lang}/grocery/sell`)
        return
    }

    if (to.name === 'Register' && isAuthenticated) {
        console.log('📝 [ShopGuard] Register + authenticated → /grocery/sell')
        next(`/${country}/${lang}/grocery/sell`)
        return
    }

    // ── Dynamic grocery route switching on country change ──────
    if (country !== currentGroceryCountry) {
        console.log('🌍 [ShopGuard] Country changed:', currentGroceryCountry, '→', country)
        const switched = switchGroceryRoutes(country)
        if (switched && to.path.includes('/grocery/')) {
            console.log('🔄 [ShopGuard] Re-triggering navigation after route switch')
            next({ path: to.path, replace: true })
            return
        }
    }

    // ── Sync locale store ──────────────────────────────────────
    if (localeStore.language !== lang || localeStore.country !== country) {
        console.log('🌍 [ShopGuard] Updating locale store')
        localeStore.country = country
        localeStore.language = lang
        localStorage.setItem('country', country)
        localStorage.setItem('language', lang)
    }

    // ── Require shop auth ──────────────────────────────────────
    if (to.meta.requiresAuth && !isAuthenticated) {
        const target = `/${country}/${lang}/login`
        if (to.path !== target) {
            console.log('🔒 [ShopGuard] Auth required → login')
            next(target)
            return
        }
    }

    // ── Guest-only pages: redirect authenticated users out ─────
    if (to.meta.guest && isAuthenticated) {
        next(`/${country}/${lang}/grocery/sell`)
        return
    }

    // ── Require admin access ───────────────────────────────────
    if (to.meta.requiresAdmin) {
        if (!authStore.user || authStore.user.isAdmin !== 1) {
            const target = `/${country}/${lang}/grocery/sell`
            if (to.path !== target) {
                next(target)
                return
            }
        }
    }

    // ── Country mismatch for authenticated shop users ──────────
    if (isAuthenticated && to.meta.requiresAuth) {
        const userDetailsStore = useUserDetailsStore()
        await userDetailsStore.fetchUserProfile()

        const userCountryCode = userDetailsStore.country_details?.code?.toLowerCase()

        if (userCountryCode && country !== userCountryCode) {
            const correctUrl = to.path.replace(`/${country}/`, `/${userCountryCode}/`)
            if (to.path !== correctUrl) {
                console.log('⚠️ [ShopGuard] Country mismatch — URL:', country, '| User:', userCountryCode)
                next(correctUrl)
                return
            }
        }
    }

    console.log('✅ [ShopGuard] Navigation allowed')
    next()
}