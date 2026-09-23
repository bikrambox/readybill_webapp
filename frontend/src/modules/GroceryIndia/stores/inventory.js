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
        // async addItem(itemData) {
        //     this.loading = true
        //     this.errors = []
        //     this.successMessage = null

        //     try {
        //         // Frontend validation
        //         const validationErrors = this.validateItem(itemData)
        //         if (validationErrors.length > 0) {
        //             this.errors = validationErrors
        //             this.loading = false
        //             return { success: false, errors: this.errors }
        //         }

        //         // Helper function to safely convert to number (never null, always 0 as fallback)
        //         const toNumber = (value) => {
        //             if (value === '' || value === null || value === undefined) {
        //                 return 0
        //             }
        //             const num = parseFloat(value)
        //             return isNaN(num) ? 0 : num
        //         }

        //         // Helper function to safely convert to string
        //         const toStringOrEmpty = (value) => {
        //             if (value === null || value === undefined || value === '') {
        //                 return ''
        //             }
        //             return String(value).trim()
        //         }

        //         // Map frontend field names to backend field names with proper type conversion
        //         const payload = {
        //             item_name: toStringOrEmpty(itemData.itemName),
        //             quantity: toNumber(itemData.stockQuantity),
        //             min_stock_alert: toNumber(itemData.minStockAlert),
        //             full_unit: toStringOrEmpty(itemData.fullUnit),
        //             short_unit: toStringOrEmpty(itemData.shortUnit),
        //             hsn: toStringOrEmpty(itemData.hsnCode),
        //             mrp: toNumber(itemData.mrp),
        //             sale_price: toNumber(itemData.rate),
        //             barcode: toStringOrEmpty(itemData.barcode)
        //         }

        //         // Add tax rates - only include non-empty taxes
        //         if (itemData.taxRates && itemData.taxRates.length > 0) {
        //             let validTaxes = []

        //             // Filter and collect only valid tax entries
        //             itemData.taxRates.forEach((taxRate) => {
        //                 const taxName = toStringOrEmpty(taxRate.tax)
        //                 const taxRateValue = toNumber(taxRate.rate)

        //                 // Only add if both tax name and rate are valid
        //                 if (taxName && taxRateValue >= 0) {
        //                     validTaxes.push({
        //                         tax: taxName,
        //                         rate: taxRateValue
        //                     })
        //                 }
        //             })

        //             // Add valid taxes to payload with proper numbering
        //             validTaxes.forEach((tax, index) => {
        //                 const taxNumber = index + 1
        //                 payload[`tax${taxNumber}`] = tax.tax
        //                 payload[`rate${taxNumber}`] = tax.rate
        //             })

        //             // If no valid taxes, add default tax1 and rate1
        //             if (validTaxes.length === 0) {
        //                 payload.tax1 = ''
        //                 payload.rate1 = 0
        //             }
        //         } else {
        //             // No tax rates provided, set defaults
        //             payload.tax1 = ''
        //             payload.rate1 = 0
        //         }

        //         console.log('Payload being sent:', payload) // Debug log

        //         const { data } = await api.post(
        //             `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}add-item`,
        //             payload
        //         )

        //         if (data.status === 1) {
        //             this.successMessage = data.message || this.$t('common.Item added successfully')
        //             if (data.data) {
        //                 this.items.push(data.data)
        //             }
        //             return { success: true, data: data.data }
        //         } else {
        //             // Handle status 0 with errors
        //             if (data.data?.errors) {
        //                 // Check if errors is an object or string
        //                 if (typeof data.data.errors === 'string') {
        //                     this.errors = [data.data.errors]
        //                 } else {
        //                     this.errors = Object.values(data.data.errors).flat()
        //                 }
        //             } else {
        //                 this.errors = [data.message || this.$t('common.Failed to add item')]
        //             }
        //             return { success: false, errors: this.errors }
        //         }
        //     } catch (error) {
        //         console.error('Error adding item:', error)

        //         // Handle different error response structures
        //         if (error.response?.data?.data?.errors) {
        //             const errors = error.response.data.data.errors
        //             if (typeof errors === 'string') {
        //                 this.errors = [errors]
        //             } else {
        //                 this.errors = Object.values(errors).flat()
        //             }
        //         } else if (error.response?.data?.message) {
        //             this.errors = [error.response.data.message]
        //         } else if (error.message) {
        //             this.errors = [error.message]
        //         } else {
        //             this.errors = [this.$t('common.Failed to add item. Please try again.')]
        //         }

        //         return { success: false, errors: this.errors }
        //     } finally {
        //         this.loading = false
        //     }
        // },


        async addItem(itemData) {
            this.loading = true
            this.errors = []
            this.successMessage = null

            try {
                const validationErrors = this.validateItem(itemData)
                if (validationErrors.length > 0) {
                    this.errors = validationErrors
                    return { success: false, errors: this.errors }
                }

                const toNumber = (value) => {
                    if (value === '' || value === null || value === undefined) return 0
                    const num = parseFloat(value)
                    return isNaN(num) ? 0 : num
                }

                const toStringOrEmpty = (value) => {
                    if (value === null || value === undefined || value === '') return ''
                    return String(value).trim()
                }

                const payload = {
                    sku: toStringOrEmpty(itemData.sku),
                    category_id: toNumber(itemData.category_id),
                    item_name: toStringOrEmpty(itemData.itemName),
                    quantity: toNumber(itemData.stockQuantity),
                    min_stock_alert: toNumber(itemData.minStockAlert),
                    full_unit: toStringOrEmpty(itemData.fullUnit),
                    short_unit: toStringOrEmpty(itemData.shortUnit),
                    hsn: toStringOrEmpty(itemData.hsnCode),
                    purchase_price: toNumber(itemData.purchase_price),
                    mrp: toNumber(itemData.mrp),
                    sale_price: toNumber(itemData.rate),
                    barcode: toStringOrEmpty(itemData.barcode),
                    tax1: toStringOrEmpty(itemData.tax1) || 'GST',
                    rate1: toNumber(itemData.rate1),
                    tax2: toStringOrEmpty(itemData.tax2) || 'CESS',
                    rate2: toNumber(itemData.rate2),
                }

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}add-item`,
                    payload
                )

                if (data.status === 1 || data.status === '1') {
                    this.successMessage = data.message || this.$t('common.Item added successfully')
                    if (data.data) {
                        this.items.push(data.data)
                    }
                    return { success: true, data: data.data }
                }

                if (data.data?.errors) {
                    if (typeof data.data.errors === 'string') {
                        this.errors = [data.data.errors]
                    } else {
                        this.errors = Object.values(data.data.errors).flat()
                    }
                } else {
                    this.errors = [data.message || this.$t('common.Failed to add item')]
                }

                return { success: false, errors: this.errors }
            } catch (error) {
                console.error('Error adding item:', error)

                if (error.response?.data?.data?.errors) {
                    const errors = error.response.data.data.errors
                    this.errors = typeof errors === 'string'
                        ? [errors]
                        : Object.values(errors).flat()
                } else if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = [this.$t('common.Failed to add item. Please try again.')]
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        validateItem(itemData) {
            const errors = []

            const preferencesStore = useUserPreferencesStore()
            const preferences = preferencesStore.preferences || {}

            const isEmpty = (value) =>
                value === null || value === undefined || String(value).trim() === ''

            const isPositiveNumber = (value) =>
                !isEmpty(value) && !isNaN(value) && Number(value) > 0

            const isNonNegativeNumber = (value) =>
                !isEmpty(value) && !isNaN(value) && Number(value) >= 0

            const isSKURequired =
                preferences.preference_sku === 1 || preferences.preference_sku === true

            if (isSKURequired && isEmpty(itemData.sku)) {
                errors.push(this.$t('common.SKU is required'))
            }

            const isCategoryRequired =
                preferences.preference_category === 1 || preferences.preference_category === true

            if (isCategoryRequired && (itemData.category_id === null || itemData.category_id === undefined || itemData.category_id === '')) {
                errors.push(this.$t('common.Category is required'))
            }

            if (isEmpty(itemData.itemName)) {
                errors.push(this.$t('common.Item name is required'))
            }

            const isStockRequired =
                preferences.preference_quantity === 1 || preferences.preference_quantity === true

            if (isStockRequired) {
                if (!isPositiveNumber(itemData.stockQuantity)) {
                    errors.push(this.$t('common.Stock quantity is required and must be greater than 0'))
                }
            } else if (!isEmpty(itemData.stockQuantity) && !isPositiveNumber(itemData.stockQuantity)) {
                errors.push(this.$t('common.Stock quantity must be a valid number greater than 0'))
            }

            if (!isEmpty(itemData.minStockAlert) && !isNonNegativeNumber(itemData.minStockAlert)) {
                errors.push(this.$t('common.Minimum stock alert must be a valid number'))
            }

            if (isEmpty(itemData.fullUnit)) {
                errors.push(this.$t('common.Full unit is required'))
            }

            if (isEmpty(itemData.shortUnit)) {
                errors.push(this.$t('common.Short unit is required'))
            }

            const isHsnRequired =
                preferences.preference_hsn === 1 || preferences.preference_hsn === true

            if (isHsnRequired && isEmpty(itemData.hsnCode)) {
                errors.push(this.$t('common.HSN/ SAC Code is required'))
            }

            const isMrpRequired =
                preferences.preference_mrp === 1 || preferences.preference_mrp === true

            if (isMrpRequired) {
                if (!isPositiveNumber(itemData.mrp)) {
                    errors.push(this.$t('common.MRP is required and must be greater than 0'))
                }
            } else if (!isEmpty(itemData.mrp) && !isPositiveNumber(itemData.mrp)) {
                errors.push(this.$t('common.MRP must be a valid number greater than 0'))
            }

            const isPurchasePriceRequired =
                preferences.preference_purchase_price === 1 || preferences.preference_purchase_price === true

            if (isPurchasePriceRequired) {
                if (!isPositiveNumber(itemData.purchase_price)) {
                    errors.push(this.$t('common.Purchase Price is required and must be greater than 0'))
                }
            } else if (!isEmpty(itemData.purchase_price) && !isPositiveNumber(itemData.purchase_price)) {
                errors.push(this.$t('common.Purchase Price must be a valid number greater than 0'))
            }

            if (!isPositiveNumber(itemData.rate)) {
                errors.push(this.$t('common.Rate must be a valid number greater than 0'))
            }

            if (!isEmpty(itemData.rate1) && !isNonNegativeNumber(itemData.rate1)) {
                errors.push(this.$t('common.GST must be a valid number'))
            }

            if (!isEmpty(itemData.rate2) && !isNonNegativeNumber(itemData.rate2)) {
                errors.push(this.$t('common.CESS must be a valid number'))
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

        // async exportData() {
        //     this.loading = true
        //     this.errors = []

        //     try {
        //         const { data } = await api.get(
        //             `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}export`
        //         )

        //         if (data.status == 1 || data.status === '1') {
        //             this.successMessage = data.message || this.$t('common.Export successful')

        //             // Construct the file URL and trigger download
        //             const fileUrl = `${import.meta.env.VITE_MEDIA_URL}${data.file}`
        //             window.location.href = fileUrl

        //             return { success: true, message: data.message }
        //         } else {
        //             this.errors = [data.message || this.$t('common.Export failed')]
        //             return { success: false, errors: this.errors }
        //         }
        //     } catch (error) {
        //         console.error('Error exporting data:', error)

        //         if (error.response?.data?.message) {
        //             this.errors = [error.response.data.message]
        //         } else {
        //             this.errors = [this.$t('common.Failed to export data. Please try again.')]
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
                const response = await api.get(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}export`,
                    {
                        responseType: 'blob',
                        headers: {
                            Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        }
                    }
                )

                const blob = new Blob([response.data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                })

                let fileName = 'items_export.xlsx'
                const disposition = response.headers['content-disposition']

                if (disposition) {
                    const match = disposition.match(/filename="?([^"]+)"?/)
                    if (match && match[1]) {
                        fileName = match[1]
                    }
                }

                const url = window.URL.createObjectURL(blob)
                const a = document.createElement('a')
                a.href = url
                a.download = fileName
                document.body.appendChild(a)
                a.click()
                a.remove()
                window.URL.revokeObjectURL(url)

                this.successMessage = this.$t('common.Export successful')
                return { success: true }
            } catch (error) {
                console.error('Error exporting data:', error)

                this.errors = [this.$t('common.Failed to export data. Please try again.')]
                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },


        // async getProductByBarcode(barcode) {
        //     try {
        //         const response = await api.get(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}item/scan/${barcode}`);

        //         // Success — API returned 2xx
        //         return {
        //             success: true,
        //             data: response.data.data ?? response.data,
        //             message: null,
        //         };
        //     } catch (error) {
        //         const message =
        //             error.response?.data?.message ??
        //             error.response?.data?.error ??
        //             "No product found for this barcode.";

        //         return {
        //             success: false,
        //             data: null,
        //             message,
        //         };
        //     }
        // },

        async getProductByBarcode(barcode) {
            try {
                const formData = new FormData();
                formData.append("barcode", barcode);

                const response = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}item/scan`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            Accept: "application/json",
                        },
                    }
                );

                return {
                    success: true,
                    data: response.data.data ?? response.data,
                    message: null,
                    errors: {},
                };
            } catch (error) {
                const responseData = error.response?.data ?? {};
                const backendErrors = responseData.errors ?? {};
                const barcodeErrors = Array.isArray(backendErrors.barcode)
                    ? backendErrors.barcode
                    : [];

                const message =
                    barcodeErrors[0] ??
                    responseData.message ??
                    responseData.error ??
                    "No product found for this barcode.";

                return {
                    success: false,
                    data: null,
                    message,
                    errors: backendErrors,
                    status: error.response?.status ?? null,
                };
            }
        },

        async checkBarcodeExists(barcode, itemId = null) {
            try {
                // Build URL — append item_id as query param if provided
                const url = itemId
                    ? `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}check-barcode/${barcode}?item_id=${itemId}`
                    : `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}check-barcode/${barcode}`;

                const { data } = await api.get(url);

                return {
                    success: true,
                    exists: data.data === true,
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


        async validateItemOrSku(payload) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}validate-item-or-sku`,
                    payload
                )

                return {
                    success: true,
                    data: data.data ?? null,
                    message: data.message ?? null,
                    status: data.status ?? 200,
                }
            } catch (error) {
                return {
                    success: false,
                    data: error.response?.data?.data ?? null,
                    message:
                        error.response?.data?.message ??
                        error.response?.data?.error ??
                        'Validation failed.',
                    errors: error.response?.data?.errors ?? null,
                    status: error.response?.status ?? 500,
                }
            }
        }

    }

})