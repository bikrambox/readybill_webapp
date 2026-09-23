<template>
  <div class="mobile-list">
    <!-- Search Bar -->
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

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
      </div>
      <p class="text-muted mt-2 mb-0">{{ $t("common.Loading data") }}...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="items.length === 0" class="text-center py-5">
      <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
      <p class="text-muted">{{ $t("common.No data available") }}</p>
    </div>

    <!-- Mobile Cards -->
    <div v-else class="mobile-cards">
      <div
        v-for="(row, rowIndex) in items"
        :key="row.id + '-' + rowIndex"
        class="card mb-3"
        :class="{ 'border-warning': row.isNew }"
      >
        <div class="card-body">
          <!-- Checkbox and Item Name -->
          <div class="d-flex align-items-start mb-3">
            <input
              type="checkbox"
              v-model="selectedRows"
              :value="row.id"
              class="form-check-input me-3 mt-1"
              :disabled="row.id === 0"
            />
            <div class="flex-grow-1">
              <label class="form-label text-muted small mb-1">{{
                $t("common.Item Name")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 1)"
                @click="startEdit(row, row.original_index, 1, 'item_name')"
              >
                <div
                  v-if="!isEditing(row.original_index, 1)"
                  class="field-display"
                >
                  <strong>{{ row.item_name }}</strong>
                  <span
                    v-if="isCellUpdating(row.original_index, 1)"
                    class="spinner-border spinner-border-sm text-warning ms-2"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 1)"
                    class="bi bi-check-circle-fill text-success ms-2"
                  ></i>
                  <i
                    v-if="isCellError(row.original_index, 1)"
                    class="bi bi-exclamation-circle-fill text-danger ms-2"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="text"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 1)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 1)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 1)
                    "
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Two Column Layout -->
          <div class="row g-2">
            <!-- Quantity -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.Quantity")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 2)"
                @click="startEdit(row, row.original_index, 2, 'quantity')"
              >
                <div
                  v-if="!isEditing(row.original_index, 2)"
                  class="field-display"
                >
                  {{ parseFloat(row.quantity).toFixed(2) }}
                  <span
                    v-if="isCellUpdating(row.original_index, 2)"
                    class="spinner-border spinner-border-sm text-warning ms-1"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 2)"
                    class="bi bi-check-circle-fill text-success ms-1"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 2)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 2)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 2)
                    "
                  />
                </div>
              </div>
            </div>

            <!-- Min Stock Alert -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.Min Stock Alert")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 3)"
                @click="
                  startEdit(row, row.original_index, 3, 'min_stock_alert')
                "
              >
                <div
                  v-if="!isEditing(row.original_index, 3)"
                  class="field-display"
                >
                  {{ parseFloat(row.min_stock_alert).toFixed(2) }}
                  <span
                    v-if="isCellUpdating(row.original_index, 3)"
                    class="spinner-border spinner-border-sm text-warning ms-1"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 3)"
                    class="bi bi-check-circle-fill text-success ms-1"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 3)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 3)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 3)
                    "
                  />
                </div>
              </div>
            </div>

            <!-- MRP -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.MRP")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 4)"
                @click="startEdit(row, row.original_index, 4, 'mrp')"
              >
                <div
                  v-if="!isEditing(row.original_index, 4)"
                  class="field-display"
                >
                  {{ $formatCurrency(parseFloat(row.mrp).toFixed(2)) }}
                  <span
                    v-if="isCellUpdating(row.original_index, 4)"
                    class="spinner-border spinner-border-sm text-warning ms-1"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 4)"
                    class="bi bi-check-circle-fill text-success ms-1"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 4)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 4)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 4)
                    "
                  />
                </div>
              </div>
            </div>

            <!-- Sale Price -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.Sale Price")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 5)"
                @click="startEdit(row, row.original_index, 5, 'sale_price')"
              >
                <div
                  v-if="!isEditing(row.original_index, 5)"
                  class="field-display"
                >
                  {{ $formatCurrency(parseFloat(row.sale_price).toFixed(2)) }}
                  <span
                    v-if="isCellUpdating(row.original_index, 5)"
                    class="spinner-border spinner-border-sm text-warning ms-1"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 5)"
                    class="bi bi-check-circle-fill text-success ms-1"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 5)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 5)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 5)
                    "
                  />
                </div>
              </div>
            </div>

            <!-- Unit -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.Unit")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 6)"
              >
                <select
                  v-model="row.unit"
                  class="form-select form-select-sm"
                  :disabled="isCellUpdating(row.original_index, 6)"
                  @change="handleSelectChange(row, row.original_index, 6)"
                >
                  <option value="">Select Unit</option>
                  <option
                    v-for="unit in unitList"
                    :key="unit.value"
                    :value="unit.value"
                  >
                    {{ unit.label }}
                  </option>
                </select>
              </div>
            </div>

            <!-- Barcode -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.Barcode")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 7)"
                @click="startEdit(row, row.original_index, 7, 'barcode')"
              >
                <div
                  v-if="!isEditing(row.original_index, 7)"
                  class="field-display"
                >
                  {{ row.barcode || "-" }}
                  <span
                    v-if="isCellUpdating(row.original_index, 7)"
                    class="spinner-border spinner-border-sm text-warning ms-1"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 7)"
                    class="bi bi-check-circle-fill text-success ms-1"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="text"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 7)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 7)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 7)
                    "
                  />
                </div>
              </div>
            </div>

            <!-- VAT -->
            <div class="col-6">
              <label class="form-label text-muted small mb-1">{{
                $t("common.VAT (%)")
              }}</label>
              <div
                class="editable-field"
                :class="getCellClasses(row.original_index, 8)"
                @click="startEdit(row, row.original_index, 8, 'vat')"
              >
                <div
                  v-if="!isEditing(row.original_index, 8)"
                  class="field-display"
                >
                  {{ parseFloat(row.vat).toFixed(2) }}%
                  <span
                    v-if="isCellUpdating(row.original_index, 8)"
                    class="spinner-border spinner-border-sm text-warning ms-1"
                  ></span>
                  <i
                    v-if="isCellSuccess(row.original_index, 8)"
                    class="bi bi-check-circle-fill text-success ms-1"
                  ></i>
                </div>
                <div v-else>
                  <input
                    :ref="(el) => setEditInputRef(el)"
                    v-model="editValue"
                    type="number"
                    step="0.01"
                    class="form-control form-control-sm"
                    @blur="saveCell(row, row.original_index, 8)"
                    @keydown.enter.prevent="
                      saveCell(row, row.original_index, 8)
                    "
                    @keydown.esc.prevent="
                      cancelEdit(row, row.original_index, 8)
                    "
                  />
                </div>
              </div>
            </div>



          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Pagination -->
    <div class="mobile-pagination mt-3 p-3 bg-light rounded">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted">{{ $t("common.Rows") }}:</span>
          <select
            v-model="localPageSize"
            @change="handlePageSizeChange"
            class="form-select form-select-sm"
            style="width: 65px"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <div class="d-flex align-items-center gap-2">
          <span class="small text-muted text-nowrap">{{ displayRange }}</span>
          <div class="btn-group" role="group">
            <button
              class="btn btn-sm btn-outline-secondary"
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              class="btn btn-sm btn-outline-secondary"
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage >= totalPages"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>

      <div v-if="hasErrors" class="text-danger text-center small">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        {{ errorCount }} error(s) found
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from "vue";
import { useUploadDataStore } from "@/modules/GroceryGermany/stores/uploadDataStore";
import { useConfigStore } from "@/stores/config";
import { storeToRefs } from "pinia";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  items: {
    type: Array,
    required: true,
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
]);

const uploadDataStore = useUploadDataStore();
const configStore = useConfigStore();
const { unitsArray } = storeToRefs(configStore);

const selectedRows = ref([]);
const searchQuery = ref("");
const localPageSize = ref(props.pageSize);
const currentPage = ref(props.currentPage);

// Editing state
const editingCell = ref({ rowIndex: null, colIndex: null });
const editInputRef = ref(null);
const editValue = ref(null);
const originalValue = ref(null);

// Cell state tracking
const updatingCells = ref(new Set());
const successCells = ref(new Set());
const errorCells = ref(new Set());

let searchTimeout = null;

const errorData = computed(() => uploadDataStore.errors);

const unitList = computed(() => {
  if (unitsArray.value && unitsArray.value.length > 0) {
    return unitsArray.value;
  }
  return props.unitList;
});

const totalPages = computed(() =>
  Math.ceil(props.totalRecords / localPageSize.value)
);

const displayRange = computed(() => {
  if (props.totalRecords === 0) return "0-0 of 0";
  const start = (currentPage.value - 1) * localPageSize.value + 1;
  const end = Math.min(
    currentPage.value * localPageSize.value,
    props.totalRecords
  );
  return `${start}-${end} of ${props.totalRecords}`;
});

const hasErrors = computed(() => {
  return (
    errorData.value?.grid_coordinates &&
    errorData.value.grid_coordinates.length > 0
  );
});

const errorCount = computed(() => {
  return errorData.value?.grid_coordinates?.length || 0;
});

// Cell state functions
const getCellKey = (rowIndex, colIndex) => `${rowIndex}-${colIndex}`;

const isCellUpdating = (rowIndex, colIndex) => {
  return updatingCells.value.has(getCellKey(rowIndex, colIndex));
};

const isCellSuccess = (rowIndex, colIndex) => {
  return successCells.value.has(getCellKey(rowIndex, colIndex));
};

const isCellError = (rowIndex, colIndex) => {
  if (!errorData.value?.grid_coordinates) return false;

  const paddedRow = String(rowIndex).padStart(2, "0");
  const paddedCol = String(colIndex).padStart(2, "0");
  const coordinate = `${paddedRow},${paddedCol}`;

  return errorData.value.grid_coordinates.includes(coordinate);
};

const getCellClasses = (rowIndex, colIndex) => {
  return {
    "field-updating": isCellUpdating(rowIndex, colIndex),
    "field-success": isCellSuccess(rowIndex, colIndex),
    "field-error": isCellError(rowIndex, colIndex),
  };
};

// Editing functions
const isEditing = (rowIndex, colIndex) => {
  return (
    editingCell.value.rowIndex === rowIndex &&
    editingCell.value.colIndex === colIndex
  );
};

const setEditInputRef = (el) => {
  editInputRef.value = el;
};

const getFieldValue = (row, colIndex) => {
  const fieldMap = {
    1: "item_name",
    2: "quantity",
    3: "min_stock_alert",
    4: "mrp",
    5: "sale_price",
    6: "unit",
    7: "barcode",
    8: "vat",
  };
  return row[fieldMap[colIndex]];
};

const setFieldValue = (row, colIndex, value) => {
  const fieldMap = {
    1: "item_name",
    2: "quantity",
    3: "min_stock_alert",
    4: "mrp",
    5: "sale_price",
    6: "unit",
    7: "barcode",
    8: "vat",
  };
  row[fieldMap[colIndex]] = value;
};

const startEdit = (row, rowIndex, colIndex, fieldName) => {
  if (colIndex === 6) return; // Skip unit column

  const currentValue = getFieldValue(row, colIndex);
  originalValue.value = currentValue;
  editValue.value = currentValue;

  editingCell.value = { rowIndex, colIndex };

  nextTick(() => {
    if (editInputRef.value) {
      const input = Array.isArray(editInputRef.value)
        ? editInputRef.value[0]
        : editInputRef.value;
      input?.focus();
      if (input?.select) {
        input.select();
      }
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

  try {
    setFieldValue(row, colIndex, editValue.value);

    updatingCells.value.add(cellKey);
    errorCells.value.delete(cellKey);

    const formData = new FormData();
    formData.append("id", row.id);
    formData.append("item_name", row.item_name || "");
    formData.append("quantity", parseFloat(row.quantity) || 0);
    formData.append("min_stock_alert", parseFloat(row.min_stock_alert) || 0);
    formData.append("mrp", parseFloat(row.mrp) || 0);
    formData.append("sale_price", parseFloat(row.sale_price) || 0);
    formData.append("short_unit", row.unit || "");
    formData.append("barcode", row.barcode || "");
    formData.append("vat", parseFloat(row.vat) || 0);
    formData.append("row_index", rowIndex);
    formData.append("cell_index", colIndex);

    const result = await uploadDataStore.updateCellData(formData);

    updatingCells.value.delete(cellKey);
    successCells.value.add(cellKey);

    editingCell.value = { rowIndex: null, colIndex: null };
    editValue.value = null;
    originalValue.value = null;

    uploadDataStore.clearCellError(rowIndex, colIndex);

    if (row.id === 0 && result.data && result.data.id) {
      row.id = result.data.id;
      row.original_index = result.data.original_index || rowIndex;
      row.isNew = false;
    }

    if (result.data) {
      const fieldMapping = {
        id: "id",
        item_name: "item_name",
        quantity: "quantity",
        min_stock_alert: "min_stock_alert",
        mrp: "mrp",
        sale_price: "sale_price",
        short_unit: "unit",
        barcode: 'barcode',
        vat: "vat",
      };

      Object.keys(result.data).forEach((key) => {
        if (fieldMapping[key] && row[fieldMapping[key]] !== undefined) {
          row[fieldMapping[key]] = result.data[key];
        }
      });
    }

    setTimeout(() => {
      successCells.value.delete(cellKey);
    }, 3000);
  } catch (error) {
    console.error("Update failed:", error);

    setFieldValue(row, colIndex, originalValue.value);

    updatingCells.value.delete(cellKey);
    errorCells.value.add(cellKey);

    editingCell.value = { rowIndex: null, colIndex: null };
    editValue.value = null;
    originalValue.value = null;

    setTimeout(() => {
      errorCells.value.delete(cellKey);
    }, 5000);
  }
};

const cancelEdit = (row, rowIndex, colIndex) => {
  setFieldValue(row, colIndex, originalValue.value);

  editingCell.value = { rowIndex: null, colIndex: null };
  editValue.value = null;
  originalValue.value = null;
};

const handleSelectChange = async (row, rowIndex, colIndex) => {
  const cellKey = getCellKey(rowIndex, colIndex);

  try {
    updatingCells.value.add(cellKey);
    errorCells.value.delete(cellKey);

    const formData = new FormData();
    formData.append("id", row.id);
    formData.append("item_name", row.item_name || "");
    formData.append("quantity", parseFloat(row.quantity) || 0);
    formData.append("min_stock_alert", parseFloat(row.min_stock_alert) || 0);
    formData.append("mrp", parseFloat(row.mrp) || 0);
    formData.append("sale_price", parseFloat(row.sale_price) || 0);
    formData.append("short_unit", row.unit || "");
    formData.append('barcode', row.barcode || '');
    formData.append("vat", parseFloat(row.vat) || 0);
    formData.append("row_index", rowIndex);
    formData.append("cell_index", colIndex);

    const result = await uploadDataStore.updateCellData(formData);

    updatingCells.value.delete(cellKey);
    successCells.value.add(cellKey);

    uploadDataStore.clearCellError(rowIndex, colIndex);

    if (row.id === 0 && result.data && result.data.id) {
      row.id = result.data.id;
      row.original_index = result.data.original_index || rowIndex;
      row.isNew = false;
    }

    if (result.data) {
      const fieldMapping = {
        id: "id",
        item_name: "item_name",
        quantity: "quantity",
        min_stock_alert: "min_stock_alert",
        mrp: "mrp",
        sale_price: "sale_price",
        short_unit: "unit",
        barcode: 'barcode',
        vat: "vat",
      };

      Object.keys(result.data).forEach((key) => {
        if (fieldMapping[key] && row[fieldMapping[key]] !== undefined) {
          row[fieldMapping[key]] = result.data[key];
        }
      });
    }

    setTimeout(() => {
      successCells.value.delete(cellKey);
    }, 3000);
  } catch (error) {
    console.error("Update failed:", error);

    updatingCells.value.delete(cellKey);
    errorCells.value.add(cellKey);

    setTimeout(() => {
      errorCells.value.delete(cellKey);
    }, 5000);
  }
};

const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    uploadDataStore.setSearchQuery(searchQuery.value);
    currentPage.value = 1;
    emit("page-change", 1);
  }, 500);
};

const clearSearch = () => {
  searchQuery.value = "";
  uploadDataStore.setSearchQuery("");
  currentPage.value = 1;
  emit("page-change", 1);
};

const handlePageSizeChange = () => {
  emit("page-size-change", localPageSize.value);
};

const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  emit("page-change", page);
};

watch(selectedRows, (newVal) => {
  emit("selection-change", newVal);
});

watch(
  () => props.currentPage,
  (newVal) => {
    currentPage.value = newVal;
  }
);

watch(
  () => props.pageSize,
  (newVal) => {
    localPageSize.value = newVal;
  }
);
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
}

.editable-field:active {
  background: #e9ecef;
}

.field-display {
  display: flex;
  align-items: center;
  min-height: 24px;
}

.field-updating {
  background-color: #fff3cd !important;
  border: 2px solid #ffc107;
}

.field-success {
  background-color: #d1e7dd !important;
  border: 2px solid #198754;
  animation: successPulse 0.5s ease;
}

.field-error {
  background-color: #f8d7da !important;
  border: 2px solid #dc3545;
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

.form-check-input {
  width: 1.25em;
  height: 1.25em;
}

.mobile-pagination {
  border: 1px solid #dee2e6;
}

.btn-group .btn {
  padding: 0.25rem 0.5rem;
}
</style>
