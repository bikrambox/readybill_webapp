import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/config/api'

export const useLocaleStore = defineStore('locale', () => {
    const country = ref('in')
    const language = ref('en')
    const translations = ref({})
    const isLoading = ref(false)
    const isInitialized = ref(false)

    const currentLocale = computed(() => `${country.value}/${language.value}`)
    const availableLanguages = computed(() => ['en', 'hi'])
    const availableCountries = computed(() => ['in', 'us'])

    const loadTranslations = async (lang) => {
        // Return cached if exists
        if (translations.value[lang]) {
            return translations.value[lang]
        }

        isLoading.value = true

        try {
            const response = await api.get(`/translations/${lang}`)

            if (!response.data || typeof response.data !== 'object' || Object.keys(response.data).length === 0) {
                return null
            }

            translations.value[lang] = response.data
            return response.data

        } catch (error) {
            console.error(`Failed to load translations for ${lang}:`, error)

            // Fallback for English
            if (lang === 'en') {
                const fallback = {
                    common: {
                        home: 'Home',
                        about: 'About',
                        contact: 'Contact',
                        settings: 'Settings',
                        welcome: 'Welcome'
                    }
                }
                translations.value[lang] = fallback
                return fallback
            }

            return null

        } finally {
            isLoading.value = false
        }
    }

    const setLocale = async (newCountry, newLanguage) => {
        country.value = newCountry
        language.value = newLanguage

        if (!translations.value[newLanguage]) {
            await loadTranslations(newLanguage)
        }

        localStorage.setItem('country', newCountry)
        localStorage.setItem('language', newLanguage)
    }

    return {
        country,
        language,
        translations,
        isLoading,
        isInitialized,
        currentLocale,
        availableLanguages,
        availableCountries,
        loadTranslations,
        setLocale
    }
})
