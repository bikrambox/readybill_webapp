// stores/report.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/config/api'

export const useReportStore = defineStore('report', () => {
    const loading = ref(false)
    const transactions = ref([])
    const reports = ref([])
    const totalRecords = ref(0)
    const filteredRecords = ref(0)
    const totalReports = ref(0)
    const filteredReports = ref(0)
    const transactionDetail = ref(null)
    const error = ref(null)
    const monthlyTotals = ref([]) // ✅

    const loadingReports = ref(false)
    const loadingTransactions = ref(false)

    // ✅ request version guard
    const latestTransactionRequestId = ref(0)

    // ✅ Helper - compute monthly totals from transaction list
    const computeMonthlyTotals = (transactionList) => {
        const distribution = {}

        transactionList.forEach((transaction) => {
            if (transaction?.created_at) {
                const date = new Date(transaction.created_at)
                if (!isNaN(date.getTime())) {
                    const monthKey = date.toLocaleDateString('en-IN', {
                        year: 'numeric',
                        month: 'short',
                    })
                    if (!distribution[monthKey]) {
                        distribution[monthKey] = { month: monthKey, total: 0 }
                    }
                    distribution[monthKey].total += parseFloat(transaction.total_price || 0)
                }
            }
        })

        return Object.values(distribution).sort((a, b) => {
            const aDate = new Date(a.month.split(' ')[1] + '-' + a.month.split(' ')[0])
            const bDate = new Date(b.month.split(' ')[1] + '-' + b.month.split(' ')[0])
            return bDate - aDate
        })
    }

    const fetchTransactions = async (params) => {
        const requestId = ++latestTransactionRequestId.value
        loadingTransactions.value = true
        error.value = null

        try {
            const payload = {
                draw: params.draw || 1,
                start: params.start || 0,
                length: params.length || 100,
                date_from: params.date_from || '',
                date_to: params.date_to || '',
                filter_option: params.filter_option || 'invoice_number',
                search: {
                    value: params.search || '',
                    regex: false
                },
                order: [{
                    column: params.order?.[0]?.column || 4,
                    dir: params.order?.[0]?.dir || 'desc'
                }],
                columns: [
                    { data: 'invoice_number', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'user_name', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } }
                ]
            }

            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}transaction-report`,
                payload
            )

            // ✅ Ignore stale response
            if (requestId !== latestTransactionRequestId.value) {
                return data
            }

            transactions.value = data.data || []
            totalRecords.value = data.recordsTotal || 0
            filteredRecords.value = data.recordsFiltered || 0

            if (data.monthly_distribution && data.monthly_distribution.length > 0) {
                monthlyTotals.value = data.monthly_distribution
            } else {
                monthlyTotals.value = computeMonthlyTotals(data.data || [])
            }

            return data
        } catch (err) {
            // ✅ Ignore stale failed response too
            if (requestId !== latestTransactionRequestId.value) {
                return
            }

            console.error('Failed to fetch transactions:', err)

            if (err.response?.data?.status === 'failed') {
                const errorData = err.response.data
                let message = errorData.message || 'An error occurred'
                message = message.replace(/\\\\/g, '/')
                error.value = {
                    message,
                    data: errorData.data
                }
            } else {
                error.value = {
                    message: err.response?.data?.message || err.message || 'Failed to fetch transactions'
                }
            }

            transactions.value = []
            totalRecords.value = 0
            filteredRecords.value = 0
            monthlyTotals.value = []

            throw err
        } finally {
            // ✅ only latest request controls loading
            if (requestId === latestTransactionRequestId.value) {
                loadingTransactions.value = false
            }
        }
    }

    const fetchTransactionDetails = async (transactionId) => {
        loading.value = true
        try {
            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}transaction/${transactionId}`
            )
            transactionDetail.value = data.data || data
            return transactionDetail.value
        } catch (error) {
            console.error('Failed to fetch transaction details:', error)
            throw error
        } finally {
            loading.value = false
        }
    }

    const fetchReports = async (params = {}) => {
        // loading.value = true
        loadingReports.value = true
        try {
            const payload = {
                draw: 1,
                start: params.start || 0,
                length: params.length || 10,
                search: {
                    value: params.search || '',
                    regex: false
                },
                order: [{
                    column: params.order?.[0]?.column || 3,
                    dir: params.order?.[0]?.dir || 'desc'
                }],
                columns: [
                    { data: 'slno', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'report_type', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'date_range', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: 'created_at', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
                    { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } }
                ]
            }

            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}reports`,
                payload
            )

            reports.value = data.data || []
            totalReports.value = data.recordsTotal || 0
            filteredReports.value = data.recordsFiltered || 0
            return data
        } catch (error) {
            console.error('Failed to fetch reports:', error)
            throw error
        } finally {
            // loading.value = false
            loadingReports.value = false
        }
    }

    const requestReport = async (params) => {
        loading.value = true
        try {
            const payload = {
                date_from: params.date_from,
                date_to: params.date_to,
                report_type: params.report_type
            }
            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}request-report`,
                payload
            )
            return data
        } catch (error) {
            console.error('Failed to request report:', error)
            throw error
        } finally {
            loading.value = false
        }
    }

    const downloadReport = async (reportId) => {
        loading.value = true
        try {
            const token = localStorage.getItem('token')

            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}reports/download/${reportId}`,
                { headers: { 'Authorization': `Bearer ${token}` } }
            )

            const temporaryUrl = data.url
            const fileName = data.filename || `transaction_report_${reportId}`
            const contentType = data.contentType || 'application/octet-stream'
            const apiSecretKey = data.api_secret_key

            let extension = '.bin'
            if (contentType.includes('pdf')) extension = '.pdf'
            else if (contentType.includes('excel') || contentType.includes('spreadsheet')) extension = '.xlsx'
            else if (contentType.includes('csv')) extension = '.csv'

            const fullFileName = fileName.includes('.') ? fileName : fileName + extension

            console.log('📥 Downloading file:', { temporaryUrl, fullFileName, apiSecretKey })

            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest()
                xhr.open('GET', temporaryUrl, true)
                xhr.responseType = 'blob'

                if (apiSecretKey) {
                    xhr.setRequestHeader('X-API-Secret', apiSecretKey)
                }

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        const blob = xhr.response
                        const blobUrl = URL.createObjectURL(blob)
                        const link = document.createElement('a')
                        link.href = blobUrl
                        link.download = fullFileName
                        link.style.display = 'none'
                        document.body.appendChild(link)
                        link.click()
                        setTimeout(() => {
                            document.body.removeChild(link)
                            URL.revokeObjectURL(blobUrl)
                        }, 100)
                        console.log('✅ Download completed successfully')
                        loading.value = false
                        resolve({ success: true, fileName: fullFileName })
                    } else if (xhr.status === 204) {
                        console.log('✅ Download completed (204 status)')
                        loading.value = false
                        resolve({ success: true, fileName: fullFileName })
                    } else {
                        loading.value = false
                        reject(new Error(`Download failed with status: ${xhr.status}`))
                    }
                }

                xhr.onerror = function () {
                    loading.value = false
                    reject(new Error('Network error during download'))
                }

                xhr.onloadend = function () {
                    if (xhr.status === 200 && xhr.response.type === 'application/json') {
                        const reader = new FileReader()
                        reader.onload = function () {
                            try {
                                const errorData = JSON.parse(reader.result)
                                if (errorData.error) {
                                    loading.value = false
                                    reject(new Error(errorData.error))
                                }
                            } catch (e) { /* Not JSON */ }
                        }
                        reader.readAsText(xhr.response)
                    }
                }

                xhr.send()
            })
        } catch (error) {
            loading.value = false
            throw error
        }
    }

    const clearTransactions = () => {
        transactions.value = []
        monthlyTotals.value = []
        totalRecords.value = 0
        filteredRecords.value = 0
        error.value = null
    }

    const clearTransactionDetail = () => {
        transactionDetail.value = null
    }

    const clearError = () => {
        error.value = null
    }

    return {
        loading,
        loadingReports,
        loadingTransactions,
        transactions,
        reports,
        totalRecords,
        filteredRecords,
        totalReports,
        filteredReports,
        transactionDetail,
        error,
        monthlyTotals,
        fetchTransactions,
        fetchTransactionDetails,
        fetchReports,
        requestReport,
        downloadReport,
        clearTransactions,
        clearTransactionDetail,
        clearError
    }
})