// src/router/guards/agentGuard.js
import Cookies from 'js-cookie'
import { useLoginStore } from '@/modules/AuthorizedAgents/stores/loginStore'
import { useAuthStore } from '@/modules/Authentication/stores/authStore'
import { isAgentAuthTokenExpired } from '@/utils/tokenUtils'

/**
 * Handles all /authorized-agents/* route protection.
 * Returns true  → guard handled the navigation (next() or redirect called)
 * Returns false → not an agent route, let shop guard handle it
 */
export async function agentGuard(to, from, next, { country, lang }) {

    const isAgentRoute = to.path.includes('/authorized-agents')
    if (!isAgentRoute) return false  // not our concern, pass to shop guard

    const agentLoginStore = useLoginStore()

    // ── Clear expired agent token ──────────────────────────────
    if (Cookies.get('agent_auth_token') && isAgentAuthTokenExpired()) {
        console.log('🔐 [AgentGuard] Agent token expired — clearing')
        agentLoginStore.logout()
    }

    const isAgentAuthenticated = agentLoginStore.isAuthenticated
    const authStore = useAuthStore()
    const isShopAuthenticated = authStore.isAuthenticated || !!Cookies.get('auth_token')

    console.log('🔐 [AgentGuard] isAgentAuthenticated:', isAgentAuthenticated)
    console.log('🔐 [AgentGuard] isShopAuthenticated:', isShopAuthenticated)

    // ── 🔒 Unauthenticated agent hitting a protected route ─────
    // Check this FIRST — if agent is not logged in, handle it regardless
    // of whether a shop account is active.
    if (to.meta.requiresAgentAuth && !isAgentAuthenticated) {
        // If shop is also not logged in → go to agent login
        // If shop IS logged in but agent is NOT → block, go to shop sell
        // (shop user has no business on agent-protected pages)
        if (isShopAuthenticated) {
            console.log('🚫 [AgentGuard] Shop user (no agent session) blocked from protected agent route → /grocery/sell')
            next(`/${country}/${lang}/grocery/sell`)
        } else {
            console.log('🔒 [AgentGuard] Agent auth required → agent login')
            next(`/${country}/${lang}/authorized-agents/login`)
        }
        return true
    }

    // ── 👤 Already-logged-in agent hitting a guest-only page ───
    // e.g. agent visits /authorized-agents/login after already being logged in
    // Works even if shop is also logged in — agent session takes priority here
    if (to.meta.agentGuest && isAgentAuthenticated) {
        console.log('👤 [AgentGuard] Agent already logged in → agent dashboard')
        next(`/${country}/${lang}/authorized-agents/dashboard`)
        return true
    }

    // ── ✅ All other agent routes — allow ──────────────────────
    // This covers:
    // - Agent logged in (with or without shop also logged in) hitting protected routes
    // - Anyone hitting agent guest pages (login/register) when no agent session exists
    console.log('✅ [AgentGuard] Agent route allowed')
    next()
    return true
}