// src/stores/uploadDataStore.js
import { defineStore } from 'pinia'
import api from '@/config/api'

export const DATASET_COLUMNS = [
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

const DEFAULT_ROW_VALUES = {
    flag: 1,
    sku: '',
    barcode: '',
    item_name: 'New Item',
    category_id: '',
    short_unit: 'PCS',
    hsn: '',
    quantity: 0,
    min_stock_alert: 0,
    purchase_price: 0,
    mrp: 0,
    sale_price: 0,
    gst: 0,
    cess: 0,
}

const NUMBER_FIELDS = new Set(
    DATASET_COLUMNS
        .filter(col => col.type === 'number' && col.field)
        .map(col => col.field)
)

const SERVER_COLUMNS = DATASET_COLUMNS.filter(col => col.serverColumn && col.field)

const buildDefaultRow = (overrides = {}) => ({
    id: 0,
    original_index: -1,
    isNew: true,
    ...DEFAULT_ROW_VALUES,
    ...overrides,
})

const normalizeRow = (row = {}) => ({
    id: row.id ?? 0,
    original_index: row.original_index ?? -1,
    isNew: row.isNew ?? false,
    ...DEFAULT_ROW_VALUES,
    ...Object.fromEntries(
        SERVER_COLUMNS.map(col => [col.field, row[col.field] ?? DEFAULT_ROW_VALUES[col.field]])
    ),
    flag: row.flag ?? 1,
})

const buildRowFormData = ({ row = {}, rowIndex = -1, colIndex = 1, jobId = null }) => {
    const formData = new FormData()

    if (jobId) {
        formData.append('job_id', jobId)
    }

    formData.append('id', row.id ?? 0)

    SERVER_COLUMNS.forEach((col) => {
        let value = row[col.field]

        if (NUMBER_FIELDS.has(col.field)) {
            value = parseFloat(value ?? 0)
            value = Number.isNaN(value) ? 0 : value
        }

        formData.append(col.field, value ?? '')
    })

    formData.append('row_index', rowIndex)
    formData.append('cell_index', colIndex)

    return formData
}

export const useUploadDataStore = defineStore('uploadData', {
    state: () => ({
        excelData: [],
        totalRecords: 0,
        currentPage: 1,
        pageSize: 100,
        errors: null,
        jobId: null,
        unitList: [],
        searchQuery: ''
    }),

    getters: {
        hasErrors: (state) => state.errors !== null &&
            (state.errors.grid_coordinates?.length > 0 || state.errors.messages?.length > 0),
        datasetColumns: () => DATASET_COLUMNS,

        editableColumns: () => DATASET_COLUMNS.filter(col => col.editable),

        serverColumns: () => SERVER_COLUMNS,

        defaultRowTemplate: () => buildDefaultRow(),

    },

    actions: {

        async previewExcel(file) {
            const formData = new FormData()
            formData.append('file', file)

            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}preview/excel`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )

                if (data.job_id) {
                    this.jobId = data.job_id
                }

                return data
            } catch (error) {
                throw error.response?.data || error
            }
        },

        async checkUploadProgress(jobId) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}preview/excel`,
                    { job_id: jobId }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.progress?.message || this.$t('common.Progress check failed'))
            }
        },

        async fetchExcelDataProgress(jobId) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}preview/fetch/excel/data`,
                    { job_id: jobId }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || this.$t('common.Fetch progress check failed'))
            }
        },

        async fetchExcelData(jobId, params = {}) {
            try {
                const payload = {
                    job_id: jobId,
                    start: params.start || 0,
                    length: params.length || this.pageSize,
                    draw: params.draw || 1,
                    search: { value: this.searchQuery || '', regex: false }
                }

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}preview/fetch/excel/data`,
                    payload
                )

                // Store the data with updated values
                if (data.data) {
                    this.excelData = data.data.map(row => normalizeRow(row))
                    this.totalRecords = data.recordsTotal || data.recordsFiltered || 0
                }

                // Store errors if present (merge with existing errors if any)
                if (data.errors) {
                    this.errors = data.errors
                } else if (!this.errors) {
                    // Only clear errors if there are no existing errors from export
                    this.errors = null
                }

                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || this.$t('common.Fetch failed'))
            }
        },

        async exportToInventory(payload) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}export-to-inventory`,
                    payload
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || this.$t('common.Export failed'))
            }
        },

        // async checkExportProgress(jobId) {
        //     try {
        //         const { data } = await api.post(
        //             `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}export-to-inventory`,
        //             { job_id: jobId }
        //         )
        //         return data
        //     } catch (error) {
        //         throw new Error(error.response?.data?.message || error.response?.data?.progress?.message || this.$t('common.Progress check failed'))
        //     }
        // },


        // AFTER — preserves structured error payload
        async checkExportProgress(jobId) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}export-to-inventory`,
                    { job_id: jobId }
                )
                return data
            } catch (error) {
                throw error.response?.data || error
            }
        },

        // async updateCellData(formData) {
        //     try {
        //         const { data } = await api.post(
        //             `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}upload-data/update-cell-data`,
        //             formData,
        //             {
        //                 headers: {
        //                     'Content-Type': 'multipart/form-data'
        //                 }
        //             }
        //         )
        //         return data
        //     } catch (error) {
        //         throw error.response?.data || error
        //     }
        // },

        async updateCellData(formData) {
            try {
                // ✅ Append job_id from store state if not already present
                if (this.jobId && !formData.has('job_id')) {
                    formData.append('job_id', this.jobId)
                }

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}upload-data/update-cell-data`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                )
                return data
            } catch (error) {
                throw error.response?.data || error
            }
        },

        async deleteSelectedItems(ids) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}upload-data/multiple-delete`,
                    { ids }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || this.$t('common.Delete failed'))
            }
        },

        async getActiveJob() {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}get-active-job`,
                    {}
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || this.$t('common.Failed to check active job'))
            }
        },

        setUnitList(units) {
            this.unitList = Object.entries(units).map(([label, value]) => ({
                label,
                value
            }))
        },

        clearErrors() {
            this.errors = null
        },

        clearCellError(rowIndex, colIndex) {
            if (!this.errors?.grid_coordinates) return

            const paddedRow = String(rowIndex).padStart(2, '0')
            const paddedCol = String(colIndex).padStart(2, '0')
            const coordinate = `${paddedRow},${paddedCol}`

            const index = this.errors.grid_coordinates.indexOf(coordinate)
            if (index > -1) {
                this.errors.grid_coordinates.splice(index, 1)
                if (this.errors.messages && Array.isArray(this.errors.messages)) {
                    this.errors.messages.splice(index, 1)
                }
            }

            // Clear all errors if none remaining
            if (this.errors.grid_coordinates.length === 0) {
                this.errors = null
            }
        },

        setPageSize(size) {
            this.pageSize = size
        },

        setCurrentPage(page) {
            this.currentPage = page
        },

        setSearchQuery(query) {
            this.searchQuery = query
        },

        // Add new row to the beginning
        addNewRow(overrides = {}) {
            const newRow = buildDefaultRow(overrides)
            this.excelData.unshift(newRow)
            this.totalRecords++
        },

        buildUpdateCellFormData(row, rowIndex, colIndex) {
            return buildRowFormData({
                row,
                rowIndex,
                colIndex,
                jobId: this.jobId,
            })
        },

        // Update row after successful API call
        updateRowWithId(tempIndex, newId, rowData = {}) {
            const row = this.excelData.find(r => r.original_index === tempIndex)
            if (row) {
                Object.assign(row, normalizeRow({
                    ...row,
                    ...rowData,
                    id: newId,
                    isNew: false
                }))
            }
        },

        // Add this new action — polls until the job is complete, then returns data
        async fetchExcelDataSync(jobId, params = {}) {
            return new Promise((resolve, reject) => {
                const payload = {
                    job_id: jobId,
                    start: params.start || 0,
                    length: params.length || this.pageSize,
                    draw: params.draw || 1,
                    search: { value: this.searchQuery || '', regex: false }
                }

                const poll = async () => {
                    try {
                        const { data } = await api.post(
                            `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}preview/fetch/excel/data`,
                            payload
                        )

                        // Job still running — keep polling
                        if (data.progress?.percentage < 100 || data.progress?.success === null) {
                            setTimeout(poll, 800)
                            return
                        }

                        // Job done — update store and resolve
                        if (data.data) {
                            this.excelData = data.data.map(row => normalizeRow(row))
                            this.totalRecords = data.recordsTotal || data.recordsFiltered || 0
                        }

                        if (data.errors) {
                            this.errors = data.errors
                        } else if (!this.errors) {
                            this.errors = null
                        }

                        resolve(data)
                    } catch (error) {
                        reject(new Error(error.response?.data?.message || 'Fetch failed'))
                    }
                }

                poll()
            })
        },

        async addNewRowToDb(jobId) {
            const row = buildDefaultRow()

            const formData = buildRowFormData({
                row,
                rowIndex: -1,
                colIndex: 1,
                jobId,
            })

            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}upload-data/update-cell-data`,
                    formData,
                    { headers: { 'Content-Type': 'multipart/form-data' } }
                )

                const newRow = normalizeRow({
                    ...row,
                    ...data.data,
                    isNew: true
                })

                this.excelData.unshift(newRow)
                this.totalRecords++

                return data
            } catch (error) {
                throw error.response?.data || error
            }
        },
    }
})
