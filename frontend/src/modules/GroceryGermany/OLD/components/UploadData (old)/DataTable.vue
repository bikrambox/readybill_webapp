<template>
  <div>
    <TableToolbar 
      :selected-count="selectedRows.length"
      :total-items="totalItems"
      @add-row="addNewRow"
      @delete-selected="deleteSelectedRows"
    />

    <div class="alert alert-danger" v-if="errorMessages.length > 0">
      <ul class="mb-0 ps-3">
        <li v-for="(msg, idx) in errorMessages" :key="idx">{{ msg }}</li>
      </ul>
    </div>

    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
      <table class="table table-hover table-bordered table-sm align-middle mb-0">
        <thead class="table-dark sticky-top">
          <tr>
            <th style="width: 50px;">
              <input 
                type="checkbox" 
                class="form-check-input" 
                v-model="selectAll"
                @change="toggleSelectAll"
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
          <TableRow 
            v-for="(row, index) in tableData" 
            :key="row.id || index"
            :row="row"
            :row-index="index"
            :unit-list="unitList"
            :is-selected="selectedRows.includes(row.id)"
            :error-coordinates="errorCoordinates"
            @select="toggleRowSelection(row.id)"
            @update="handleRowUpdate"
          />
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <button 
        type="button" 
        class="btn btn-primary"
        @click="emit('export')"
      >
        {{ $t('common.Export Data') }}
      </button>

      <TablePagination 
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        @page-change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import TableToolbar from './TableToolbar.vue'
import TableRow from './TableRow.vue'
import TablePagination from './TablePagination.vue'
// import { 
//   fetchTableData, 
//   updateCellData, 
//   deleteMultipleItems 
// } from '../services/uploadService'

const props = defineProps({
  jobId: {
    type: String,
    required: true
  },
  unitList: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['export'])

// State
const tableData = ref([])
const selectedRows = ref([])
const selectAll = ref(false)
const totalItems = ref(0)
const currentPage = ref(1)
const pageSize = ref(10)
const totalPages = ref(0)
const errorMessages = ref([])
const errorCoordinates = ref([])

// Load table data
const loadTableData = async () => {
  try {
    const response = await fetchTableData({
      jobId: props.jobId,
      page: currentPage.value,
      pageSize: pageSize.value
    })

    if (response.status === 1) {
      tableData.value = response.data
      totalItems.value = response.recordsTotal
      totalPages.value = Math.ceil(response.recordsTotal / pageSize.value)

      if (response.errors) {
        errorMessages.value = response.errors.messages || []
        errorCoordinates.value = response.errors.grid_coordinates || []
      }
    }
  } catch (error) {
    console.error('Error loading table data:', error)
  }
}

// Toggle select all
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedRows.value = tableData.value.map(row => row.id)
  } else {
    selectedRows.value = []
  }
}

// Toggle row selection
const toggleRowSelection = (id) => {
  const index = selectedRows.value.indexOf(id)
  if (index > -1) {
    selectedRows.value.splice(index, 1)
  } else {
    selectedRows.value.push(id)
  }
}

// Add new row
const addNewRow = () => {
  tableData.value.push({
    id: null,
    item_name: '',
    quantity: 0,
    min_stock_alert: 0,
    mrp: 0,
    sale_price: 0,
    unit: '',
    hsn: '',
    gst: 0,
    cess: 0
  })
}

// Delete selected rows
const deleteSelectedRows = async () => {
  if (selectedRows.value.length === 0) {
    alert('Please select at least one item to delete.')
    return
  }

  if (!confirm('Are you sure you want to delete the selected items?')) {
    return
  }

  try {
    await deleteMultipleItems(selectedRows.value)
    selectedRows.value = []
    selectAll.value = false
    await loadTableData()
  } catch (error) {
    alert('Failed to delete items. Please try again.')
    console.error('Error:', error)
  }
}

// Handle row update
const handleRowUpdate = async (rowData) => {
  try {
    await updateCellData(rowData)
    await loadTableData()
  } catch (error) {
    console.error('Error updating row:', error)
  }
}

// Handle page change
const handlePageChange = (page) => {
  currentPage.value = page
  loadTableData()
}

// Handle page size change
const handlePageSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  loadTableData()
}

// Watch for selectAll changes
watch(() => selectedRows.value.length, (newLength) => {
  selectAll.value = newLength > 0 && newLength === tableData.value.length
})

// Load data on mount
onMounted(() => {
  loadTableData()
})
</script>

<style scoped>
.table-responsive {
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
}

thead.sticky-top {
  position: sticky;
  top: 0;
  z-index: 10;
}
</style>
