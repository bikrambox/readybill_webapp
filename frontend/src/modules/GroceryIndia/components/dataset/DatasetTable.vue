<script setup>
import {
  defineProps,
  defineEmits,
  computed,
  ref,
  watch,
  nextTick,
  onMounted,
  onUnmounted,
} from "vue";
import { DATASET_COLUMNS } from "@/modules/GroceryIndia/stores/datasetStore";
import { useDatasetStore } from "@/modules/GroceryIndia/stores/datasetStore";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { useUserDetailsStore } from "@/modules/Authentication/stores/userDetails";
import { storeToRefs } from "pinia";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const preferencesStore = useUserPreferencesStore();
const { preferences } = storeToRefs(preferencesStore);

const userStore = useUserDetailsStore();
const { item_categories } = storeToRefs(userStore);

const itemCategories = computed(() => item_categories.value || []);

const props = defineProps({
  datasets: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
  currentPage: { type: Number, default: 0 },
  pageLength: { type: Number, default: 10 },
  totalRecords: { type: Number, default: 0 },
  filteredRecords: { type: Number, default: 0 },
  units: { type: Array, default: () => [] },
});

const emit = defineEmits([
  "toggle-item",
  "toggle-all",
  "page-change",
  "page-length-change",
  "cell-updated",
  "cell-error",
]);

const datasetStore = useDatasetStore();
const tableKey = ref(0);
const editInputRef = ref(null);
const editValue = ref(null);
const originalValue = ref(null);

// ── Custom scrollbar ─────────────────────────────────────────────────────────
const tableScroll = ref(null);
const scrollTrack = ref(null);
const thumbLeft = ref(0);
const thumbWidth = ref(0);
const showScrollbar = ref(false);
let isDragging = false;
let dragStartX = 0;
let dragStartScrollLeft = 0;

// ── Selection ────────────────────────────────────────────────────────────────
const allSelected = computed(
  () => props.datasets.length > 0 && props.datasets.every((d) => d.selected)
);

// ── Cell edit state ──────────────────────────────────────────────────────────
const editingCell = ref({ rowIndex: null, colKey: null });
const updatingCells = ref(new Set());
const successCells = ref(new Set());
const errorCells = ref(new Set());
const savingCells = ref(new Set());

// ── Preferences ──────────────────────────────────────────────────────────────
const isPurchasePriceRequired = computed(
  () =>
    preferences.value.preference_purchase_price === 1 ||
    preferences.value.preference_purchase_price === true
);

// ── Column options ────────────────────────────────────────────────────────────
const unitOptions = computed(() => {
  if (props.units && props.units.length > 0) {
    return props.units.map((unit) => ({
      value: String(unit.value),
      label: unit.label,
    }));
  }
  return [
    { value: "PCS", label: "Pieces" },
    { value: "BTL", label: "Bottle" },
    { value: "PCK", label: "Pack" },
    { value: "KG", label: "Kilogram" },
    { value: "LTR", label: "Liter" },
  ];
});

const categoryOptions = computed(() =>
  itemCategories.value.map((category) => ({
    value: category?.value ?? category?.id ?? category?.name ?? category,
    label: category?.label ?? category?.name ?? category?.title ?? category,
  }))
);

const getSelectOptions = (config) => {
  if (config.key === "category") return categoryOptions.value;
  if (config.key === "unit") return unitOptions.value;
  return [];
};

const getCategoryLabel = (val) => {
  const found = categoryOptions.value.find((c) => c.value === val);
  return found ? found.label : val;
};

const getUnitLabel = (val) => {
  const found = unitOptions.value.find((u) => u.value === String(val ?? ""));
  return found ? found.label : val;
};

// ── Columns (single source of truth from store) ───────────────────────────────
const columnConfig = DATASET_COLUMNS;

const visibleColumns = computed(() =>
  columnConfig.filter((col) => {
    if (col.key === "flag") return true;
    // if (col.key === "purchase_price") return isPurchasePriceRequired.value;
    return true;
  })
);

const dataColumns = computed(() => visibleColumns.value.slice(1));
const getColumnIndex = (config) =>
  columnConfig.findIndex((col) => col.key === config.key);

// ── Custom scrollbar logic ────────────────────────────────────────────────────
const updateThumb = () => {
  const el = tableScroll.value;
  if (!el) return;
  const scrollable = el.scrollWidth > el.clientWidth + 1;
  showScrollbar.value = scrollable;
  if (!scrollable) return;

  const trackWidth = scrollTrack.value?.clientWidth || el.clientWidth;
  const ratio = el.clientWidth / el.scrollWidth;
  thumbWidth.value = Math.max(40, trackWidth * ratio);

  const maxThumbLeft = trackWidth - thumbWidth.value;
  thumbLeft.value =
    el.scrollLeft <= 0
      ? 0
      : (el.scrollLeft / (el.scrollWidth - el.clientWidth)) * maxThumbLeft;
};

const onTableScroll = () => updateThumb();

const startDrag = (e) => {
  isDragging = true;
  dragStartX = e.clientX;
  dragStartScrollLeft = tableScroll.value.scrollLeft;

  const onMove = (ev) => {
    if (!isDragging) return;
    const el = tableScroll.value;
    const trackWidth = scrollTrack.value.clientWidth;
    const dx = ev.clientX - dragStartX;
    const ratio = dx / (trackWidth - thumbWidth.value);
    el.scrollLeft = dragStartScrollLeft + ratio * (el.scrollWidth - el.clientWidth);
  };

  const onUp = () => {
    isDragging = false;
    window.removeEventListener("mousemove", onMove);
    window.removeEventListener("mouseup", onUp);
  };

  window.addEventListener("mousemove", onMove);
  window.addEventListener("mouseup", onUp);
};

const onTrackClick = (e) => {
  const track = scrollTrack.value;
  const el = tableScroll.value;
  if (!track || !el) return;

  const rect = track.getBoundingClientRect();
  const clickX = e.clientX - rect.left;
  const ratio = Math.max(
    0,
    Math.min(1, (clickX - thumbWidth.value / 2) / (rect.width - thumbWidth.value))
  );
  el.scrollLeft = ratio * (el.scrollWidth - el.clientWidth);
};

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  nextTick(updateThumb);
  window.addEventListener("resize", updateThumb);
  preferencesStore.fetchUserPreferences();
});

onUnmounted(() => {
  window.removeEventListener("resize", updateThumb);
});

// ── Watchers ──────────────────────────────────────────────────────────────────
watch(tableKey, () => nextTick(updateThumb));
watch(visibleColumns, () => {
  tableKey.value++;
  nextTick(updateThumb);
});

watch(
  () => props.datasets,
  () => {
    tableKey.value++;
  },
  { deep: true }
);

watch(
  () => props.filteredRecords,
  (newVal, oldVal) => {
    if (newVal !== oldVal) tableKey.value++;
  }
);

// ── Pagination helpers ────────────────────────────────────────────────────────
const paginationInfo = computed(() => {
  if (props.filteredRecords === 0) return "0 of 0";
  const start = props.currentPage * props.pageLength + 1;
  const end = Math.min((props.currentPage + 1) * props.pageLength, props.filteredRecords);
  return `${start}-${end} of ${props.filteredRecords}`;
});

const totalPages = computed(
  () => Math.ceil(props.filteredRecords / props.pageLength) || 1
);
const canGoPrev = computed(() => props.currentPage > 0);
const canGoNext = computed(() => props.currentPage < totalPages.value - 1);

// ── Cell state helpers ─────────────────────────────────────────────────────────
const getCellKey = (r, c) => `${r}-${c}`;
const getSavingKey = (r, key) => `${r}-${key}`;

const isCellUpdating = (r, c) => updatingCells.value.has(getCellKey(r, c));
const isCellSuccess = (r, c) => successCells.value.has(getCellKey(r, c));
const isCellError = (r, c) => errorCells.value.has(getCellKey(r, c));
const isEditing = (r, colKey) =>
  editingCell.value.rowIndex === r && editingCell.value.colKey === colKey;

// ── Edit logic ─────────────────────────────────────────────────────────────────
const startEdit = (rowIndex, config) => {
  if (!config?.editable || props.isLoading) return;
  if (isEditing(rowIndex, config.key)) return;

  const dataset = props.datasets[rowIndex];
  originalValue.value = dataset[config.key];

  editValue.value =
    config.type === "select" ? String(dataset[config.key] ?? "") : dataset[config.key];

  editingCell.value = { rowIndex, colKey: config.key };

  nextTick(() => {
    const input = Array.isArray(editInputRef.value)
      ? editInputRef.value[0]
      : editInputRef.value;
    input?.focus();
    if (config.type === "text" || config.type === "number") {
      input?.select();
    }
  });
};

const saveCell = async (dataset, rowIndex, config) => {
  const colIndex = getColumnIndex(config);
  const cellKey = getCellKey(rowIndex, colIndex);
  const savingKey = getSavingKey(rowIndex, config.key);

  if (savingCells.value.has(savingKey)) return;

  if (editValue.value === originalValue.value) {
    editingCell.value = { rowIndex: null, colKey: null };
    return;
  }

  try {
    savingCells.value.add(savingKey);
    dataset[config.key] = editValue.value;

    updatingCells.value.add(cellKey);
    errorCells.value.delete(cellKey);

    const result = await datasetStore.updateCellData(dataset, rowIndex, colIndex);

    updatingCells.value.delete(cellKey);
    successCells.value.add(cellKey);
    editingCell.value = { rowIndex: null, colKey: null };

    emit("cell-updated", result.message || t("common.Cell updated successfully"));
    setTimeout(() => successCells.value.delete(cellKey), 3000);
  } catch (error) {
    dataset[config.key] = originalValue.value;
    updatingCells.value.delete(cellKey);
    errorCells.value.add(cellKey);
    editingCell.value = { rowIndex: null, colKey: null };

    emit("cell-error", error.message || t("common.Failed to update cell"));
    setTimeout(() => errorCells.value.delete(cellKey), 5000);
  } finally {
    savingCells.value.delete(savingKey);
  }
};

const cancelEdit = () => {
  editingCell.value = { rowIndex: null, colKey: null };
  editValue.value = null;
  originalValue.value = null;
};

const getCellValue = (dataset, config) => dataset[config.key];

// ── Event handlers ─────────────────────────────────────────────────────────────
const handleNumberInput = (event) => {
  const parsed = parseFloat(event.target.value);
  editValue.value = isNaN(parsed) ? 0 : parsed;
};

const handleKeydown = (event, dataset, rowIndex, config) => {
  if (event.key === "Enter") {
    event.preventDefault();
    event.target.blur();
  } else if (event.key === "Escape") {
    event.preventDefault();
    dataset[config.key] = originalValue.value;
    cancelEdit();
  }
};

const handleBlur = (dataset, rowIndex, config) => {
  if (!isEditing(rowIndex, config.key)) return;
  saveCell(dataset, rowIndex, config);
};

const handleSelectChange = (dataset, rowIndex, config) => {
  saveCell(dataset, rowIndex, config);
};

// ── Pagination handlers ────────────────────────────────────────────────────────
const handlePageLengthChange = (event) => {
  if (props.isLoading) return;
  emit("page-length-change", parseInt(event.target.value));
};

const handlePrevPage = () => {
  if (canGoPrev.value && !props.isLoading) {
    emit("page-change", props.currentPage - 1);
  }
};

const handleNextPage = () => {
  if (canGoNext.value && !props.isLoading) {
    emit("page-change", props.currentPage + 1);
  }
};
</script>

<template>
  <div class="card border-0 shadow-sm position-relative">
    <div class="card-body p-0">
      <div class="table-scroll-wrapper">
        <div class="table-responsive" ref="tableScroll" @scroll="onTableScroll">
          <table
            ref="tableRef"
            :key="`table-refresh-${tableKey}`"
            class="table table-hover align-middle mb-0"
          >
            <thead class="table-light">
              <tr>
                <th class="col-flag ps-3">
                  <input
                    type="checkbox"
                    class="form-check-input"
                    :checked="allSelected"
                    @change="emit('toggle-all')"
                    :disabled="isLoading"
                  />
                </th>

                <th
                  v-for="config in dataColumns"
                  :key="config.key"
                  :class="[
                    `col-${config.key}`,
                    {
                      'text-end pe-3': [
                        'mrp',
                        'salePrice',
                        'purchase_price',
                        'gst',
                        'cess',
                        'quantity',
                        'minimumStockAlert',
                      ].includes(config.key),
                      'text-center': ['unit', 'category'].includes(config.key),
                    },
                  ]"
                >
                  {{ config.label }}
                </th>
              </tr>
            </thead>

            <tbody>
              <template v-if="isLoading">
                <tr>
                  <td
                    :colspan="visibleColumns.length"
                    class="text-center py-5 text-muted"
                  >
                    <div
                      class="spinner-border spinner-border-sm text-primary"
                      role="status"
                    >
                      <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
                    </div>
                    <span class="ms-2">{{ $t("common.Loading data") }}...</span>
                  </td>
                </tr>
              </template>

              <template v-else-if="datasets.length === 0">
                <tr>
                  <td
                    :colspan="visibleColumns.length"
                    class="text-center py-5 text-muted"
                  >
                    <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem"></i>
                    {{ $t("common.No datasets found") }}
                  </td>
                </tr>
              </template>

              <template v-else>
                <tr
                  v-for="(dataset, rowIndex) in datasets"
                  :key="`row-${dataset.id}-${tableKey}`"
                  :class="{ 'table-active': dataset.selected }"
                >
                  <td class="col-flag ps-3">
                    <input
                      type="checkbox"
                      class="form-check-input"
                      :checked="dataset.selected"
                      @change="emit('toggle-item', dataset.id)"
                      :disabled="isLoading"
                    />
                  </td>

                  <td
                    v-for="config in dataColumns"
                    :key="config.key"
                    :class="[
                      `col-${config.key}`,
                      {
                        'text-end pe-3': [
                          'mrp',
                          'salePrice',
                          'purchase_price',
                          'gst',
                          'cess',
                          'quantity',
                          'minimumStockAlert',
                        ].includes(config.key),
                        'text-center': ['unit', 'category'].includes(config.key),
                        'bg-danger bg-opacity-10 text-danger': datasetStore.isCellError(
                          rowIndex,
                          getColumnIndex(config)
                        ),
                        'position-relative': isEditing(rowIndex, config.key),
                        'cursor-pointer': config.editable,
                        'cell-updating': isCellUpdating(rowIndex, getColumnIndex(config)),
                        'cell-success': isCellSuccess(rowIndex, getColumnIndex(config)),
                        'cell-error': isCellError(rowIndex, getColumnIndex(config)),
                      },
                    ]"
                    @click="startEdit(rowIndex, config)"
                    tabindex="0"
                  >
                    <template v-if="!isEditing(rowIndex, config.key)">
                      <span
                        :class="{
                          'fw-semibold': config.key === 'itemName',
                          'badge bg-light text-dark border': [
                            'unit',
                            'category',
                          ].includes(config.key),
                        }"
                      >
                        <template v-if="config.key === 'unit'">
                          {{ getUnitLabel(getCellValue(dataset, config)) }}
                          <i class="bi bi-chevron-down ms-1 small"></i>
                        </template>

                        <template v-else-if="config.key === 'category'">
                          {{ getCategoryLabel(getCellValue(dataset, config)) }}
                          <i class="bi bi-chevron-down ms-1 small"></i>
                        </template>

                        <template
                          v-else-if="
                            ['mrp', 'salePrice', 'purchase_price'].includes(config.key)
                          "
                        >
                          {{ $formatCurrency(getCellValue(dataset, config)) }}
                        </template>

                        <template v-else>
                          {{ getCellValue(dataset, config) }}
                        </template>
                      </span>

                      <i
                        v-if="isCellSuccess(rowIndex, getColumnIndex(config))"
                        class="bi bi-check-circle-fill text-success ms-2"
                      ></i>

                      <i
                        v-if="
                          isCellError(rowIndex, getColumnIndex(config)) ||
                          datasetStore.isCellError(rowIndex, getColumnIndex(config))
                        "
                        class="bi bi-exclamation-circle-fill text-danger ms-2"
                      ></i>

                      <span
                        v-if="isCellUpdating(rowIndex, getColumnIndex(config))"
                        class="spinner-border spinner-border-sm text-primary ms-2"
                        role="status"
                        style="width: 1rem; height: 1rem"
                      >
                        <span class="visually-hidden"
                          >{{ $t("common.Updating") }}...</span
                        >
                      </span>
                    </template>

                    <div
                      v-else
                      class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center px-2 bg-white edit-cell"
                    >
                      <input
                        v-if="config.type === 'text'"
                        v-model="editValue"
                        class="form-control form-control-sm border-primary shadow-sm"
                        @keydown="handleKeydown($event, dataset, rowIndex, config)"
                        @blur="handleBlur(dataset, rowIndex, config)"
                        autocomplete="off"
                        ref="editInputRef"
                      />

                      <input
                        v-else-if="config.type === 'number'"
                        :value="editValue"
                        type="number"
                        :step="
                          ['quantity', 'minimumStockAlert'].includes(config.key)
                            ? 1
                            : 0.01
                        "
                        min="0"
                        class="form-control form-control-sm text-end border-primary shadow-sm"
                        @input="handleNumberInput($event)"
                        @keydown="handleKeydown($event, dataset, rowIndex, config)"
                        @blur="handleBlur(dataset, rowIndex, config)"
                        autocomplete="off"
                        ref="editInputRef"
                      />

                      <select
                        v-else-if="config.type === 'select'"
                        v-model="editValue"
                        class="form-select form-select-sm border-primary shadow-sm"
                        @keydown="handleKeydown($event, dataset, rowIndex, config)"
                        @change="handleSelectChange(dataset, rowIndex, config)"
                        ref="editInputRef"
                      >
                        <option
                          v-for="option in getSelectOptions(config)"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </option>
                      </select>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div
          v-show="showScrollbar"
          class="table-scroll-track"
          ref="scrollTrack"
          @mousedown="onTrackClick"
        >
          <div
            class="table-scroll-thumb"
            :style="{ left: thumbLeft + 'px', width: thumbWidth + 'px' }"
            @mousedown.stop="startDrag"
          ></div>
        </div>
      </div>
    </div>

    <div class="card-footer bg-white border-top">
      <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small fw-medium"
            >{{ $t("common.Rows per page") }}:</span
          >
          <select
            class="form-select form-select-sm"
            style="width: auto"
            :value="pageLength"
            @change="handlePageLengthChange"
            :disabled="isLoading"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
            <option :value="100">100</option>
          </select>
        </div>

        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small fw-medium">{{ paginationInfo }}</span>
          <div class="btn-group" role="group">
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary"
              @click="handlePrevPage"
              :disabled="!canGoPrev || isLoading"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
            <button
              type="button"
              class="btn btn-sm btn-outline-secondary"
              @click="handleNextPage"
              :disabled="!canGoNext || isLoading"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.table-scroll-wrapper {
  position: relative;
}

.table-responsive {
  overflow-x: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.table-responsive::-webkit-scrollbar {
  display: none;
}

.table-scroll-track {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  margin: 4px 12px 6px;
  position: relative;
  cursor: pointer;
  user-select: none;
}

.table-scroll-thumb {
  position: absolute;
  top: 0;
  height: 6px;
  background: #adb5bd;
  border-radius: 3px;
  cursor: grab;
  transition: background 0.2s ease, width 0.1s ease;
}
.table-scroll-thumb:hover {
  background: #6c757d;
}
.table-scroll-thumb:active {
  cursor: grabbing;
  background: #495057;
}

/* Column widths */
.col-flag {
  width: 48px;
  min-width: 48px;
}
.col-sku {
  min-width: 110px;
}
.col-itemName {
  min-width: 180px;
}
.col-hsn {
  min-width: 100px;
}
.col-category {
  min-width: 140px;
}
.col-unit {
  min-width: 100px;
}
.col-quantity {
  min-width: 100px;
}
.col-minimumStockAlert {
  min-width: 140px;
}
.col-mrp {
  min-width: 110px;
}
.col-salePrice {
  min-width: 110px;
}
.col-purchase_price {
  min-width: 130px;
}
.col-gst {
  min-width: 90px;
}
.col-cess {
  min-width: 90px;
}

/* Table base */
.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 10px 12px;
  font-size: 13px;
  font-weight: 600;
  color: #6c757d;
  white-space: nowrap;
}
.table tbody td {
  padding: 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
  white-space: nowrap;
}
.table tbody tr {
  transition: background-color 0.15s ease-in-out;
}
.table tbody tr:hover:not(.table-active) {
  background-color: #f5f5f5;
}
.table-active {
  background-color: #e3f2fd !important;
}

/* Edit overlay */
.cursor-pointer {
  cursor: pointer;
}
.cursor-pointer:hover:not(.bg-danger):not(.cell-error) {
  background-color: #f8f9fa;
}
.edit-cell {
  z-index: 10;
}
.edit-cell .form-control:focus,
.edit-cell .form-select:focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}
td:focus-within:not(.bg-danger):not(.cell-error) {
  background-color: #e7f1ff !important;
  border-left: 3px solid #0d6efd;
}

/* Cell states */
.cell-updating {
  background-color: #fff3cd !important;
  transition: background-color 0.3s ease;
}
.cell-success {
  background-color: #d1e7dd !important;
  transition: background-color 0.3s ease;
}
.cell-error {
  background-color: #f8d7da !important;
  color: #721c24 !important;
  transition: background-color 0.3s ease;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.card-footer {
  padding: 16px 20px;
}

@media (max-width: 768px) {
  .card-footer {
    padding: 12px 15px;
  }
  .d-flex.gap-3 {
    gap: 0.5rem !important;
  }
  .d-flex.gap-2 {
    gap: 0.5rem !important;
  }
}
</style>
