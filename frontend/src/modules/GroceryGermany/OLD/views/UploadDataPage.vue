<!-- src/views/UploadDataPage.vue -->
<template>
  <div>
    <PageTitle 
      :title="$t('upload_data_page.title')"
      :info="$t('upload_data_page.info')"
      :note="$t('upload_data_page.note')"
    />

    <section class="section" id="uploadDataSection">
      <div class="row">
        <div class="col-xl-8">
          <!-- Subscription Error Box -->
          <FormErrorBox 
            :messages="subscriptionErrors" 
            @clear="clearSubscriptionErrors" 
            class="mb-4"
          />
        </div>

        <div class="col-12">
          <div class="card">
            <div class="card-body pt-3">
              <div class="mb-3">
                <!-- Upload Error Box -->
                <FormErrorBox 
                  :messages="uploadErrors" 
                  @clear="clearUploadErrors" 
                  class="mb-4"
                />

                <FileUploadForm 
                  v-if="!showInventoryForm"
                  @upload="handleFileUpload"
                />
              </div>

              <ProgressSection
                v-if="showProgress"
                :progress="progress"
              />

              <InventoryForm
                v-if="showInventoryForm"
                :job-id="currentJobId"
                :global-errors="globalErrors"
                @export="handleExportData"
                @clear-errors="clearGlobalErrors"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Result Modal -->
    <ResultModal
      :show="showResultModal"
      :status="resultData.status"
      :message="resultData.message"
      :title="resultData.title"
      @close="closeResultModal"
    />
  </div>
</template>

<script setup>

import { ref, onMounted } from 'vue'
import { useUploadDataStore } from '@/modules/GroceryGermany/stores/uploadDataStore'
import PageTitle from '@/modules/GroceryGermany/components/UploadData/PageTitle.vue'
import FileUploadForm from '@/modules/GroceryGermany/components/UploadData/FileUploadForm.vue'
import ProgressSection from '@/modules/GroceryGermany/components/UploadData/ProgressSection.vue'
import InventoryForm from '@/modules/GroceryGermany/components/UploadData/InventoryForm.vue'

const uploadDataStore = useUploadDataStore()

const subscriptionErrors = ref([])
const uploadErrors = ref([])
const globalErrors = ref([])
const showProgress = ref(false)
const progress = ref({ percentage: 0, message: '' })
const showInventoryForm = ref(false)
const currentJobId = ref(null)
const showResultModal = ref(false)
const resultData = ref({
  status: 'success',
  message: '',
  title: ''
})

const clearSubscriptionErrors = () => {
  subscriptionErrors.value = []
}

const clearUploadErrors = () => {
  uploadErrors.value = []
}

const clearGlobalErrors = () => {
  globalErrors.value = []
  uploadDataStore.clearErrors()
}

const handleFileUpload = async (file) => {
  try {
    uploadErrors.value = []
    globalErrors.value = []
    uploadDataStore.clearErrors()
    showProgress.value = true
    progress.value = { percentage: 0, message: 'Initiating upload...' }

    const result = await uploadDataStore.previewExcel(file)
    
    if (result.status === 1 && result.job_id) {
      currentJobId.value = result.job_id
      pollProgress(result.job_id)
    } else {
      showProgress.value = false
      uploadErrors.value = [result.message]
    }
  } catch (error) {
    showProgress.value = false
    uploadErrors.value = [error.message || 'An unexpected error occurred.']
  }
}

const pollProgress = async (jobId) => {
  const interval = setInterval(async () => {
    try {
      const response = await uploadDataStore.checkUploadProgress(jobId)
      
      progress.value = response.progress

      if (response.progress.percentage === 100) {
        clearInterval(interval)

        if (response.status === 2) {
          pollFetchProgress(jobId)
        } else {
          showProgress.value = false
          uploadErrors.value = [response.progress.message]
        }
      }
    } catch (error) {
      clearInterval(interval)
      showProgress.value = false
      uploadErrors.value = [error.message || 'An unexpected error occurred.']
    }
  }, 1000)
}

const pollFetchProgress = async (jobId) => {
  showProgress.value = true
  progress.value = { percentage: 0, message: 'Fetching data...' }
  
  const interval = setInterval(async () => {
    try {
      const response = await uploadDataStore.fetchExcelDataProgress(jobId)
      
      progress.value = response.progress

      if (response.progress.percentage === 100) {
        clearInterval(interval)
        showProgress.value = false

        if (response.status === 1) {
          showInventoryForm.value = true
        } else {
          uploadErrors.value = [response.progress.message || 'Failed to fetch data']
        }
      }
    } catch (error) {
      clearInterval(interval)
      showProgress.value = false
      uploadErrors.value = [error.message]
    }
  }, 1000)
}

const handleExportData = async () => {
  try {
    globalErrors.value = []
    uploadDataStore.clearErrors()
    showProgress.value = true
    progress.value = { percentage: 0, message: 'Initiating export...' }

    const result = await uploadDataStore.exportToInventory({ action: 0 })

    if (result.status === 1 && result.job_id) {
      pollExportProgress(result.job_id)
    } else {
      showProgress.value = false
      globalErrors.value = [result.message || 'Export failed']
    }
  } catch (error) {
    showProgress.value = false
    globalErrors.value = [error.message]
  }
}

const pollExportProgress = async (jobId) => {
  const interval = setInterval(async () => {
    try {
      const response = await uploadDataStore.checkExportProgress(jobId)
      
      progress.value = response.progress

      if (response.progress.percentage === 100) {
        clearInterval(interval)
        showProgress.value = false

        if (response.status === 1) {
          resultData.value = {
            status: 'success',
            message: response.progress.message,
            title: 'Export Successful'
          }
          showResultModal.value = true
          
          setTimeout(() => {
            location.reload()
          }, 2000)
        } else {
          // Set error message for FormErrorBox
          globalErrors.value = [response.progress.message]
          
          // Store errors in Pinia store
          if (response.result?.errors) {
            uploadDataStore.errors = response.result.errors
            
            // Refresh table to show error highlighting
            await uploadDataStore.fetchExcelData(currentJobId.value, {
              start: 0,
              length: uploadDataStore.pageSize
            })
          }
        }
      }
    } catch (error) {
      clearInterval(interval)
      showProgress.value = false
      globalErrors.value = [error.message]
    }
  }, 1000)
}

const closeResultModal = () => {
  showResultModal.value = false
}

const checkActiveJob = async () => {
  try {
    const response = await uploadDataStore.getActiveJob()
    
    if (response.status === 1 && response.job_id) {
      showProgress.value = true
      progress.value = response.progress
      currentJobId.value = response.job_id

      if (response.job_type === 'upload') {
        pollProgress(response.job_id)
      } else if (response.job_type === 'export') {
        showInventoryForm.value = true
        pollExportProgress(response.job_id)
      }
    }
  } catch (error) {
    console.error('Error checking active job:', error)
  }
}

onMounted(() => {
  checkActiveJob()
})
</script>
