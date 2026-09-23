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
        unitList: []
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
                    draw: params.draw || 1
                }

                const { data } = await api.post(
                    `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}preview/fetch/excel/data`,
                    payload
                )

                // Store the data
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

        setPageSize(size) {
            this.pageSize = size
        },

        setCurrentPage(page) {
            this.currentPage = page
        }
    }
})
