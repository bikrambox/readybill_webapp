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

    const fetchTransactions = async (params) => {
        loading.value = true
        try {
            const payload = {
                draw: params.draw || 1,
                start: params.start || 0,
                length: params.length || 100,
                date_from: params.date_from || '',
                date_to: params.date_to || '',
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
                `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}transaction-report`,
                payload
            )

            transactions.value = data.data || []
            totalRecords.value = data.recordsTotal || 0
            filteredRecords.value = data.recordsFiltered || 0
            return data
        } catch (err) {
            console.error('Failed to fetch transactions:', err)

            // Handle validation error response
            if (err.response?.data?.status === 'failed') {
                const errorData = err.response.data

                // Parse the escaped message
                let message = errorData.message || 'An error occurred'
                message = message.replace(/\\\\\\\\/g, '/')

                error.value = {
                    message: message,
                    data: errorData.data
                }
            } else {
                error.value = {
                    message: err.response?.data?.message || err.message || 'Failed to fetch transactions'
                }
            }

            // Clear transactions on error
            transactions.value = []
            totalRecords.value = 0
            filteredRecords.value = 0

            throw err
        }

        finally {
            loading.value = false
        }
    }

    const fetchTransactionDetails = async (transactionId) => {
        loading.value = true
        try {
            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}transaction/${transactionId}`
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
        loading.value = true
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
                `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}reports`,
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
            loading.value = false
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
                `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}request-report`,
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

    // ✅ NEW: Download Report Method
    // const downloadReport = async (reportId) => {
    //     loading.value = true
    //     try {
    //         const token = localStorage.getItem('token')

    //         // Step 1: Get download URL from API
    //         const { data } = await api.get(
    //             `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}reports/download/${reportId}`,
    //             {
    //                 headers: {
    //                     'Authorization': `Bearer ${token}`
    //                 }
    //             }
    //         )

    //         const temporaryUrl = data.url
    //         const fileName = data.filename || `transaction_report_${reportId}`
    //         const contentType = data.contentType || 'application/octet-stream'
    //         const apiSecretKey = data.api_secret_key

    //         // Determine file extension
    //         let extension = '.bin'
    //         if (contentType.includes('pdf')) extension = '.pdf'
    //         else if (contentType.includes('excel') || contentType.includes('spreadsheet')) extension = '.xlsx'
    //         else if (contentType.includes('csv')) extension = '.csv'

    //         const fullFileName = fileName.includes('.') ? fileName : fileName + extension

    //         console.log('📥 Downloading file:', { temporaryUrl, fullFileName, apiSecretKey })

    //         // Step 2: Download file using axios with blob responseType
    //         const response = await api.get(temporaryUrl, {
    //             responseType: 'blob',
    //             headers: {
    //                 'X-API-Secret': apiSecretKey
    //             }
    //         })

    //         // Create blob and download
    //         const blob = new Blob([response.data], { type: contentType })
    //         const blobUrl = URL.createObjectURL(blob)

    //         const link = document.createElement('a')
    //         link.href = blobUrl
    //         link.download = fullFileName
    //         link.style.display = 'none'
    //         document.body.appendChild(link)
    //         link.click()

    //         // Cleanup
    //         setTimeout(() => {
    //             document.body.removeChild(link)
    //             URL.revokeObjectURL(blobUrl)
    //         }, 100)

    //         console.log('✅ Download completed successfully')
    //         return { success: true, fileName: fullFileName }

    //     } catch (error) {
    //         console.error('❌ Failed to download report:', error)

    //         let errorMessage = '';
    //         if (error.response?.status === 404) {
    //             errorMessage = 'File not found. Please try again later.'
    //         } else if (error.response?.status === 401) {
    //             errorMessage = 'Unauthorized. Please log in again.'
    //         } 

    //         // else if (error.message && !error.message.includes('Network Error')) {
    //         //     errorMessage = error.message
    //         // }

    //         throw new Error(errorMessage)
    //     } finally {
    //         loading.value = false
    //     }
    // }


    const downloadReport = async (reportId) => {
        loading.value = true
        try {
            const token = localStorage.getItem('token')

            // Step 1: Get download URL and credentials from API
            const { data } = await api.get(
                `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}reports/download/${reportId}`,
                {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                }
            )

            const temporaryUrl = data.url
            const fileName = data.filename || `transaction_report_${reportId}`
            const contentType = data.contentType || 'application/octet-stream'
            const apiSecretKey = data.api_secret_key

            // Determine file extension
            let extension = '.bin'
            if (contentType.includes('pdf')) extension = '.pdf'
            else if (contentType.includes('excel') || contentType.includes('spreadsheet')) extension = '.xlsx'
            else if (contentType.includes('csv')) extension = '.csv'

            const fullFileName = fileName.includes('.') ? fileName : fileName + extension

            console.log('📥 Downloading file:', { temporaryUrl, fullFileName, apiSecretKey })

            // ✅ Step 2: Download using XMLHttpRequest with X-API-Secret header
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest()
                xhr.open('GET', temporaryUrl, true)
                xhr.responseType = 'blob'

                // ✅ IMPORTANT: Set the secret key header
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

                        // Cleanup
                        setTimeout(() => {
                            document.body.removeChild(link)
                            URL.revokeObjectURL(blobUrl)
                        }, 100)

                        console.log('✅ Download completed successfully')
                        loading.value = false
                        resolve({ success: true, fileName: fullFileName })
                    } else if (xhr.status === 204) {
                        // 204 No Content but file was downloaded
                        console.log('✅ Download completed (204 status)')
                        loading.value = false
                        resolve({ success: true, fileName: fullFileName })
                    } else {
                        console.error('❌ Download failed with status:', xhr.status)
                        loading.value = false
                        reject(new Error(`Download failed with status: ${xhr.status}`))
                    }
                }

                xhr.onerror = function () {
                    console.error('❌ Network error during download')
                    loading.value = false
                    reject(new Error('Network error during download'))
                }

                xhr.onloadend = function () {
                    // Check if response has error
                    if (xhr.status === 200 && xhr.response.type === 'application/json') {
                        // If response is JSON, it might be an error
                        const reader = new FileReader()
                        reader.onload = function () {
                            try {
                                const errorData = JSON.parse(reader.result)
                                if (errorData.error) {
                                    console.error('❌ API Error:', errorData.error)
                                    loading.value = false
                                    reject(new Error(errorData.error))
                                }
                            } catch (e) {
                                // Not JSON, continue normally
                            }
                        }
                        reader.readAsText(xhr.response)
                    }
                }

                xhr.send()
            })

        } catch (error) {
            // console.error('❌ Failed to download report:', error)

            // let errorMessage = 'An error occurred while downloading the file.'
            // if (error.response?.status === 404) {
            //     errorMessage = 'File not found. Please try again later.'
            // } else if (error.response?.status === 401) {
            //     errorMessage = 'Unauthorized. Please log in again.'
            // } else if (error.message) {
            //     errorMessage = error.message
            // }

            // loading.value = false
            // throw new Error(errorMessage)
        }
    }



    const clearTransactions = () => {
        transactions.value = []
    }

    const clearTransactionDetail = () => {
        transactionDetail.value = null
    }


    const clearError = () => {
        error.value = null
    }

    return {
        loading,
        transactions,
        reports,
        totalRecords,
        filteredRecords,
        totalReports,
        filteredReports,
        transactionDetail,
        error,
        fetchTransactions,
        fetchTransactionDetails,
        fetchReports,
        requestReport,
        downloadReport, // ✅ NEW
        clearTransactions,
        clearTransactionDetail,
        clearError
    }
})
