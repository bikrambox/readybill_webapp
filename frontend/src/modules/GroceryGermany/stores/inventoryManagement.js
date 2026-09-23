import { defineStore } from 'pinia'
import api from '@/config/api'
import { useUserPreferencesStore } from './userPreferences'
import { useConfigStore } from '@/stores/config'


export const useInventoryManagementStore = defineStore('inventoryManagement', {
    state: () => ({
        items: [],
        loading: false,
        errors: [],
        successMessage: null,
        selectedItems: new Set(),
        recordsTotal: 0,
        recordsFiltered: 0,
        currentPage: 0,
        pageLength: 10,
        searchValue: '',
        filterColumn: 'item_name',
        draw: 0
    }),


    getters: {
        isLoading: (state) => state.loading,
        hasErrors: (state) => state.errors.length > 0,
        selectedCount: (state) => state.selectedItems.size,
        uniqueItemNames: (state) => {
            return [...new Set(state.items.map(item => item.name))].filter(Boolean)
        },
        totalPages: (state) => {
            return Math.ceil(state.recordsFiltered / state.pageLength) || 1
        }
    },


    actions: {
        // Helper function to find tax value from tax name
        findTaxValueFromName(taxName) {
            if (!taxName || taxName === '–' || taxName.trim() === '') {
                return ''
            }

            const configStore = useConfigStore()

            // Clean the tax name (remove parentheses and percentages)
            const cleanTaxName = taxName
                .replace(/\s*\([^)]*\)\s*/g, '')
                .replace(/\s*\d+\.?\d*\s*%?\s*$/g, '')
                .trim()
                .toLowerCase()

            console.log('Looking for tax name:', cleanTaxName)
            console.log('Available taxes:', configStore.taxesArray)

            // Try to find matching tax by comparing cleaned label
            const matchedTax = configStore.taxesArray.find(tax => {
                const cleanLabel = tax.label
                    .replace(/\s*\([^)]*\)\s*/g, '')
                    .replace(/\s*\d+\.?\d*\s*%?\s*$/g, '')
                    .trim()
                    .toLowerCase()

                return cleanLabel === cleanTaxName
            })

            if (matchedTax) {
                console.log('Matched tax:', matchedTax)
                return matchedTax.value
            }

            // Fallback: return the original name as lowercase
            console.log('No match found, returning lowercase:', cleanTaxName)
            return cleanTaxName
        },

        async fetchItems(page = 0, length = 10, searchValue = '', filterColumn = 'item_name') {
            this.loading = true
            this.errors = []
            this.draw++

            console.log('🔄 Fetching items...', { page, length, searchValue, filterColumn, draw: this.draw })

            try {
                // Create FormData with DataTables server-side format
                const formData = new FormData()

                // Draw counter
                formData.append('draw', this.draw)

                // Pagination
                formData.append('start', page * length)
                formData.append('length', length)

                // Columns definition
                const columns = [
                    { data: '', name: '', searchable: true, orderable: false },
                    { data: 'item_name', name: '', searchable: true, orderable: true },
                    { data: '', name: '', searchable: true, orderable: true },
                    { data: '', name: '', searchable: true, orderable: true },
                    { data: '', name: '', searchable: true, orderable: true },
                    { data: '', name: '', searchable: true, orderable: true },
                    { data: '', name: '', searchable: true, orderable: false }
                ]

                columns.forEach((col, index) => {
                    formData.append(`columns[${index}][data]`, col.data)
                    formData.append(`columns[${index}][name]`, col.name)
                    formData.append(`columns[${index}][searchable]`, col.searchable)
                    formData.append(`columns[${index}][orderable]`, col.orderable)
                    formData.append(`columns[${index}][search][value]`, '')
                    formData.append(`columns[${index}][search][regex]`, false)
                })

                // Order
                formData.append('order[0][column]', 1)
                formData.append('order[0][dir]', 'asc')

                // Search
                formData.append('search[value]', searchValue)
                formData.append('search[regex]', false)

                // Custom filter option
                formData.append('filter_option', filterColumn)

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}items`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    }
                )

                console.log('✅ API Response:', data)

                if (data && Array.isArray(data.data)) {
                    this.items = []

                    // Map backend data to frontend format
                    const mappedItems = data.data.map(item => {
                        // Convert tax names to tax values for dropdown binding
                        const tax1Value = this.findTaxValueFromName(item.tax1)
                        const tax2Value = this.findTaxValueFromName(item.tax2)

                        // console.log('Mapping item:', item.item_name, {
                        //     backendTax1: item.tax1,
                        //     frontendTax1: tax1Value,
                        //     backendTax2: item.tax2,
                        //     frontendTax2: tax2Value
                        // })

                        return {
                            id: item.id,
                            name: item.item_name,
                            stock: parseFloat(item.quantity) || 0,
                            minStockAlert: parseFloat(item.min_stock_alert) || 0,
                            mrp: parseFloat(item.mrp) || 0,
                            rate: parseFloat(item.sale_price) || 0,
                            unit: item.short_unit || item.full_unit,
                            fullUnit: item.full_unit,
                            shortUnit: item.short_unit,
                            hsnCode: item.hsn || '',
                            tax1: tax1Value,  // Use mapped value for dropdown
                            rate1: parseFloat(item.rate1) || 0,
                            tax2: tax2Value,  // Use mapped value for dropdown
                            rate2: parseFloat(item.rate2) || 0,
                            tags: item.tags ? JSON.parse(item.tags) : [],
                            barcode: item.barcode,
                            createdAt: item.created_at,
                            updatedAt: item.updated_at,
                            _original: item
                        }
                    })

                    this.items = mappedItems
                    this.recordsTotal = data.recordsTotal || 0
                    this.recordsFiltered = data.recordsFiltered || 0
                    this.currentPage = page
                    this.pageLength = length
                    this.searchValue = searchValue
                    this.filterColumn = filterColumn

                    console.log('✅ Items updated:', {
                        count: this.items.length,
                        recordsTotal: this.recordsTotal,
                        recordsFiltered: this.recordsFiltered
                    })

                    return { success: true }
                } else {
                    this.errors = ['Invalid response format from server']
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('❌ Error fetching items:', error)

                if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = ['Failed to fetch items. Please try again.']
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        async updateItem(itemData) {
            this.loading = true
            this.errors = []
            this.successMessage = null

            try {
                // Helper function to safely convert to string
                const toStringOrEmpty = (value) => {
                    if (value === null || value === undefined || value === '') {
                        return ''
                    }
                    return String(value).trim()
                }

                const payload = {
                    id: itemData.id,
                    item_name: itemData.name,
                    quantity: parseFloat(itemData.stock) || 0,
                    min_stock_alert: parseFloat(itemData.minStockAlert) || 0,
                    full_unit: itemData.fullUnit,
                    short_unit: itemData.shortUnit || itemData.unit,
                    hsn: itemData.hsnCode || '',
                    mrp: parseFloat(itemData.mrp) || 0,
                    sale_price: parseFloat(itemData.rate) || 0,
                    barcode: itemData.barcode,
                }

                // Handle tax1 - get clean name from value and send with rate
                if (itemData.tax1) {
                    const configStore = useConfigStore()
                    const selectedTax1 = configStore.taxesArray.find(tax => tax.value === itemData.tax1)

                    console.log('Tax1 input:', itemData.tax1)
                    console.log('Found tax1:', selectedTax1)

                    // Get tax name and clean it
                    let tax1Name = selectedTax1 ? toStringOrEmpty(selectedTax1.label) : toStringOrEmpty(itemData.tax1)

                    // Remove parentheses and percentages: "VAT (19%)" -> "VAT"
                    tax1Name = tax1Name
                        .replace(/\s*\([^)]*\)\s*/g, '')
                        .replace(/\s*\d+\.?\d*\s*%?\s*$/g, '')
                        .trim()

                    payload.tax1 = tax1Name
                    payload.rate1 = parseFloat(itemData.rate1) || 0

                    console.log('Tax1 being sent:', { tax1: payload.tax1, rate1: payload.rate1 })
                } else {
                    payload.tax1 = ''
                    payload.rate1 = 0
                }

                // Handle tax2 - get clean name from value and send with rate
                if (itemData.tax2 && itemData.rate2 > 0) {
                    const configStore = useConfigStore()
                    const selectedTax2 = configStore.taxesArray.find(tax => tax.value === itemData.tax2)

                    console.log('Tax2 input:', itemData.tax2)
                    console.log('Found tax2:', selectedTax2)

                    // Get tax name and clean it
                    let tax2Name = selectedTax2 ? toStringOrEmpty(selectedTax2.label) : toStringOrEmpty(itemData.tax2)

                    // Remove parentheses and percentages: "GST (7%)" -> "GST"
                    tax2Name = tax2Name
                        .replace(/\s*\([^)]*\)\s*/g, '')
                        .replace(/\s*\d+\.?\d*\s*%?\s*$/g, '')
                        .trim()

                    payload.tax2 = tax2Name
                    payload.rate2 = parseFloat(itemData.rate2)

                    console.log('Tax2 being sent:', { tax2: payload.tax2, rate2: payload.rate2 })
                }

                if (itemData.tags && Array.isArray(itemData.tags)) {
                    payload.tags = JSON.stringify(itemData.tags)
                }

                console.log('=== FINAL UPDATE PAYLOAD ===')
                console.log(JSON.stringify(payload, null, 2))

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}update-item`,
                    payload
                )

                if (data.status === 1 || data.success) {
                    // Refresh the table data
                    await this.fetchItems(this.currentPage, this.pageLength, this.searchValue, this.filterColumn)

                    this.successMessage = data.message || 'Item updated successfully'
                    return { success: true, data: data.data }
                } else {
                    this.errors = [data.message || 'Failed to update item']
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('Error updating item:', error)

                if (error.response?.data?.data && Array.isArray(error.response.data.data)) {
                    const validationErrors = []
                    error.response.data.data.forEach(errorObj => {
                        Object.keys(errorObj).forEach(field => {
                            const fieldErrors = errorObj[field]
                            if (Array.isArray(fieldErrors)) {
                                validationErrors.push(...fieldErrors)
                            } else if (typeof fieldErrors === 'string') {
                                validationErrors.push(fieldErrors)
                            }
                        })
                    })
                    this.errors = validationErrors.length > 0 ? validationErrors : [error.response.data.message || 'Validation Error']
                } else if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = ['Failed to update item. Please try again.']
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        async deleteItems(itemIds) {
            this.loading = true
            this.errors = []
            this.successMessage = null

            try {
                const idsArray = Array.from(itemIds)
                console.log('🗑️ Starting delete for IDs:', idsArray)

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}multiple-delete`,
                    {
                        ids: idsArray,
                    }
                )

                if (data.status === 'success') {
                    this.selectedItems.clear()
                    console.log('🔄 Selection cleared')

                    console.log('🔄 Refreshing table data...')
                    const fetchResult = await this.fetchItems(
                        this.currentPage,
                        this.pageLength,
                        this.searchValue,
                        this.filterColumn
                    )

                    this.successMessage = data.message || `Successfully deleted ${idsArray.length} item(s)`

                    return { success: true }
                } else {
                    this.errors = [data.message || 'Failed to delete items']
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('❌ Error deleting items:', error)

                if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = ['Failed to delete items. Please try again.']
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        async changePage(page) {
            await this.fetchItems(page, this.pageLength, this.searchValue, this.filterColumn)
        },

        async changePageLength(length) {
            await this.fetchItems(0, length, this.searchValue, this.filterColumn)
        },

        async performSearch(searchValue, filterColumn) {
            await this.fetchItems(0, this.pageLength, searchValue, filterColumn)
        },

        selectItem(id) {
            this.selectedItems.add(id)
        },

        deselectItem(id) {
            this.selectedItems.delete(id)
        },

        selectAllItems(itemIds) {
            itemIds.forEach(id => this.selectedItems.add(id))
        },

        clearSelection() {
            this.selectedItems.clear()
        },

        shouldHighlightRow(item) {
            const preferencesStore = useUserPreferencesStore()
            const stockPreference = preferencesStore.preferences.preference_quantity

            if (stockPreference === 1 || stockPreference === true) {
                const quantity = parseFloat(item.stock) || 0
                const minStockAlert = parseFloat(item.minStockAlert) || 0

                if (quantity === 0) {
                    return true
                }
                if (minStockAlert >= quantity && minStockAlert > 0) {
                    return true
                }
            }

            return false
        },

        clearErrors() {
            this.errors = []
        },

        clearSuccessMessage() {
            this.successMessage = null
        }
    }
})
