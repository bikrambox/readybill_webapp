import { defineStore } from 'pinia'
import api from '@/config/api'
import { useUserPreferencesStore } from './userPreferences'

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

                // Columns definition (7 columns as per your table)
                const columns = [
                    { data: '', name: '', searchable: true, orderable: false }, // Checkbox
                    { data: 'item_name', name: '', searchable: true, orderable: true }, // Name
                    { data: 'sku', name: '', searchable: true, orderable: true }, // SKU
                    { data: 'category_name', name: '', searchable: true, orderable: true }, // Category Name
                    { data: '', name: '', searchable: true, orderable: true }, // Stock
                    { data: '', name: '', searchable: true, orderable: true }, // Purchase Price
                    { data: '', name: '', searchable: true, orderable: true }, // MRP
                    { data: '', name: '', searchable: true, orderable: true }, // Rate
                    { data: '', name: '', searchable: true, orderable: true }, // Unit
                    { data: '', name: '', searchable: true, orderable: false }  // Action
                ]

                columns.forEach((col, index) => {
                    formData.append(`columns[${index}][data]`, col.data)
                    formData.append(`columns[${index}][name]`, col.name)
                    formData.append(`columns[${index}][searchable]`, col.searchable)
                    formData.append(`columns[${index}][orderable]`, col.orderable)
                    formData.append(`columns[${index}][search][value]`, '')
                    formData.append(`columns[${index}][search][regex]`, false)
                })

                // Order (sort by column 1 - item_name)
                formData.append('order[0][column]', 1)
                formData.append('order[0][dir]', 'asc')

                // Search
                formData.append('search[value]', searchValue)
                formData.append('search[regex]', false)

                // Custom filter option
                formData.append('filter_option', filterColumn)

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}items`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        }
                    }
                )

                console.log('✅ API Response:', data)

                // Check if response has the expected structure
                if (data && Array.isArray(data.data)) {
                    // Clear items first to trigger reactivity
                    this.items = []

                    // Map backend data to frontend format
                    const mappedItems = data.data.map(item => ({
                        id: item.id,
                        name: item.item_name,
                        sku: item.sku,
                        category_id: item.category_id,
                        category_name: item.category_name,
                        stock: parseFloat(item.quantity) || 0,
                        minStockAlert: parseFloat(item.min_stock_alert) || 0,
                        purchase_price: parseFloat(item.purchase_price) || 0,
                        mrp: parseFloat(item.mrp) || 0,
                        rate: parseFloat(item.sale_price) || 0,
                        unit: item.short_unit || item.full_unit,
                        fullUnit: item.full_unit,
                        shortUnit: item.short_unit,
                        hsnCode: item.hsn || '',
                        tax1: item.tax1 || '',
                        rate1: parseFloat(item.rate1) || 0,
                        tax2: item.tax2 || '',
                        rate2: parseFloat(item.rate2) || 0,
                        tags: item.tags ? JSON.parse(item.tags) : [],
                        // barcode: item.barcode,
                        barcode: (item.barcode && item.barcode.trim() !== '' && item.barcode !== '–')
                            ? item.barcode.trim()
                            : null,
                        createdAt: item.created_at,
                        updatedAt: item.updated_at,
                        _original: item
                    }))

                    // Set new items
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
                        recordsFiltered: this.recordsFiltered,
                        currentPage: this.currentPage
                    })

                    return { success: true }
                } else {
                    this.errors = [this.$t('common.Invalid response format from server')]
                    // console.error('❌ Invalid response format')
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                // console.error('❌ Error fetching items:', error)

                if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = [this.$t('common.Failed to fetch items. Please try again.')]
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
                // console.log('🏁 Fetch completed, loading:', this.loading)
            }
        },

        async updateItem(itemData) {
            this.loading = true
            this.errors = []
            this.successMessage = null

            try {
                const payload = {
                    id: itemData.id,
                    sku: itemData.sku,
                    category_id: itemData.category_id,
                    item_name: itemData.name,
                    quantity: parseFloat(itemData.stock) || 0,
                    min_stock_alert: parseFloat(itemData.minStockAlert) || 0,
                    full_unit: itemData.fullUnit,
                    short_unit: itemData.shortUnit || itemData.unit,
                    hsn: itemData.hsnCode || '',
                    purchase_price: parseFloat(itemData.purchase_price) || 0,
                    mrp: parseFloat(itemData.mrp) || 0,
                    sale_price: parseFloat(itemData.rate) || 0,
                    tax1: itemData.tax1 || '',
                    rate1: parseFloat(itemData.rate1) || 0,
                    barcode: itemData.barcode,
                    // barcode: (itemData.barcode && itemData.barcode.trim() !== '' && itemData.barcode !== '–')
                    //     ? itemData.barcode.trim()
                    //     : null,
                }

                if (itemData.tax2 && itemData.rate2 > 0) {
                    payload.tax2 = itemData.tax2
                    payload.rate2 = parseFloat(itemData.rate2)
                }

                if (itemData.tags && Array.isArray(itemData.tags)) {
                    payload.tags = JSON.stringify(itemData.tags)
                }

                console.log("📦 Store updateItem payload.barcode:", payload.barcode);

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}update-item`,
                    payload
                )

                if (data.status === 1 || data.success) {
                    // Refresh the table data
                    await this.fetchItems(this.currentPage, this.pageLength, this.searchValue, this.filterColumn)

                    this.successMessage = data.message || this.$t('common.Item updated successfully')
                    return { success: true, data: data.data }
                } else {
                    this.errors = [data.message || this.$t('common.Failed to update item')]
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('Error updating item:', error)

                // Handle the specific error format: {"status":0,"message":"Validation Error","data":[{"hsn":["error message"]}]}
                if (error.response?.data?.data && Array.isArray(error.response.data.data)) {
                    // Extract validation errors from data array
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
                    this.errors = validationErrors.length > 0 ? validationErrors : [error.response.data.message || this.$t('common.Validation Error')]
                } else if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = [this.$t('common.Failed to update item. Please try again.')]
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
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}multiple-delete`,
                    {
                        ids: idsArray,
                    }
                )

                // console.log('✅ Delete API Response:', data)

                if (data.status === 'success') {
                    // Clear selection
                    this.selectedItems.clear()
                    console.log('🔄 Selection cleared')

                    // Force refresh the data
                    console.log('🔄 Refreshing table data...')
                    const fetchResult = await this.fetchItems(
                        this.currentPage,
                        this.pageLength,
                        this.searchValue,
                        this.filterColumn
                    )

                    // this.successMessage = data.message || `Successfully deleted ${idsArray.length} item(s)`
                    this.successMessage = data.message || this.$t('common.succesfull_delete_items', { count: idsArray.length })

                    return { success: true }
                } else {
                    this.errors = [data.message || this.$t('common.Failed to delete items')]
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('❌ Error deleting items:', error)

                if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else if (error.message) {
                    this.errors = [error.message]
                } else {
                    this.errors = [this.$t('common.Failed to delete items. Please try again.')]
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
                // console.log('🏁 Delete operation completed')
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
