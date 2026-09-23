import { defineStore } from 'pinia'
import api from '@/config/api'
import { useUserPreferencesStore } from './userPreferences'

export const useInventoryStore = defineStore('inventory', {
    state: () => ({
        items: [],
        loading: false,
        errors: [],
        successMessage: null
    }),

    getters: {
        isLoading: (state) => state.loading,
        hasErrors: (state) => state.errors.length > 0
    },

    actions: {
        async addItem(itemData) {
            this.loading = true
            this.errors = []
            this.successMessage = null

            try {
                // Frontend validation
                const validationErrors = this.validateItem(itemData)
                if (validationErrors.length > 0) {
                    this.errors = validationErrors
                    this.loading = false
                    return { success: false, errors: this.errors }
                }

                // Helper function to safely convert to number (never null, always 0 as fallback)
                const toNumber = (value) => {
                    if (value === '' || value === null || value === undefined) {
                        return 0
                    }
                    const num = parseFloat(value)
                    return isNaN(num) ? 0 : num
                }

                // Helper function to safely convert to string
                const toStringOrEmpty = (value) => {
                    if (value === null || value === undefined || value === '') {
                        return ''
                    }
                    return String(value).trim()
                }

                // Map frontend field names to backend field names with proper type conversion
                const payload = {
                    item_name: toStringOrEmpty(itemData.itemName),
                    quantity: toNumber(itemData.stockQuantity),
                    min_stock_alert: toNumber(itemData.minStockAlert),
                    full_unit: toStringOrEmpty(itemData.fullUnit),
                    short_unit: toStringOrEmpty(itemData.shortUnit),
                    hsn_code: toStringOrEmpty(itemData.hsnCode),
                    mrp: toNumber(itemData.mrp),
                    sale_price: toNumber(itemData.rate)
                }

                // Add tax rates - only include non-empty taxes
                if (itemData.taxRates && itemData.taxRates.length > 0) {
                    let validTaxes = []

                    // Filter and collect only valid tax entries
                    itemData.taxRates.forEach((taxRate) => {
                        const taxName = toStringOrEmpty(taxRate.tax)
                        const taxRateValue = toNumber(taxRate.rate)

                        // Only add if both tax name and rate are valid
                        if (taxName && taxRateValue > 0) {
                            validTaxes.push({
                                tax: taxName,
                                rate: taxRateValue
                            })
                        }
                    })

                    // Add valid taxes to payload with proper numbering
                    validTaxes.forEach((tax, index) => {
                        const taxNumber = index + 1
                        payload[`tax${taxNumber}`] = tax.tax
                        payload[`rate${taxNumber}`] = tax.rate
                    })

                    // If no valid taxes, add default tax1 and rate1
                    if (validTaxes.length === 0) {
                        payload.tax1 = ''
                        payload.rate1 = 0
                    }
                } else {
                    // No tax rates provided, set defaults
                    payload.tax1 = ''
                    payload.rate1 = 0
                }

                console.log('Payload being sent:', payload) // Debug log

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}add-item`,
                    payload
                )

                if (data.status === 1) {
                    this.successMessage = data.message || 'Item added successfully'
                    if (data.data) {
                        this.items.push(data.data)
                    }
                    return { success: true, data: data.data }
                } else {
                    // Handle status 0 with errors
                    if (data.data?.errors) {
                        // Check if errors is an object or string
                        if (typeof data.data.errors === 'string') {
                            this.errors = [data.data.errors]
                        } else {
                            this.errors = Object.values(data.data.errors).flat()
                        }
                    } else {
                        this.errors = [data.message || 'Failed to add item']
                    }
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('Error adding item:', error)

                // Handle different error response structures
                if (error.response?.data?.data?.errors) {
                    const errors = error.response.data.data.errors
                    if (typeof errors === 'string') {
                        this.errors = [errors]
                    } else {
                        this.errors = Object.values(errors).flat()
                    }
                } else if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = ['Failed to add item. Please try again.']
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        validateItem(itemData) {
            const errors = []

            // Get user preferences
            const preferencesStore = useUserPreferencesStore()
            const preferences = preferencesStore.preferences

            if (!itemData.itemName || itemData.itemName.trim() === '') {
                errors.push('Item name is required')
            }

            // Stock quantity - required if preference_quantity is enabled
            const isStockRequired = preferences.preference_quantity === 1 || preferences.preference_quantity === true
            if (isStockRequired) {
                if (!itemData.stockQuantity || isNaN(itemData.stockQuantity) || parseFloat(itemData.stockQuantity) <= 0) {
                    errors.push('Stock quantity is required and must be greater than 0')
                }
            } else {
                // Optional validation - if provided must be valid
                if (itemData.stockQuantity !== null && itemData.stockQuantity !== undefined && itemData.stockQuantity !== '') {
                    if (isNaN(itemData.stockQuantity) || parseFloat(itemData.stockQuantity) <= 0) {
                        errors.push('Stock quantity must be a valid number greater than 0')
                    }
                }
            }

            // Min stock alert validation (optional but if provided must be valid)
            if (itemData.minStockAlert !== null && itemData.minStockAlert !== undefined && itemData.minStockAlert !== '') {
                if (isNaN(itemData.minStockAlert) || parseFloat(itemData.minStockAlert) < 0) {
                    errors.push('Minimum stock alert must be a valid number')
                }
            }

            if (!itemData.fullUnit || itemData.fullUnit === '') {
                errors.push('Full unit is required')
            }

            if (!itemData.shortUnit || itemData.shortUnit === '') {
                errors.push('Short unit is required')
            }

            // MRP - required if preference_mrp is enabled
            const isMrpRequired = preferences.preference_mrp === 1 || preferences.preference_mrp === true
            if (isMrpRequired) {
                if (!itemData.mrp || isNaN(itemData.mrp) || parseFloat(itemData.mrp) <= 0) {
                    errors.push('MRP is required and must be greater than 0')
                }
            } else {
                // Optional validation - if provided must be valid
                if (itemData.mrp !== null && itemData.mrp !== undefined && itemData.mrp !== '') {
                    if (isNaN(itemData.mrp) || parseFloat(itemData.mrp) <= 0) {
                        errors.push('MRP must be a valid number greater than 0')
                    }
                }
            }

            if (!itemData.rate || isNaN(itemData.rate) || parseFloat(itemData.rate) <= 0) {
                errors.push('Rate must be a valid number greater than 0')
            }

            // Validate tax rates if provided
            if (itemData.taxRates && itemData.taxRates.length > 0) {
                itemData.taxRates.forEach((taxRate, index) => {
                    // Only validate if tax has been started (either field filled)
                    const hasTaxName = taxRate.tax && taxRate.tax.trim() !== ''
                    const hasTaxRate = taxRate.rate && parseFloat(taxRate.rate) > 0

                    // If either field is filled, both must be filled
                    if (hasTaxName || hasTaxRate) {
                        if (!hasTaxName) {
                            errors.push(`Tax type for tax ${index + 1} is required`)
                        }
                        if (!hasTaxRate) {
                            errors.push(`Tax value for tax ${index + 1} must be greater than 0`)
                        }
                    }
                })
            }

            return errors
        },

        clearErrors() {
            this.errors = []
        },

        clearSuccessMessage() {
            this.successMessage = null
        },

        // async exportData() {
        //     this.loading = true
        //     this.errors = []

        //     try {
        //         const { data } = await api.get(
        //             `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}export`,
        //             { responseType: 'blob' }
        //         )

        //         if (data) {
        //             // Create blob URL and trigger download
        //             const url = window.URL.createObjectURL(new Blob([data]))
        //             const link = document.createElement('a')
        //             link.href = url
        //             link.setAttribute('download', 'exported_data.xlsx')
        //             document.body.appendChild(link)
        //             link.click()

        //             // Cleanup
        //             link.remove()
        //             window.URL.revokeObjectURL(url)

        //             this.successMessage = 'Export successful'
        //             return { success: true }
        //         }
        //     } catch (error) {
        //         console.error('Error exporting data:', error)

        //         if (error.response?.data?.message) {
        //             this.errors = [error.response.data.message]
        //         } else {
        //             this.errors = ['Failed to export data. Please try again.']
        //         }

        //         return { success: false, errors: this.errors }
        //     } finally {
        //         this.loading = false
        //     }
        // },

        async exportData() {
            this.loading = true
            this.errors = []

            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}export`
                )

                if (data.status == 1 || data.status === '1') {
                    this.successMessage = data.message || 'Export successful'

                    // Construct the file URL and trigger download
                    const fileUrl = `${import.meta.env.VITE_MEDIA_URL}${data.file}`
                    window.location.href = fileUrl

                    return { success: true, message: data.message }
                } else {
                    this.errors = [data.message || 'Export failed']
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('Error exporting data:', error)

                if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else {
                    this.errors = ['Failed to export data. Please try again.']
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },



    }

})