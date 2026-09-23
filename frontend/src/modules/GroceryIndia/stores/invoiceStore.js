import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/config/api";

export const useInvoiceStore = defineStore("invoice", () => {
    const invoiceData = ref(null);
    const loading = ref(false);
    const error = ref(false);
    const errorMessage = ref("");
    const statusCode = ref(null);
    const currentToken = ref(null);

    const getInvoiceToken = async (billId) => {
        try {
            const res = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}generate/invoice/token`,
                { bill_id: billId }
            );

            if (res.data?.status === "success") {
                return res.data.token;
            }

            throw new Error("Failed to get invoice token");
        } catch (err) {
            error.value = true;
            errorMessage.value =
                err.response?.data?.message || err.message || "Failed to get invoice token";
            statusCode.value = 0;
            throw err;
        }
    };

    const fetchInvoice = async (token) => {
        if (!token || loading.value) return;
        if (invoiceData.value && currentToken.value === token) return invoiceData.value;

        loading.value = true;
        error.value = false;
        errorMessage.value = "";
        statusCode.value = null;
        invoiceData.value = null;

        try {
            const response = await api.post(
                `${import.meta.env.VITE_GROCERY_INDIA_PREFIX}generate/bill/pdf`,
                { token }
            );

            const res = response.data;
            statusCode.value = Number(res["status-code"]);

            if (statusCode.value === 2) {
                invoiceData.value = res.data;
                currentToken.value = token;
                return invoiceData.value;
            }

            error.value = true;

            if (statusCode.value === 0) {
                errorMessage.value = "Invalid invoice link";
            } else if (statusCode.value === 1) {
                errorMessage.value = "Invoice token expired";
            } else {
                errorMessage.value = res?.message || "Invoice not found";
            }

            return null;
        } catch (err) {
            const res = err.response?.data || {};
            statusCode.value = Number(res["status-code"] ?? 0);
            error.value = true;
            errorMessage.value =
                res?.message || err.message || "Failed to fetch invoice";
            invoiceData.value = null;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const resetInvoice = () => {
        invoiceData.value = null;
        loading.value = false;
        error.value = false;
        errorMessage.value = "";
        statusCode.value = null;
        currentToken.value = null;
    };

    return {
        invoiceData,
        loading,
        error,
        errorMessage,
        statusCode,
        fetchInvoice,
        getInvoiceToken,
        resetInvoice,
    };
});