<script setup>
import { defineProps, defineEmits, computed, ref } from 'vue'
import { useDatasetStore } from '@/modules/GroceryGermany/stores/datasetStore'

const props = defineProps({
  datasets: { type: Array, required: true }
})

const emit = defineEmits(['toggle-item', 'toggle-all'])
const datasetStore = useDatasetStore()

const allSelected = computed(() =>
  props.datasets.length > 0 && props.datasets.every(d => d.selected)
)

const editingCell = ref({ rowIndex: null, colIndex: null })

// Column definitions for mapping
const columnConfig = [
  { key: 'flag', editable: false, type: 'checkbox' },
  { key: 'itemName', editable: true, type: 'text', field: 'item_name' },
  { key: 'quantity', editable: true, type: 'number', field: 'quantity' },
  { key: 'minimumStockAlert', editable: true, type: 'number', field: 'min_stock_alert' },
  { key: 'mrp', editable: true, type: 'number', field: 'mrp' },
  { key: 'salePrice', editable: true, type: 'number', field: 'sale_price' },
  { key: 'unit', editable: true, type: 'select', field: 'short_unit', options: ['PCS', 'BTL', 'PCK', 'KG', 'LTR'] },
  { key: 'vat', editable: true, type: 'number', field: 'vat' }
]

// Check if cell is being edited
const isEditing = (rowIndex, colIndex) => {
  return editingCell.value.rowIndex === rowIndex && editingCell.value.colIndex === colIndex
}

// Start editing cell
const startEdit = (rowIndex, colIndex) => {
  if (columnConfig[colIndex]?.editable) {
    editingCell.value = { rowIndex, colIndex }
  }
}

// Save cell data
const saveCell = async (dataset, rowIndex, colIndex) => {
  try {
    await datasetStore.updateCellData(dataset, rowIndex, colIndex)
    editingCell.value = { rowIndex: null, colIndex: null }
  } catch (error) {
    console.error('Update failed:', error)
    // Revert changes or show error
  }
}

// Cancel editing
const cancelEdit = () => {
  editingCell.value = { rowIndex: null, colIndex: null }
}

// Get cell value
const getCellValue = (dataset, colIndex) => {
  const config = columnConfig[colIndex]
  return dataset[config.key]
}

// Handle Enter/Escape keys
const handleKeydown = (event, dataset, rowIndex, colIndex) => {
  if (event.key === 'Enter') {
    event.preventDefault()
    saveCell(dataset, rowIndex, colIndex)
  } else if (event.key === 'Escape') {
    event.preventDefault()
    cancelEdit()
  }
}
</script>

<template>
  <div>
    <!-- Desktop Table -->
    <div class="d-none d-md-block bg-white rounded shadow-sm overflow-auto mx-2">
      <table class="table table-hover mb-0 align-middle responsive-table">
        <thead class="table-light">
          <tr>
            <th style="width:48px">
              <input 
                type="checkbox" 
                class="form-check-input" 
                :checked="allSelected" 
                @change="emit('toggle-all')"
              >
            </th>
            <th v-for="(config, colIndex) in columnConfig.slice(1)" 
                :key="colIndex" 
                class="text-center"
                :class="{
                  'text-end': ['mrp', 'salePrice'].includes(config.key)
                }"
            >
              {{ config.key.replace(/([A-Z])/g, ' $1').replace(/^./, str => str.toUpperCase()) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(dataset, rowIndex) in datasets" 
            :key="dataset.id" 
            :class="{ 'table-active': dataset.selected }"
          >
            <td>
              <input 
                type="checkbox" 
                class="form-check-input" 
                :checked="dataset.selected" 
                @change="emit('toggle-item', dataset.id)"
              >
            </td>
            
            <!-- Dynamic cells -->
            <td 
              v-for="(config, colIndex) in columnConfig.slice(1)" 
              :key="colIndex"
              :class="{
                'text-center': !['mrp', 'salePrice'].includes(config.key),
                'text-end': ['mrp', 'salePrice'].includes(config.key),
                'error-cell': datasetStore.isCellError(rowIndex, colIndex + 1),
                'position-relative': isEditing(rowIndex, colIndex + 1)
              }"
              @click="startEdit(rowIndex, colIndex + 1)"
              tabindex="0"
              @keydown.enter="startEdit(rowIndex, colIndex + 1)"
            >
              <!-- Display mode -->
              <template v-if="!isEditing(rowIndex, colIndex + 1)">
                <span 
                  class="cell-content"
                  :class="{ 
                    'fw-medium': config.key === 'itemName',
                    'badge bg-light text-dark border': config.key === 'unit'
                  }"
                >
                  <template v-if="config.key === 'unit'">
                    {{ getCellValue(dataset, colIndex + 1) }}
                    <i class="bi bi-chevron-down ms-1 small"></i>
                  </template>
                  <template v-else-if="['mrp', 'salePrice'].includes(config.key)">
                    ₹{{ getCellValue(dataset, colIndex + 1).toFixed(2) }}
                  </template>
                  <template v-else>
                    {{ getCellValue(dataset, colIndex + 1) }}
                  </template>
                </span>
              </template>

              <!-- Edit mode -->
              <div v-else class="edit-overlay w-100 h-100 d-flex align-items-center px-2">
                <input 
                  v-if="config.type === 'text'"
                  v-model="dataset[config.key]" 
                  class="form-control form-control-sm"
                  @keydown="handleKeydown($event, dataset, rowIndex, colIndex + 1)"
                  @blur="saveCell(dataset, rowIndex, colIndex + 1)"
                  autocomplete="off"
                  ref="editInput"
                />
                
                <input 
                  v-else-if="config.type === 'number'"
                  v-model.number="dataset[config.key]" 
                  type="number"
                  step="0.01"
                  min="0"
                  class="form-control form-control-sm text-end"
                  @keydown="handleKeydown($event, dataset, rowIndex, colIndex + 1)"
                  @blur="saveCell(dataset, rowIndex, colIndex + 1)"
                  autocomplete="off"
                />

                <select 
                  v-else-if="config.type === 'select'"
                  v-model="dataset[config.key]"
                  class="form-select form-select-sm"
                  @keydown="handleKeydown($event, dataset, rowIndex, colIndex + 1)"
                  @change="saveCell(dataset, rowIndex, colIndex + 1)"
                >
                  <option v-for="option in config.options" :key="option" :value="option">
                    {{ option }}
                  </option>
                </select>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile layout remains same (no editing for mobile) -->
    <div class="d-block d-md-none px-2">
      <!-- ... existing mobile code ... -->
    </div>
  </div>
</template>

<style scoped>
.error-cell {
  background-color: #f8d7da !important;
  color: #721c24 !important;
}

.position-relative {
  cursor: pointer;
}

.position-relative:hover {
  background-color: #f8f9fa !important;
}

.cell-content {
  display: block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.edit-overlay {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 10;
  background: white;
  border-radius: inherit;
}

.edit-overlay .form-control,
.edit-overlay .form-select {
  border: 2px solid #0d6efd !important;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.table td:focus-within {
  background-color: #e3f2fd !important;
  border-left: 3px solid #0d6efd;
}
</style>
