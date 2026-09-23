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
                    { data: 'sku', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'category', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'item_name', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'quantity', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'min_stock_alert', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'purchase_price', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'mrp', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'sale_price', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'unit', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'hsn', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'gst', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'cess', name: '', searchable: true, orderable: true, search: { value: '', regex: false } }
                ]


                const payload = {
                    draw: this.pagination.draw,
                    // draw: -1,
                    columns,
                    order: [{ column: 1, dir: 'asc' }],
                    start: this.pagination.start,
                    // start: -4,
                    length: this.pagination.length,
                    search: { value: this.searchQuery, regex: false }
                }


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}dataset`, payload)


                // Map datasets
                this.datasets = data.data.map((item, index) => ({
                    id: item.id,
                    sku: item.sku,
                    category: item.category_id,
                    itemName: item.item_name,
                    quantity: parseFloat(item.quantity),
                    minimumStockAlert: parseFloat(item.min_stock_alert),
                    purchase_price: parseFloat(item.purchase_price),
                    mrp: parseFloat(item.mrp),
                    salePrice: parseFloat(item.sale_price),
                    unit: item.unit,
                    hsn: item.hsn,
                    gst: parseFloat(item.gst),
                    cess: parseFloat(item.cess),
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
                // formData.append('id', dataset.id)
                // formData.append('sku', dataset.sku)
                // formData.append('category_id', dataset.category)
                // formData.append('item_name', dataset.itemName)
                // formData.append('quantity', dataset.quantity)
                // formData.append('min_stock_alert', dataset.minimumStockAlert)
                // formData.append('mrp', dataset.mrp)
                // formData.append('sale_price', dataset.salePrice)
                // formData.append('purchase_price', dataset.purchase_price)
                // formData.append('short_unit', dataset.unit)
                // formData.append('hsn', dataset.hsn)
                // formData.append('gst', dataset.gst)
                // formData.append('cess', dataset.cess)
                // formData.append('row_index', rowIndex)
                // formData.append('cell_index', cellIndex)

                
                formData.append('id', dataset.id)
                formData.append('sku', dataset.sku)
                formData.append('category_id', dataset.category)
                formData.append('item_name', dataset.itemName)
                formData.append('quantity', dataset.quantity)
                formData.append('min_stock_alert', dataset.minimumStockAlert)
                formData.append('mrp', dataset.mrp)
                formData.append('sale_price', dataset.salePrice)
                formData.append('purchase_price', dataset.purchaseprice)
                formData.append('short_unit', dataset.unit)
                formData.append('hsn', dataset.hsn)
                formData.append('gst', dataset.gst)
                formData.append('cess', dataset.cess)
                formData.append('row_index', rowIndex)
                formData.append('cell_index', cellIndex)


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}update-cell-data`, formData, {
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
                            sku: 'sku',
                            category: 'category',
                            quantity: 'quantity',
                            item_name: 'itemName',
                            min_stock_alert: 'minimumStockAlert',
                            purchase_price: 'purchase_price',
                            mrp: 'mrp',
                            sale_price: 'salePrice',
                            short_unit: 'unit',
                            hsn: 'hsn',
                            gst: 'gst',
                            cess: 'cess'
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
                const { data } = await api.get(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}reset-dataset`)
                await this.fetchDatasets()
                return data
            } catch (err) {
                this.error = err.response?.data?.message || this.$t('common.Failed to reset dataset')
                throw err
            }
        },

        async downloadPreDataset() {
            this.loading = true
            this.errors = []

            try {
                const response = await api.get(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}download/sample-dataset`,
                    {
                        responseType: 'blob',
                        headers: {
                            Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        }
                    }
                )

                let fileName = 'dataset.xlsx'
                const disposition = response.headers['content-disposition']

                if (disposition) {
                    const match = disposition.match(/filename="?([^"]+)"?/)
                    if (match && match[1]) {
                        fileName = match[1]
                    }
                }

                const blob = new Blob([response.data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                })

                const url = window.URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', fileName)
                document.body.appendChild(link)
                link.click()
                link.remove()
                window.URL.revokeObjectURL(url)

                return { success: true }
            } catch (err) {
                let message = this.$t('common.Failed to download dataset')

                if (err.response?.data instanceof Blob) {
                    try {
                        const text = await err.response.data.text()
                        const json = JSON.parse(text)
                        message = json.message || message
                    } catch (_) { }
                } else if (err.response?.data?.message) {
                    message = err.response.data.message
                }

                this.errors = [message]
                throw err
            } finally {
                this.loading = false
            }
        },

        async deleteSelected() {
            try {
                const ids = this.datasets.filter(d => d.selected).map(d => d.id)


                if (ids.length === 0) return


                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}dataset/multiple-delete`, { ids })


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
                    sku: '',
                    category: 1,
                    itemName: 'New Item',
                    quantity: 0,
                    minimumStockAlert: 0,
                    purchase_price: 0,
                    mrp: 0,
                    salePrice: 0,
                    unit: 'PCS',
                    hsn: '',
                    gst: 0,
                    cess: 0,
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
                // const result = await this.updateCellData(newRow, 0, 1)

                const result = await this.updateCellData(newRow, 0, 3)

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
        },

        async downloadDataset() {
            this.loading = true
            this.errors = []

            try {
                const { data } = await api.get(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}download-dataset`
                )

                if (data.status == 1 || data.status === '1') {
                    this.successMessage = data.message || this.$t('common.Download Dataset successful')

                    // Construct the file URL and trigger download
                    const fileUrl = `${import.meta.env.VITE_MEDIA_URL}${data.file}`
                    window.location.href = fileUrl

                    return { success: true, message: data.message }
                } else {
                    this.errors = [data.message || this.$t('common.Export failed')]
                    return { success: false, errors: this.errors }
                }
            } catch (error) {
                console.error('Error exporting data:', error)

                if (error.response?.data?.message) {
                    this.errors = [error.response.data.message]
                } else {
                    this.errors = [this.$t('common.Failed to export data. Please try again.')]
                }

                return { success: false, errors: this.errors }
            } finally {
                this.loading = false
            }
        },

        async exportToInventory(action) {
            this.loading = true;
            this.clearErrors();

            try {
                const items = this.datasets.map((item) => ({
                    sku: item.sku,
                    category_id: item.category,
                    itemname: item.itemName,
                    quantity: Number(item.quantity || 0).toFixed(2),
                    minstockalert: Number(item.minimumStockAlert || 0).toFixed(2),
                    purchaseprice: Number(item.purchase_price ?? item.purchaseprice ?? 0).toFixed(2),
                    mrp: Number(item.mrp || 0).toFixed(2),
                    saleprice: Number(item.salePrice || 0).toFixed(2),
                    unit: item.unit,
                    hsn: item.hsn,
                    gst: Number(item.gst || 0).toFixed(2),
                    cess: Number(item.cess || 0).toFixed(2),
                }));

                const payload = { items, action };

                const data = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}inventory-store-multiple`,
                    payload
                );

                if (data.status === "success" || data.status === 1) {
                    this.clearErrors();
                    return data;
                }

                if (data.status === 0 && data.errors) {
                    this.errorDetails = {
                        coordinates: data.errors.coordinates || [],
                        gridCoordinates: data.errors.gridcoordinates || [],
                        messages: data.errors.messages || [],
                    };

                    if (Array.isArray(data.errors.gridcoordinates)) {
                        data.errors.gridcoordinates.forEach((coord) => {
                            const [rowStr, colStr] = coord.split(",");
                            const rowIndex = parseInt(rowStr, 10);
                            const colIndex = parseInt(colStr, 10);
                            this.errorCellsMap.set(`${rowIndex},${colIndex}`, true);
                        });
                    }

                    throw new Error(
                        data.errors.messages?.join(", ") ||
                        data.message ||
                        this.t("common.Validation failed")
                    );
                }

                throw new Error(data.message || this.t("common.Export failed"));
            } catch (err) {
                console.error("Error exporting to inventory:", err);

                if (err.response?.data?.errors) {
                    const errorData = err.response.data;

                    this.errorDetails = {
                        coordinates: errorData.errors.coordinates || [],
                        gridCoordinates: errorData.errors.gridcoordinates || [],
                        messages: errorData.errors.messages || [],
                    };

                    this.errorCellsMap.clear();
                    if (Array.isArray(errorData.errors.gridcoordinates)) {
                        errorData.errors.gridcoordinates.forEach((coord) => {
                            const [rowStr, colStr] = coord.split(",");
                            const rowIndex = parseInt(rowStr, 10);
                            const colIndex = parseInt(colStr, 10);
                            this.errorCellsMap.set(`${rowIndex},${colIndex}`, true);
                        });
                    }
                }

                throw new Error(
                    err.response?.data?.message ||
                    err.message ||
                    this.t("common.Failed to export data")
                );
            } finally {
                this.loading = false;
            }
        },
        
    }
})
