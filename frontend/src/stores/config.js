import { defineStore } from 'pinia'
import api from '@/config/api'

export const useConfigStore = defineStore('config', {
    state: () => ({
        units: {},
        taxes: {
            gst: {},
            cess: {}
        },
        allConfigData: null,
        locale: 'india',
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

        // Convert units object to array for dropdown
        unitsArray: (state) => {
            return Object.entries(state.units).map(([fullUnit, shortUnit]) => ({
                label: `${fullUnit} (${shortUnit})`,
                fullUnit: fullUnit,
                shortUnit: shortUnit,
                value: shortUnit
            }))
        },

        // GST options only (India) — labels stay as-is: "GST @ 18%"
        gstArray: (state) => {
            if (
                typeof state.taxes?.gst === 'object' &&
                !Array.isArray(state.taxes.gst) &&
                state.taxes.gst !== null
            ) {
                return Object.entries(state.taxes.gst).map(([label, value]) => ({
                    label,
                    value,
                    name: label,
                    type: 'gst'
                }))
            }
            return []
        },

        // CESS options only (India) — relabel "GST @ X%" → "CESS @ X%"
        cessArray: (state) => {
            if (
                typeof state.taxes?.cess === 'object' &&
                !Array.isArray(state.taxes.cess) &&
                state.taxes.cess !== null
            ) {
                return Object.entries(state.taxes.cess).map(([label, value]) => ({
                    label: label.replace(/^GST/, 'CESS'), // "CESS @ 18%"
                    value,
                    name: label.replace(/^GST/, 'CESS'),
                    type: 'cess'
                }))
            }
            return []
        },

        // taxesArray:
        // - India:  returns { gst: [...], cess: [...] }
        // - German: returns flat array [{ label, value, name }]
        taxesArray: (state) => {
            // German format: flat array [{ name, value }]
            if (Array.isArray(state.taxes)) {
                return state.taxes.map(tax => ({
                    label: `${tax.name} (${tax.value}%)`,
                    value: tax.value,
                    name: tax.name
                }))
            }

            // India format: { gst: { "GST @ 5%": "5" }, cess: { "GST @ 5%": "5" } }
            if (typeof state.taxes === 'object' && state.taxes !== null) {
                const gst = Object.entries(state.taxes.gst || {}).map(([label, value]) => ({
                    label,                        // "GST @ 18%"
                    value,                        // "18"
                    name: label,
                    type: 'gst'
                }))

                const cess = Object.entries(state.taxes.cess || {}).map(([label, value]) => ({
                    label: label.replace(/^GST/, 'CESS'), // "CESS @ 18%"
                    value,                                 // "18"
                    name: label.replace(/^GST/, 'CESS'),
                    type: 'cess'
                }))

                return { gst, cess }
            }

            return []
        }
    },

    actions: {
        async fetchConfigData(forceRefresh = false, country = 'india') {
            if (this.initialized && !forceRefresh) {
                return { success: true }
            }

            this.loading = true
            this.errors = []

            try {
                const { data } = await api.get('/config-data')

                if (data.status === 1) {
                    this.allConfigData = data.data
                    this.locale = country
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
                this.taxes = {
                    gst: this.allConfigData.india_tax?.gst || {},
                    cess: this.allConfigData.india_tax?.cess || {}
                }
            } else if (locale === 'german' || locale === 'germany') {
                this.units = this.allConfigData.german_units || {}
                this.taxes = this.allConfigData.german_tax || []
            }
        },

        // Switch locale dynamically
        switchLocale(locale) {
            if (locale !== 'india' && locale !== 'german') {
                console.warn('Invalid locale. Use "india" or "german"')
                return
            }
            this.setLocaleData(locale)
        },

        clearErrors() {
            this.errors = []
        },

        resetStore() {
            this.units = {}
            this.taxes = { gst: {}, cess: {} }
            this.allConfigData = null
            this.loading = false
            this.errors = []
            this.initialized = false
            this.locale = 'india'
        }
    }
})