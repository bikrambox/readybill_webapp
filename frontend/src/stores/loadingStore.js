import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLoadingStore = defineStore('loading', () => {
    const isLoading = ref(true) // Start as true for initial load
    const loadingMessage = ref('Loading...')
    const forceHidden = ref(false) // NEW: Force hide flag

    const showLoading = (message = 'Loading...') => {
        if (forceHidden.value) return // Don't show if force hidden
        isLoading.value = true
        loadingMessage.value = message
    }

    const hideLoading = () => {
        isLoading.value = false
    }

    const forceHideLoading = () => {
        console.log('🛑 FORCE HIDING LOADER')
        isLoading.value = false
        forceHidden.value = true
    }

    return {
        isLoading,
        loadingMessage,
        forceHidden,
        showLoading,
        hideLoading,
        forceHideLoading
    }
})
