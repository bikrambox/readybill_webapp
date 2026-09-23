<template>
  <div
    class="modal fade"
    :class="{ 'show d-block': showModal }"
    tabindex="-1"
    aria-hidden="true"
    ref="modalElement"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        
        <!-- Close Button -->
        <button 
          type="button" 
          class="btn-close position-absolute top-0 end-0 m-3" 
          @click="close"
          :disabled="isLoading"
          aria-label="Close"
          style="z-index: 1051;"
        ></button>

        <!-- Body -->
        <div class="modal-body px-4 py-5">
          <!-- Error Box -->
          <FormErrorBox 
            v-if="errorMessages.length > 0"
            :messages="errorMessages" 
            @clear="clearErrors" 
            class="mb-4"
          />

          <!-- Success Message -->
          <div v-if="successMessage" class="alert alert-success alert-dismissible fade show">
            {{ successMessage }}
            <button type="button" class="btn-close" @click="successMessage = ''"></button>
          </div>

          <h4 class="fw-bold text-center mb-2">{{ $t('support_page.ask_your_questions') }}</h4>
          
          <p class="text-center text-muted mb-4 small">
            {{ $t('common.Fields marked with') }} <span class="text-danger fw-bold">*</span> {{ $t('common.are mandatory') }}
          </p>

          <form @submit.prevent="submit" novalidate>
            <!-- Title Input -->
            <div class="mb-3">
              <label for="title" class="form-label fw-semibold">
                {{ $t('common.Title') }} <span class="text-danger">*</span>
              </label>
              <input
                id="title"
                v-model="title"
                type="text"
                class="form-control"
                :class="{ 'border-danger': errors.title }"
                :placeholder="$t('common.Title')"
                autocomplete="off"
                :disabled="isLoading"
                @input="errors.title = ''"
              />
              <span v-if="errors.title" class="text-danger small d-block mt-1">{{ errors.title }}</span>
            </div>

            <!-- Description Textarea -->
            <div class="mb-3">
              <label for="description" class="form-label fw-semibold">
                {{ $t('common.Description') }} <span class="text-danger">*</span>
              </label>
              <textarea
                id="description"
                v-model="description"
                rows="4"
                class="form-control"
                :class="{ 'border-danger': errors.description }"
                :placeholder="$t('common.Description')"
                :disabled="isLoading"
                @input="errors.description = ''"
              ></textarea>
              <span v-if="errors.description" class="text-danger small d-block mt-1">{{ errors.description }}</span>
            </div>

            <!-- Attachment -->
            <div class="mb-4">
              <label for="attachment" class="form-label fw-semibold">
                {{ $t('common.Attachment') }}
                <span class="badge bg-secondary ms-2 fw-normal">{{ $t('common.Optional') }}</span>
              </label>
              <input
                id="attachment"
                ref="fileInput"
                type="file"
                class="form-control"
                :class="{ 'border-danger': errors.attachment }"
                @change="onFileChange"
                :disabled="isLoading"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.rtf"
              />
              <small class="form-text text-muted d-block mt-1">
                <i class="bi bi-info-circle me-1"></i>
                {{ $t('common.Max 20MB, Images, PDF, DOC, DOCX, RTF') }}
              </small>
              
              <!-- Preview -->
              <div v-if="attachmentPreview" class="mt-3 position-relative d-inline-block">
                <img
                  v-if="isImageFile"
                  :src="attachmentPreview"
                  alt="Preview"
                  class="img-thumbnail rounded"
                  style="max-width: 150px; max-height: 150px; object-fit: cover;"
                />
                <div v-else class="card border p-3 text-center" style="width: 150px;">
                  <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                  <small class="text-muted mt-2">{{ attachment?.name }}</small>
                </div>
                <button
                  type="button"
                  class="btn btn-danger btn-sm position-absolute top-0 start-100 translate-middle rounded-circle"
                  @click="clearAttachment"
                  style="width: 28px; height: 28px; padding: 0;"
                  aria-label="Remove attachment"
                  :disabled="isLoading"
                >
                  <i class="bi bi-x"></i>
                </button>
              </div>
              
              <span v-if="errors.attachment" class="text-danger small d-block mt-1">{{ errors.attachment }}</span>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-3 justify-content-center">
              <button
                type="button"
                class="btn btn-outline-secondary px-4 py-2"
                @click="close"
                :disabled="isLoading"
                style="min-width: 100px;"
              >
                {{ $t('common.Cancel') }}
              </button>
              <button
                type="submit"
                class="btn btn-primary px-4 py-2 fw-semibold"
                :disabled="isLoading"
                style="min-width: 100px;"
              >
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                {{ $t('common.Submit') }}
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>

  <!-- Backdrop -->
  <div 
    v-if="showModal" 
    class="modal-backdrop fade show"
    @click="close"
  ></div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { useI18n } from 'vue-i18n'
import { useSupportStore } from '@/modules/GroceryGermany/stores/supportStore'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

const { t } = useI18n()
const supportStore = useSupportStore()

const modalElement = ref(null)
const modalInstance = ref(null)
const showModal = ref(false)
const title = ref('')
const description = ref('')
const attachment = ref(null)
const attachmentPreview = ref('')
const fileInput = ref(null)
const errorMessages = ref([])
const successMessage = ref('')

const errors = reactive({
  title: '',
  description: '',
  attachment: ''
})

const allowedTypes = [
  'image/jpeg',
  'image/jpg',
  'image/png',
  'image/gif',
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/rtf',
]

const maxSize = 20 * 1024 * 1024 // 20MB

const isLoading = computed(() => supportStore.isSubmitting)

const isImageFile = computed(() => {
  return attachment.value?.type?.startsWith('image/')
})

onMounted(() => {
  if (modalElement.value) {
    modalInstance.value = new Modal(modalElement.value, {
      backdrop: 'static',
      keyboard: false
    })
    
    modalElement.value.addEventListener('hidden.bs.modal', () => {
      showModal.value = false
    })
  }
})

watch(showModal, (newVal) => {
  if (newVal) {
    if (modalInstance.value) {
      modalInstance.value.show()
    }
  } else {
    if (modalInstance.value) {
      modalInstance.value.hide()
    }
  }
})

function open() {
  resetForm()
  showModal.value = true
}

function close() {
  if (!isLoading.value) {
    showModal.value = false
  }
}

function resetForm() {
  title.value = ''
  description.value = ''
  attachment.value = null
  attachmentPreview.value = ''
  if (fileInput.value) fileInput.value.value = ''
  clearErrors()
  successMessage.value = ''
}

function clearErrors() {
  errorMessages.value = []
  errors.title = ''
  errors.description = ''
  errors.attachment = ''
  supportStore.clearErrors()
}

function onFileChange(e) {
  const file = e.target.files[0]
  errors.attachment = ''
  errorMessages.value = errorMessages.value.filter(msg => !msg.includes('file') && !msg.includes('attachment'))

  if (file) {
    // Validate file type
    if (!allowedTypes.includes(file.type)) {
      errors.attachment = t('support_page.invalid_file_type') || t('common.Invalid file type. Please upload images, PDF, or document files.')
      errorMessages.value.push(errors.attachment)
      attachment.value = null
      attachmentPreview.value = ''
      if (fileInput.value) fileInput.value.value = ''
      return
    }

    // Validate file size
    if (file.size > maxSize) {
      errors.attachment = t('support_page.file_too_large') || t('common.File size exceeds 20MB limit.')
      errorMessages.value.push(errors.attachment)
      attachment.value = null
      attachmentPreview.value = ''
      if (fileInput.value) fileInput.value.value = ''
      return
    }

    attachment.value = file

    // Create preview for images
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        attachmentPreview.value = e.target.result
      }
      reader.readAsDataURL(file)
    } else {
      attachmentPreview.value = 'file'
    }
  }
}

function clearAttachment() {
  attachment.value = null
  attachmentPreview.value = ''
  errors.attachment = ''
  if (fileInput.value) fileInput.value.value = ''
}

function validateForm() {
  clearErrors()
  let isValid = true

  // Validate title
  if (!title.value.trim()) {
    errors.title = t('support_page.enter_title') || t('common.Title is required')
    errorMessages.value.push(errors.title)
    isValid = false
  } else if (title.value.trim().length < 3) {
    errors.title = t('common.Title must be at least 3 characters')
    errorMessages.value.push(errors.title)
    isValid = false
  } else if (title.value.trim().length > 255) {
    errors.title = t('common.Title must not exceed 255 characters')
    errorMessages.value.push(errors.title)
    isValid = false
  }

  // Validate description
  if (!description.value.trim()) {
    errors.description = t('support_page.enter_description') || t('common.Description is required')
    errorMessages.value.push(errors.description)
    isValid = false
  } else if (description.value.trim().length < 10) {
    errors.description = t('common.Description must be at least 10 characters')
    errorMessages.value.push(errors.description)
    isValid = false
  } else if (description.value.trim().length > 5000) {
    errors.description = t('common.Description must not exceed 5000 characters')
    errorMessages.value.push(errors.description)
    isValid = false
  }

  return isValid
}

async function submit() {
  // Frontend validation
  if (!validateForm()) {
    return
  }

  // Prepare FormData
  const formData = new FormData()
  formData.append('title', title.value.trim())
  formData.append('description', description.value.trim())
  
  if (attachment.value) {
    formData.append('attachment', attachment.value)
  }

  // Submit to backend
  const result = await supportStore.createQuery(formData)

  if (result.success) {
    successMessage.value = supportStore.successMessage || t('support_page.thank_you') || t('common.Your query has been submitted successfully!')
    
    // Close modal after showing success
    setTimeout(() => {
      close()
      resetForm()
    }, 2000)
  } else {
    // Handle backend errors
    handleBackendErrors(result)
  }
}

function handleBackendErrors(result) {
  errorMessages.value = []

  if (result.errors) {
    // Field-specific errors
    if (result.errors.title) {
      errors.title = Array.isArray(result.errors.title) 
        ? result.errors.title[0] 
        : result.errors.title
      errorMessages.value.push(errors.title)
    }

    if (result.errors.description) {
      errors.description = Array.isArray(result.errors.description) 
        ? result.errors.description[0] 
        : result.errors.description
      errorMessages.value.push(errors.description)
    }

    if (result.errors.attachment) {
      errors.attachment = Array.isArray(result.errors.attachment) 
        ? result.errors.attachment[0] 
        : result.errors.attachment
      errorMessages.value.push(errors.attachment)
    }

    // Other field errors
    Object.keys(result.errors).forEach(field => {
      if (!['title', 'description', 'attachment'].includes(field)) {
        if (Array.isArray(result.errors[field])) {
          errorMessages.value.push(...result.errors[field])
        } else {
          errorMessages.value.push(result.errors[field])
        }
      }
    })
  } else if (result.message) {
    errorMessages.value = [result.message]
  } else {
    errorMessages.value = [`${t('common.An error occurred')}. ${t('common.Please try again')}.`]
  }
}

defineExpose({ open, close })
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
}

.modal-content {
  border-radius: 1rem !important;
}

.form-control {
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.form-control.border-danger {
  border-color: #dc3545 !important;
}

.form-control.border-danger:focus {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
}

.btn {
  border-radius: 8px;
  font-weight: 500;
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0b5ed7;
  border-color: #0a58ca;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-primary:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-outline-secondary:hover:not(:disabled) {
  transform: translateY(-1px);
}

.badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}

.text-danger.small {
  font-size: 0.875rem;
}

.img-thumbnail {
  border: 2px solid #dee2e6;
  padding: 0.25rem;
}

@media (max-width: 576px) {
  .modal-body {
    padding: 2rem 1.5rem !important;
  }

  h4 {
    font-size: 1.25rem;
  }

  .d-flex.gap-3 {
    flex-direction: column;
  }

  .d-flex.gap-3 button {
    width: 100%;
  }
}
</style>
