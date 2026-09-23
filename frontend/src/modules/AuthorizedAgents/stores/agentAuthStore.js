// modules/AuthorizedAgents/stores/agentAuthStore.js

import { defineStore } from 'pinia'
import Cookies from 'js-cookie'
import { isAuthTokenExpired } from '@/utils/tokenUtils' // reuse your existing util

export const useAgentAuthStore = defineStore('agentAuth', {
    state: () => ({
        agent: null,
        token: Cookies.get('agent_auth_token') || null,
    }),

    getters: {
        // ✅ Cookie alone is enough — mirrors how shop authStore works
        // isAuthenticated: (state) => {
        //     const cookieToken = Cookies.get('agent_auth_token')
        //     return !!cookieToken
        // },

        isAuthenticated: () => !!Cookies.get('agent_auth_token'),
    },

    actions: {
        setAuth(agent, token) {
            this.agent = agent
            this.token = token
            Cookies.set('agent_auth_token', token, { expires: 7, sameSite: 'Lax' })
        },

        clearAuth() {
            this.agent = null
            this.token = null
            Cookies.remove('agent_auth_token')
            localStorage.removeItem('agent_auth')
        },
    },

    persist: {
        key: 'agent_auth',
        storage: localStorage,
        paths: ['agent'],
    }
})