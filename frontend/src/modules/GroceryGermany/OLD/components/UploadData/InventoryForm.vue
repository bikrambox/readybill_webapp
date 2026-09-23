<!-- src/components/UploadData/InventoryForm.vue -->
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
        <div class="d-flex justify-content-between align-items-center mb-3">
          <button
            type="button"
            class="btn btn-danger btn-sm"
            :disabled="selectedIds.length === 0 || isDeleting"
            @click="handleDeleteSelected"
          >
            <i class="bi bi-trash me-1"></i>
            {{ $t('common.Delete Selected') }}
            <span v-if="selectedIds.length > 0" class="badge bg-white text-danger ms-1">
              {{ selectedIds.length }}
            </span>
          </button>
          
          <div class="d-flex gap-2 align-items-center">
            <select 
              v-model="pageSize" 
              class="form-select form-select-sm" 
              style="width: auto;"
              @change="handlePageSizeChange"
            >
              <option :value="10">10 per page</option>
              <option :value="25">25 per page</option>
              <option :value="50">50 per page</option>
              <option :value="100">100 per page</option>
            </select>
            
            <button type="button" class="btn btn-success btn-sm" @click="handleAddRow">
              <i class="bi bi-plus-circle me-1"></i>
              {{ $t('common.Add Row') }}
            </button>
          </div>
        </div>

        <div class="text-center mb-3">
          <h5 class="text-primary mb-0">
            <i class="bi bi-box-seam me-2"></i>
            {{ $t('common.Total Items') }}: {{ totalRecords }}
          </h5>
        </div>

        <DataTable
          :job-id="jobId"
          :page-size="pageSize"
          @selection-change="handleSelectionChange"
          @refresh="refreshData"
        />
      </div>

      <div class="text-start mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="bi bi-box-arrow-down me-2"></i>
          {{ $t('common.Export Data') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useUploadDataStore } from '@/modules/GroceryGermany/stores/uploadDataStore'
import DataTable from './DataTable.vue'

const props = defineProps({
  jobId: {
    type: String,
    required: true
  },
  globalErrors: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['export', 'clear-errors'])

const uploadDataStore = useUploadDataStore()
const selectedIds = ref([])
const isDeleting = ref(false)
const pageSize = ref(uploadDataStore.pageSize || 100)

const totalRecords = computed(() => uploadDataStore.totalRecords)

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
  console.log('Add new row functionality')
}

const handlePageSizeChange = () => {
  uploadDataStore.setPageSize(pageSize.value)
  refreshData()
}

const handleExport = () => {
  emit('export')
}

const refreshData = () => {
  // Trigger handled by DataTable watching props
}
</script>
