import { defineStore } from 'pinia'
import api from '@/config/api'
import { useUserPreferencesStore } from './userPreferences'
import { useConfigStore } from '@/stores/config'


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
                    sale_price: toNumber(itemData.rate),
                    barcode: toStringOrEmpty(itemData.barcode)
                }


                // Add tax as two separate fields: tax1 (clean name) and rate1 (numeric value)
                if (itemData.taxRates && itemData.taxRates.length > 0) {
                    const taxRate = itemData.taxRates[0] // Get first (and only) tax

                    // Get selected tax value from form
                    const selectedTaxValue = taxRate.tax
                    const taxRateValue = toNumber(taxRate.rate)

                    console.log('Tax from form:', { selectedTaxValue, taxRateValue })

                    // Find tax in configStore to get the label
                    const configStore = useConfigStore()
                    const selectedTaxObj = configStore.taxesArray.find(tax => tax.value === selectedTaxValue)

                    console.log('Tax object from configStore:', selectedTaxObj)
                    console.log('All taxes in configStore:', configStore.taxesArray)

                    if (selectedTaxValue && taxRateValue >= 0) {
                        // Get the label from tax object
                        let taxName = selectedTaxObj ? toStringOrEmpty(selectedTaxObj.label) : toStringOrEmpty(selectedTaxValue)

                        console.log('Tax name before cleaning:', taxName)

                        // Remove everything in parentheses and any trailing numbers/percentages
                        // Examples: "VAT (19%)" -> "VAT", "GST (7%)" -> "GST", "VAT 7%" -> "VAT"
                        taxName = taxName
                            .replace(/\s*\([^)]*\)\s*/g, '')  // Remove anything in parentheses
                            .replace(/\s*\d+\.?\d*\s*%?\s*$/g, '')  // Remove trailing numbers and %
                            .trim()

                        console.log('Tax name after cleaning:', taxName)

                        // tax1 = Clean tax name (e.g., "VAT", "GST")
                        // rate1 = Numeric rate value (e.g., 7, 19)
                        payload.tax1 = taxName
                        payload.rate1 = taxRateValue

                        console.log('Tax being sent to backend:', {
                            tax1: payload.tax1,
                            rate1: payload.rate1
                        })
                    } else {
                        // Set defaults if invalid
                        payload.tax1 = ''
                        payload.rate1 = 0
                    }
                } else {
                    // No tax rates provided, set defaults
                    payload.tax1 = ''
                    payload.rate1 = 0
                }


                console.log('=== FINAL PAYLOAD ===')
                console.log(JSON.stringify(payload, null, 2))


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


            console.log('Validating itemData:', itemData, itemData.taxRates)


            // Validate single tax rate if provided
            if (itemData.taxRates && itemData.taxRates.length > 0) {
                const taxRate = itemData.taxRates[0] // Only validate first tax

                console.log('Validating tax:', taxRate.tax, taxRate.rate)

                // Check if tax name exists (handle both string and non-string values)
                const hasTaxName = taxRate.tax !== null &&
                    taxRate.tax !== undefined &&
                    taxRate.tax !== '' &&
                    String(taxRate.tax).trim() !== ''

                // Check if tax rate is valid (must be >= 0)
                const hasTaxRate = taxRate.rate !== null &&
                    taxRate.rate !== '' &&
                    taxRate.rate !== undefined &&
                    !isNaN(taxRate.rate) &&
                    Number(taxRate.rate) >= 0


                console.log('Tax validation:', { hasTaxName, hasTaxRate })


                // If either field is filled, both must be filled
                if (hasTaxName || hasTaxRate) {
                    if (!hasTaxName) {
                        errors.push('Tax type is required')
                    }
                    if (!hasTaxRate) {
                        errors.push('Tax rate is required and must be a valid number')
                    }
                }
            }


            if (itemData.barcode && !/^[A-Za-z0-9]{1,50}$/.test(itemData.barcode)) {
                errors.push(this.$t('common.Barcode must be alphanumeric and max 50 characters'))
            }


            return errors
        },


        clearErrors() {
            this.errors = []
        },


        clearSuccessMessage() {
            this.successMessage = null
        },


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

        async getProductByBarcode(barcode) {
            try {
                const response = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}item/scan/${barcode}`);

                // Success — API returned 2xx
                return {
                    success: true,
                    data: response.data.data ?? response.data,
                    message: null,
                };
            } catch (error) {
                const message =
                    error.response?.data?.message ??
                    error.response?.data?.error ??
                    "No product found for this barcode.";

                return {
                    success: false,
                    data: null,
                    message,
                };
            }
        },

        async checkBarcodeExists(barcode) {
            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}check-barcode/${barcode}`
                );

                return {
                    success: true,
                    exists: data.data === true,  // ✅ true = exists, false = available
                    message: data.message ?? null,
                    data: data.data ?? null,
                };
            } catch (error) {
                const message =
                    error.response?.data?.message ??
                    error.response?.data?.error ??
                    "Failed to check barcode.";

                return {
                    success: false,
                    exists: false,
                    message,
                    data: null,
                };
            }
        },



    }
})
