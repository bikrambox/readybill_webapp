// stores/earningsStore.js
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import Cookies from "js-cookie";
import apiAgent from "@/config/apiAgent";

export const useEarningsStore = defineStore("earnings", () => {
    const items = ref([]);
    const loading = ref(false);
    const errors = ref([]);
    const successMessage = ref("");

    const recordsTotal = ref(0);
    const recordsFiltered = ref(0);
    const totalEarnings = ref(0); // ← from API (overall total across all filtered records)
    const currentPage = ref(0);
    const pageLength = ref(10);

    const searchQuery = ref("");
    const filterColumn = ref("shop_name");
    const shopSubsType = ref(null);

    const selectedItems = ref(new Set());
    const selectedCount = computed(() => selectedItems.value.size);

    const selectedCommission = ref(null);
    const detailLoading = ref(false);
    const detailError = ref(null);

    // ── Derived totals from current page items ────────────────────────────
    // Total paid = sum of agent_amount where payment_status === "paid"
    const totalPaid = computed(() =>
        items.value
            .filter((i) => i.payment_status === "paid")
            .reduce((sum, i) => sum + parseFloat(i.agent_amount || 0), 0)
    );

    // Total pending = sum of agent_amount where payment_status === "unpaid"
    const totalPending = computed(() =>
        items.value
            .filter((i) => i.payment_status === "unpaid")
            .reduce((sum, i) => sum + parseFloat(i.agent_amount || 0), 0)
    );

    // ── Formatters ────────────────────────────────────────────────────────
    const formatINR = (value) =>
        new Intl.NumberFormat("en-IN", {
            style: "currency",
            currency: "INR",
            minimumFractionDigits: 2,
        }).format(value);

    const formattedTotalEarnings = computed(() => formatINR(totalEarnings.value));
    const formattedTotalPaid = computed(() => formatINR(totalPaid.value));
    const formattedTotalPending = computed(() => formatINR(totalPending.value));

    // ── Fetch single commission detail ────────────────────────────────────
    const fetchCommissionDetail = async (subscriptionCommissionId) => {
        detailLoading.value = true;
        detailError.value = null;
        selectedCommission.value = null;
        try {
            const response = await apiAgent.get(
                `/authorized-agent/earning/agent-details/${subscriptionCommissionId}`
            );
            selectedCommission.value = response.data.data;
        } catch (err) {
            detailError.value =
                err?.response?.data?.message || "Failed to load commission details.";
        } finally {
            detailLoading.value = false;
        }
    };

    const clearCommissionDetail = () => {
        selectedCommission.value = null;
        detailError.value = null;
    };

    // ── Core fetch ────────────────────────────────────────────────────────
    const fetchEarnings = async (
        start = 0,
        length = 10,
        search = "",
        column = "shop_name",
        subsType = null
    ) => {
        loading.value = true;
        errors.value = [];
        try {
            const params = { start, length, search, filter_option: column };

            if (subsType) {
                params.shop_subs_type = subsType;
            }

            const response = await apiAgent.post("/authorized-agent/earnings", { params });
            const data = response.data;

            items.value = data.data;
            recordsTotal.value = data.recordsTotal;
            recordsFiltered.value = data.recordsFiltered;
            totalEarnings.value = data.totalEarnings ?? 0;
            // totalPaid and totalPending are auto-computed from items
        } catch (err) {
            errors.value = [
                err?.response?.data?.message || "Failed to load earnings.",
            ];
        } finally {
            loading.value = false;
        }
    };

    // ── Search ────────────────────────────────────────────────────────────
    const performSearch = async (search, column) => {
        searchQuery.value = search;
        filterColumn.value = column;
        currentPage.value = 0;
        await fetchEarnings(0, pageLength.value, search, column, shopSubsType.value);
    };

    // ── Platform filter ───────────────────────────────────────────────────
    const filterByPlatform = async (subsType) => {
        shopSubsType.value = subsType;
        currentPage.value = 0;
        await fetchEarnings(0, pageLength.value, searchQuery.value, filterColumn.value, subsType);
    };

    // ── Pagination ────────────────────────────────────────────────────────
    const changePage = async (page) => {
        currentPage.value = page;
        await fetchEarnings(
            page * pageLength.value,
            pageLength.value,
            searchQuery.value,
            filterColumn.value,
            shopSubsType.value
        );
    };

    const changePageLength = async (length) => {
        pageLength.value = length;
        currentPage.value = 0;
        await fetchEarnings(0, length, searchQuery.value, filterColumn.value, shopSubsType.value);
    };

    // ── Helpers ───────────────────────────────────────────────────────────
    const clearErrors = () => (errors.value = []);
    const clearSuccessMessage = () => (successMessage.value = "");
    const resetFilters = async () => {
        searchQuery.value = "";
        filterColumn.value = "shop_name";
        shopSubsType.value = null;
        currentPage.value = 0;
        await fetchEarnings(0, pageLength.value);
    };

    return {
        // state
        items,
        loading,
        errors,
        successMessage,
        recordsTotal,
        recordsFiltered,
        totalEarnings,
        currentPage,
        pageLength,
        searchQuery,
        filterColumn,
        shopSubsType,
        selectedItems,
        // computed
        selectedCount,
        totalPaid,
        totalPending,
        formattedTotalEarnings,
        formattedTotalPaid,
        formattedTotalPending,
        // actions
        fetchEarnings,
        performSearch,
        filterByPlatform,
        changePage,
        changePageLength,
        clearErrors,
        clearSuccessMessage,
        resetFilters,
        selectedCommission,
        detailLoading,
        detailError,
        fetchCommissionDetail,
        clearCommissionDetail,
    };
});