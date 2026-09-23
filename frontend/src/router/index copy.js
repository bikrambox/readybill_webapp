// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import coreRoutes from '@/modules/Core/router'
import { useLocaleStore } from '@/stores/localeStore'
import { useAuthStore } from '@/modules/Authentication/stores/authStore'
import authenticationRoutes from '@/modules/Authentication/router'
import authorizedAgentsRoutes from '@/modules/AuthorizedAgents/router'
import groceryIndiaRoutes from '@/modules/GroceryIndia/router'
import groceryGermanyRoutes from '@/modules/GroceryGermany/router'
import NotFound from '@/modules/Core/components/404Page.vue'
import Cookies from 'js-cookie'
import { useUserDetailsStore } from '@/modules/Authentication/stores/userDetails'
import { isAuthTokenExpired, isAgentAuthTokenExpired } from '@/utils/tokenUtils'
import { useLoginStore } from '@/modules/AuthorizedAgents/stores/loginStore'

const base = import.meta.env.VITE_BASE_URL || '/'

console.log('📦 GroceryIndia routes loaded:', groceryIndiaRoutes.length)
console.log('📦 GroceryGermany routes loaded:', groceryGermanyRoutes.length)
console.log('📦 India first route name:', groceryIndiaRoutes[0]?.name)
console.log('📦 Germany first route name:', groceryGermanyRoutes[0]?.name)

// Wrap routes with locale prefix
const createLocalizedRoutes = (routes, prefix = '') => {
  return routes.map(route => ({
    ...route,
    path: `/:country/:lang${prefix}${route.path}`
  }))
}

const getInitialCountry = () => {
  return localStorage.getItem('country') || 'in'
}

const initialCountry = getInitialCountry()
const initialGroceryRoutes = initialCountry === 'de'
  ? createLocalizedRoutes(groceryGermanyRoutes, '/grocery')
  : createLocalizedRoutes(groceryIndiaRoutes, '/grocery')

console.log('🌍 Initial country:', initialCountry)
console.log('📦 Loading', initialGroceryRoutes.length, 'grocery routes')

const routes = [
  {
    path: '/',
    redirect: to => {
      const country = localStorage.getItem('country') || 'in'
      const lang = localStorage.getItem('language') || 'en'
      console.log('🏠 Root redirect to:', `/${country}/${lang}`)
      return `/${country}/${lang}`
    }
  },
  ...createLocalizedRoutes(coreRoutes),
  ...createLocalizedRoutes(authenticationRoutes),
  ...createLocalizedRoutes(authorizedAgentsRoutes),
  ...initialGroceryRoutes,
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: { title: '404 - Page Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(base),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

let currentGroceryCountry = initialCountry

console.log('🗺️ Total routes registered:', router.getRoutes().length)
console.log('🗺️ Grocery routes:',
  router.getRoutes()
    .filter(r => r.path.includes('/grocery/'))
    .map(r => ({ name: r.name, path: r.path }))
)

const switchGroceryRoutes = (newCountry) => {
  if (currentGroceryCountry === newCountry) {
    console.log('✅ Already on correct country routes')
    return false
  }

  console.log('🔄 Switching grocery routes:', currentGroceryCountry, '→', newCountry)

  const currentGroceryRouteNames = router.getRoutes()
    .filter(r => r.path.includes('/grocery/'))
    .map(r => r.name)
    .filter(name => name)

  console.log('🗑️ Removing routes:', currentGroceryRouteNames)
  currentGroceryRouteNames.forEach(name => router.removeRoute(name))

  const newRoutes = newCountry === 'de'
    ? createLocalizedRoutes(groceryGermanyRoutes, '/grocery')
    : createLocalizedRoutes(groceryIndiaRoutes, '/grocery')

  console.log('➕ Adding', newRoutes.length, 'new routes for', newCountry)

  router.removeRoute('not-found')

  newRoutes.forEach(route => {
    router.addRoute(route)
    console.log('  ✅ Added:', route.name)
  })

  router.addRoute({
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: NotFound,
    meta: { title: '404 - Page Not Found' }
  })

  currentGroceryCountry = newCountry
  console.log('✅ Switched to', newCountry, 'grocery routes')
  return true
}


// ============================================================
// NAVIGATION GUARD
// ============================================================
router.beforeEach(async (to, from, next) => {
  console.log('\n========================================')
  console.log('🧭 Navigation to:', to.path)
  console.log('  Route name:', to.name)
  console.log('========================================')

  // ── Allow root redirect immediately ──────────────────────
  if (to.path === '/') {
    next()
    return
  }

  // ── Skip 404 page ─────────────────────────────────────────
  if (to.name === 'not-found') {
    console.log('💀 404 page - allowing')
    next()
    return
  }

  // ── Resolve country & lang FIRST — everything below needs them ──
  let country = to.params.country ? String(to.params.country).toLowerCase() : null
  let lang = to.params.lang ? String(to.params.lang).toLowerCase() : null

  if (!country) {
    const lsCountry = localStorage.getItem('country')
    if (lsCountry) country = lsCountry.toLowerCase()
  }
  if (!lang) {
    const lsLang = localStorage.getItem('language')
    if (lsLang) lang = lsLang.toLowerCase()
  }

  if (!country || !lang) {
    console.log('⚠️ Missing params, redirecting to root')
    next('/')
    return
  }

  console.log('📍 Country:', country, '| Lang:', lang)


  // ============================================================
  // SECTION 1 — AGENT GUARDS
  // Must run BEFORE shop guards so agent routes are never
  // intercepted by shop guest/auth logic
  // ============================================================
  const isAgentRoute = to.path.includes('/authorized-agents')
  const agentLoginStore = useLoginStore()

  // Agent token expiry — clear if expired
  if (Cookies.get('agent_auth_token') && isAgentAuthTokenExpired()) {
    console.log('🔐 Agent token expired — clearing')
    agentLoginStore.logout()
  }

  const isAgentAuthenticated = agentLoginStore.isAuthenticated
  console.log('🔐 Agent isAuthenticated:', isAgentAuthenticated)

  // 🚫 Only block shop users from PROTECTED agent routes
  // Agent guest pages (login) remain accessible
  if (isAgentRoute) {
    const authStore = useAuthStore()
    const isShopAuthenticated = authStore.isAuthenticated || !!Cookies.get('auth_token')

    if (isShopAuthenticated && to.meta.requiresAgentAuth) {
      console.log('🚫 Shop user tried to access protected agent route — blocked')
      next(`/${country}/${lang}/grocery/sell`)
      return
    }
  }

  // 🔒 Protect agent-authenticated pages
  if (to.meta.requiresAgentAuth && !isAgentAuthenticated) {
    console.log('🔒 Agent auth required — redirecting to agent login')
    next(`/${country}/${lang}/authorized-agents/login`)
    return
  }

  // 👤 Redirect logged-in agents away from guest-only pages
  if (to.meta.agentGuest && isAgentAuthenticated) {
    console.log('👤 Agent already logged in — redirecting to dashboard')
    next(`/${country}/${lang}/authorized-agents/dashboard`)
    return
  }

  // ✅ All remaining agent routes are allowed
  if (isAgentRoute) {
    console.log('✅ Agent route — navigation allowed')
    next()
    return
  }

  // ============================================================
  // SECTION 2 — SHOP / EMPLOYEE GUARDS
  // Only reached for non-agent routes
  // ============================================================
  const localeStore = useLocaleStore()
  const authStore = useAuthStore()
  const token = Cookies.get('auth_token')

  // Shop token expiry — country & lang are now guaranteed defined
  if (token && isAuthTokenExpired()) {
    console.log('🔐 Shop token expired — clearing')
    Cookies.remove('auth_token')
    Cookies.remove('x_api_key')
    authStore.clearAuth()
    if (to.meta.requiresAuth) {
      next(`/${country}/${lang}/login`)
      return
    }
  }

  const isAuthenticated = authStore.isAuthenticated

  console.log('🔐 Shop token exists:', !!token)
  console.log('🔐 Shop isAuthenticated:', isAuthenticated)

  // Redirect authenticated shop users away from Home/Login/Register
  if (to.name === 'Home' && isAuthenticated) {
    console.log('🏠 Home: Authenticated → redirecting to sell')
    next(`/${country}/${lang}/grocery/sell`)
    return
  }

  if (to.name === 'Login' && isAuthenticated) {
    console.log('🔐 Login: Authenticated → redirecting to sell')
    next(`/${country}/${lang}/grocery/sell`)
    return
  }

  if (to.name === 'Register' && isAuthenticated) {
    console.log('📝 Register: Authenticated → redirecting to sell')
    next(`/${country}/${lang}/grocery/sell`)
    return
  }

  // Switch grocery routes dynamically when country changes
  if (country !== currentGroceryCountry) {
    console.log('🌍 Country changed! Current:', currentGroceryCountry, '→ New:', country)
    const switched = switchGroceryRoutes(country)
    if (switched && to.path.includes('/grocery/')) {
      console.log('🔄 Re-triggering navigation after route switch')
      next({ path: to.path, replace: true })
      return
    }
  }

  // Update locale store
  if (localeStore.language !== lang || localeStore.country !== country) {
    console.log('🌍 Updating locale store')
    localeStore.country = country
    localeStore.language = lang
    localStorage.setItem('country', country)
    localStorage.setItem('language', lang)
  }

  // Require authentication for protected routes
  if (to.meta.requiresAuth && !isAuthenticated) {
    const target = `/${country}/${lang}/login`
    if (to.path !== target) {
      console.log('🔒 Auth required, redirecting to login')
      next(target)
      return
    }
  }

  // Redirect authenticated users away from guest-only pages
  if (to.meta.guest && isAuthenticated) {
    next(`/${country}/${lang}/grocery/sell`)
    return
  }

  // Require admin access
  if (to.meta.requiresAdmin) {
    if (!authStore.user || authStore.user.isAdmin !== 1) {
      const target = `/${country}/${lang}/grocery/sell`
      if (to.path !== target) {
        next(target)
        return
      }
    }
  }

  // Country mismatch validation for authenticated shop users
  if (isAuthenticated && to.meta.requiresAuth) {
    const userDetailsStore = useUserDetailsStore()
    await userDetailsStore.fetchUserProfile()

    const userCountryCode = userDetailsStore.country_details?.code?.toLowerCase()

    if (userCountryCode && country !== userCountryCode) {
      const correctUrl = to.path.replace(`/${country}/`, `/${userCountryCode}/`)
      if (to.path !== correctUrl) {
        console.log('⚠️ Country mismatch! URL:', country, '| User country:', userCountryCode)
        next(correctUrl)
        return
      }
    }
  }

  console.log('✅ Navigation allowed')
  next()
})


// Helper: check if subscription is expired
const checkSubscriptionExpired = (endDate) => {
  if (!endDate) return true
  return new Date() > new Date(endDate)
}

export default router