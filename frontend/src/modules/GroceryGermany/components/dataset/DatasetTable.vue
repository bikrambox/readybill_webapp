<script setup>
import { defineProps, defineEmits, computed, ref, watch, nextTick } from 'vue'
import { useDatasetStore } from '@/modules/GroceryGermany/stores/datasetStore'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
  datasets: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
  currentPage: { type: Number, default: 0 },
  pageLength: { type: Number, default: 10 },
  totalRecords: { type: Number, default: 0 },
  filteredRecords: { type: Number, default: 0 },
  units: { type: Array, default: () => [] } // Units from config store
})

const emit = defineEmits([
  'toggle-item', 
  'toggle-all',
  'page-change',
  'page-length-change',
  'cell-updated',
  'cell-error'
])

const datasetStore = useDatasetStore()
const tableKey = ref(0)
const editInputRef = ref(null)
const editValue = ref(null)
const originalValue = ref(null)

const allSelected = computed(() =>
  props.datasets.length > 0 && props.datasets.every(d => d.selected)
)

const editingCell = ref({ rowIndex: null, colIndex: null })
const updatingCells = ref(new Set())
const successCells = ref(new Set())
const errorCells = ref(new Set())

// Compute unit options from props.units
const unitOptions = computed(() => {
  if (props.units && props.units.length > 0) {
    return props.units.map(unit => ({
      value: unit.value,
      label: unit.label
    }))
  }
  // Fallback to hardcoded values if config not loaded
  return [
    { value: 'PCS', label: 'Pieces' },
    { value: 'BTL', label: 'Bottle' },
    { value: 'PCK', label: 'Pack' },
    { value: 'KG', label: 'Kilogram' },
    { value: 'LTR', label: 'Liter' }
  ]
})

const columnConfig = [
  { key: 'flag', editable: false, type: 'checkbox' },
  { key: 'itemName', editable: true, type: 'text', field: 'item_name', label: 'Item Name' },
  { key: 'quantity', editable: true, type: 'number', field: 'quantity', label: 'Quantity' },
  { key: 'minimumStockAlert', editable: true, type: 'number', field: 'min_stock_alert', label: 'Min Stock Alert' },
  { key: 'mrp', editable: true, type: 'number', field: 'mrp', label: 'MRP' },
  { key: 'salePrice', editable: true, type: 'number', field: 'sale_price', label: 'Sale Price' },
  { key: 'unit', editable: true, type: 'select', field: 'short_unit', label: 'Unit' }, // Will use dynamic options
  // { key: 'hsn', editable: true, type: 'text', field: 'hsn', label: 'HSN' },
  { key: 'vat', editable: true, type: 'number', field: 'vat', label: 'VAT' },
  // { key: 'cess', editable: true, type: 'number', field: 'cess', label: 'CESS' }
]

// Watch for data changes
watch(() => props.datasets, (newData, oldData) => {
  console.log('🔄 Table data changed:', {
    oldCount: oldData?.length || 0,
    newCount: newData.length
  })
  tableKey.value++
}, { deep: true })

watch(() => props.filteredRecords, (newVal, oldVal) => {
  console.log('📊 RecordsFiltered changed:', { old: oldVal, new: newVal })
  if (newVal !== oldVal) {
    tableKey.value++
  }
})

// Pagination computeds
const paginationInfo = computed(() => {
  if (props.filteredRecords === 0) {
    return '0-0 of 0'
  }
  const start = props.currentPage * props.pageLength + 1
  const end = Math.min((props.currentPage + 1) * props.pageLength, props.filteredRecords)
  return `${start}-${end} of ${props.filteredRecords}`
})

const totalPages = computed(() => {
  return Math.ceil(props.filteredRecords / props.pageLength) || 1
})

const canGoPrev = computed(() => props.currentPage > 0)
const canGoNext = computed(() => props.currentPage < totalPages.value - 1)

// Cell state functions
const getCellKey = (rowIndex, colIndex) => `${rowIndex}-${colIndex}`

const isCellUpdating = (rowIndex, colIndex) => {
  return updatingCells.value.has(getCellKey(rowIndex, colIndex))
}

const isCellSuccess = (rowIndex, colIndex) => {
  return successCells.value.has(getCellKey(rowIndex, colIndex))
}

const isCellError = (rowIndex, colIndex) => {
  return errorCells.value.has(getCellKey(rowIndex, colIndex))
}

// Cell editing functions
const isEditing = (rowIndex, colIndex) => {
  return editingCell.value.rowIndex === rowIndex && editingCell.value.colIndex === colIndex
}

const startEdit = (rowIndex, colIndex) => {
  if (columnConfig[colIndex]?.editable && !props.isLoading) {
    const dataset = props.datasets[rowIndex]
    const config = columnConfig[colIndex]
    
    originalValue.value = dataset[config.key]
    editValue.value = dataset[config.key]
    
    editingCell.value = { rowIndex, colIndex }
    
    nextTick(() => {
      if (editInputRef.value) {
        const input = Array.isArray(editInputRef.value) ? editInputRef.value[0] : editInputRef.value
        input?.focus()
        if (input?.select && config.type !== 'select') {
          input.select()
        }
      }
    })
  }
}

const saveCell = async (dataset, rowIndex, colIndex) => {
  const cellKey = getCellKey(rowIndex, colIndex)
  const config = columnConfig[colIndex]
  
  if (editValue.value === originalValue.value) {
    console.log('No change detected, skipping save')
    editingCell.value = { rowIndex: null, colIndex: null }
    return
  }
  
  try {
    dataset[config.key] = editValue.value
    
    updatingCells.value.add(cellKey)
    errorCells.value.delete(cellKey)
    
    console.log('Calling updateCellData API...', {
      rowIndex,
      colIndex,
      oldValue: originalValue.value,
      newValue: editValue.value
    })
    
    const result = await datasetStore.updateCellData(dataset, rowIndex, colIndex)
    
    console.log('API Response:', result)
    
    updatingCells.value.delete(cellKey)
    
    successCells.value.add(cellKey)
    
    editingCell.value = { rowIndex: null, colIndex: null }
    
    emit('cell-updated', result.message || t('common.Cell updated successfully'))
    
    setTimeout(() => {
      successCells.value.delete(cellKey)
    }, 3000)
    
  } catch (error) {
    console.error('Update failed:', error)
    
    dataset[config.key] = originalValue.value
    
    updatingCells.value.delete(cellKey)
    
    errorCells.value.add(cellKey)
    
    editingCell.value = { rowIndex: null, colIndex: null }
    
    emit('cell-error', error.message || t('common.Failed to update cell'))
    
    setTimeout(() => {
      errorCells.value.delete(cellKey)
    }, 5000)
  }
}

const cancelEdit = () => {
  editingCell.value = { rowIndex: null, colIndex: null }
  editValue.value = null
  originalValue.value = null
}

const getCellValue = (dataset, colIndex) => {
  const config = columnConfig[colIndex]
  return dataset[config.key]
}

// Get display label for unit
const getUnitLabel = (shortUnit) => {
  const unit = unitOptions.value.find(u => u.value === shortUnit)
  return unit ? unit.label : shortUnit
}

const handleKeydown = (event, dataset, rowIndex, colIndex) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    console.log('Enter key pressed - saving cell')
    saveCell(dataset, rowIndex, colIndex)
  } else if (event.key === 'Escape') {
    event.preventDefault()
    console.log('Escape key pressed - canceling edit')
    
    const config = columnConfig[colIndex]
    dataset[config.key] = originalValue.value
    
    cancelEdit()
  }
}

const handleBlur = (dataset, rowIndex, colIndex) => {
  console.log('Blur event - saving cell')
  saveCell(dataset, rowIndex, colIndex)
}

const handleSelectChange = (dataset, rowIndex, colIndex) => {
  console.log('Select changed - saving cell')
  saveCell(dataset, rowIndex, colIndex)
}

// Pagination handlers
const handlePageLengthChange = (event) => {
  if (props.isLoading) return
  const newLength = parseInt(event.target.value)
  emit('page-length-change', newLength)
}

const handlePrevPage = () => {
  if (canGoPrev.value && !props.isLoading) {
    emit('page-change', props.currentPage - 1)
  }
}

const handleNextPage = () => {
  if (canGoNext.value && !props.isLoading) {
    emit('page-change', props.currentPage + 1)
  }
}
</script>

<template>
  <div class="card border-0 shadow-sm position-relative">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table 
          ref="tableRef" 
          :key="`table-refresh-${tableKey}`"
          class="table table-hover align-middle mb-0"
          style="width: 100%"
        >
          <thead class="table-light">
            <tr>
              <th class="ps-4" style="width: 48px">
                <input 
                  type="checkbox" 
                  class="form-check-input" 
                  :checked="allSelected" 
                  @change="emit('toggle-all')"
                  :disabled="isLoading"
                >
              </th>
              <th 
                v-for="(config, colIndex) in columnConfig.slice(1)" 
                :key="colIndex" 
                :class="{
                  'text-center': !['mrp', 'salePrice'].includes(config.key),
                  'text-end pe-3': ['mrp', 'salePrice'].includes(config.key)
                }"
              >
                {{ config.label }}
              </th>
            </tr>
          </thead>
          <tbody>
            <!-- Loading State -->
            <template v-if="isLoading">
              <tr>
                <td :colspan="columnConfig.length" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
                  </div>
                  <span class="ms-2">{{ $t('common.Loading data') }}...</span>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <template v-else-if="datasets.length === 0">
              <tr>
                <td :colspan="columnConfig.length" class="text-center py-4 text-muted">
                  <i class="bi bi-inbox d-block mb-2" style="font-size: 2.5rem;"></i>
                  {{ $t('common.No datasets found') }}
                </td>
              </tr>
            </template>

            <!-- Data Rows -->
            <template v-else>
              <tr 
                v-for="(dataset, rowIndex) in datasets" 
                :key="`row-${dataset.id}-${tableKey}`" 
                :class="{ 'table-active': dataset.selected }"
              >
                <td class="ps-4">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :checked="dataset.selected" 
                    @change="emit('toggle-item', dataset.id)"
                    :disabled="isLoading"
                  >
                </td>
                
                <!-- Dynamic cells -->
                <td 
                  v-for="(config, colIndex) in columnConfig.slice(1)" 
                  :key="colIndex"
                  :class="{
                    'text-center': !['mrp', 'salePrice'].includes(config.key),
                    'text-end pe-3': ['mrp', 'salePrice'].includes(config.key),
                    'bg-danger bg-opacity-10 text-danger': datasetStore.isCellError(rowIndex, colIndex + 1),
                    'position-relative': isEditing(rowIndex, colIndex + 1),
                    'cursor-pointer': columnConfig[colIndex + 1]?.editable,
                    'cell-updating': isCellUpdating(rowIndex, colIndex + 1),
                    'cell-success': isCellSuccess(rowIndex, colIndex + 1),
                    'cell-error': isCellError(rowIndex, colIndex + 1)
                  }"
                  @click="startEdit(rowIndex, colIndex + 1)"
                  tabindex="0"
                >
                  <!-- Display mode -->
                  <template v-if="!isEditing(rowIndex, colIndex + 1)">
                    <span 
                      :class="{ 
                        'fw-semibold': config.key === 'itemName',
                        'badge bg-light text-dark border': config.key === 'unit'
                      }"
                    >
                      <template v-if="config.key === 'unit'">
                        {{ getUnitLabel(getCellValue(dataset, colIndex + 1)) }}
                        <i class="bi bi-chevron-down ms-1 small"></i>
                      </template>
                      <template v-else-if="['mrp', 'salePrice'].includes(config.key)">
                        <!-- ₹{{ getCellValue(dataset, colIndex + 1).toFixed(2) }} -->
                        {{ $formatCurrency(getCellValue(dataset, colIndex + 1)) }}
                      </template>
                      <template v-else>
                        {{ getCellValue(dataset, colIndex + 1) }}
                      </template>
                    </span>
                    
                    <!-- Success icon -->
                    <i 
                      v-if="isCellSuccess(rowIndex, colIndex + 1)" 
                      class="bi bi-check-circle-fill text-success ms-2"
                    ></i>
                    
                    <!-- Error icon -->
                    <i 
                      v-if="isCellError(rowIndex, colIndex + 1) || datasetStore.isCellError(rowIndex, colIndex + 1)" 
                      class="bi bi-exclamation-circle-fill text-danger ms-2"
                    ></i>
                    
                    <!-- Updating spinner -->
                    <span 
                      v-if="isCellUpdating(rowIndex, colIndex + 1)" 
                      class="spinner-border spinner-border-sm text-primary ms-2" 
                      role="status"
                      style="width: 1rem; height: 1rem;"
                    >
                      <span class="visually-hidden">{{ $t('common.Updating') }}...</span>
                    </span>
                  </template>

                  <!-- Edit mode -->
                  <div v-else class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center px-2 bg-white edit-cell">
                    <input 
                      v-if="config.type === 'text'"
                      v-model="editValue" 
                      class="form-control form-control-sm border-primary shadow-sm"
                      @keydown="handleKeydown($event, dataset, rowIndex, colIndex + 1)"
                      @blur="handleBlur(dataset, rowIndex, colIndex + 1)"
                      autocomplete="off"
                      ref="editInputRef"
                    />
                    
                    <input 
                      v-else-if="config.type === 'number'"
                      v-model.number="editValue" 
                      type="number"
                      step="0.01"
                      min="0"
                      class="form-control form-control-sm text-end border-primary shadow-sm"
                      @keydown="handleKeydown($event, dataset, rowIndex, colIndex + 1)"
                      @blur="handleBlur(dataset, rowIndex, colIndex + 1)"
                      autocomplete="off"
                      ref="editInputRef"
                    />

                    <select 
                      v-else-if="config.type === 'select'"
                      v-model="editValue"
                      class="form-select form-select-sm border-primary shadow-sm"
                      @keydown="handleKeydown($event, dataset, rowIndex, colIndex + 1)"
                      @change="handleSelectChange(dataset, rowIndex, colIndex + 1)"
                      ref="editInputRef"
                    >
                      <option v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                        {{ unit.label }}
                      </option>
                    </select>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Custom Footer with Pagination -->
    <div class="card-footer bg-white border-top">
      <div class="d-flex justify-content-between align-items-center">
        <!-- Left: Rows per page -->
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted small fw-medium">{{ $t('common.Rows per page') }}:</span>
          <select 
            class="form-select form-select-sm" 
            style="width: auto;"
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
        
        <!-- Right: Info and Arrows -->
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
/* Cursor pointer for editable cells */
.cursor-pointer {
  cursor: pointer;
}

.cursor-pointer:hover:not(.bg-danger):not(.cell-error) {
  background-color: #f8f9fa;
}

/* Edit cell overlay */
.edit-cell {
  z-index: 10;
}

.edit-cell .form-control:focus,
.edit-cell .form-select:focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

/* Focus state for cells */
td:focus-within:not(.bg-danger):not(.cell-error) {
  background-color: #e7f1ff !important;
  border-left: 3px solid #0d6efd;
}

/* Cell updating state */
.cell-updating {
  background-color: #fff3cd !important;
  transition: background-color 0.3s ease;
}

/* Cell success state */
.cell-success {
  background-color: #d1e7dd !important;
  transition: background-color 0.3s ease;
}

/* Cell error state */
.cell-error {
  background-color: #f8d7da !important;
  color: #721c24 !important;
  transition: background-color 0.3s ease;
}

/* Table styling */
.table thead th {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  padding: 12px;
  font-size: 14px;
  font-weight: 600;
  color: #6c757d;
  white-space: nowrap;
}

.table tbody td {
  padding: 14px 12px;
  border-bottom: 1px solid #dee2e6;
  font-size: 14px;
  color: #212529;
  vertical-align: middle;
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

/* Buttons */
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Footer */
.card-footer {
  padding: 16px 20px;
}

/* Mobile */
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
