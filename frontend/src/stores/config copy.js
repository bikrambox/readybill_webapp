import { defineStore } from 'pinia'
import api from '@/config/api'

export const useConfigStore = defineStore('config', {
    state: () => ({
        units: {},
        taxes: [],
        allConfigData: null, // Store complete response
        locale: 'india', // Default locale: 'india' or 'german'
        loading: false,
        errors: [],
        initialized: false
    }),

    getters: {
        isLoading: (state) => state.loading,
        getUnits: (state) => state.units,
        getTaxes: (state) => state.taxes,
        isInitialized: (state) => state.initialized,
        getCurrentLocale: (state) => state.locale,

        // Convert units object to array for dropdown with full and short unit info
        unitsArray: (state) => {

            console.log('unitsArray', state.units);

            return Object.entries(state.units).map(([fullUnit, shortUnit]) => ({
                label: `${fullUnit} (${shortUnit})`,
                fullUnit: fullUnit,
                shortUnit: shortUnit,
                value: shortUnit // Use short unit as value for easy identification
            }))
        },

        // Convert taxes array to dropdown format
        taxesArray: (state) => {
            // Handle both formats: string array (India) and object array (German)
            return state.taxes.map(tax => {
                if (typeof tax === 'string') {
                    return {
                        label: tax,
                        value: tax
                    }
                } else {
                    // German format: { name: "VAT", value: 7 }
                    return {
                        label: `${tax.name} (${tax.value}%)`,
                        value: tax.value,
                        name: tax.name
                    }
                }
            })
        }
    },

    actions: {
        async fetchConfigData(forceRefresh = false, country = 'india') {
            // Prevent multiple fetches unless forced
            if (this.initialized && !forceRefresh) {
                return { success: true }
            }

            this.loading = true
            this.errors = []

            try {
                const { data } = await api.get('/config-data')

                if (data.status === 1) {
                    // Store complete response
                    this.allConfigData = data.data

                    this.locale = country;
                    // Set data based on current locale
                    this.setLocaleData(this.locale)

                    this.initialized = true
                    return { success: true }
                } else {
                    this.errors = [data.message || 'Failed to fetch configuration data']
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('Error fetching config data:', error)

                if (error.response?.data?.data?.errors) {
                    this.errors = Object.values(error.response.data.data.errors).flat()
                } else if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else {
                    this.errors = ['Failed to fetch configuration data']
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        // Set units and taxes based on locale
        setLocaleData(locale) {
            if (!this.allConfigData) return

            this.locale = locale

            if (locale === 'india') {
                this.units = this.allConfigData.india_units || {}
                this.taxes = this.allConfigData.india_tax || []
            } else if (locale === 'german' || locale === 'germany') {
                this.units = this.allConfigData.german_units || {}
                this.taxes = this.allConfigData.german_tax || []
            }
        },

        // Switch locale dynamically
        switchLocale(locale) {

            console.log('unitsArray', locale);

            if (locale !== 'india' && locale !== 'german') {
                console.warn('Invalid locale. Use "india" or "german"')
                return
            }

            this.setLocaleData(locale)
        },

        clearErrors() {
            this.errors = []
        },

        // Reset store
        resetStore() {
            this.units = {}
            this.taxes = []
            this.allConfigData = null
            this.loading = false
            this.errors = []
            this.initialized = false
            this.locale = 'india'
        }
    }
})
