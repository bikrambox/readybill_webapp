import { defineStore } from "pinia";
import { ref } from "vue";
import apiAgent from "@/config/apiAgent";

export const useAgentStore = defineStore("agent", () => {
    const agents = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const totalRecords = ref(0);
    const draw = ref(0);

    // DataTable params
    const start = ref(0);
    const length = ref(10);
    const searchValue = ref("");
    const filter_option = ref("name");
    const sortColumn = ref(0);
    const sortDir = ref("asc");

    async function fetchAgents(params = {}) {
        loading.value = true;
        error.value = null;

        try {
            draw.value++;

            // Build as FormData so Laravel receives it as proper POST body
            const formData = new FormData();
            formData.append("draw", draw.value);
            formData.append("start", params.start ?? start.value);
            formData.append("length", params.length ?? length.value);
            formData.append("order[0][column]", params.sortColumn ?? sortColumn.value);
            formData.append("order[0][dir]", params.sortDir ?? sortDir.value);
            formData.append("search[value]", params.searchValue ?? searchValue.value);
            formData.append("filter_option", params.filter_option ?? filter_option.value);

            const response = await apiAgent.post("/authorized-agent/list", formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });

            agents.value = response.data.data;
            totalRecords.value = response.data.recordsTotal;

        } catch (err) {
            error.value = err?.response?.data?.message ?? "Failed to fetch agents.";
        } finally {
            loading.value = false;
        }
    }

    function resetFilters() {
        searchValue.value = "";
        filter_option.value = "name";
        sortColumn.value = 0;
        sortDir.value = "asc";
        start.value = 0;
        fetchAgents();
    }

    return {
        agents,
        loading,
        error,
        totalRecords,
        start,
        length,
        searchValue,
        filter_option,
        sortColumn,
        sortDir,
        fetchAgents,
        resetFilters,
    };
});