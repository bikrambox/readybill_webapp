import { defineStore } from 'pinia'
import api from '@/config/api'

// const columnConfig = [
//     { key: "flag", editable: false, type: "checkbox" },
//     {
//         key: "sku",
//         editable: true,
//         type: "text",
//         field: "sku",
//         label: "SKU",
//     },
//     {
//         key: "category",
//         editable: true,
//         type: "select",
//         field: "category",
//         label: "Category",
//     },
//     {
//         key: "itemName",
//         editable: true,
//         type: "text",
//         field: "item_name",
//         label: "Item Name",
//     },
//     {
//         key: "quantity",
//         editable: true,
//         type: "number",
//         field: "quantity",
//         label: "Quantity",
//     },
//     {
//         key: "minimumStockAlert",
//         editable: true,
//         type: "number",
//         field: "min_stock_alert",
//         label: "Min Stock Alert",
//     },

//     { key: "mrp", editable: true, type: "number", field: "mrp", label: "MRP" },
//     {
//         key: "salePrice",
//         editable: true,
//         type: "number",
//         field: "sale_price",
//         label: "Sale Price",
//     },
//     {
//         key: "purchase_price",
//         editable: true,
//         type: "number",
//         field: "purchase_price",
//         label: "Purchase Price",
//     },
//     { key: "unit", editable: true, type: "select", field: "short_unit", label: "Unit" },
//     { key: "hsn", editable: true, type: "text", field: "hsn", label: "HSN" },
//     {
//         key: "gst",
//         editable: true,
//         type: "number",
//         field: "gst",
//         label: "GST",
//         width: "110px",
//     },
//     {
//         key: "cess",
//         editable: true,
//         type: "number",
//         field: "cess",
//         label: "CESS",
//         width: "110px",
//     },
// ];


// ─── SINGLE SOURCE OF TRUTH ────────────────────────────────────────────────
// index matches PHP COLUMN_MAP (1-based). Adding a column here auto-updates:
//   - fetchDatasets() columns payload
//   - updateCellData() formData
//   - exportToInventory() items mapping
//   - DatasetTable columnConfig (imported from here)
export const DATASET_COLUMNS = [
    // index 0 is the checkbox/flag column — not sent to server
    { index: 0, key: 'flag', field: null, label: 'Flag', type: 'checkbox', editable: false, serverColumn: false },
    { index: 1, key: 'sku', field: 'sku', label: 'SKU', type: 'text', editable: true, serverColumn: true },
    { index: 2, key: 'barcode', field: 'barcode', label: 'Barcode', type: 'text', editable: true, serverColumn: true },
    { index: 3, key: 'itemName', field: 'item_name', label: 'Item Name', type: 'text', editable: true, serverColumn: true },
    { index: 4, key: 'category', field: 'category_id', label: 'Category', type: 'select', editable: true, serverColumn: true },
    { index: 5, key: 'unit', field: 'short_unit', label: 'Unit', type: 'select', editable: true, serverColumn: true },
    { index: 6, key: 'hsn', field: 'hsn', label: 'HSN', type: 'text', editable: true, serverColumn: true },
    { index: 7, key: 'quantity', field: 'quantity', label: 'Quantity', type: 'number', editable: true, serverColumn: true },
    { index: 8, key: 'minimumStockAlert', field: 'min_stock_alert', label: 'Min Stock Alert', type: 'number', editable: true, serverColumn: true },
    { index: 9, key: 'purchase_price', field: 'purchase_price', label: 'Purchase Price', type: 'number', editable: true, serverColumn: true },
    { index: 10, key: 'mrp', field: 'mrp', label: 'MRP', type: 'number', editable: true, width: null, serverColumn: true },
    { index: 11, key: 'salePrice', field: 'sale_price', label: 'Sale Price', type: 'number', editable: true, width: null, serverColumn: true },
    { index: 12, key: 'gst', field: 'gst', label: 'GST', type: 'number', editable: true, width: '110px', serverColumn: true },
    { index: 13, key: 'cess', field: 'cess', label: 'CESS', type: 'number', editable: true, width: '110px', serverColumn: true },
]

// Helper: build a parsed row object from a raw backend item
// parseFloat is applied to all number-type columns automatically
export const parseDatasetRow = (item, index) => {
    const row = { id: item.id, flag: item.flag, selected: false, rowIndex: index }
    DATASET_COLUMNS.forEach(col => {
        if (!col.field) return
        const raw = item[col.field]
        row[col.key] = col.type === 'number' ? parseFloat(raw) : raw
    })
    return row
}

// Helper: build FormData from a dataset row for updateCellData
export const buildRowFormData = (dataset, rowIndex, cellIndex) => {
    const fd = new FormData()
    fd.append('id', dataset.id)
    fd.append('row_index', rowIndex)
    fd.append('cell_index', cellIndex)
    DATASET_COLUMNS.forEach(col => {
        if (!col.field) return
        fd.append(col.field, dataset[col.key] ?? '')
    })
    return fd
}

// Helper: parse backend grid_coordinates errors into the errorCellsMap
const applyGridErrors = (map, gridCoordinates) => {
    if (!Array.isArray(gridCoordinates)) return
    gridCoordinates.forEach(coord => {
        const [r, c] = coord.split(',').map(Number)
        map.set(`${r},${c}`, true)
    })
}
// ───────────────────────────────────────────────────────────────────────────

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
        errors: [],          
        successMessage: '',   
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

        _setErrorDetails(errors, coordKey = 'grid_coordinates') {
            this.errorDetails = {
                coordinates: errors.coordinates || [],
                gridCoordinates: errors[coordKey] || [],
                messages: errors.messages || []
            }
            applyGridErrors(this.errorCellsMap, errors[coordKey])
        },

        async fetchDatasets() {
            this.loading = true
            this.error = null
            this.errorDetails = null
            this.errorCellsMap.clear()

            try {
                // Build columns array dynamically from DATASET_COLUMNS
                const columns = DATASET_COLUMNS.map(col => ({
                    data: col.field ?? col.key,
                    name: '',
                    searchable: col.serverColumn,
                    orderable: col.serverColumn,
                    search: { value: '', regex: false }
                }))

                const payload = {
                    draw: this.pagination.draw,
                    columns,
                    order: [{ column: 1, dir: 'asc' }],
                    start: this.pagination.start,
                    length: this.pagination.length,
                    search: { value: this.searchQuery, regex: false }
                }

                const { data } = await api.post(`${import.meta.env.VITE_GROCERY_INDIA_PREFIX}dataset`, payload)

                // Use the shared parser
                this.datasets = data.data.map((item, index) => parseDatasetRow(item, index))

                this.pagination.recordsTotal = data.recordsTotal
                this.pagination.recordsFiltered = data.recordsFiltered
                this.pagination.draw++

                if (data.status === 0 && data.errors) {
                    this._setErrorDetails(data.errors)
                }

            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to fetch datasets'
                console.error('Error fetching datasets:', err)
            } finally {
                this.loading = false
            }
        },


        clearErrors() {
            this.errorDetails = null
            this.errorCellsMap.clear()
            this.errors = []
            this.error = null
        },


        async updateCellData(dataset, rowIndex, cellIndex) {
            try {
                const formData = buildRowFormData(dataset, rowIndex, cellIndex)  // ← uses helper

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}update-cell-data`,
                    formData,
                    { headers: { 'Content-Type': 'multipart/form-data' } }
                )

                if (data.status === 'failed' && data.errors) {
                    this._setErrorDetails(data.errors)
                    throw new Error(data.errors.messages?.join(', ') || data.message || 'Validation failed')
                }

                if (data.status === 'success') {
                    this.errorCellsMap.delete(`${rowIndex},${cellIndex}`)
                    if (this.errorCellsMap.size === 0) this.errorDetails = null

                    if (data.data) {
                        if (dataset.id === 0 && data.data.id) dataset.id = data.data.id

                        // Use DATASET_COLUMNS field→key map to update row from response
                        DATASET_COLUMNS.forEach(col => {
                            if (!col.field) return
                            if (data.data[col.field] !== undefined) {
                                dataset[col.key] = col.type === 'number'
                                    ? parseFloat(data.data[col.field])
                                    : data.data[col.field]
                            }
                        })
                    }
                    return data
                }

                throw new Error(data.message || 'Update failed')

            } catch (err) {
                if (err.response?.data?.errors) {
                    this._setErrorDetails(err.response.data.errors)
                }
                this.error = err.message || err.response?.data?.message || 'Failed to update cell data'
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

        async exportData(action) {
            this.loading = true
            this.clearErrors()

            try {
                // Build items array dynamically from DATASET_COLUMNS
                const items = this.datasets.map(item => {
                    const row = {}
                    DATASET_COLUMNS.forEach(col => {
                        if (!col.field) return
                        row[col.field] = col.type === 'number'
                            ? Number(item[col.key] || 0).toFixed(2)
                            : (item[col.key] ?? '')
                    })
                    return row
                })

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}inventory-store-multiple`,
                    { items, action }
                )

                if (data.status === 'success' || data.status === 1) {
                    this.clearErrors()
                    return data
                }

                if (data.status === 0 && data.errors) {
                    this._setErrorDetails(data.errors, 'gridcoordinates')
                    throw new Error(data.errors.messages?.join(', ') || data.message || 'Validation failed')
                }

                throw new Error(data.message || 'Export failed')

            } catch (err) {
                if (err.response?.data?.errors) {
                    this._setErrorDetails(err.response.data.errors, 'gridcoordinates')
                }
                throw new Error(err.response?.data?.message || err.message || 'Failed to export data')
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
