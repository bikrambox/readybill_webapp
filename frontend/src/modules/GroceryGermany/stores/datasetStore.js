import { defineStore } from 'pinia'
import api from '@/config/api'


export const useDatasetStore = defineStore('dataset', {
    state: () => ({
        datasets: [],
        pagination: {
            draw: 1,
            start: 0,
            length: 10,
            recordsTotal: 0,
            recordsFiltered: 0
        },
        searchQuery: '',
        loading: false,
        error: null,
        errorDetails: null,
        errorCellsMap: new Map() // Map to store error cells: key="rowIndex,colIndex"
    }),


    getters: {
        filteredDatasets: (state) => state.datasets,
        selectedCount: (state) => state.datasets.filter(item => item.selected).length,
        hasSelection: (state) => state.datasets.some(item => item.selected),
        totalPages: (state) => Math.ceil(state.pagination.recordsFiltered / state.pagination.length),
        currentPage: (state) => Math.floor(state.pagination.start / state.pagination.length) + 1,
        displayRange: (state) => {
            const start = state.pagination.start + 1
            const end = Math.min(state.pagination.start + state.pagination.length, state.pagination.recordsFiltered)
            return `${start}-${end} of ${state.pagination.recordsFiltered}`
        },
        hasErrors: (state) => state.errorDetails !== null,
        // Helper to check if a cell has error
        isCellError: (state) => (rowIndex, colIndex) => {
            return state.errorCellsMap.has(`${rowIndex},${colIndex}`)
        }
    },


    actions: {

        async fetchDatasets() {
            this.loading = true
            this.error = null
            this.errorDetails = null
            this.errorCellsMap.clear()


            try {
                const columns = [
                    { data: 'flag', name: '', searchable: true, orderable: false, search: { value: '', regex: false } },
                    { data: 'item_name', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'quantity', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'min_stock_alert', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'mrp', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'sale_price', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'unit', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    // { data: 'hsn', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'vat', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    // { data: 'cess', name: '', searchable: true, orderable: true, search: { value: '', regex: false } }
                ]


                const payload = {
                    draw: this.pagination.draw,
                    columns,
                    order: [{ column: 1, dir: 'asc' }],
                    start: this.pagination.start,
                    length: this.pagination.length,
                    search: { value: this.searchQuery, regex: false }
                }


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}dataset`, payload)


                // Map datasets
                this.datasets = data.data.map((item, index) => ({
                    id: item.id,
                    itemName: item.item_name,
                    quantity: parseFloat(item.quantity),
                    minimumStockAlert: parseFloat(item.min_stock_alert),
                    mrp: parseFloat(item.mrp),
                    salePrice: parseFloat(item.sale_price),
                    unit: item.unit,
                    // hsn: item.hsn,
                    vat: parseFloat(item.vat),
                    // cess: parseFloat(item.cess),
                    flag: item.flag,
                    selected: false,
                    rowIndex: index // Track row index for error mapping
                }))


                this.pagination.recordsTotal = data.recordsTotal
                this.pagination.recordsFiltered = data.recordsFiltered
                this.pagination.draw++


                // Check for errors in response
                if (data.status === 0 && data.errors) {
                    this.errorDetails = {
                        coordinates: data.errors.coordinates || [],
                        gridCoordinates: data.errors.grid_coordinates || [],
                        messages: data.errors.messages || []
                    }


                    // Build error cells map from grid_coordinates
                    if (data.errors.grid_coordinates && Array.isArray(data.errors.grid_coordinates)) {
                        data.errors.grid_coordinates.forEach(coord => {
                            // coord format: "00,01" where first part is row, second is column
                            const [rowStr, colStr] = coord.split(',')
                            const rowIndex = parseInt(rowStr, 10)
                            const colIndex = parseInt(colStr, 10)


                            // Store in map for quick lookup
                            this.errorCellsMap.set(`${rowIndex},${colIndex}`, true)
                        })
                    }
                }
            } catch (err) {
                this.error = err.response?.data?.message || this.$t('common.Failed to fetch datasets')
                console.error('Error fetching datasets:', err)
            } finally {
                this.loading = false
            }
        },


        clearErrors() {
            this.errorDetails = null
            this.errorCellsMap.clear()
        },


        async updateCellData(dataset, rowIndex, cellIndex) {
            try {
                const formData = new FormData()
                formData.append('id', dataset.id)
                formData.append('item_name', dataset.itemName)
                formData.append('quantity', dataset.quantity)
                formData.append('min_stock_alert', dataset.minimumStockAlert)
                formData.append('mrp', dataset.mrp)
                formData.append('sale_price', dataset.salePrice)
                formData.append('short_unit', dataset.unit)
                // formData.append('hsn', dataset.hsn)
                formData.append('vat', dataset.vat)
                // formData.append('cess', dataset.cess)
                formData.append('row_index', rowIndex)
                formData.append('cell_index', cellIndex)


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}update-cell-data`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })


                console.log('Cell update response:', data)

                // Check for validation errors
                if (data.status === 'failed' && data.errors) {
                    // Store error details
                    this.errorDetails = {
                        coordinates: data.errors.coordinates || [],
                        gridCoordinates: data.errors.grid_coordinates || [],
                        messages: data.errors.messages || []
                    }

                    // Build error cells map from grid_coordinates
                    if (data.errors.grid_coordinates && Array.isArray(data.errors.grid_coordinates)) {
                        data.errors.grid_coordinates.forEach(coord => {
                            const [rowStr, colStr] = coord.split(',')
                            const rowIndexErr = parseInt(rowStr, 10)
                            const colIndexErr = parseInt(colStr, 10)
                            this.errorCellsMap.set(`${rowIndexErr},${colIndexErr}`, true)
                        })
                    }

                    // Throw error with messages
                    const errorMessage = data.errors.messages?.join(', ') || data.message || this.$t('common.Validation failed')
                    throw new Error(errorMessage)
                }


                if (data.status === 'success') {
                    // Clear any previous errors for this cell
                    this.errorCellsMap.delete(`${rowIndex},${cellIndex}`)

                    // Clear error details if no more errors
                    if (this.errorCellsMap.size === 0) {
                        this.errorDetails = null
                    }

                    // Update the dataset with response data
                    if (data.data) {
                        // If this was a new row (id: 0), update with real ID from backend
                        if (dataset.id === 0 && data.data.id) {
                            dataset.id = data.data.id
                            console.log('New row created with ID:', data.data.id)
                        }

                        const fieldMapping = {
                            id: 'id',
                            quantity: 'quantity',
                            item_name: 'itemName',
                            min_stock_alert: 'minimumStockAlert',
                            mrp: 'mrp',
                            sale_price: 'salePrice',
                            short_unit: 'unit',
                            // hsn: 'hsn',
                            vat: 'vat',
                            // cess: 'cess'
                        }


                        Object.keys(data.data).forEach(key => {
                            if (fieldMapping[key] && dataset[fieldMapping[key]] !== undefined) {
                                dataset[fieldMapping[key]] = data.data[key]
                            }
                        })
                    }
                    return data
                } else {
                    throw new Error(data.message || this.$t('common.Update failed'))
                }
            } catch (err) {
                console.error('Cell update error:', err)

                // If it's an axios error with response data
                if (err.response?.data) {
                    const errorData = err.response.data

                    // Handle error response structure
                    if (errorData.errors) {
                        this.errorDetails = {
                            coordinates: errorData.errors.coordinates || [],
                            gridCoordinates: errorData.errors.grid_coordinates || [],
                            messages: errorData.errors.messages || []
                        }

                        // Build error cells map
                        if (errorData.errors.grid_coordinates && Array.isArray(errorData.errors.grid_coordinates)) {
                            errorData.errors.grid_coordinates.forEach(coord => {
                                const [rowStr, colStr] = coord.split(',')
                                const rowIndexErr = parseInt(rowStr, 10)
                                const colIndexErr = parseInt(colStr, 10)
                                this.errorCellsMap.set(`${rowIndexErr},${colIndexErr}`, true)
                            })
                        }
                    }
                }

                this.error = err.message || err.response?.data?.message || this.$t('common.Failed to update cell data')
                throw err
            }
        },


        async resetDataset() {
            try {
                const { data } = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}reset-dataset`)
                await this.fetchDatasets()
                return data
            } catch (err) {
                this.error = err.response?.data?.message || this.$t('common.Failed to reset dataset')
                throw err
            }
        },


        async exportData(action) {
            try {
                const items = this.datasets.map(item => ({
                    item_name: item.itemName,
                    quantity: item.quantity,
                    min_stock_alert: item.minimumStockAlert.toFixed(2),
                    mrp: item.mrp.toFixed(2),
                    sale_price: item.salePrice.toFixed(2),
                    unit: item.unit,
                    // hsn: item.hsn,
                    vat: item.vat.toFixed(2),
                    // cess: item.cess.toFixed(2)
                }))


                const payload = {
                    items,
                    action
                }


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}inventory-store-multiple`, payload)


                if (data.status === 'success') {
                    // Clear any previous errors
                    this.clearErrors()
                    return data
                } else if (data.status === 0 && data.errors) {
                    // Store error details
                    this.errorDetails = {
                        coordinates: data.errors.coordinates || [],
                        gridCoordinates: data.errors.grid_coordinates || [],
                        messages: data.errors.messages || []
                    }


                    // Build error cells map
                    if (data.errors.grid_coordinates) {
                        data.errors.grid_coordinates.forEach(coord => {
                            const [rowStr, colStr] = coord.split(',')
                            const rowIndex = parseInt(rowStr, 10)
                            const colIndex = parseInt(colStr, 10)
                            this.errorCellsMap.set(`${rowIndex},${colIndex}`, true)
                        })
                    }


                    throw new Error(data.message)
                }


                return data
            } catch (err) {
                this.error = err.response?.data?.message || err.message || this.$t('common.Failed to export data')
                throw err
            }
        },


        async deleteSelected() {
            try {
                const ids = this.datasets.filter(d => d.selected).map(d => d.id)


                if (ids.length === 0) return


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}dataset/multiple-delete`, { ids })


                if (data.status === 'success') {
                    await this.fetchDatasets()
                }


                return data
            } catch (err) {
                this.error = err.response?.data?.message || this.$t('common.Failed to delete items')
                throw err
            }
        },


        toggleSelection(id) {
            const item = this.datasets.find(d => d.id === id)
            if (item) {
                item.selected = !item.selected
            }
        },


        toggleAll() {
            const allSelected = this.datasets.every(d => d.selected)
            this.datasets.forEach(d => {
                d.selected = !allSelected
            })
        },


        deselectAll() {
            this.datasets.forEach(d => {
                d.selected = false
            })
        },


        setRowsPerPage(length) {
            this.pagination.length = parseInt(length, 10)
            this.pagination.start = 0
            this.fetchDatasets()
        },


        nextPage() {
            if (this.pagination.start + this.pagination.length < this.pagination.recordsFiltered) {
                this.pagination.start += this.pagination.length
                this.fetchDatasets()
            }
        },


        previousPage() {
            if (this.pagination.start >= this.pagination.length) {
                this.pagination.start -= this.pagination.length
                this.fetchDatasets()
            }
        },


        setSearchQuery(query) {
            this.searchQuery = query
            this.pagination.start = 0
            this.fetchDatasets()
        },


        async addRow() {
            try {
                // Create new row with id: 0 (indicates new record to backend)
                const newRow = {
                    id: 0, // Backend will create new record and return real ID
                    itemName: 'New Item',
                    quantity: 0,
                    minimumStockAlert: 0,
                    mrp: 0,
                    salePrice: 0,
                    unit: 'PCS',
                    // hsn: '',
                    vat: 0,
                    // cess: 0,
                    selected: false,
                    isNew: true
                }

                // Add row at the top of the array
                this.datasets.unshift(newRow)

                // Update recordsFiltered count
                this.pagination.recordsFiltered++

                console.log('Creating new row via API with id: 0')

                // Call API to create the row
                // rowIndex: 0 (top row), cellIndex: 1 (item_name column)
                const result = await this.updateCellData(newRow, 0, 1)

                console.log('New row created successfully:', result)

                // The updateCellData already updates the row's id with real ID from backend
                return result

            } catch (err) {
                console.error('Failed to create new row:', err)

                // Remove the row if creation failed
                this.datasets.shift()
                this.pagination.recordsFiltered--

                this.error = err.message || this.$t('Failed to create new row')
                throw err
            }
        }
    }
})
