<template>
  <div class="upload-data-page">
    <PageHeader 
      :title="$t('upload_data_page.title')"
      :description="$t('upload_data_page.info')"
      :note="$t('upload_data_page.note')"
    />

    <section class="section">
      <div class="row">
        <div class="col-xl-8">
          <ErrorAlert 
            :show="showSubscriptionError"
            :heading="$t('common.Subscription Alert')"
            @close="showSubscriptionError = false"
          />
        </div>

        <div class="col-12">
          <div class="card">
            <div class="card-body pt-3">
              <ErrorAlert 
                :show="showUploadError"
                :heading="$t('common.Error')"
                :message="uploadErrorMessage"
                @close="showUploadError = false"
              />

              <FileUploadZone 
                v-if="!showDataTable"
                @file-selected="handleFileUpload"
              />

              <ProgressBar 
                v-if="showProgress"
                :percentage="progressPercentage"
                :message="progressMessage"
              />

              <ErrorList 
                v-if="globalError"
                :error="globalError"
              />

              <DataTable 
                v-if="showDataTable"
                :job-id="jobId"
                :unit-list="unitList"
                @export="handleExport"
              />
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
// import { useAuthStore } from '@/modules/Authentication/stores/authStore'
import PageHeader from '../components/UploadData/PageHeader.vue'
import ErrorAlert from '../components/UploadData/ErrorAlert.vue'
import FileUploadZone from '../components/UploadData/FileUploadZone.vue'
import ProgressBar from '../components/UploadData/ProgressBar.vue'
import ErrorList from '../components/UploadData/ErrorList.vue'
import DataTable from '../components/UploadData/DataTable.vue'
// import { uploadExcelFile, checkActiveJob } from '../services/uploadService'

const router = useRouter()
// const authStore = useAuthStore()

// State
const showSubscriptionError = ref(false)
const showUploadError = ref(false)
const uploadErrorMessage = ref('')
const showProgress = ref(false)
const progressPercentage = ref(0)
const progressMessage = ref('')
const globalError = ref(null)
const showDataTable = ref(false)
const jobId = ref(null)
const unitList = ref([])

// Load unit list from config or API
const loadUnitList = () => {
  // Replace with actual API call or config
  unitList.value = [
    { label: 'KG', value: 'KG' },
    { label: 'L', value: 'L' },
    { label: 'PCS', value: 'PCS' },
    // Add more units as needed
  ]
}

// Handle file upload
const handleFileUpload = async (file) => {
  try {
    showProgress.value = true
    progressPercentage.value = 0
    progressMessage.value = 'Initiating upload...'

    const response = await uploadExcelFile(file)
    
    if (response.status === 1 && response.job_id) {
      jobId.value = response.job_id
      pollUploadProgress(response.job_id)
    } else {
      showUploadError.value = true
      uploadErrorMessage.value = response.message || 'Upload failed'
      showProgress.value = false
    }
  } catch (error) {
    showUploadError.value = true
    uploadErrorMessage.value = error.message || 'An unexpected error occurred'
    showProgress.value = false
  }
}

// Poll upload progress
const pollUploadProgress = (id) => {
  const interval = setInterval(async () => {
    try {
      const response = await checkActiveJob(id)
      
      progressPercentage.value = response.progress.percentage
      progressMessage.value = response.progress.message

      if (response.progress.percentage === 100) {
        clearInterval(interval)
        showProgress.value = false

        if (response.status === 2) {
          showDataTable.value = true
        } else {
          showUploadError.value = true
          uploadErrorMessage.value = response.progress.message
        }
      }
    } catch (error) {
      clearInterval(interval)
      showProgress.value = false
      showUploadError.value = true
      uploadErrorMessage.value = error.message || 'An unexpected error occurred'
    }
  }, 1000)
}

// Handle export
const handleExport = () => {
  // Export logic here
  console.log('Exporting data...')
}

// Check for active jobs on mount
onMounted(async () => {
  loadUnitList()
  
  try {
    const response = await checkActiveJob()
    if (response.status === 1 && response.job_id) {
      jobId.value = response.job_id
      showProgress.value = true
      progressPercentage.value = response.progress.percentage
      progressMessage.value = response.progress.message

      if (response.job_type === 'upload') {
        pollUploadProgress(response.job_id)
      }
    }
  } catch (error) {
    console.error('Error checking active job:', error)
  }
})
</script>

<style scoped>
.upload-data-page {
  background-color: #f8f9fa;
  min-height: 100vh;
}
</style>
