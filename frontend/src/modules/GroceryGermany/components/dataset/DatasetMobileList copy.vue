<script setup>
import { defineProps, defineEmits, ref, nextTick, computed } from 'vue'
import { useDatasetStore } from '@/modules/GroceryIndia/stores/datasetStore'

const props = defineProps({
  datasets: { type: Array, required: true },
  isLoading: { type: Boolean, default: false },
  units: { type: Array, default: () => [] } // Units from config store
})

const emit = defineEmits(['toggle-item', 'cell-updated', 'cell-error'])
const datasetStore = useDatasetStore()

// Edit state
const editingCell = ref({ rowIndex: null, field: null })
const editValue = ref(null)
const originalValue = ref(null)
const editInputRef = ref(null)

// Cell state tracking
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
  // Fallback
  return [
    { value: 'PCS', label: 'Pieces' },
    { value: 'BTL', label: 'Bottle' },
    { value: 'PCK', label: 'Pack' },
    { value: 'KG', label: 'Kilogram' },
    { value: 'LTR', label: 'Liter' }
  ]
})

// Field configuration
const fieldConfig = {
  itemName: { type: 'text', label: 'Item Name', colIndex: 1 },
  quantity: { type: 'number', label: 'Quantity', colIndex: 2 },
  minimumStockAlert: { type: 'number', label: 'Min Stock', colIndex: 3 },
  mrp: { type: 'number', label: 'MRP', colIndex: 4 },
  salePrice: { type: 'number', label: 'Sale Price', colIndex: 5 },
  unit: { type: 'select', label: 'Unit', colIndex: 6 },
  hsn: { type: 'text', label: 'HSN', colIndex: 7 },
  gst: { type: 'number', label: 'GST', colIndex: 8 },
  cess: { type: 'number', label: 'CESS', colIndex: 9 }
}

// Helper functions
const getCellKey = (rowIndex, field) => `${rowIndex}-${field}`

const isCellUpdating = (rowIndex, field) => {
  return updatingCells.value.has(getCellKey(rowIndex, field))
}

const isCellSuccess = (rowIndex, field) => {
  return successCells.value.has(getCellKey(rowIndex, field))
}

const isCellError = (rowIndex, field) => {
  return errorCells.value.has(getCellKey(rowIndex, field))
}

const hasStoreError = (rowIndex, field) => {
  const colIndex = fieldConfig[field]?.colIndex
  return colIndex ? datasetStore.isCellError(rowIndex, colIndex) : false
}

const isEditing = (rowIndex, field) => {
  return editingCell.value.rowIndex === rowIndex && editingCell.value.field === field
}

// Get display label for unit
const getUnitLabel = (shortUnit) => {
  const unit = unitOptions.value.find(u => u.value === shortUnit)
  return unit ? unit.label : shortUnit
}

// Start editing
const startEdit = (rowIndex, field, dataset) => {
  if (props.isLoading) return
  
  originalValue.value = dataset[field]
  editValue.value = dataset[field]
  editingCell.value = { rowIndex, field }
  
  nextTick(() => {
    if (editInputRef.value) {
      const input = Array.isArray(editInputRef.value) ? editInputRef.value[0] : editInputRef.value
      input?.focus()
      if (input?.select && fieldConfig[field].type !== 'select') {
        input.select()
      }
    }
  })
}

// Save cell
const saveCell = async (dataset, rowIndex, field) => {
  const cellKey = getCellKey(rowIndex, field)
  const config = fieldConfig[field]
  
  if (editValue.value === originalValue.value) {
    console.log('No change detected, skipping save')
    editingCell.value = { rowIndex: null, field: null }
    return
  }
  
  try {
    dataset[field] = editValue.value
    
    updatingCells.value.add(cellKey)
    errorCells.value.delete(cellKey)
    
    console.log('Calling API for mobile cell...', {
      rowIndex,
      field,
      colIndex: config.colIndex,
      oldValue: originalValue.value,
      newValue: editValue.value
    })
    
    const result = await datasetStore.updateCellData(dataset, rowIndex, config.colIndex)
    
    console.log('Mobile API Response:', result)
    
    updatingCells.value.delete(cellKey)
    
    successCells.value.add(cellKey)
    
    editingCell.value = { rowIndex: null, field: null }
    
    emit('cell-updated', result.message || 'Cell updated successfully')
    
    setTimeout(() => {
      successCells.value.delete(cellKey)
    }, 3000)
    
  } catch (error) {
    console.error('Mobile update failed:', error)
    
    dataset[field] = originalValue.value
    
    updatingCells.value.delete(cellKey)
    
    errorCells.value.add(cellKey)
    
    editingCell.value = { rowIndex: null, field: null }
    
    emit('cell-error', error.message || 'Failed to update cell')
    
    setTimeout(() => {
      errorCells.value.delete(cellKey)
    }, 5000)
  }
}

// Cancel edit
const cancelEdit = (dataset) => {
  dataset[editingCell.value.field] = originalValue.value
  editingCell.value = { rowIndex: null, field: null }
  editValue.value = null
  originalValue.value = null
}

// Handle keydown
const handleKeydown = (event, dataset, rowIndex, field) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    console.log('Enter pressed - saving mobile cell')
    saveCell(dataset, rowIndex, field)
  } else if (event.key === 'Escape') {
    event.preventDefault()
    console.log('Escape pressed - canceling mobile edit')
    cancelEdit(dataset)
  }
}

// Handle blur
const handleBlur = (dataset, rowIndex, field) => {
  console.log('Blur - saving mobile cell')
  saveCell(dataset, rowIndex, field)
}

// Handle select change
const handleSelectChange = (dataset, rowIndex, field) => {
  console.log('Select changed - saving mobile cell')
  saveCell(dataset, rowIndex, field)
}

// Format display value
const formatValue = (value, field) => {
  if (field === 'mrp' || field === 'salePrice') {
    return `₹${parseFloat(value).toFixed(2)}`
  } else if (field === 'gst' || field === 'cess') {
    return `${parseFloat(value).toFixed(2)}%`
  }
  return value
}
</script>

<template>
  <div class="mobile-list">
    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-5">
      <div class="spinner-border text-primary mb-2" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="text-muted mb-0 small">Loading datasets...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="datasets.length === 0" class="text-center py-5">
      <i class="bi bi-inbox d-block mb-2 text-muted" style="font-size: 3rem"></i>
      <p class="text-muted mb-0">No datasets found</p>
    </div>

    <!-- Dataset Cards -->
    <div v-else class="row g-3">
      <div 
        v-for="(dataset, index) in datasets" 
        :key="dataset.id" 
        class="col-12"
      >
        <div 
          class="card shadow-sm h-100 transition-all"
          :class="{ 
            'border-primary border-2 bg-primary bg-opacity-10': dataset.selected,
            'border-0': !dataset.selected
          }"
        >
          <div class="card-body p-3">
            
            <!-- Header with checkbox -->
            <div class="d-flex align-items-start gap-3 mb-3 pb-3 border-bottom">
              <input 
                type="checkbox" 
                class="form-check-input mt-1 flex-shrink-0" 
                style="width: 1.25rem; height: 1.25rem"
                :checked="dataset.selected" 
                @change="emit('toggle-item', dataset.id)"
                :disabled="isLoading"
              />
              <div class="flex-grow-1">
                <!-- Item Name - Editable -->
                <div 
                  v-if="!isEditing(index, 'itemName')"
                  class="editable-field mb-1"
                  :class="{
                    'field-updating': isCellUpdating(index, 'itemName'),
                    'field-success': isCellSuccess(index, 'itemName'),
                    'field-error': isCellError(index, 'itemName') || hasStoreError(index, 'itemName')
                  }"
                  @click="startEdit(index, 'itemName', dataset)"
                >
                  <h6 class="fw-semibold mb-0 d-flex align-items-center gap-2">
                    {{ dataset.itemName }}
                    <i class="bi bi-pencil-fill small text-muted edit-icon"></i>
                    <span 
                      v-if="isCellUpdating(index, 'itemName')" 
                      class="spinner-border spinner-border-sm text-primary"
                      style="width: 0.9rem; height: 0.9rem"
                    ></span>
                    <i v-if="isCellSuccess(index, 'itemName')" class="bi bi-check-circle-fill text-success"></i>
                    <i v-if="isCellError(index, 'itemName') || hasStoreError(index, 'itemName')" class="bi bi-exclamation-circle-fill text-danger"></i>
                  </h6>
                </div>
                <div v-else class="mb-1">
                  <input 
                    v-model="editValue"
                    type="text"
                    class="form-control form-control-sm border-primary"
                    @keydown="handleKeydown($event, dataset, index, 'itemName')"
                    @blur="handleBlur(dataset, index, 'itemName')"
                    ref="editInputRef"
                  />
                </div>
                
                <!-- Unit - Editable -->
                <div 
                  v-if="!isEditing(index, 'unit')"
                  class="editable-field d-inline-block"
                  :class="{
                    'field-updating': isCellUpdating(index, 'unit'),
                    'field-success': isCellSuccess(index, 'unit'),
                    'field-error': isCellError(index, 'unit') || hasStoreError(index, 'unit')
                  }"
                  @click="startEdit(index, 'unit', dataset)"
                >
                  <span class="badge bg-light text-dark border d-flex align-items-center gap-1">
                    {{ getUnitLabel(dataset.unit) }}
                    <i class="bi bi-chevron-down small"></i>
                    <span 
                      v-if="isCellUpdating(index, 'unit')" 
                      class="spinner-border spinner-border-sm text-primary ms-1"
                      style="width: 0.7rem; height: 0.7rem"
                    ></span>
                    <i v-if="isCellSuccess(index, 'unit')" class="bi bi-check-circle-fill text-success ms-1 small"></i>
                    <i v-if="isCellError(index, 'unit') || hasStoreError(index, 'unit')" class="bi bi-exclamation-circle-fill text-danger ms-1 small"></i>
                  </span>
                </div>
                <div v-else class="d-inline-block">
                  <select 
                    v-model="editValue"
                    class="form-select form-select-sm border-primary"
                    style="width: auto; min-width: 120px"
                    @change="handleSelectChange(dataset, index, 'unit')"
                    ref="editInputRef"
                  >
                    <option v-for="unit in unitOptions" :key="unit.value" :value="unit.value">
                      {{ unit.label }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Rest of the fields (same as before) -->
            <div class="row g-3">
              <!-- All other fields remain the same -->
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

.transition-all {
  transition: all 0.2s ease;
}

.card:active {
  transform: scale(0.98);
}

/* Editable fields */
.editable-field {
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
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
  opacity: 0.5;
}

/* Field states - Same as desktop */
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

/* Form inputs in edit mode */
.form-control:focus,
.form-select:focus {
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
