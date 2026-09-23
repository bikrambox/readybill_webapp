import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/config/api'

export const useInvoiceStore = defineStore('invoice', () => {
    const invoiceData = ref(null)
    const loading = ref(false)
    const error = ref(null)

    const fetchInvoice = async (billId) => {
        loading.value = true
        error.value = null

        try {
            const response = await api.get(`${import.meta.env.VITE_GROCERY_GERMANY_PREFIX}generate/bill/pdf/${billId}`)

            // Check if response has the nested structure
            if (response.data && response.data.status === 'success') {
                invoiceData.value = response.data.data
            } else {
                invoiceData.value = response.data
            }

            return invoiceData.value
        } catch (err) {
            error.value = err.response?.data?.message || err.message || 'Failed to fetch invoice'
            console.error('Error fetching invoice:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const resetInvoice = () => {
        invoiceData.value = null
        error.value = null
        loading.value = false
    }

    return {
        invoiceData,
        loading,
        error,
        fetchInvoice,
        resetInvoice
    }
})
