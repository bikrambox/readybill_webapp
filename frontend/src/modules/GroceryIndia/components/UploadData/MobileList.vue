<template>
  <div class="mobile-list">
    <div class="search-section p-3 mb-3 bg-light rounded">
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

      <div class="text-muted small mt-2 text-center">
        <i class="bi bi-info-circle me-1"></i>
        {{ $t("common.Tap on any field to edit") }}
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
      </div>
      <p class="text-muted mt-2 mb-0">{{ $t("common.Loading data") }}...</p>
    </div>

    <div v-else-if="items.length === 0" class="text-center py-5">
      <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
      <p class="text-muted">{{ $t("common.No data available") }}</p>
    </div>

    <div v-else class="mobile-cards">
      <div
        v-for="(row, rowIndex) in items"
        :key="row.id || row.original_index || rowIndex"
        class="card mb-3"
        :class="{ 'border-warning': row.isNew }"
      >
        <div class="card-body">
          <div class="d-flex align-items-start mb-3">
            <input
              type="checkbox"
              v-model="selectedRows"
              :value="row.id"
              class="form-check-input me-3 mt-1"
              :disabled="row.id === 0"
            />

            <div class="flex-grow-1">
              <label class="form-label text-muted small mb-1">
                {{ primaryColumn?.label || "Item" }}
              </label>

              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, primaryColumn?.index)"
                @click="handleFieldClick(row, row.original_index, primaryColumn)"
              >
                <template v-if="primaryColumn && primaryColumn.type === 'select'">
                  <div class="position-relative">
                    <select
                      class="form-select form-select-sm"
                      :value="normalizeValue(row[primaryColumn.field])"
                      :disabled="isCellUpdating(row.original_index, primaryColumn.index)"
                      @change="
                        handleSelectChange(
                          row,
                          row.original_index,
                          primaryColumn.index,
                          $event
                        )
                      "
                      @click.stop
                    >
                      <option value="">{{ getSelectPlaceholder(primaryColumn) }}</option>
                      <option
                        v-for="option in getOptionsForColumn(primaryColumn)"
                        :key="option.value"
                        :value="option.value"
                      >
                        {{ option.label }}
                      </option>
                    </select>
                    <span
                      v-if="isCellUpdating(row.original_index, primaryColumn.index)"
                      class="select-indicator"
                    >
                      <span class="spinner-border spinner-border-sm text-warning"></span>
                    </span>
                  </div>
                </template>

                <template v-else-if="primaryColumn">
                  <div
                    v-if="!isEditing(row.original_index, primaryColumn.index)"
                    class="field-display"
                  >
                    <strong>{{
                      formatCellValue(row[primaryColumn.field], primaryColumn)
                    }}</strong>
                    <span
                      v-if="isCellUpdating(row.original_index, primaryColumn.index)"
                      class="spinner-border spinner-border-sm text-warning ms-2"
                    ></span>
                    <i
                      v-if="isCellSuccess(row.original_index, primaryColumn.index)"
                      class="bi bi-check-circle-fill text-success ms-2"
                    ></i>
                    <i
                      v-if="isCellError(row.original_index, primaryColumn.index)"
                      class="bi bi-exclamation-circle-fill text-danger ms-2"
                    ></i>
                  </div>

                  <div v-else class="cell-edit" @click.stop>
                    <input
                      ref="setEditInputRef"
                      v-model="editValue"
                      :type="primaryColumn.type === 'number' ? 'number' : 'text'"
                      :step="primaryColumn.type === 'number' ? '0.01' : undefined"
                      class="form-control form-control-sm"
                      @blur="saveCell(row, row.original_index, primaryColumn.index)"
                      @keydown.enter.prevent="
                        saveCell(row, row.original_index, primaryColumn.index)
                      "
                      @keydown.esc.prevent="
                        cancelEdit(row, row.original_index, primaryColumn.index)
                      "
                    />
                  </div>
                </template>
              </div>
            </div>
          </div>

          <div class="row g-2">
            <div v-for="column in secondaryColumns" :key="column.index" class="col-6">
              <label class="form-label text-muted small mb-1">
                {{ column.label }}
              </label>

              <div
                class="editable-field"
                :class="[
                  getCellClasses(row.original_index, column.index),
                  { 'select-field': column.type === 'select' },
                ]"
                @click="handleFieldClick(row, row.original_index, column)"
              >
                <template v-if="column.type === 'select'">
                  <div class="position-relative">
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
                    class="field-display"
                  >
                    {{ formatCellValue(row[column.field], column) }}
                    <span
                      v-if="isCellUpdating(row.original_index, column.index)"
                      class="spinner-border spinner-border-sm text-warning ms-1"
                    ></span>
                    <i
                      v-if="isCellSuccess(row.original_index, column.index)"
                      class="bi bi-check-circle-fill text-success ms-1"
                    ></i>
                    <i
                      v-if="isCellError(row.original_index, column.index)"
                      class="bi bi-exclamation-circle-fill text-danger ms-1"
                    ></i>
                  </div>

                  <div v-else class="cell-edit" @click.stop>
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
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mobile-pagination mt-3 p-3 bg-light rounded">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">{{ $t("common.Rows") }}</span>
            <select
              v-model="localPageSize"
              @change="handlePageSizeChange"
              class="form-select form-select-sm"
              style="width: 65px"
            >
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted text-nowrap">{{ displayRange }}</span>
            <div class="btn-group" role="group">
              <button
                class="btn btn-sm btn-outline-secondary"
                @click="goToPage(currentLocalPage - 1)"
                :disabled="currentLocalPage === 1"
                type="button"
              >
                <i class="bi bi-chevron-left"></i>
              </button>
              <button
                class="btn btn-sm btn-outline-secondary"
                @click="goToPage(currentLocalPage + 1)"
                :disabled="currentLocalPage === totalPages"
                type="button"
              >
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>

        <div v-if="hasErrors" class="text-danger text-center small">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          {{ errorCount }} {{ $t("common.errors found") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from "vue";
import { storeToRefs } from "pinia";
import { useUploadDataStore } from "@/modules/GroceryIndia/stores/uploadDataStore";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { useConfigStore } from "@/stores/config";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  unitList: {
    type: Array,
    default: () => [],
  },
  currentPage: {
    type: Number,
    default: 1,
  },
  pageSize: {
    type: Number,
    default: 10,
  },
  totalRecords: {
    type: Number,
    default: 0,
  },
});

const emit = defineEmits([
  "selection-change",
  "page-change",
  "page-size-change",
  "validation-error",
]);

const uploadDataStore = useUploadDataStore();
const configStore = useConfigStore();
const preferencesStore = useUserPreferencesStore();
const userDetailsStore = useUserDetailsStore();

const { unitsArray } = storeToRefs(configStore);
const { preferences } = storeToRefs(preferencesStore);
const { item_categories } = storeToRefs(userDetailsStore);

const selectedRows = ref([]);
const searchQuery = ref("");
const localPageSize = ref(props.pageSize);
const currentLocalPage = ref(props.currentPage);

const editingCell = ref({ rowIndex: null, colIndex: null });
const editInputRef = ref(null);
const editValue = ref(null);
const originalValue = ref(null);

const updatingCells = ref(new Set());
const successCells = ref(new Set());
const errorCells = ref(new Set());

let searchTimeout = null;

const datasetColumns = computed(() =>
  props.columns?.length ? props.columns : uploadDataStore.datasetColumns || []
);

const visibleColumns = computed(() =>
  datasetColumns.value.filter((col) => col.index !== 0)
);

const primaryColumn = computed(() => visibleColumns.value[0] || null);

const secondaryColumns = computed(() => visibleColumns.value.slice(1));

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

const errorData = computed(() => uploadDataStore.errors);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(props.totalRecords / localPageSize.value))
);

const displayRange = computed(() => {
  if (props.totalRecords === 0) return "0-0 of 0";
  const start = (currentLocalPage.value - 1) * localPageSize.value + 1;
  const end = Math.min(currentLocalPage.value * localPageSize.value, props.totalRecords);
  return `${start}-${end} of ${props.totalRecords}`;
});

const hasErrors = computed(() => !!errorData.value?.grid_coordinates?.length);

const errorCount = computed(() => errorData.value?.grid_coordinates?.length || 0);

const unitOptions = computed(() => {
  if (unitsArray.value && unitsArray.value.length > 0) {
    return unitsArray.value;
  }
  return props.unitList || [];
});

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

const getCellKey = (rowIndex, colIndex) => `${rowIndex}-${colIndex}`;

const getColumnByIndex = (colIndex) =>
  datasetColumns.value.find((col) => col.index === colIndex);

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

const getCellClasses = (rowIndex, colIndex) => ({
  "field-updating": isCellUpdating(rowIndex, colIndex),
  "field-success": isCellSuccess(rowIndex, colIndex),
  "field-error": isCellError(rowIndex, colIndex),
});

const isEditing = (rowIndex, colIndex) =>
  editingCell.value.rowIndex === rowIndex && editingCell.value.colIndex === colIndex;

const setEditInputRef = (el) => {
  editInputRef.value = el;
};

const getFieldValue = (row, colIndex) => {
  const field = columnMap.value[colIndex];
  return field ? row[field] : null;
};

const setFieldValue = (row, colIndex, value) => {
  const field = columnMap.value[colIndex];
  if (field) {
    row[field] = value;
  }
};

const getOptionsForColumn = (column) => {
  if (column.field === "short_unit") return unitOptions.value;
  if (column.field === "category_id") return categoryOptions.value;
  return [];
};

const getSelectPlaceholder = (column) => {
  if (column.field === "short_unit") return t("common.Select Unit");
  if (column.field === "category_id") return t("common.Select Category");
  return `Select ${column.label}`;
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

const extractErrorMessages = (error) => {
  if (Array.isArray(error?.errors?.messages) && error.errors.messages.length > 0) {
    return error.errors.messages;
  }

  if (error?.errors && typeof error.errors === "object") {
    const messages = Object.values(error.errors)
      .flat()
      .filter(
        (msg) =>
          typeof msg === "string" && msg.trim() !== "" && !msg.startsWith("validation.")
      );
    if (messages.length > 0) return messages;
  }

  if (error?.message) return [error.message];

  return [t("common.Update failed. Please try again.")];
};

const applyStoreErrors = (error) => {
  if (error?.errors?.grid_coordinates?.length > 0) {
    const existing = uploadDataStore.errors || {
      grid_coordinates: [],
      messages: [],
    };

    uploadDataStore.errors = {
      grid_coordinates: [...existing.grid_coordinates, ...error.errors.grid_coordinates],
      messages: [...existing.messages, ...(error.errors.messages || [])],
    };
  }
};

const startEdit = async (row, rowIndex, colIndex) => {
  const column = getColumnByIndex(colIndex);
  if (!column?.editable || column.type === "select") return;

  originalValue.value = getFieldValue(row, colIndex);

  if (numericCols.value.has(colIndex)) {
    const num = parseFloat(originalValue.value ?? 0);
    editValue.value = Number.isNaN(num) ? "0.00" : num.toFixed(2);
  } else {
    editValue.value = originalValue.value ?? "";
  }

  editingCell.value = { rowIndex, colIndex };

  await nextTick();

  const input = Array.isArray(editInputRef.value)
    ? editInputRef.value[0]
    : editInputRef.value;
  input?.focus();
  input?.select?.();
};

const applyServerResponse = (row, result) => {
  if (!result?.data) return;

  Object.keys(result.data).forEach((key) => {
    if (key in row) {
      row[key] = result.data[key];
    }
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

  const oldValue = getFieldValue(row, colIndex);

  setFieldValue(row, colIndex, editValue.value);
  updatingCells.value.add(cellKey);
  errorCells.value.delete(cellKey);

  editingCell.value = { rowIndex: null, colIndex: null };
  editValue.value = null;
  originalValue.value = null;

  try {
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

    applyServerResponse(row, result);

    setTimeout(() => {
      successCells.value.delete(cellKey);
    }, 3000);
  } catch (error) {
    setFieldValue(row, colIndex, oldValue);
    updatingCells.value.delete(cellKey);

    applyStoreErrors(error);

    if (!error?.errors?.grid_coordinates?.length) {
      errorCells.value.add(cellKey);
      setTimeout(() => {
        errorCells.value.delete(cellKey);
      }, 5000);
    }

    emit("validation-error", extractErrorMessages(error));
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const cancelEdit = (row, rowIndex, colIndex) => {
  setFieldValue(row, colIndex, originalValue.value);
  editingCell.value = { rowIndex: null, colIndex: null };
  editValue.value = null;
  originalValue.value = null;
};

const handleSelectChange = async (row, rowIndex, colIndex, event) => {
  const cellKey = getCellKey(rowIndex, colIndex);
  const field = columnMap.value[colIndex];

  if (field) {
    row[field] = normalizeValue(event.target.value);
  }

  updatingCells.value.add(cellKey);
  errorCells.value.delete(cellKey);

  try {
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

    applyServerResponse(row, result);

    setTimeout(() => {
      successCells.value.delete(cellKey);
    }, 3000);
  } catch (error) {
    updatingCells.value.delete(cellKey);

    applyStoreErrors(error);

    if (!error?.errors?.grid_coordinates?.length) {
      errorCells.value.add(cellKey);
      setTimeout(() => {
        errorCells.value.delete(cellKey);
      }, 5000);
    }

    emit("validation-error", extractErrorMessages(error));
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const handleFieldClick = (row, rowIndex, column) => {
  if (!column?.editable) return;
  if (column.type === "select") return;
  startEdit(row, rowIndex, column.index);
};

const handleSearch = () => {
  clearTimeout(searchTimeout);

  searchTimeout = setTimeout(() => {
    uploadDataStore.setSearchQuery(searchQuery.value);
    currentLocalPage.value = 1;
    emit("page-change", 1);
  }, 500);
};

const clearSearch = () => {
  searchQuery.value = "";
  uploadDataStore.setSearchQuery("");
  currentLocalPage.value = 1;
  emit("page-change", 1);
};

const handlePageSizeChange = () => {
  emit("page-size-change", Number(localPageSize.value));
};

const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  currentLocalPage.value = page;
  emit("page-change", page);
};

watch(selectedRows, (newVal) => {
  emit("selection-change", newVal);
});

watch(
  () => props.currentPage,
  (newVal) => {
    currentLocalPage.value = newVal;
  }
);

watch(
  () => props.pageSize,
  (newVal) => {
    localPageSize.value = newVal;
  }
);

onMounted(async () => {
  if (!unitsArray.value || unitsArray.value.length === 0) {
    await configStore.fetchConfigData();
  }
});
</script>

<style scoped>
.mobile-list {
  padding: 0;
}

.mobile-cards .card {
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.mobile-cards .card.border-warning {
  border-width: 2px;
}

.editable-field {
  min-height: 38px;
  padding: 6px 10px;
  border-radius: 6px;
  background: #f8f9fa;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid transparent;
}

.editable-field:hover:not(.field-error):not(.field-updating) {
  background: #e7f3ff;
  border-color: #b6d4fe;
}

.editable-field:active {
  background: #e9ecef;
}

.select-field {
  padding: 4px 6px;
}

.field-display {
  display: flex;
  align-items: center;
  min-height: 24px;
  flex-wrap: wrap;
  gap: 4px;
  word-break: break-word;
}

.cell-edit input {
  width: 100%;
  border: 2px solid #0d6efd !important;
  outline: none;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.cell-edit input:focus {
  border-color: #0a58ca !important;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.field-updating {
  background-color: #fff3cd !important;
  border: 2px solid #ffc107 !important;
}

.field-success {
  background-color: #d1e7dd !important;
  border: 2px solid #198754 !important;
  animation: successPulse 0.5s ease;
}

.field-error {
  background-color: #f8d7da !important;
  border: 2px solid #dc3545 !important;
}

@keyframes successPulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.02);
  }
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
  width: 1.25em;
  height: 1.25em;
  cursor: pointer;
}

.form-check-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.mobile-pagination {
  border: 1px solid #dee2e6;
}

.btn-group .btn {
  padding: 0.25rem 0.5rem;
}
</style>
