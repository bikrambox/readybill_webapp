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

    // const fetchTransactions = async (params) => {
    //     loading.value = true
    //     try {
    //         const payload = {
    //             draw: params.draw || 1,
    //             start: params.start || 0,
    //             length: params.length || 100,
    //             date_from: params.date_from || '',
    //             date_to: params.date_to || '',
    //             search: {
    //                 value: params.search || '',
    //                 regex: false
    //             },
    //             order: [{
    //                 column: params.order?.[0]?.column || 4,
    //                 dir: params.order?.[0]?.dir || 'desc'
    //             }],
    //             columns: [
    //                 { data: 'invoice_number', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
    //                 { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
    //                 { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
    //                 { data: 'user_name', name: '', searchable: true, orderable: true, search: { value: '', regex: false } },
    //                 { data: '', name: '', searchable: true, orderable: true, search: { value: '', regex: false } }
    //             ]
    //         }

    //         const { data } = await api.post(
    //             `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}transaction-report`,
    //             payload
    //         )

    //         transactions.value = data.data || []
    //         totalRecords.value = data.recordsTotal || 0
    //         filteredRecords.value = data.recordsFiltered || 0
    //         return data
    //     } catch (error) {
    //         console.error('Failed to fetch transactions:', error)
    //         throw error
    //     } finally {
    //         loading.value = false
    //     }
    // }


    const fetchTransactions = async (params) => {
        loading.value = true
        try {
            // ✅ Check if params is FormData
            let requestData;

            if (params instanceof FormData) {
                requestData = params;
            } else {
                // Build payload as before
                requestData = {
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
            }

            const { data } = await api.post(
                `${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}transaction-report`,
                requestData
            )

            transactions.value = data.data || []
            totalRecords.value = data.recordsTotal || 0
            filteredRecords.value = data.recordsFiltered || 0
            return data
        } catch (error) {
            console.error('Failed to fetch transactions:', error)
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

    const clearTransactions = () => {
        transactions.value = []
    }


    return {
        loading,
        transactions,
        reports,
        totalRecords,
        filteredRecords,
        totalReports,
        filteredReports,
        fetchTransactions,
        fetchReports,
        requestReport,
        clearTransactions
    }
})
