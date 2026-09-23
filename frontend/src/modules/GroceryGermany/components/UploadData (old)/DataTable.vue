<!-- src/components/UploadData/DataTable.vue -->
<template>
  <div class="table-wrapper">
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
            <th style="min-width: 100px;">{{ $t('common.HSN') }}</th>
            <th style="min-width: 100px;">{{ $t('common.GST (%)') }}</th>
            <th style="min-width: 100px;">{{ $t('common.CESS (%)') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="10" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="text-muted mt-2 mb-0">Loading data...</p>
            </td>
          </tr>
          <tr v-else-if="paginatedData.length === 0">
            <td colspan="10" class="text-center py-4 text-muted">
              <i class="bi bi-inbox fs-1 d-block mb-2"></i>
              No data available
            </td>
          </tr>
          <tr
            v-else
            v-for="(row, rowIndex) in paginatedData"
            :key="row.id || rowIndex"
            :data-id="row.id"
            :data-original-index="row.original_index"
          >
            <td>
              <input
                type="checkbox"
                v-model="selectedRows"
                :value="row.id"
                class="form-check-input"
              />
            </td>
            <!-- Item Name with error highlighting -->
            <td 
              class="editable-cell item-name-cell"
              :class="{ 'has-error': hasCellError(row.original_index, 1) }"
              @click="startEdit(row, 'item_name', rowIndex)"
            >
              <div v-if="!isEditing(row, 'item_name')" class="cell-content">
                <span class="value-display">{{ row.item_name }}</span>
              </div>
              <div v-else class="cell-edit">
                <input
                  :ref="el => setInputRef(el, row, 'item_name')"
                  v-model="row.item_name"
                  type="text"
                  class="form-control form-control-sm"
                  @blur="finishEdit(row, 'item_name', rowIndex, 1)"
                  @keypress.enter="finishEdit(row, 'item_name', rowIndex, 1)"
                />
              </div>
            </td>
            
            <EditableCell
              v-model="row.quantity"
              :row-index="row.original_index"
              :col-index="2"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 2)"
              :error-message="getCellError(row.original_index, 2)"
              type="number"
              @update="handleCellUpdate"
            />
            <EditableCell
              v-model="row.min_stock_alert"
              :row-index="row.original_index"
              :col-index="3"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 3)"
              :error-message="getCellError(row.original_index, 3)"
              type="number"
              @update="handleCellUpdate"
            />
            <EditableCell
              v-model="row.mrp"
              :row-index="row.original_index"
              :col-index="4"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 4)"
              :error-message="getCellError(row.original_index, 4)"
              type="number"
              prefix="₹"
              @update="handleCellUpdate"
            />
            <EditableCell
              v-model="row.sale_price"
              :row-index="row.original_index"
              :col-index="5"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 5)"
              :error-message="getCellError(row.original_index, 5)"
              type="number"
              prefix="₹"
              @update="handleCellUpdate"
            />
            <td :class="{ 'has-error': hasCellError(row.original_index, 6) }">
              <select
                v-model="row.unit"
                class="form-select form-select-sm"
                @change="handleCellUpdate(row.original_index, 6, row)"
                :class="{ 'border-danger': hasCellError(row.original_index, 6) }"
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
            </td>
            <EditableCell
              v-model="row.hsn"
              :row-index="row.original_index"
              :col-index="7"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 7)"
              :error-message="getCellError(row.original_index, 7)"
              @update="handleCellUpdate"
            />
            <EditableCell
              v-model="row.gst"
              :row-index="row.original_index"
              :col-index="8"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 8)"
              :error-message="getCellError(row.original_index, 8)"
              type="number"
              @update="handleCellUpdate"
            />
            <EditableCell
              v-model="row.cess"
              :row-index="row.original_index"
              :col-index="9"
              :row-id="row.id"
              :has-error="hasCellError(row.original_index, 9)"
              :error-message="getCellError(row.original_index, 9)"
              type="number"
              @update="handleCellUpdate"
            />
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls -->
    <div class="d-flex justify-content-between align-items-center mt-3 px-3">
      <div class="text-muted">
        Showing {{ startRecord }} to {{ endRecord }} of {{ totalRecords }} entries
        <span v-if="hasErrors" class="text-danger ms-2">
          <i class="bi bi-exclamation-triangle-fill"></i>
          {{ errorCount }} error(s) found
        </span>
      </div>
      <nav v-if="totalPages > 1">
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">
              <i class="bi bi-chevron-left"></i>
            </a>
          </li>
          <li
            v-for="page in visiblePages"
            :key="page"
            class="page-item"
            :class="{ active: page === currentPage }"
          >
            <a class="page-link" href="#" @click.prevent="goToPage(page)">
              {{ page }}
            </a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">
              <i class="bi bi-chevron-right"></i>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useUploadDataStore } from '@/modules/GroceryIndia/stores/uploadDataStore'
import EditableCell from './EditableCell.vue'

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

const selectAll = ref(false)
const selectedRows = ref([])
const currentPage = ref(1)
const loading = ref(false)
const editingCells = ref(new Map())
const inputRefs = ref(new Map())

const unitList = computed(() => uploadDataStore.unitList)
const tableData = computed(() => uploadDataStore.excelData)
const totalRecords = computed(() => uploadDataStore.totalRecords)
const errorData = computed(() => uploadDataStore.errors)

const totalPages = computed(() => Math.ceil(totalRecords.value / props.pageSize))

const startRecord = computed(() => {
  if (totalRecords.value === 0) return 0
  return (currentPage.value - 1) * props.pageSize + 1
})

const endRecord = computed(() =>
  Math.min(currentPage.value * props.pageSize, totalRecords.value)
)

const paginatedData = computed(() => tableData.value)

const hasErrors = computed(() => {
  return errorData.value?.grid_coordinates && errorData.value.grid_coordinates.length > 0
})

const errorCount = computed(() => {
  return errorData.value?.grid_coordinates?.length || 0
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

const handleSelectAll = () => {
  if (selectAll.value) {
    selectedRows.value = tableData.value.map((row) => row.id)
  } else {
    selectedRows.value = []
  }
}

const goToPage = async (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  await fetchData()
}

const fetchData = async () => {
  try {
    loading.value = true
    const start = (currentPage.value - 1) * props.pageSize
    await uploadDataStore.fetchExcelData(props.jobId, {
      start,
      length: props.pageSize,
      draw: currentPage.value
    })
  } catch (error) {
    console.error('Error fetching data:', error)
  } finally {
    loading.value = false
  }
}

// Inline editing for item_name
const getCellKey = (row, field) => `${row.id}-${field}`

const isEditing = (row, field) => {
  return editingCells.value.has(getCellKey(row, field))
}

const setInputRef = (el, row, field) => {
  if (el) {
    inputRefs.value.set(getCellKey(row, field), el)
  }
}

const startEdit = async (row, field, rowIndex) => {
  editingCells.value.set(getCellKey(row, field), true)
  await nextTick()
  const input = inputRefs.value.get(getCellKey(row, field))
  if (input) {
    input.focus()
  }
}

const finishEdit = async (row, field, rowIndex, colIndex) => {
  editingCells.value.delete(getCellKey(row, field))
  await handleCellUpdate(row.original_index, colIndex, row)
}

const handleCellUpdate = async (rowIndex, colIndex, row) => {
  const formData = new FormData()
  formData.append('id', row.id)
  formData.append('item_name', row.item_name || '')
  formData.append('quantity', parseFloat(row.quantity) || 0)
  formData.append('min_stock_alert', parseInt(row.min_stock_alert) || 0)
  formData.append('mrp', parseFloat(row.mrp) || 0)
  formData.append('sale_price', parseFloat(row.sale_price) || 0)
  formData.append('short_unit', row.unit || '')
  formData.append('hsn', row.hsn || '')
  formData.append('gst', parseFloat(row.gst) || 0)
  formData.append('cess', parseFloat(row.cess) || 0)
  formData.append('row_index', rowIndex)
  formData.append('cell_index', colIndex)

  try {
    await uploadDataStore.updateCellData(formData)
    await fetchData()
  } catch (error) {
    console.error('Error updating cell:', error)
  }
}

const hasRowError = (originalIndex) => {
  if (!errorData.value?.grid_coordinates) return false
  
  const paddedRow = String(originalIndex).padStart(2, '0')
  
  return errorData.value.grid_coordinates.some((coord) => {
    const [row] = coord.split(',')
    return row === paddedRow
  })
}

const hasCellError = (originalIndex, colIndex) => {
  if (!errorData.value?.grid_coordinates) return false
  
  const paddedRow = String(originalIndex).padStart(2, '0')
  const paddedCol = String(colIndex).padStart(2, '0')
  const coordinate = `${paddedRow},${paddedCol}`
  
  return errorData.value.grid_coordinates.includes(coordinate)
}

const getCellError = (originalIndex, colIndex) => {
  if (!errorData.value?.messages || !errorData.value?.grid_coordinates) return ''
  
  const paddedRow = String(originalIndex).padStart(2, '0')
  const paddedCol = String(colIndex).padStart(2, '0')
  const coordinate = `${paddedRow},${paddedCol}`
  
  const coordIndex = errorData.value.grid_coordinates.findIndex(
    (coord) => coord === coordinate
  )
  
  if (coordIndex >= 0) {
    if (Array.isArray(errorData.value.messages)) {
      return errorData.value.messages[coordIndex] || errorData.value.messages[0] || ''
    } else {
      return errorData.value.messages || ''
    }
  }
  
  return ''
}

watch(selectedRows, (newVal) => {
  emit('selection-change', newVal)
  selectAll.value = newVal.length === tableData.value.length && tableData.value.length > 0
})

watch(() => props.pageSize, () => {
  currentPage.value = 1
  fetchData()
})

watch(() => props.jobId, () => {
  currentPage.value = 1
  selectedRows.value = []
  selectAll.value = false
  fetchData()
}, { immediate: false })

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.table-wrapper {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
}

.table-responsive {
  border-radius: 8px 8px 0 0;
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

/* Error highlighting for item name cells (column 1) */
.item-name-cell.has-error {
  background-color: #f8d7da !important;
  cursor: pointer;
}

.item-name-cell.has-error:hover {
  background-color: #f1c2c7 !important;
}

/* General error cell styling */
.has-error {
  background-color: #f8d7da !important;
  border: 2px solid #dc3545 !important;
}

.editable-cell {
  cursor: pointer;
  min-width: 80px;
  padding: 8px 12px;
}

.editable-cell:hover:not(.has-error) {
  background-color: #f0f8ff;
}

.cell-content {
  min-height: 20px;
}

.value-display {
  display: inline-block;
  width: 100%;
}

.cell-edit input {
  width: 100%;
  border: 2px solid #0d6efd;
  outline: none;
  padding: 4px 8px;
  border-radius: 4px;
}

.form-check-input {
  cursor: pointer;
  width: 1.2em;
  height: 1.2em;
}

.pagination {
  --bs-pagination-active-bg: #0d6efd;
  --bs-pagination-active-border-color: #0d6efd;
}
</style>
