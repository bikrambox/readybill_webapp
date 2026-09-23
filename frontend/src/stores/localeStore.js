import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/config/api'

export const useLocaleStore = defineStore('locale', () => {
    const country = ref('in')
    const language = ref('en')
    const translations = ref({})
    const isLoading = ref(false)
    const isInitialized = ref(false)
    const detectedData = ref(null)
    const availableLanguages = ref([])
    const availableCountries = ref(['in', 'us', 'de'])
    const hasError = ref(false)
    const errorInfo = ref(null)

    const currentLocale = computed(() => `${country.value}/${language.value}`)

    /**
     * Detect country and language from API
     */
    const detectCountryLanguage = async () => {
        try {
            const response = await api.get('/country-code')
            detectedData.value = response.data

            if (response.data.all_languages && Array.isArray(response.data.all_languages)) {
                availableLanguages.value = response.data.all_languages
            }

            hasError.value = false
            errorInfo.value = null

            return {
                countryCode: response.data.countryCode?.toLowerCase() || 'in',
                language: response.data.national_language || 'en',
                dialCode: response.data.dialCode || '+91',
                allLanguages: response.data.all_languages || [],
                country: response.data.country || 'India',
                region: response.data.region || '',
                city: response.data.city || '',
                timezone: response.data.timezone || ''
            }
        } catch (error) {
            console.error('Failed to detect country/language:', error)

            // Set error state but still return fallback
            hasError.value = true
            errorInfo.value = {
                type: 'DETECTION_ERROR',
                message: 'Failed to detect your location',
                details: error.message,
                timestamp: new Date().toISOString()
            }

            return {
                countryCode: 'in',
                language: 'en',
                dialCode: '+91',
                allLanguages: [
                    { language: 'English', short_code: 'en', flag: '🇬🇧' }
                ],
                country: 'India',
                region: '',
                city: '',
                timezone: 'Asia/Kolkata'
            }
        }
    }

    /**
     * Validate country and language combination
     */
    const validateCountryLanguage = async (countryCode, languageCode) => {

        const formData = new FormData()
        formData.append('country_code', countryCode)
        formData.append('language_code', languageCode)

        try {
            const response = await api.post('/check-country-language-code', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })

            const data = response.data
            const isCountryValid = data.country?.is_valid === 1
            const isLanguageValid = data.language?.is_valid === 1
            const isValid = isCountryValid && isLanguageValid

            hasError.value = false
            errorInfo.value = null

            return {
                isValid,
                country: {
                    is_valid: data.country?.is_valid || 0,
                    country_code: data.country?.country_code || countryCode,
                    dial_code: data.country?.dial_code || ''
                },
                language: {
                    is_valid: data.language?.is_valid || 0,
                    language_code: data.language?.language_code || languageCode,
                    language_name: data.language?.language_name || null
                }
            }
        } catch (error) {
            console.error('Validation failed:', error)

            hasError.value = true
            errorInfo.value = {
                type: 'VALIDATION_ERROR',
                message: 'Failed to validate country/language',
                details: error.message,
                timestamp: new Date().toISOString()
            }

            return {
                isValid: false,
                country: {
                    is_valid: 0,
                    country_code: countryCode,
                    dial_code: ''
                },
                language: {
                    is_valid: 0,
                    language_code: languageCode,
                    language_name: null
                }
            }
        }
    }

    /**
     * Load translations from API
     */
    const loadTranslations = async (lang) => {
        if (translations.value[lang]) {
            return translations.value[lang]
        }

        isLoading.value = true

        try {
            const response = await api.get(`/translations/${lang}`)

            if (!response.data || typeof response.data !== 'object' || Object.keys(response.data).length === 0) {
                throw new Error('Invalid translations response')
            }

            translations.value[lang] = response.data
            hasError.value = false
            errorInfo.value = null

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
                        welcome: 'Welcome',
                        login: 'Login',
                        register: 'Register',
                        logout: 'Logout'
                    }
                }
                translations.value[lang] = fallback
                return fallback
            }

            hasError.value = true
            errorInfo.value = {
                type: 'TRANSLATION_ERROR',
                message: `Failed to load translations for ${lang}`,
                details: error.message,
                timestamp: new Date().toISOString()
            }

            return null

        } finally {
            isLoading.value = false
        }
    }

    /**
     * Set locale with validation
     */
    // const setLocale = async (newCountry, newLanguage) => {
    //     const countryLower = newCountry.toLowerCase()
    //     const langLower = newLanguage.toLowerCase()

    //     const validation = await validateCountryLanguage(countryLower, langLower)

    //     if (!validation.isValid) {
    //         console.warn(
    //             `Invalid country/language combination: ${countryLower}/${langLower}`,
    //             'Validation result:', validation
    //         )

    //         return {
    //             success: false,
    //             message: 'Invalid country/language combination',
    //             validation
    //         }
    //     }

    //     country.value = countryLower
    //     language.value = langLower

    //     if (!translations.value[langLower]) {
    //         await loadTranslations(langLower)
    //     }

    //     localStorage.setItem('country', countryLower)
    //     localStorage.setItem('language', langLower)

    //     return {
    //         success: true,
    //         message: 'Locale updated successfully',
    //         validation
    //     }
    // }

    /**
 * Set locale with validation
 */
    const setLocale = async (newCountry, newLanguage) => {
        const countryLower = newCountry.toLowerCase()
        const langLower = newLanguage.toLowerCase()

        const validation = await validateCountryLanguage(countryLower, langLower)

        if (!validation.isValid) {
            console.warn(
                `Invalid country/language combination: ${countryLower}/${langLower}`,
                'Validation result:', validation
            )

            // CHANGED: Still update the values even if validation fails
            // This allows the router to handle the navigation
            country.value = countryLower
            language.value = langLower

            localStorage.setItem('country', countryLower)
            localStorage.setItem('language', langLower)

            return {
                success: false,
                message: 'Invalid country/language combination',
                validation
            }
        }

        country.value = countryLower
        language.value = langLower

        if (!translations.value[langLower]) {
            await loadTranslations(langLower)
        }

        localStorage.setItem('country', countryLower)
        localStorage.setItem('language', langLower)

        return {
            success: true,
            message: 'Locale updated successfully',
            validation
        }
    }


    /**
     * Initialize locale - main entry point
     */
    const initializeLocale = async (urlCountry = null, urlLanguage = null) => {
        isLoading.value = true
        hasError.value = false
        errorInfo.value = null

        try {
            const detected = await detectCountryLanguage()

            let finalCountry = urlCountry?.toLowerCase() || detected.countryCode
            let finalLanguage = urlLanguage?.toLowerCase() || detected.language

            const validation = await validateCountryLanguage(finalCountry, finalLanguage)

            if (!validation.isValid) {
                console.warn('Invalid locale, using detected values')
                finalCountry = detected.countryCode
                finalLanguage = detected.language

                const detectedValidation = await validateCountryLanguage(finalCountry, finalLanguage)

                if (!detectedValidation.isValid) {
                    finalCountry = 'in'
                    finalLanguage = 'en'
                }
            }

            country.value = finalCountry
            language.value = finalLanguage

            await loadTranslations(finalLanguage)

            localStorage.setItem('country', finalCountry)
            localStorage.setItem('language', finalLanguage)

            isInitialized.value = true

            return {
                success: true,
                country: finalCountry,
                language: finalLanguage,
                detected
            }

        } catch (error) {
            console.error('Locale initialization failed:', error)

            hasError.value = true
            errorInfo.value = {
                type: 'INITIALIZATION_ERROR',
                message: 'Failed to initialize application',
                details: error.message,
                timestamp: new Date().toISOString()
            }

            // Still set fallback values
            country.value = 'in'
            language.value = 'en'
            await loadTranslations('en')

            isInitialized.value = true

            // Return error state
            return {
                success: false,
                country: 'in',
                language: 'en',
                error: error.message,
                errorInfo: errorInfo.value
            }

        } finally {
            isLoading.value = false
        }
    }

    const getAvailableLanguagesForCountry = computed(() => {
        if (detectedData.value?.all_languages) {
            return detectedData.value.all_languages
        }
        return availableLanguages.value
    })

    const clearError = () => {
        hasError.value = false
        errorInfo.value = null
    }

    return {
        country,
        language,
        translations,
        isLoading,
        isInitialized,
        detectedData,
        availableLanguages,
        availableCountries,
        hasError,
        errorInfo,
        currentLocale,
        getAvailableLanguagesForCountry,
        detectCountryLanguage,
        validateCountryLanguage,
        loadTranslations,
        setLocale,
        initializeLocale,
        clearError
    }
})
