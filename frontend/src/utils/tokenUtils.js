// src/utils/tokenUtils.js
import Cookies from 'js-cookie'


export const decodeToken = (token) => {
    try {
        const base64Url = token.split('.')[1]
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
        const jsonPayload = decodeURIComponent(
            atob(base64)
                .split('')
                .map(c => `%${('00' + c.charCodeAt(0).toString(16)).slice(-2)}`)
                .join('')
        )
        return JSON.parse(jsonPayload)
    } catch {
        return null
    }
}


export const isTokenExpired = (token) => {
    if (!token) {
        console.warn('🔐 [Token Check] No token found')
        return true
    }

    const payload = decodeToken(token)

    if (!payload?.exp) {
        console.warn('🔐 [Token Check] Token has no expiry (exp) field')
        return true
    }

    const currentTime = Math.floor(Date.now() / 1000)
    const bufferSeconds = 30
    const expiresAt = new Date(payload.exp * 1000)
    const secondsLeft = payload.exp - currentTime
    const minutesLeft = Math.floor(secondsLeft / 60)

    console.group('🔐 [Token Check]')
    console.log('⏰ Token expires at :', expiresAt.toLocaleString())
    console.log('🕐 Current time     :', new Date().toLocaleString())
    console.log('⏳ Time left        :', secondsLeft > 0
        ? `${minutesLeft}m ${secondsLeft % 60}s`
        : `Expired ${Math.abs(minutesLeft)}m ${Math.abs(secondsLeft % 60)}s ago`
    )
    console.log('🛡️ Buffer           :', `${bufferSeconds}s`)

    const expired = payload.exp < (currentTime + bufferSeconds)
    console.log(expired ? '❌ Status: EXPIRED' : '✅ Status: VALID')
    console.groupEnd()

    return expired
}


// ✅ Shop / Employee — unchanged
export const getAuthToken = () => Cookies.get('auth_token')
export const isAuthTokenExpired = () => isTokenExpired(getAuthToken())


// ✅ Authorized Agent — separate cookie, same logic
export const getAgentAuthToken = () => Cookies.get('agent_auth_token')
export const isAgentAuthTokenExpired = () => isTokenExpired(getAgentAuthToken())