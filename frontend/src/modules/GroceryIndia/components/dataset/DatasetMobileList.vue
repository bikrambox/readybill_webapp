<script setup>
import { defineProps, defineEmits, ref, nextTick, computed } from "vue";
import {
  useDatasetStore,
  DATASET_COLUMNS,
} from "@/modules/GroceryIndia/stores/datasetStore";
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

const categoryOptions = computed(() => {
  return itemCategories.value.map((category) => ({
    value: category?.value ?? category?.id ?? category?.name ?? category,
    label: category?.label ?? category?.name ?? category?.title ?? category,
  }));
});

const getCategoryLabel = (categoryValue) => {
  const category = categoryOptions.value.find((item) => item.value === categoryValue);
  return category ? category.label : categoryValue || "No Category";
};

const props = defineProps({
  datasets: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
  units: { type: Array, default: () => [] },
});

const emit = defineEmits(["toggle-item", "cell-updated", "cell-error"]);
const datasetStore = useDatasetStore();

const editingCell = ref({ rowId: null, field: null });
const editValue = ref(null);
const originalValue = ref(null);
const editInputRef = ref(null);

const savingCells = ref(new Set());
const updatingCells = ref(new Set());
const successCells = ref(new Set());
const errorCells = ref(new Set());

const isPurchasePriceRequired = computed(
  () =>
    preferences.value?.preference_purchase_price === 1 ||
    preferences.value?.preference_purchase_price === true
);

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

// Read field config from dataset store
const fieldConfig = DATASET_COLUMNS.reduce((acc, column, index) => {
  acc[column.key] = {
    type: column.type || "text",
    label: column.label,
    colIndex: index,
    editable: column.editable ?? true,
  };
  return acc;
}, {});

const getRowId = (dataset, fallbackIndex = null) => dataset?.id ?? `row-${fallbackIndex}`;
const getCellKey = (rowId, field) => `${rowId}-${field}`;

const isCellUpdating = (rowId, field) =>
  updatingCells.value.has(getCellKey(rowId, field));
const isCellSuccess = (rowId, field) => successCells.value.has(getCellKey(rowId, field));
const isCellError = (rowId, field) => errorCells.value.has(getCellKey(rowId, field));

const hasStoreError = (rowIndex, field) => {
  const colIndex = fieldConfig[field]?.colIndex;
  return colIndex !== undefined ? datasetStore.isCellError(rowIndex, colIndex) : false;
};

const isEditing = (rowId, field) => {
  return editingCell.value.rowId === rowId && editingCell.value.field === field;
};

const getUnitLabel = (shortUnit) => {
  const unit = unitOptions.value.find((u) => u.value === String(shortUnit));
  return unit ? unit.label : shortUnit;
};

const resetEditState = () => {
  editingCell.value = { rowId: null, field: null };
  editValue.value = null;
  originalValue.value = null;
};

const focusEditInput = async () => {
  await nextTick();

  const input = Array.isArray(editInputRef.value)
    ? editInputRef.value[0]
    : editInputRef.value;

  if (input?.focus) {
    input.focus();
    if (input?.select) input.select();
  }
};

const startEdit = async (dataset, rowIndex, field) => {
  if (props.isLoading) return;

  const rowId = getRowId(dataset, rowIndex);

  originalValue.value = dataset[field];
  editValue.value =
    fieldConfig[field]?.type === "select" ? String(dataset[field] ?? "") : dataset[field];

  editingCell.value = { rowId, field };

  await focusEditInput();
};

const saveCell = async (dataset, rowIndex, field) => {
  const rowId = getRowId(dataset, rowIndex);
  const cellKey = getCellKey(rowId, field);
  const config = fieldConfig[field];

  if (!config) return;
  if (savingCells.value.has(cellKey)) return;

  if (editValue.value === originalValue.value) {
    resetEditState();
    return;
  }

  try {
    savingCells.value.add(cellKey);
    dataset[field] = editValue.value;

    updatingCells.value.add(cellKey);
    errorCells.value.delete(cellKey);

    const result = await datasetStore.updateCellData(dataset, rowIndex, config.colIndex);

    updatingCells.value.delete(cellKey);
    successCells.value.add(cellKey);

    resetEditState();

    emit("cell-updated", result?.message || t("common.Cell updated successfully"));

    setTimeout(() => {
      successCells.value.delete(cellKey);
    }, 3000);
  } catch (error) {
    dataset[field] = originalValue.value;

    updatingCells.value.delete(cellKey);
    errorCells.value.add(cellKey);

    resetEditState();

    emit("cell-error", error?.message || t("common.Failed to update cell"));

    setTimeout(() => {
      errorCells.value.delete(cellKey);
    }, 5000);
  } finally {
    savingCells.value.delete(cellKey);
  }
};

const cancelEdit = (dataset, field) => {
  if (field) dataset[field] = originalValue.value;
  resetEditState();
};

const handleKeydown = (event, dataset, rowIndex, field) => {
  if (event.key === "Enter") {
    event.preventDefault();
    event.target.blur();
  } else if (event.key === "Escape") {
    event.preventDefault();
    cancelEdit(dataset, field);
  }
};

const handleBlur = (dataset, rowIndex, field) => {
  if (!isEditing(getRowId(dataset, rowIndex), field)) return;
  saveCell(dataset, rowIndex, field);
};

const handleSelectChange = (dataset, rowIndex, field) => {
  saveCell(dataset, rowIndex, field);
};

const integerFields = ["quantity", "minimumStockAlert"];

const handleNumberInput = (event, field) => {
  const parsed = parseFloat(event.target.value);

  if (isNaN(parsed)) {
    editValue.value = 0;
    return;
  }

  editValue.value = integerFields.includes(field) ? Math.round(parsed) : parsed;
};

const getNumberStep = (field) => {
  return integerFields.includes(field) ? 1 : 0.01;
};

const formatValue = (value, field) => {
  if (value === null || value === undefined || value === "") return "-";

  const num = Number(value);

  if (Number.isNaN(num)) return value;

  if (field === "mrp" || field === "salePrice" || field === "purchase_price") {
    return `₹${num.toFixed(2)}`;
  } else if (field === "gst" || field === "cess") {
    return `${num.toFixed(2)}%`;
  }

  return value;
};
</script>

<template>
  <div class="mobile-list">
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary mb-2" role="status">
        <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
      </div>
      <p class="text-muted mb-0 small">{{ $t("common.Loading datasets") }}...</p>
    </div>

    <div v-else-if="datasets.length === 0" class="text-center py-5">
      <i class="bi bi-inbox d-block mb-2 text-muted" style="font-size: 3rem"></i>
      <p class="text-muted mb-0">{{ $t("common.No datasets found") }}</p>
    </div>

    <div v-else class="row g-3">
      <div v-for="(dataset, index) in datasets" :key="dataset.id" class="col-12">
        <div
          class="card inventory-card shadow-sm h-100"
          :class="{
            'card-selected': dataset.selected,
            'border-0': !dataset.selected,
          }"
        >
          <div class="card-body p-3">
            <div class="d-flex align-items-start gap-3 mb-3">
              <input
                type="checkbox"
                class="form-check-input mt-1 flex-shrink-0"
                style="width: 1.15rem; height: 1.15rem"
                :checked="dataset.selected"
                @change="emit('toggle-item', dataset.id)"
                :disabled="isLoading"
              />

              <div class="flex-grow-1 min-w-0">
                <div class="top-meta mb-2">
                  <div class="meta-block">
                    <label class="meta-label">SKU</label>

                    <div
                      v-if="!isEditing(dataset.id, 'sku')"
                      class="editable-field title-field"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'sku'),
                        'field-success': isCellSuccess(dataset.id, 'sku'),
                        'field-error':
                          isCellError(dataset.id, 'sku') || hasStoreError(index, 'sku'),
                      }"
                      @click="startEdit(dataset, index, 'sku')"
                    >
                      <div class="fw-semibold text-dark d-flex align-items-center gap-2">
                        <span class="text-truncate">{{ dataset.sku || "-" }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>

                    <div v-else>
                      <input
                        v-model="editValue"
                        type="text"
                        class="form-control form-control-sm border-primary"
                        @keydown="handleKeydown($event, dataset, index, 'sku')"
                        @blur="handleBlur(dataset, index, 'sku')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="chip-row">
                    <div
                      v-if="!isEditing(dataset.id, 'category')"
                      class="editable-field d-inline-block"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'category'),
                        'field-success': isCellSuccess(dataset.id, 'category'),
                        'field-error':
                          isCellError(dataset.id, 'category') ||
                          hasStoreError(index, 'category'),
                      }"
                      @click="startEdit(dataset, index, 'category')"
                    >
                      <span class="badge rounded-pill soft-badge category-badge">
                        <i class="bi bi-tag me-1"></i>
                        {{ getCategoryLabel(dataset.category) }}
                        <i class="bi bi-chevron-down ms-1 small"></i>
                      </span>
                    </div>

                    <div v-else class="d-inline-block">
                      <select
                        v-model="editValue"
                        class="form-select form-select-sm border-primary"
                        style="min-width: 150px"
                        @change="handleSelectChange(dataset, index, 'category')"
                        ref="editInputRef"
                      >
                        <option
                          v-for="category in categoryOptions"
                          :key="category.value"
                          :value="category.value"
                        >
                          {{ category.label }}
                        </option>
                      </select>
                    </div>

                    <div
                      v-if="!isEditing(dataset.id, 'unit')"
                      class="editable-field d-inline-block"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'unit'),
                        'field-success': isCellSuccess(dataset.id, 'unit'),
                        'field-error':
                          isCellError(dataset.id, 'unit') || hasStoreError(index, 'unit'),
                      }"
                      @click="startEdit(dataset, index, 'unit')"
                    >
                      <span class="badge rounded-pill soft-badge unit-badge">
                        <i class="bi bi-box-seam me-1"></i>
                        {{ getUnitLabel(dataset.unit) }}
                        <i class="bi bi-chevron-down ms-1 small"></i>
                      </span>
                    </div>

                    <div v-else class="d-inline-block">
                      <select
                        v-model="editValue"
                        class="form-select form-select-sm border-primary"
                        style="min-width: 120px"
                        @change="handleSelectChange(dataset, index, 'unit')"
                        ref="editInputRef"
                      >
                        <option
                          v-for="unit in unitOptions"
                          :key="unit.value"
                          :value="unit.value"
                        >
                          {{ unit.label }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="meta-block mb-3">
                  <label class="meta-label">{{ $t("common.Item Name") }}</label>

                  <div
                    v-if="!isEditing(dataset.id, 'itemName')"
                    class="editable-field name-field"
                    :class="{
                      'field-updating': isCellUpdating(dataset.id, 'itemName'),
                      'field-success': isCellSuccess(dataset.id, 'itemName'),
                      'field-error':
                        isCellError(dataset.id, 'itemName') ||
                        hasStoreError(index, 'itemName'),
                    }"
                    @click="startEdit(dataset, index, 'itemName')"
                  >
                    <div class="fw-semibold item-title d-flex align-items-center gap-2">
                      <span>{{ dataset.itemName || "-" }}</span>
                      <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                    </div>
                  </div>

                  <div v-else>
                    <input
                      v-model="editValue"
                      type="text"
                      class="form-control form-control-sm border-primary"
                      @keydown="handleKeydown($event, dataset, index, 'itemName')"
                      @blur="handleBlur(dataset, index, 'itemName')"
                      ref="editInputRef"
                    />
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.Quantity") }}
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'quantity')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'quantity'),
                        'field-success': isCellSuccess(dataset.id, 'quantity'),
                        'field-error':
                          isCellError(dataset.id, 'quantity') ||
                          hasStoreError(index, 'quantity'),
                      }"
                      @click="startEdit(dataset, index, 'quantity')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ dataset.quantity }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('quantity')"
                        min="0"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'quantity')"
                        @keydown="handleKeydown($event, dataset, index, 'quantity')"
                        @blur="handleBlur(dataset, index, 'quantity')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.Min Stock") }}
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'minimumStockAlert')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'minimumStockAlert'),
                        'field-success': isCellSuccess(dataset.id, 'minimumStockAlert'),
                        'field-error':
                          isCellError(dataset.id, 'minimumStockAlert') ||
                          hasStoreError(index, 'minimumStockAlert'),
                      }"
                      @click="startEdit(dataset, index, 'minimumStockAlert')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ dataset.minimumStockAlert }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('minimumStockAlert')"
                        min="0"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'minimumStockAlert')"
                        @keydown="
                          handleKeydown($event, dataset, index, 'minimumStockAlert')
                        "
                        @blur="handleBlur(dataset, index, 'minimumStockAlert')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div v-if="isPurchasePriceRequired" class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.Purchase Price") }}
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'purchase_price')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'purchase_price'),
                        'field-success': isCellSuccess(dataset.id, 'purchase_price'),
                        'field-error':
                          isCellError(dataset.id, 'purchase_price') ||
                          hasStoreError(index, 'purchase_price'),
                      }"
                      @click="startEdit(dataset, index, 'purchase_price')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{
                          formatValue(dataset.purchase_price, "purchase_price")
                        }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('purchase_price')"
                        min="0"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'purchase_price')"
                        @keydown="handleKeydown($event, dataset, index, 'purchase_price')"
                        @blur="handleBlur(dataset, index, 'purchase_price')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.MRP") }}
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'mrp')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'mrp'),
                        'field-success': isCellSuccess(dataset.id, 'mrp'),
                        'field-error':
                          isCellError(dataset.id, 'mrp') || hasStoreError(index, 'mrp'),
                      }"
                      @click="startEdit(dataset, index, 'mrp')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ formatValue(dataset.mrp, "mrp") }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('mrp')"
                        min="0"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'mrp')"
                        @keydown="handleKeydown($event, dataset, index, 'mrp')"
                        @blur="handleBlur(dataset, index, 'mrp')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.Sale Price") }}
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'salePrice')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'salePrice'),
                        'field-success': isCellSuccess(dataset.id, 'salePrice'),
                        'field-error':
                          isCellError(dataset.id, 'salePrice') ||
                          hasStoreError(index, 'salePrice'),
                      }"
                      @click="startEdit(dataset, index, 'salePrice')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ formatValue(dataset.salePrice, "salePrice") }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('salePrice')"
                        min="0"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'salePrice')"
                        @keydown="handleKeydown($event, dataset, index, 'salePrice')"
                        @blur="handleBlur(dataset, index, 'salePrice')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="col-12">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.HSN Code") }}
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'hsn')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'hsn'),
                        'field-success': isCellSuccess(dataset.id, 'hsn'),
                        'field-error':
                          isCellError(dataset.id, 'hsn') || hasStoreError(index, 'hsn'),
                      }"
                      @click="startEdit(dataset, index, 'hsn')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ dataset.hsn || "Not Set" }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        v-model="editValue"
                        type="text"
                        class="form-control form-control-sm border-primary"
                        @keydown="handleKeydown($event, dataset, index, 'hsn')"
                        @blur="handleBlur(dataset, index, 'hsn')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.GST") }} (%)
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'gst')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'gst'),
                        'field-success': isCellSuccess(dataset.id, 'gst'),
                        'field-error':
                          isCellError(dataset.id, 'gst') || hasStoreError(index, 'gst'),
                      }"
                      @click="startEdit(dataset, index, 'gst')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ formatValue(dataset.gst, "gst") }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('gst')"
                        min="0"
                        max="100"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'gst')"
                        @keydown="handleKeydown($event, dataset, index, 'gst')"
                        @blur="handleBlur(dataset, index, 'gst')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>

                  <div class="col-6">
                    <label class="form-label small text-muted mb-1">
                      {{ $t("common.CESS") }} (%)
                    </label>
                    <div
                      v-if="!isEditing(dataset.id, 'cess')"
                      class="editable-field value-box"
                      :class="{
                        'field-updating': isCellUpdating(dataset.id, 'cess'),
                        'field-success': isCellSuccess(dataset.id, 'cess'),
                        'field-error':
                          isCellError(dataset.id, 'cess') || hasStoreError(index, 'cess'),
                      }"
                      @click="startEdit(dataset, index, 'cess')"
                    >
                      <div
                        class="fw-medium d-flex align-items-center justify-content-between gap-2"
                      >
                        <span>{{ formatValue(dataset.cess, "cess") }}</span>
                        <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                      </div>
                    </div>
                    <div v-else>
                      <input
                        :value="editValue"
                        type="number"
                        :step="getNumberStep('cess')"
                        min="0"
                        max="100"
                        class="form-control form-control-sm border-primary"
                        @input="handleNumberInput($event, 'cess')"
                        @keydown="handleKeydown($event, dataset, index, 'cess')"
                        @blur="handleBlur(dataset, index, 'cess')"
                        ref="editInputRef"
                      />
                    </div>
                  </div>
                </div>

                <div
                  class="status-row mt-3"
                  v-if="
                    isCellUpdating(dataset.id, 'sku') ||
                    isCellUpdating(dataset.id, 'category') ||
                    isCellUpdating(dataset.id, 'itemName') ||
                    isCellUpdating(dataset.id, 'quantity') ||
                    isCellUpdating(dataset.id, 'minimumStockAlert') ||
                    isCellUpdating(dataset.id, 'purchase_price') ||
                    isCellUpdating(dataset.id, 'mrp') ||
                    isCellUpdating(dataset.id, 'salePrice') ||
                    isCellUpdating(dataset.id, 'unit') ||
                    isCellUpdating(dataset.id, 'hsn') ||
                    isCellUpdating(dataset.id, 'gst') ||
                    isCellUpdating(dataset.id, 'cess')
                  "
                >
                  <span class="small text-primary d-flex align-items-center gap-2">
                    <span
                      class="spinner-border spinner-border-sm"
                      style="width: 0.85rem; height: 0.85rem"
                    ></span>
                    Updating...
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.mobile-list {
  padding-bottom: 1rem;
}

.inventory-card {
  border: 1px solid #e9ecef;
  border-radius: 16px;
  transition: all 0.2s ease;
  background: #fff;
}

.inventory-card:hover {
  box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.08);
}

.card-selected {
  border-color: rgba(13, 110, 253, 0.35);
  background: linear-gradient(180deg, rgba(13, 110, 253, 0.05), #ffffff);
}

.meta-label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  color: #6c757d;
  margin-bottom: 0.3rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.item-title {
  font-size: 1rem;
  color: #212529;
  line-height: 1.35;
}

.title-field,
.name-field {
  padding: 0.35rem 0.45rem;
  border-radius: 10px;
}

.top-meta {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.meta-block {
  min-width: 0;
}

.chip-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.soft-badge {
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.5rem 0.75rem;
  border: 1px solid transparent;
}

.category-badge {
  background: #eef6ff;
  color: #0d6efd;
  border-color: #cfe2ff;
}

.unit-badge {
  background: #f3f4f6;
  color: #495057;
  border-color: #dee2e6;
}

.transition-all {
  transition: all 0.2s ease;
}

.card:active {
  transform: scale(0.99);
}

.editable-field {
  cursor: pointer;
  padding: 4px;
  border-radius: 10px;
  transition: background-color 0.2s ease, border-color 0.2s ease;
  position: relative;
}

.editable-field:hover {
  background-color: #f8f9fa;
}

.editable-field .edit-icon {
  opacity: 0;
  transition: opacity 0.2s ease;
}

.editable-field:hover .edit-icon {
  opacity: 0.55;
}

.value-box {
  background: #f8f9fa;
  border: 1px solid #eef1f4;
  border-radius: 12px;
  padding: 0.55rem 0.7rem;
  min-height: 42px;
  display: flex;
  align-items: center;
}

.field-updating {
  background-color: #fff3cd !important;
}

.field-success {
  background-color: #d1e7dd !important;
}

.field-error {
  background-color: #f8d7da !important;
  color: #721c24 !important;
}

.status-row {
  border-top: 1px dashed #e9ecef;
  padding-top: 0.75rem;
}

.form-control,
.form-select {
  border-radius: 10px;
  min-height: 40px;
}

.form-control:focus,
.form-select:focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.18);
}

@media (max-width: 576px) {
  .card-body {
    padding: 0.9rem !important;
  }

  .soft-badge {
    font-size: 0.74rem;
    padding: 0.45rem 0.65rem;
  }

  .item-title {
    font-size: 0.95rem;
  }
}
</style>
