<template>
  <div class="table-wrapper">
    <div class="search-section p-3 border-bottom">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input
              type="text"
              class="form-control"
              :placeholder="$t('common.Search')"
              v-model="searchQuery"
              @input="handleSearch"
            />
            <button
              v-if="searchQuery"
              class="btn btn-outline-secondary"
              type="button"
              @click="clearSearch"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <div class="col-md-6 text-md-end mt-2 mt-md-0">
          <span class="text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            {{ $t("common.Click on any cell to edit") }}
          </span>
        </div>
      </div>
    </div>

    <div class="table-responsive" style="max-height: 500px; overflow-y: auto">
      <table class="table table-hover table-bordered mb-0" id="excelTable">
        <thead>
          <tr>
            <th style="width: 50px">
              <input
                type="checkbox"
                v-model="selectAll"
                @change="handleSelectAll"
                class="form-check-input"
              />
            </th>

            <th
              v-for="column in visibleColumns"
              :key="column.index"
              :style="{ minWidth: column.width || getColumnWidth(column) }"
            >
              {{ column.label }}
            </th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="loading">
            <td :colspan="visibleColumns.length + 1" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
              </div>
              <p class="text-muted mt-2 mb-0">{{ $t("common.Loading data") }}...</p>
            </td>
          </tr>

          <tr v-else-if="paginatedData.length === 0">
            <td :colspan="visibleColumns.length + 1" class="text-center py-4 text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              {{ $t("common.No data available") }}
            </td>
          </tr>

          <tr
            v-else
            v-for="(row, rowIndex) in paginatedData"
            :key="row.id || row.original_index || rowIndex"
            :data-id="row.id"
            :data-original-index="row.original_index"
            :class="{ 'table-warning': row.isNew }"
          >
            <td>
              <input
                type="checkbox"
                v-model="selectedRows"
                :value="row.id"
                class="form-check-input"
                :disabled="row.id === 0"
              />
            </td>

            <td
              v-for="column in visibleColumns"
              :key="column.index"
              class="editable-cell"
              :class="[
                getCellClasses(row.original_index, column.index),
                { 'select-cell': column.type === 'select' },
              ]"
              :title="getCellErrorMessage(row.original_index, column.index)"
              @click="handleCellClick(row, row.original_index, column)"
            >
              <template v-if="column.type === 'select'">
                <div class="cell-display position-relative">
                  <select
                    class="form-select form-select-sm"
                    :class="{
                      'border-danger': isCellError(row.original_index, column.index),
                    }"
                    :value="normalizeValue(row[column.field])"
                    :disabled="isCellUpdating(row.original_index, column.index)"
                    @change="
                      handleSelectChange(row, row.original_index, column.index, $event)
                    "
                    @click.stop
                  >
                    <option value="">
                      {{ getSelectPlaceholder(column) }}
                    </option>
                    <option
                      v-for="option in getOptionsForColumn(column)"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>

                  <span
                    v-if="isCellUpdating(row.original_index, column.index)"
                    class="select-indicator"
                  >
                    <span class="spinner-border spinner-border-sm text-warning"></span>
                  </span>
                  <i
                    v-if="isCellSuccess(row.original_index, column.index)"
                    class="select-indicator bi bi-check-circle-fill text-success"
                  ></i>
                  <i
                    v-if="isCellError(row.original_index, column.index)"
                    class="select-indicator bi bi-exclamation-circle-fill text-danger"
                  ></i>
                </div>
              </template>

              <template v-else>
                <div
                  v-if="!isEditing(row.original_index, column.index)"
                  class="cell-display"
                >
                  <span class="cell-value">
                    {{ formatCellValue(row[column.field], column) }}
                  </span>

                  <span
                    v-if="isCellUpdating(row.original_index, column.index)"
                    class="spinner-border spinner-border-sm text-warning ms-2"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, column.index)"
                    class="bi bi-check-circle-fill text-success ms-2"
                  ></i>
                  <i
                    v-if="isCellError(row.original_index, column.index)"
                    class="bi bi-exclamation-circle-fill text-danger ms-2"
                  ></i>
                </div>

                <div
                  v-show="isEditing(row.original_index, column.index)"
                  class="cell-edit"
                  @click.stop
                >
                  <input
                    ref="setEditInputRef"
                    v-model="editValue"
                    :type="column.type === 'number' ? 'number' : 'text'"
                    :step="column.type === 'number' ? '0.01' : undefined"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, column.index)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, column.index)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, column.index)
                    "
                  />
                </div>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      class="pagination-footer p-3 border-top d-flex justify-content-between align-items-center"
    >
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">{{ $t("common.Rows per page") }}</span>
        <select
          v-model="localPageSize"
          @change="handlePageSizeChange"
          class="form-select form-select-sm pagination-select"
        >
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>

      <div class="d-flex align-items-center gap-3">
        <span class="text-muted small pagination-info">{{ paginationText }}</span>

        <div class="pagination-controls d-flex align-items-center gap-1">
          <button
            class="btn btn-sm btn-icon"
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1 || loading"
            type="button"
          >
            <i class="bi bi-chevron-left"></i>
          </button>

          <button
            class="btn btn-sm btn-icon"
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages || loading"
            type="button"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from "vue";
import { storeToRefs } from "pinia";
import { useUploadDataStore } from "@/modules/GroceryIndia/stores/uploadDataStore";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { useConfigStore } from "@/stores/config";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  jobId: {
    type: String,
    required: true,
  },
  pageSize: {
    type: Number,
    default: 100,
  },
  columns: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["selection-change", "refresh", "cell-error"]);

const uploadDataStore = useUploadDataStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();
const userDetailsStore = useUserDetailsStore();

const { unitsArray } = storeToRefs(configStore);
const { preferences } = storeToRefs(preferencesStore);
const { item_categories } = storeToRefs(userDetailsStore);

const selectAll = ref(false);
const selectedRows = ref([]);
const currentPage = ref(1);
const loading = ref(false);
const searchQuery = ref("");
const localPageSize = ref(props.pageSize);
let searchTimeout = null;

const editingCell = ref({ rowIndex: null, colIndex: null });
const editInputRef = ref(null);
const editValue = ref(null);
const originalValue = ref(null);

const updatingCells = ref(new Set());
const successCells = ref(new Set());
const errorCells = ref(new Set());

const tableData = computed(() => uploadDataStore.excelData);
const totalRecords = computed(() => uploadDataStore.totalRecords);
const errorData = computed(() => uploadDataStore.errors);

const datasetColumns = computed(() =>
  props.columns?.length ? props.columns : uploadDataStore.datasetColumns || []
);

const visibleColumns = computed(() =>
  datasetColumns.value.filter((col) => col.index !== 0)
);

const columnMap = computed(() =>
  Object.fromEntries(
    datasetColumns.value.filter((col) => col.field).map((col) => [col.index, col.field])
  )
);

const numericCols = computed(
  () =>
    new Set(
      datasetColumns.value
        .filter((col) => col.type === "number" && col.field)
        .map((col) => col.index)
    )
);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(totalRecords.value / localPageSize.value))
);

const startRecord = computed(() => {
  if (totalRecords.value === 0) return 0;
  return (currentPage.value - 1) * localPageSize.value + 1;
});

const endRecord = computed(() =>
  Math.min(currentPage.value * localPageSize.value, totalRecords.value)
);

const paginationText = computed(() => {
  if (totalRecords.value === 0) return "0-0 of 0";
  return `${startRecord.value}-${endRecord.value} of ${totalRecords.value}`;
});

const paginatedData = computed(() => tableData.value);

const unitList = computed(() => {
  if (unitsArray.value && unitsArray.value.length > 0) return unitsArray.value;
  return uploadDataStore.unitList || [];
});

// ─── CATEGORY FIX ────────────────────────────────────────────────────────────

const normalizeValue = (val) => {
  if (val === null || val === undefined || val === "") return "";
  return String(val).trim();
};

const categoryOptions = computed(() => {
  const raw = Array.isArray(item_categories.value) ? item_categories.value : [];

  return raw
    .map((category) => {
      if (category && typeof category === "object") {
        return {
          value: normalizeValue(
            category.value ?? category.id ?? category.category_id ?? category.name
          ),
          label: String(
            category.label ??
              category.name ??
              category.title ??
              category.value ??
              category.id ??
              ""
          ).trim(),
        };
      }

      return {
        value: normalizeValue(category),
        label: String(category ?? "").trim(),
      };
    })
    .filter((item) => item.value !== "" || item.label !== "");
});

const categoryMap = computed(() => {
  return categoryOptions.value.reduce((acc, item) => {
    acc[item.value] = item.label;
    return acc;
  }, {});
});

const getCategoryLabel = (val) => {
  const normalized = normalizeValue(val);
  return categoryMap.value[normalized] || normalized || "-";
};

// ─────────────────────────────────────────────────────────────────────────────

const getColumnByIndex = (colIndex) =>
  datasetColumns.value.find((col) => col.index === colIndex);

const getCellKey = (rowIndex, colIndex) => `${rowIndex}-${colIndex}`;

const isCellUpdating = (rowIndex, colIndex) =>
  updatingCells.value.has(getCellKey(rowIndex, colIndex));

const isCellSuccess = (rowIndex, colIndex) =>
  successCells.value.has(getCellKey(rowIndex, colIndex));

const isCellError = (rowIndex, colIndex) => {
  if (errorCells.value.has(getCellKey(rowIndex, colIndex))) return true;
  if (!errorData.value?.grid_coordinates) return false;

  const paddedRow = String(rowIndex).padStart(2, "0");
  const paddedCol = String(colIndex).padStart(2, "0");
  return errorData.value.grid_coordinates.includes(`${paddedRow},${paddedCol}`);
};

const getCellErrorMessage = (rowIndex, colIndex) => {
  if (!errorData.value?.grid_coordinates || !errorData.value?.messages) return "";

  const paddedRow = String(rowIndex).padStart(2, "0");
  const paddedCol = String(colIndex).padStart(2, "0");
  const index = errorData.value.grid_coordinates.indexOf(`${paddedRow},${paddedCol}`);

  if (index === -1) return "";
  return errorData.value.messages[index] ?? errorData.value.messages[0] ?? "";
};

const getCellClasses = (rowIndex, colIndex) => ({
  "cell-updating": isCellUpdating(rowIndex, colIndex),
  "cell-success": isCellSuccess(rowIndex, colIndex),
  "cell-error": isCellError(rowIndex, colIndex),
});

const getFieldValue = (row, colIndex) => {
  const field = columnMap.value[colIndex];
  return field ? row[field] : null;
};

const setFieldValue = (row, colIndex, value) => {
  const field = columnMap.value[colIndex];
  if (field) row[field] = value;
};

const isEditing = (rowIndex, colIndex) =>
  editingCell.value.rowIndex === rowIndex && editingCell.value.colIndex === colIndex;

const setEditInputRef = (el) => {
  editInputRef.value = el;
};

const formatCellValue = (value, column) => {
  if (column.type === "number") {
    const num = parseFloat(value ?? 0);
    return Number.isNaN(num) ? "0.00" : num.toFixed(2);
  }

  if (column.type === "select") {
    const options = getOptionsForColumn(column);
    const matched = options.find(
      (option) => normalizeValue(option.value) === normalizeValue(value)
    );
    return matched?.label || value || "-";
  }

  return value ?? "-";
};

const getColumnWidth = (column) => {
  if (column.type === "number") return "120px";
  if (column.type === "select") return "150px";
  return "160px";
};

const getOptionsForColumn = (column) => {
  if (column.field === "short_unit") return unitList.value;
  if (column.field === "category_id") return categoryOptions.value;
  return [];
};

const getSelectPlaceholder = (column) => {
  if (column.field === "short_unit") return t("common.Select Unit");
  if (column.field === "category_id") return t("common.Select Category");
  return `Select ${column.label}`;
};

const extractErrorMessages = (error) => {
  const messages =
    error?.errors?.messages ||
    error?.response?.data?.errors?.messages ||
    error?.messages ||
    [];

  if (Array.isArray(messages) && messages.length > 0) {
    return messages;
  }

  if (typeof error?.message === "string" && error.message.trim()) {
    return [error.message];
  }

  return [t("common.Update failed")];
};

const injectCellError = (apiError) => {
  if (!apiError?.grid_coordinates?.length) return false;

  if (!uploadDataStore.errors) {
    uploadDataStore.errors = { grid_coordinates: [], messages: [] };
  }

  apiError.grid_coordinates.forEach((coord, i) => {
    const existingIndex = uploadDataStore.errors.grid_coordinates.indexOf(coord);

    if (existingIndex === -1) {
      uploadDataStore.errors.grid_coordinates.push(coord);
      uploadDataStore.errors.messages.push(apiError.messages?.[i] ?? "Invalid value");
    } else {
      uploadDataStore.errors.messages[existingIndex] =
        apiError.messages?.[i] ?? "Invalid value";
    }
  });

  emit("cell-error", apiError.messages ?? []);
  return true;
};

const startEdit = async (row, rowIndex, colIndex) => {
  const column = getColumnByIndex(colIndex);
  if (!column?.editable || column.type === "select") return;

  const rawValue = getFieldValue(row, colIndex);
  originalValue.value = rawValue;

  if (numericCols.value.has(colIndex)) {
    const num = parseFloat(rawValue ?? 0);
    editValue.value = Number.isNaN(num) ? "0.00" : num.toFixed(2);
  } else {
    editValue.value = rawValue ?? "";
  }

  editingCell.value = { rowIndex, colIndex };

  await nextTick();
  const input = Array.isArray(editInputRef.value)
    ? editInputRef.value[0]
    : editInputRef.value;
  input?.focus();
  input?.select?.();
};

const applyServerData = (row, result) => {
  if (!result?.data) return;
  Object.keys(result.data).forEach((key) => {
    if (key in row) row[key] = result.data[key];
  });
};

const saveCell = async (row, rowIndex, colIndex) => {
  const cellKey = getCellKey(rowIndex, colIndex);

  if (editValue.value === originalValue.value) {
    editingCell.value = { rowIndex: null, colIndex: null };
    editValue.value = null;
    originalValue.value = null;
    return;
  }

  try {
    setFieldValue(row, colIndex, editValue.value);

    updatingCells.value.add(cellKey);
    errorCells.value.delete(cellKey);

    const formData = uploadDataStore.buildUpdateCellFormData(row, rowIndex, colIndex);
    const result = await uploadDataStore.updateCellData(formData);

    updatingCells.value.delete(cellKey);
    successCells.value.add(cellKey);

    editingCell.value = { rowIndex: null, colIndex: null };
    editValue.value = null;
    originalValue.value = null;

    uploadDataStore.clearCellError(rowIndex, colIndex);

    if (row.id === 0 && result.data?.id) {
      row.id = result.data.id;
      row.original_index = result.data.original_index ?? rowIndex;
      row.isNew = false;
    }

    applyServerData(row, result);

    setTimeout(() => successCells.value.delete(cellKey), 3000);
  } catch (error) {
    setFieldValue(row, colIndex, originalValue.value);

    updatingCells.value.delete(cellKey);
    editingCell.value = { rowIndex: null, colIndex: null };
    editValue.value = null;
    originalValue.value = null;

    const apiError = error?.errors ?? error?.response?.data?.errors ?? null;
    const injected = injectCellError(apiError);

    if (!injected) {
      errorCells.value.add(cellKey);
      setTimeout(() => errorCells.value.delete(cellKey), 5000);
    }

    emit("cell-error", extractErrorMessages(error));
  }
};

const cancelEdit = (row, rowIndex, colIndex) => {
  setFieldValue(row, colIndex, originalValue.value);
  editingCell.value = { rowIndex: null, colIndex: null };
  editValue.value = null;
  originalValue.value = null;
};

// ─── SELECT CHANGE — now receives $event to apply value before API call ───────

const handleSelectChange = async (row, rowIndex, colIndex, event) => {
  const cellKey = getCellKey(rowIndex, colIndex);
  const field = columnMap.value[colIndex];

  if (field) {
    row[field] = normalizeValue(event.target.value);
  }

  try {
    updatingCells.value.add(cellKey);
    errorCells.value.delete(cellKey);

    const formData = uploadDataStore.buildUpdateCellFormData(row, rowIndex, colIndex);
    const result = await uploadDataStore.updateCellData(formData);

    updatingCells.value.delete(cellKey);
    successCells.value.add(cellKey);

    uploadDataStore.clearCellError(rowIndex, colIndex);

    if (row.id === 0 && result.data?.id) {
      row.id = result.data.id;
      row.original_index = result.data.original_index ?? rowIndex;
      row.isNew = false;
    }

    applyServerData(row, result);

    setTimeout(() => successCells.value.delete(cellKey), 3000);
  } catch (error) {
    updatingCells.value.delete(cellKey);

    const apiError = error?.errors ?? error?.response?.data?.errors ?? null;
    const injected = injectCellError(apiError);

    if (!injected) {
      errorCells.value.add(cellKey);
      setTimeout(() => errorCells.value.delete(cellKey), 5000);
    }

    emit("cell-error", extractErrorMessages(error));
  }
};

// ─────────────────────────────────────────────────────────────────────────────

const handleCellClick = (row, rowIndex, column) => {
  if (!column?.editable) return;
  if (column.type === "select") return;
  startEdit(row, rowIndex, column.index);
};

const handleSelectAll = () => {
  if (selectAll.value) {
    selectedRows.value = tableData.value
      .filter((row) => row.id !== 0)
      .map((row) => row.id);
  } else {
    selectedRows.value = [];
  }
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    uploadDataStore.setSearchQuery(searchQuery.value);
    currentPage.value = 1;
    fetchData();
  }, 500);
};

const clearSearch = () => {
  searchQuery.value = "";
  uploadDataStore.setSearchQuery("");
  currentPage.value = 1;
  fetchData();
};

const handlePageSizeChange = () => {
  uploadDataStore.setPageSize(Number(localPageSize.value));
  currentPage.value = 1;
  fetchData();
};

const goToPage = async (page) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  await fetchData();
};

const fetchData = async () => {
  try {
    loading.value = true;
    const start = (currentPage.value - 1) * localPageSize.value;

    await uploadDataStore.fetchExcelDataSync(props.jobId, {
      start,
      length: Number(localPageSize.value),
      draw: currentPage.value,
    });
  } catch (error) {
    emit("cell-error", extractErrorMessages(error));
  } finally {
    loading.value = false;
  }
};

watch(selectedRows, (newVal) => {
  emit("selection-change", newVal);

  const nonNewRows = tableData.value.filter((row) => row.id !== 0);
  selectAll.value = newVal.length === nonNewRows.length && nonNewRows.length > 0;
});

watch(
  () => props.pageSize,
  (newVal) => {
    localPageSize.value = newVal;
  }
);

watch(
  () => props.jobId,
  () => {
    currentPage.value = 1;
    selectedRows.value = [];
    selectAll.value = false;
    searchQuery.value = "";
    uploadDataStore.setSearchQuery("");
    fetchData();
  },
  { immediate: false }
);

const handleClickOutside = (event) => {
  if (editingCell.value.rowIndex === null) return;

  const clickedInsideEdit = event.target.closest(".cell-edit");
  if (clickedInsideEdit) return;

  const row = tableData.value.find(
    (r) => r.original_index === editingCell.value.rowIndex
  );

  if (row) {
    saveCell(row, editingCell.value.rowIndex, editingCell.value.colIndex);
  } else {
    editingCell.value = { rowIndex: null, colIndex: null };
    editValue.value = null;
    originalValue.value = null;
  }
};

onMounted(async () => {
  if (!unitsArray.value || unitsArray.value.length === 0) {
    await configStore.fetchConfigData();
  }

  fetchData();
  document.addEventListener("mousedown", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("mousedown", handleClickOutside);
  if (searchTimeout) clearTimeout(searchTimeout);
});
</script>

<style scoped>
.table-wrapper {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
}

.search-section {
  background: #f8f9fa;
}

.pagination-footer {
  background: #fff;
  border-top: 1px solid #dee2e6;
}

.pagination-select {
  width: 70px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 0.875rem;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6c757d;
}

.pagination-controls {
  display: flex;
  gap: 4px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dee2e6;
  background: #fff;
  color: #6c757d;
  border-radius: 4px;
  transition: all 0.2s;
}

.btn-icon:hover:not(:disabled) {
  background: #f8f9fa;
  color: #000;
  border-color: #adb5bd;
}

.btn-icon:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.table-responsive {
  border-radius: 0;
}

table {
  margin-bottom: 0;
}

thead th {
  background: #343a40;
  color: white;
  position: sticky;
  top: 0;
  z-index: 10;
  font-weight: 600;
  border: 1px solid #454d55 !important;
  vertical-align: middle;
}

tbody td {
  vertical-align: middle;
  border: 1px solid #dee2e6 !important;
}

tbody tr:hover {
  background-color: #f8f9fa;
}

tbody tr.table-warning {
  background-color: #fff3cd !important;
}

.editable-cell {
  cursor: pointer;
  min-width: 80px;
  padding: 8px 12px;
  transition: all 0.2s ease;
  position: relative;
}

.editable-cell:hover:not(.cell-error):not(.cell-updating) {
  background-color: #e7f3ff;
}

.cell-display {
  min-height: 20px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.cell-value {
  flex: 1;
}

.cell-edit {
  width: 100%;
}

.cell-edit input,
.cell-edit select {
  width: 100%;
  border: 2px solid #0d6efd !important;
  outline: none;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.cell-edit input:focus,
.cell-edit select:focus {
  border-color: #0a58ca !important;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.cell-updating {
  background-color: #fff3cd !important;
  border-left: 3px solid #ffc107 !important;
}

.cell-success {
  background-color: #d1e7dd !important;
  border-left: 3px solid #198754 !important;
  animation: successPulse 0.5s ease;
}

.cell-error {
  background-color: #f8d7da !important;
  border-left: 3px solid #dc3545 !important;
}

@keyframes successPulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.01);
  }
}

.select-cell .form-select {
  cursor: pointer;
  padding-right: 2.5rem !important;
}

.select-cell .form-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.select-indicator {
  position: absolute;
  top: 50%;
  right: 32px;
  transform: translateY(-50%);
  z-index: 5;
  pointer-events: none;
}

.form-check-input {
  cursor: pointer;
  width: 1.2em;
  height: 1.2em;
}

.form-check-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
