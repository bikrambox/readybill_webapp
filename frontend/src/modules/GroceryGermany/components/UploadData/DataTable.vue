<template>
  <div class="table-wrapper">
    <!-- Search Bar -->
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
            {{ $t('common.Click on any cell to edit') }}
          </span>
        </div>
      </div>
    </div>

    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
      <table class="table table-hover table-bordered mb-0" id="excelTable">
        <thead>
          <tr>
            <th style="width: 50px;">
              <input
                type="checkbox"
                v-model="selectAll"
                @change="handleSelectAll"
                class="form-check-input"
              />
            </th>
            <th style="min-width: 200px;">{{ $t('common.Item Name') }}</th>
            <th style="min-width: 100px;">{{ $t('common.Quantity') }}</th>
            <th style="min-width: 120px;">{{ $t('common.Min Stock Alert') }}</th>
            <th style="min-width: 100px;">{{ $t('common.MRP') }}</th>
            <th style="min-width: 100px;">{{ $t('common.Sale Price') }}</th>
            <th style="min-width: 150px;">{{ $t('common.Unit') }}</th>
            <th style="min-width: 100px;">{{ $t('common.Barcode') }}</th>
            <th style="min-width: 100px;">{{ $t('common.VAT (%)') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ $t('common.Loading') }}...</span>
              </div>
              <p class="text-muted mt-2 mb-0">{{ $t('common.Loading data') }}...</p>
            </td>
          </tr>
          <tr v-else-if="paginatedData.length === 0">
            <td colspan="10" class="text-center py-4 text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              {{ $t('common.No data available') }}
            </td>
          </tr>
          <tr
            v-else
            v-for="(row, rowIndex) in paginatedData"
            :key="row.id + '-' + rowIndex"
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
            
            <!-- Column 1: Item Name -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 1)"
              @click="startEdit(row, row.original_index, 1)"
            >
              <div v-if="!isEditing(row.original_index, 1)" class="cell-display">
                {{ row.item_name }}
                <span v-if="isCellUpdating(row.original_index, 1)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 1)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 1)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="text"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 1)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 1)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 1)"
                />
              </div>
            </td>
            
            <!-- Column 2: Quantity -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 2)"
              @click="startEdit(row, row.original_index, 2)"
            >
              <div v-if="!isEditing(row.original_index, 2)" class="cell-display">
                {{ parseFloat(row.quantity).toFixed(2) }}
                <span v-if="isCellUpdating(row.original_index, 2)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 2)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 2)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="number"
                  step="0.01"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 2)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 2)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 2)"
                />
              </div>
            </td>
            
            <!-- Column 3: Min Stock Alert -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 3)"
              @click="startEdit(row, row.original_index, 3)"
            >
              <div v-if="!isEditing(row.original_index, 3)" class="cell-display">
                {{ parseFloat(row.min_stock_alert).toFixed(2) }}
                <span v-if="isCellUpdating(row.original_index, 3)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 3)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 3)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="number"
                  step="0.01"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 3)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 3)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 3)"
                />
              </div>
            </td>
            
            <!-- Column 4: MRP -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 4)"
              @click="startEdit(row, row.original_index, 4)"
            >
              <div v-if="!isEditing(row.original_index, 4)" class="cell-display">
                {{ $formatCurrency(parseFloat(row.mrp).toFixed(2)) }}
                <span v-if="isCellUpdating(row.original_index, 4)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 4)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 4)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="number"
                  step="0.01"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 4)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 4)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 4)"
                />
              </div>
            </td>
            
            <!-- Column 5: Sale Price -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 5)"
              @click="startEdit(row, row.original_index, 5)"
            >
              <div v-if="!isEditing(row.original_index, 5)" class="cell-display">
                {{ $formatCurrency(parseFloat(row.sale_price).toFixed(2)) }}
                <span v-if="isCellUpdating(row.original_index, 5)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 5)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 5)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="number"
                  step="0.01"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 5)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 5)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 5)"
                />
              </div>
            </td>
            
            <!-- Column 6: Unit (Select) -->
            <td 
              class="editable-cell select-cell"
              :class="getCellClasses(row.original_index, 6)"
            >
              <div class="cell-display position-relative">
                <select
                  v-model="row.unit"
                  class="form-select form-select-sm"
                  :class="{ 'border-danger': isCellError(row.original_index, 6) }"
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
                <span v-if="isCellUpdating(row.original_index, 6)" class="select-indicator">
                  <span class="spinner-border spinner-border-sm text-warning"></span>
                </span>
                <i v-if="isCellSuccess(row.original_index, 6)" class="select-indicator bi bi-check-circle-fill text-success"></i>
                <i v-if="isCellError(row.original_index, 6)" class="select-indicator bi bi-exclamation-circle-fill text-danger"></i>
              </div>
            </td>
            
            <!-- Column 7: Barcode -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 7)"
              @click="startEdit(row, row.original_index, 7)"
            >
              <div v-if="!isEditing(row.original_index, 7)" class="cell-display">
                {{ row.barcode }}
                <span v-if="isCellUpdating(row.original_index, 7)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 7)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 7)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="text"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 7)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 7)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 7)"
                />
              </div>
            </td>
            
            <!-- Column 8: VAT -->
            <td 
              class="editable-cell"
              :class="getCellClasses(row.original_index, 8)"
              @click="startEdit(row, row.original_index, 8)"
            >
              <div v-if="!isEditing(row.original_index, 8)" class="cell-display">
                {{ parseFloat(row.vat).toFixed(2) }}
                <span v-if="isCellUpdating(row.original_index, 8)" class="spinner-border spinner-border-sm text-warning ms-2"></span>
                <i v-if="isCellSuccess(row.original_index, 8)" class="bi bi-check-circle-fill text-success ms-2"></i>
                <i v-if="isCellError(row.original_index, 8)" class="bi bi-exclamation-circle-fill text-danger ms-2"></i>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setEditInputRef(el)"
                  v-model="editValue"
                  type="number"
                  step="0.01"
                  class="form-control form-control-sm"
                  @blur="saveCell(row, row.original_index, 8)"
                  @keydown.enter.prevent="saveCell(row, row.original_index, 8)"
                  @keydown.esc.prevent="cancelEdit(row, row.original_index, 8)"
                />
              </div>
            </td>

          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls - Screenshot Style -->
    <div class="pagination-footer p-3 border-top d-flex justify-content-between align-items-center">
      <!-- Left: Rows per page -->
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">{{ $t('common.Rows per page') }}:</span>
        <select 
          v-model="localPageSize" 
          @change="handlePageSizeChange"
          class="form-select form-select-sm pagination-select"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>

      <!-- Right: Page info and navigation -->
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted small pagination-info">{{ paginationText }}</span>
        <div class="pagination-controls d-flex align-items-center gap-1">
          <button 
            class="btn btn-sm btn-icon" 
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1 || loading"
          >
            <i class="bi bi-chevron-left"></i>
          </button>
          <button 
            class="btn btn-sm btn-icon" 
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage >= totalPages || loading"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick, getCurrentInstance } from 'vue'
import { useUploadDataStore } from '@/modules/GroceryGermany/stores/uploadDataStore'
import { useConfigStore } from '@/stores/config'
import { storeToRefs } from 'pinia'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  jobId: {
    type: String,
    required: true
  },
  pageSize: {
    type: Number,
    default: 100
  }
})

const emit = defineEmits(['selection-change', 'refresh'])

const uploadDataStore = useUploadDataStore()
const configStore = useConfigStore()

// Get units from config store
const { unitsArray } = storeToRefs(configStore)


const { appContext } = getCurrentInstance();
const currency = appContext.config.globalProperties.$currency;
const countryName = appContext.config.globalProperties.$countryName;

const selectAll = ref(false)
const selectedRows = ref([])
const currentPage = ref(1)
const loading = ref(false)
const searchQuery = ref('')
const localPageSize = ref(props.pageSize)

// Debounce timer for search
let searchTimeout = null

// Editing state - similar to Dataset
const editingCell = ref({ rowIndex: null, colIndex: null })
const editInputRef = ref(null)
const editValue = ref(null)
const originalValue = ref(null)

// Cell state tracking - similar to Dataset
const updatingCells = ref(new Set())
const successCells = ref(new Set())
const errorCells = ref(new Set())

// Get unit list from config store
const unitList = computed(() => {
  if (unitsArray.value && unitsArray.value.length > 0) {
    return unitsArray.value
  }
  return uploadDataStore.unitList
})

const tableData = computed(() => uploadDataStore.excelData)
const totalRecords = computed(() => uploadDataStore.totalRecords)
const errorData = computed(() => uploadDataStore.errors)

const totalPages = computed(() => Math.ceil(totalRecords.value / localPageSize.value))

const startRecord = computed(() => {
  if (totalRecords.value === 0) return 0
  return (currentPage.value - 1) * localPageSize.value + 1
})

const endRecord = computed(() =>
  Math.min(currentPage.value * localPageSize.value, totalRecords.value)
)

const paginationText = computed(() => {
  if (totalRecords.value === 0) return '0-0 of 0'
  return `${startRecord.value}-${endRecord.value} of ${totalRecords.value}`
})

const paginatedData = computed(() => tableData.value)

const hasErrors = computed(() => {
  return errorData.value?.grid_coordinates && errorData.value.grid_coordinates.length > 0
})

const errorCount = computed(() => {
  return errorData.value?.grid_coordinates?.length || 0
})

// Cell state functions - from Dataset
const getCellKey = (rowIndex, colIndex) => `${rowIndex}-${colIndex}`

const isCellUpdating = (rowIndex, colIndex) => {
  return updatingCells.value.has(getCellKey(rowIndex, colIndex))
}

const isCellSuccess = (rowIndex, colIndex) => {
  return successCells.value.has(getCellKey(rowIndex, colIndex))
}

const isCellError = (rowIndex, colIndex) => {
  if (!errorData.value?.grid_coordinates) return false
  
  const paddedRow = String(rowIndex).padStart(2, '0')
  const paddedCol = String(colIndex).padStart(2, '0')
  const coordinate = `${paddedRow},${paddedCol}`
  
  return errorData.value.grid_coordinates.includes(coordinate)
}

const getCellClasses = (rowIndex, colIndex) => {
  return {
    'cell-updating': isCellUpdating(rowIndex, colIndex),
    'cell-success': isCellSuccess(rowIndex, colIndex),
    'cell-error': isCellError(rowIndex, colIndex)
  }
}

// Editing functions - from Dataset
const isEditing = (rowIndex, colIndex) => {
  return editingCell.value.rowIndex === rowIndex && editingCell.value.colIndex === colIndex
}

const setEditInputRef = (el) => {
  editInputRef.value = el
}

const getFieldValue = (row, colIndex) => {
  const fieldMap = {
    1: 'item_name',
    2: 'quantity',
    3: 'min_stock_alert',
    4: 'mrp',
    5: 'sale_price',
    6: 'unit',
    7: 'barcode',
    8: 'vat',
  }
  return row[fieldMap[colIndex]]
}

const setFieldValue = (row, colIndex, value) => {
  const fieldMap = {
    1: 'item_name',
    2: 'quantity',
    3: 'min_stock_alert',
    4: 'mrp',
    5: 'sale_price',
    6: 'unit',
    7: 'barcode',
    8: 'vat',
  }
  row[fieldMap[colIndex]] = value
}

const startEdit = (row, rowIndex, colIndex) => {
  if (colIndex === 6) return // Skip unit column (handled by select)
  
  const currentValue = getFieldValue(row, colIndex)
  originalValue.value = currentValue
  editValue.value = currentValue
  
  editingCell.value = { rowIndex, colIndex }
  
  nextTick(() => {
    if (editInputRef.value) {
      const input = Array.isArray(editInputRef.value) ? editInputRef.value[0] : editInputRef.value
      input?.focus()
      if (input?.select) {
        input.select()
      }
    }
  })
}

const saveCell = async (row, rowIndex, colIndex) => {
  const cellKey = getCellKey(rowIndex, colIndex)
  
  // Check if value changed
  if (editValue.value === originalValue.value) {
    console.log('No change detected, skipping save')
    editingCell.value = { rowIndex: null, colIndex: null }
    editValue.value = null
    originalValue.value = null
    return
  }
  
  try {
    // Update row data temporarily
    const oldValue = getFieldValue(row, colIndex)
    setFieldValue(row, colIndex, editValue.value)
    
    // Show updating state
    updatingCells.value.add(cellKey)
    errorCells.value.delete(cellKey)
    
    // console.log('Calling updateCellData API...', {
    //   rowIndex,
    //   colIndex,
    //   oldValue: originalValue.value,
    //   newValue: editValue.value,
    //   rowId: row.id
    // })
    
    // Prepare form data
    const formData = new FormData()
    formData.append('id', row.id)
    formData.append('item_name', row.item_name || '')
    formData.append('quantity', parseFloat(row.quantity) || 0)
    formData.append('min_stock_alert', parseFloat(row.min_stock_alert) || 0)
    formData.append('mrp', parseFloat(row.mrp) || 0)
    formData.append('sale_price', parseFloat(row.sale_price) || 0)
    formData.append('short_unit', row.unit || '')
    formData.append('barcode', row.barcode || '')
    formData.append('vat', parseFloat(row.vat) || 0)
    formData.append('row_index', rowIndex)
    formData.append('cell_index', colIndex)
    
    const result = await uploadDataStore.updateCellData(formData)
    
    // console.log('API Response:', result)
    
    // Remove updating state
    updatingCells.value.delete(cellKey)
    
    // Add success state
    successCells.value.add(cellKey)
    
    // Clear editing state
    editingCell.value = { rowIndex: null, colIndex: null }
    editValue.value = null
    originalValue.value = null
    
    // Clear cell-specific error if update successful
    uploadDataStore.clearCellError(rowIndex, colIndex)
    
    // If this was a new row (id = 0), update with real ID
    if (row.id === 0 && result.data && result.data.id) {
      row.id = result.data.id
      row.original_index = result.data.original_index || rowIndex
      row.isNew = false
      // console.log('New row created with ID:', row.id)
    }
    
    // Update the row with server response data
    if (result.data) {
      const fieldMapping = {
        id: 'id',
        item_name: 'item_name',
        quantity: 'quantity',
        min_stock_alert: 'min_stock_alert',
        mrp: 'mrp',
        sale_price: 'sale_price',
        short_unit: 'unit',
        barcode: 'barcode',
        vat: 'vat',
      }
      
      Object.keys(result.data).forEach(key => {
        if (fieldMapping[key] && row[fieldMapping[key]] !== undefined) {
          row[fieldMapping[key]] = result.data[key]
        }
      })
    }
    
    // Auto-clear success after 3 seconds
    setTimeout(() => {
      successCells.value.delete(cellKey)
    }, 3000)
    
  } catch (error) {
    console.error('Update failed:', error)
    
    // Restore original value
    setFieldValue(row, colIndex, originalValue.value)
    
    // Remove updating state
    updatingCells.value.delete(cellKey)
    
    // Add error state
    errorCells.value.add(cellKey)
    
    // Clear editing state
    editingCell.value = { rowIndex: null, colIndex: null }
    editValue.value = null
    originalValue.value = null
    
    // Auto-clear error after 5 seconds
    setTimeout(() => {
      errorCells.value.delete(cellKey)
    }, 5000)
  }
}

const cancelEdit = (row, rowIndex, colIndex) => {
  // Restore original value
  setFieldValue(row, colIndex, originalValue.value)
  
  // Clear editing state
  editingCell.value = { rowIndex: null, colIndex: null }
  editValue.value = null
  originalValue.value = null
}

const handleSelectChange = async (row, rowIndex, colIndex) => {
  const cellKey = getCellKey(rowIndex, colIndex)
  
  try {
    // Show updating state
    updatingCells.value.add(cellKey)
    errorCells.value.delete(cellKey)
    
    console.log('Unit changed, saving...', {
      rowIndex,
      colIndex,
      newValue: row.unit,
      rowId: row.id
    })
    
    // Prepare form data
    const formData = new FormData()
    formData.append('id', row.id)
    formData.append('item_name', row.item_name || '')
    formData.append('quantity', parseFloat(row.quantity) || 0)
    formData.append('min_stock_alert', parseFloat(row.min_stock_alert) || 0)
    formData.append('mrp', parseFloat(row.mrp) || 0)
    formData.append('sale_price', parseFloat(row.sale_price) || 0)
    formData.append('short_unit', row.unit || '')
    formData.append('barcode', row.barcode || '')
    formData.append('vat', parseFloat(row.vat) || 0)
    formData.append('row_index', rowIndex)
    formData.append('cell_index', colIndex)
    
    const result = await uploadDataStore.updateCellData(formData)
    
    console.log('API Response:', result)
    
    // Remove updating state
    updatingCells.value.delete(cellKey)
    
    // Add success state
    successCells.value.add(cellKey)
    
    // Clear cell-specific error if update successful
    uploadDataStore.clearCellError(rowIndex, colIndex)
    
    // If this was a new row (id = 0), update with real ID
    if (row.id === 0 && result.data && result.data.id) {
      row.id = result.data.id
      row.original_index = result.data.original_index || rowIndex
      row.isNew = false
      console.log('New row created with ID:', row.id)
    }
    
    // Update the row with server response data
    if (result.data) {
      const fieldMapping = {
        id: 'id',
        item_name: 'item_name',
        quantity: 'quantity',
        min_stock_alert: 'min_stock_alert',
        mrp: 'mrp',
        sale_price: 'sale_price',
        short_unit: 'unit',
        barcode: 'barcode',
        vat: 'vat',
      }
      
      Object.keys(result.data).forEach(key => {
        if (fieldMapping[key] && row[fieldMapping[key]] !== undefined) {
          row[fieldMapping[key]] = result.data[key]
        }
      })
    }
    
    // Auto-clear success after 3 seconds
    setTimeout(() => {
      successCells.value.delete(cellKey)
    }, 3000)
    
  } catch (error) {
    console.error('Update failed:', error)
    
    // Remove updating state
    updatingCells.value.delete(cellKey)
    
    // Add error state
    errorCells.value.add(cellKey)
    
    // Auto-clear error after 5 seconds
    setTimeout(() => {
      errorCells.value.delete(cellKey)
    }, 5000)
  }
}

const handleSelectAll = () => {
  if (selectAll.value) {
    selectedRows.value = tableData.value
      .filter(row => row.id !== 0) // Exclude new rows
      .map((row) => row.id)
  } else {
    selectedRows.value = []
  }
}

const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    uploadDataStore.setSearchQuery(searchQuery.value)
    currentPage.value = 1
    fetchData()
  }, 500) // Debounce 500ms
}

const clearSearch = () => {
  searchQuery.value = ''
  uploadDataStore.setSearchQuery('')
  currentPage.value = 1
  fetchData()
}

const handlePageSizeChange = () => {
  uploadDataStore.setPageSize(localPageSize.value)
  currentPage.value = 1
  fetchData()
}

const goToPage = async (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  await fetchData()
}

const fetchData = async () => {
  try {
    loading.value = true
    const start = (currentPage.value - 1) * localPageSize.value
    await uploadDataStore.fetchExcelData(props.jobId, {
      start,
      length: localPageSize.value,
      draw: currentPage.value
    })
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
}

watch(selectedRows, (newVal) => {
  emit('selection-change', newVal)
  const nonNewRows = tableData.value.filter(row => row.id !== 0)
  selectAll.value = newVal.length === nonNewRows.length && nonNewRows.length > 0
})

watch(() => props.pageSize, (newVal) => {
  localPageSize.value = newVal
})

watch(() => props.jobId, () => {
  currentPage.value = 1
  selectedRows.value = []
  selectAll.value = false
  searchQuery.value = ''
  uploadDataStore.setSearchQuery('')
  fetchData()
}, { immediate: false })

onMounted(async () => {
  // // Fetch config data first to get units
  // if (!unitsArray.value || unitsArray.value.length === 0) {
  //   configStore.fetchConfigData()
  // }

  await Promise.all([
    configStore.fetchConfigData(false, countryName.toLowerCase()),
    // Switch to German locale
    configStore.switchLocale(countryName.toLowerCase()),
  ]);

  fetchData()
})
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

/* New row highlight */
tbody tr.table-warning {
  background-color: #fff3cd !important;
}

/* Editable cell styling - from Dataset */
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

/* Cell state colors - from Dataset */
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
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.01); }
}

/* Select specific styling */
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

/* Checkbox styling */
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
