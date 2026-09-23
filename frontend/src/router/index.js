// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import coreRoutes from '@/modules/Core/router'
import authenticationRoutes from '@/modules/Authentication/router'
import authorizedAgentsRoutes from '@/modules/AuthorizedAgents/router'
import groceryIndiaRoutes from '@/modules/GroceryIndia/router'
import groceryGermanyRoutes from '@/modules/GroceryGermany/router'
import NotFound from '@/modules/Core/components/404Page.vue'
import { agentGuard } from '@/router/guards/agentGuard'
import { shopGuard } from '@/router/guards/shopGuard'


const base = import.meta.env.VITE_BASE_URL || '/'


console.log('📦 GroceryIndia routes loaded:', groceryIndiaRoutes.length)
console.log('📦 GroceryGermany routes loaded:', groceryGermanyRoutes.length)
console.log('📦 India first route name:', groceryIndiaRoutes[0]?.name)
console.log('📦 Germany first route name:', groceryGermanyRoutes[0]?.name)


// ── Wrap routes with locale prefix ────────────────────────────
const createLocalizedRoutes = (routes, prefix = '') => {
  return routes.map(route => ({
    ...route,
    path: `/:country/:lang${prefix}${route.path}`
  }))
}


const getInitialCountry = () => localStorage.getItem('country') || 'in'

const initialCountry = getInitialCountry()
const initialGroceryRoutes = initialCountry === 'de'
  ? createLocalizedRoutes(groceryGermanyRoutes, '/grocery')
  : createLocalizedRoutes(groceryIndiaRoutes, '/grocery')


console.log('🌍 Initial country:', initialCountry)
console.log('📦 Loading', initialGroceryRoutes.length, 'grocery routes')


const routes = [
  {
    path: '/',
    redirect: () => {
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


// ── Dynamic grocery route switcher ────────────────────────────
const switchGroceryRoutes = (newCountry) => {
  if (currentGroceryCountry === newCountry) {
    console.log('✅ Already on correct country routes')
    return false
  }

  console.log('🔄 Switching grocery routes:', currentGroceryCountry, '→', newCountry)

  const currentGroceryRouteNames = router.getRoutes()
    .filter(r => r.path.includes('/grocery/'))
    .map(r => r.name)
    .filter(Boolean)

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
// NAVIGATION GUARD — composes agentGuard + shopGuard
// ============================================================
router.beforeEach(async (to, from, next) => {
  console.log('\n========================================')
  console.log('🧭 Navigation to:', to.path)
  console.log('  Route name:', to.name)
  console.log('========================================')

  // ── Allow root redirect immediately ───────────────────────
  if (to.path === '/') {
    next()
    return
  }

  // ── Skip 404 page ──────────────────────────────────────────
  if (to.name === 'not-found') {
    console.log('💀 404 page — allowing')
    next()
    return
  }

  // ── Resolve country & lang ─────────────────────────────────
  let country = to.params.country ? String(to.params.country).toLowerCase() : null
  let lang = to.params.lang ? String(to.params.lang).toLowerCase() : null

  if (!country) country = localStorage.getItem('country')?.toLowerCase() ?? null
  if (!lang) lang = localStorage.getItem('language')?.toLowerCase() ?? null

  if (!country || !lang) {
    console.log('⚠️ Missing params — redirecting to root')
    next('/')
    return
  }

  console.log('📍 Country:', country, '| Lang:', lang)

  // ── 1️⃣  Agent guard — handles ALL /authorized-agents/* paths ──
  // Returns true if it handled the navigation (called next internally).
  // Returns false if it's not an agent route — fall through to shop guard.
  const handledByAgentGuard = await agentGuard(to, from, next, { country, lang })
  if (handledByAgentGuard) return

  // ── 2️⃣  Shop guard — handles all remaining routes ─────────
  await shopGuard(to, from, next, {
    country,
    lang,
    switchGroceryRoutes,
    currentGroceryCountry
  })
})


// Helper: check if subscription is expired
export const checkSubscriptionExpired = (endDate) => {
  if (!endDate) return true
  return new Date() > new Date(endDate)
}


export default router