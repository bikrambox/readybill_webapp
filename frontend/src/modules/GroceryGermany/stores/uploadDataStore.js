// src/stores/uploadDataStore.js
import { defineStore } from 'pinia'
import api from '@/config/api'

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
            (state.errors.grid_coordinates?.length > 0 || state.errors.messages?.length > 0)
    },

    actions: {
        async previewExcel(file) {
            const formData = new FormData()
            formData.append('file', file)

            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}preview/excel`,
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
                throw new Error(error.response?.data?.message || 'Upload failed')
            }
        },

        async checkUploadProgress(jobId) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}preview/excel`,
                    { job_id: jobId }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.progress?.message || 'Progress check failed')
            }
        },

        async fetchExcelDataProgress(jobId) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}preview/fetch/excel/data`,
                    { job_id: jobId }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || 'Fetch progress check failed')
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
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}preview/fetch/excel/data`,
                    payload
                )

                // Store the data with updated values
                if (data.data) {
                    this.excelData = data.data
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
                throw new Error(error.response?.data?.message || 'Fetch failed')
            }
        },

        async exportToInventory(payload) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}export-to-inventory`,
                    payload
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || 'Export failed')
            }
        },

        async checkExportProgress(jobId) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}export-to-inventory`,
                    { job_id: jobId }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || error.response?.data?.progress?.message || 'Progress check failed')
            }
        },

        async updateCellData(formData) {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}upload-data/update-cell-data`,
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
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}upload-data/multiple-delete`,
                    { ids }
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || 'Delete failed')
            }
        },

        async getActiveJob() {
            try {
                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}get-active-job`,
                    {}
                )
                return data
            } catch (error) {
                throw new Error(error.response?.data?.message || 'Failed to check active job')
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
        addNewRow() {
            const newRow = {
                id: 0, // ID 0 indicates new row
                item_name: 'New Item',
                quantity: 0,
                min_stock_alert: 0,
                mrp: 0,
                sale_price: 0,
                unit: '',
                barcode: '',
                gst: 0,
                cess: 0,
                original_index: -1, // Temporary index for new row
                isNew: true
            }

            this.excelData.unshift(newRow)
            this.totalRecords++
        },

        // Update row after successful API call
        updateRowWithId(tempIndex, newId, rowData) {
            const row = this.excelData.find(r => r.original_index === tempIndex)
            if (row) {
                row.id = newId
                row.original_index = rowData.original_index
                row.isNew = false
            }
        }
    }
})
