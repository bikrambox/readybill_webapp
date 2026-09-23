<template>
  <div>
    <!-- Error Display Above Table -->
    <FormErrorBox 
      :messages="globalErrors" 
      @clear="$emit('clear-errors')" 
      class="mb-3"
    />

    <form @submit.prevent="handleExport">
      <div class="table-container">
        <!-- Enhanced Toolbar -->
        <div class="toolbar-section mb-3 p-3 bg-light rounded">
          <div class="row align-items-center g-2">
            <div class="col-md-4 mb-2 mb-md-0">
              <button
                type="button"
                class="btn btn-danger"
                :disabled="selectedIds.length === 0 || isDeleting"
                @click="handleDeleteSelected"
              >
                <i class="bi bi-trash me-2"></i>
                <span class="d-none d-sm-inline">{{ $t('common.Delete Selected') }}</span>
                <span class="d-inline d-sm-none">Delete</span>
                <span v-if="selectedIds.length > 0" class="badge bg-white text-danger ms-2">
                  {{ selectedIds.length }}
                </span>
                <span v-if="isDeleting" class="spinner-border spinner-border-sm ms-2" role="status"></span>
              </button>
            </div>
            
            <div class="col-md-4 mb-2 mb-md-0 text-center">
              <h5 class="text-primary mb-0 fw-bold">
                <i class="bi bi-box-seam me-2"></i>
                <span class="d-none d-sm-inline">{{ $t('common.Total Items') }}:</span>
                <span class="badge bg-primary">{{ totalRecords }}</span>
              </h5>
            </div>
            
            <div class="col-md-4 text-md-end">
              <div class="d-flex gap-2 align-items-center justify-content-md-end">
                <button 
                  type="button" 
                  class="btn btn-success"
                  @click="handleAddRow"
                  :disabled="isExporting"
                >
                  <i class="bi bi-plus-circle me-2"></i>
                  <span class="d-none d-sm-inline">{{ $t('common.Add Row') }}</span>
                  <span class="d-inline d-sm-none">{{ $t('common.Add') }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Desktop Table (Hidden on Mobile) -->
        <div class="d-none d-md-block">
          <DataTable
            ref="dataTableRef"
            :job-id="jobId"
            :page-size="pageSize"
            @selection-change="handleSelectionChange"
            @refresh="refreshData"
          />
        </div>

        <!-- Mobile List (Hidden on Desktop) -->
        <div class="d-md-none">
          <MobileList
            :items="tableData"
            :loading="loading"
            :unit-list="unitList"
            :current-page="currentPage"
            :page-size="mobilePageSize"
            :total-records="totalRecords"
            @selection-change="handleSelectionChange"
            @page-change="handleMobilePageChange"
            @page-size-change="handleMobilePageSizeChange"
          />
        </div>
      </div>

      <!-- Export Button - Screenshot Style -->
      <div class="mt-4">
        <button 
          type="submit" 
          class="btn btn-export"
          :disabled="isExporting || totalRecords === 0"
        >
          <span v-if="isExporting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          <i v-else class="bi bi-download me-2"></i>
          <span v-if="isExporting">{{ $t('common.Exporting') }}...</span>
          <span v-else>{{ $t('common.Export Data') }}</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useUploadDataStore } from '@/modules/GroceryGermany/stores/uploadDataStore'
import DataTable from './DataTable.vue'
import MobileList from './MobileList.vue'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
  jobId: {
    type: String,
    required: true
  },
  globalErrors: {
    type: Array,
    default: () => []
  },
  isExporting: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['export', 'clear-errors'])

const uploadDataStore = useUploadDataStore()
const dataTableRef = ref(null)
const selectedIds = ref([])
const isDeleting = ref(false)
const pageSize = ref(uploadDataStore.pageSize || 100)
const mobilePageSize = ref(10)
const currentPage = ref(1)
const loading = ref(false)

const totalRecords = computed(() => uploadDataStore.totalRecords)
const tableData = computed(() => uploadDataStore.excelData)
const unitList = computed(() => uploadDataStore.unitList)

const handleSelectionChange = (ids) => {
  selectedIds.value = ids
}

const handleDeleteSelected = async () => {
  if (!confirm(`Are you sure you want to delete ${selectedIds.value.length} selected items?`)) {
    return
  }

  try {
    isDeleting.value = true
    await uploadDataStore.deleteSelectedItems(selectedIds.value)
    selectedIds.value = []
    refreshData()
  } catch (error) {
    alert('Failed to delete items. Please try again.')
    console.error('Error:', error)
  } finally {
    isDeleting.value = false
  }
}

const handleAddRow = () => {
  console.log('Adding new row at the top...')
  uploadDataStore.addNewRow()
  
  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const handleMobilePageChange = async (page) => {
  currentPage.value = page
  loading.value = true
  try {
    const start = (page - 1) * mobilePageSize.value
    await uploadDataStore.fetchExcelData(props.jobId, {
      start,
      length: mobilePageSize.value,
      draw: page
    })
  } catch (error) {
    console.error('Page change error:', error)
  } finally {
    loading.value = false
  }
}

const handleMobilePageSizeChange = async (size) => {
  mobilePageSize.value = size
  uploadDataStore.setPageSize(size)
  currentPage.value = 1
  loading.value = true
  try {
    await uploadDataStore.fetchExcelData(props.jobId, {
      start: 0,
      length: size,
      draw: 1
    })
  } catch (error) {
    console.error('Page size change error:', error)
  } finally {
    loading.value = false
  }
}

const handleExport = () => {
  if (!props.isExporting) {
    emit('export')
  }
}

const refreshData = () => {
  // Trigger handled by DataTable/MobileList watching props
}
</script>

<style scoped>
.toolbar-section {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #e3e6ea;
}

/* Export Button - Screenshot Style */
.btn-export {
  background-color: #198754;
  border: none;
  color: white;
  padding: 10px 24px;
  font-size: 1rem;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
}

.btn-export:hover:not(:disabled) {
  background-color: #157347;
  box-shadow: 0 4px 8px rgba(25, 135, 84, 0.4);
  transform: translateY(-1px);
}

.btn-export:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3);
}

.btn-export:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-export i {
  font-size: 1.1rem;
}

@media (max-width: 767px) {
  .btn {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }
  
  .btn-export {
    width: 100%;
    padding: 12px 24px;
  }
  
  h5 {
    font-size: 1rem;
  }
}
</style>
