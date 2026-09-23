<template>
  <div 
    class="modal fade"
    tabindex="-1"
    aria-hidden="true"
    ref="modalElement"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        
        <!-- Header with colored background -->
        <div :class="['modal-header border-0 position-relative', getHeaderClass()]">
          <button 
            type="button" 
            class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <!-- Body with Icon and Message -->
        <div class="modal-body text-center pt-0 pb-4 px-4">
          <!-- Large Icon -->
          <div class="icon-wrapper mx-auto mb-4" :class="getIconWrapperClass()">
            <i :class="['bi', getIconClass(), 'fs-1']"></i>
          </div>
          
          <!-- Title -->
          <h4 class="fw-bold mb-3">{{ getTitle() }}</h4>
          
          <!-- Message -->
          <p class="text-muted mb-4" v-html="message"></p>
          
          <!-- Action Button -->
          <button 
            type="button" 
            :class="['btn', `btn-${getVariant()}`, 'px-5 py-2 fw-semibold']"
            data-bs-dismiss="modal"
          >
            {{ getButtonText() }}
          </button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Modal } from 'bootstrap'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  status: {
    type: String,
    required: true,
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  message: {
    type: String,
    required: true
  },
  title: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])

const modalElement = ref(null)
const modalInstance = ref(null)

// Define functions before they are used
const handleClose = () => {
  emit('close')
}

const showModal = () => {
  if (modalInstance.value) {
    modalInstance.value.show()
  }
}

const hideModal = () => {
  if (modalInstance.value) {
    modalInstance.value.hide()
  }
}

onMounted(() => {
  if (modalElement.value) {
    modalInstance.value = new Modal(modalElement.value, {
      backdrop: 'static',
      keyboard: false
    })
    
    // Listen for modal hidden event
    modalElement.value.addEventListener('hidden.bs.modal', handleClose)
  }
})

onBeforeUnmount(() => {
  if (modalElement.value) {
    modalElement.value.removeEventListener('hidden.bs.modal', handleClose)
  }
  if (modalInstance.value) {
    modalInstance.value.dispose()
  }
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    showModal()
  } else {
    hideModal()
  }
}, { immediate: true })

const getHeaderClass = () => {
  const classes = {
    'success': 'bg-success-gradient',
    'error': 'bg-danger-gradient',
    'warning': 'bg-warning-gradient',
    'info': 'bg-info-gradient'
  }
  return classes[props.status] || 'bg-success-gradient'
}

const getIconWrapperClass = () => {
  const classes = {
    'success': 'icon-success',
    'error': 'icon-error',
    'warning': 'icon-warning',
    'info': 'icon-info'
  }
  return classes[props.status] || 'icon-success'
}

const getIconClass = () => {
  const icons = {
    'success': 'bi-check-circle-fill',
    'error': 'bi-x-circle-fill',
    'warning': 'bi-exclamation-triangle-fill',
    'info': 'bi-info-circle-fill'
  }
  return icons[props.status] || 'bi-check-circle-fill'
}

const getTitle = () => {
  if (props.title) return props.title
  
  const titles = {
    'success': 'Success!',
    'error': 'Error!',
    'warning': 'Warning!',
    'info': 'Information'
  }
  return titles[props.status] || 'Success!'
}

const getVariant = () => {
  return props.status === 'error' ? 'danger' : props.status
}

const getButtonText = () => {
  return props.status === 'success' ? 'Continue' : 'Close'
}
</script>

<style scoped>
.modal-dialog {
  max-width: 450px;
}

.modal-content {
  border-radius: 16px;
  overflow: hidden;
}

/* Gradient Headers */
.modal-header {
  height: 120px;
  position: relative;
}

.bg-success-gradient {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.bg-danger-gradient {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.bg-warning-gradient {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.bg-info-gradient {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

/* Icon Wrapper */
.icon-wrapper {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: -50px;
  position: relative;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.icon-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.icon-error {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.icon-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.icon-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

/* Button Styles */
.btn {
  border-radius: 8px;
  font-size: 16px;
  transition: all 0.3s ease;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Close Button */
.btn-close {
  opacity: 0.8;
  transition: opacity 0.3s ease;
}

.btn-close:hover {
  opacity: 1;
}

/* Title */
h4 {
  font-size: 24px;
  color: #1f2937;
}

/* Message */
.text-muted {
  font-size: 15px;
  line-height: 1.6;
  color: #6b7280 !important;
}

/* Animation */
.modal.fade .modal-dialog {
  transform: scale(0.8);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal.show .modal-dialog {
  transform: scale(1);
  opacity: 1;
}

/* Responsive */
@media (max-width: 576px) {
  .modal-dialog {
    margin: 1rem;
  }
  
  .icon-wrapper {
    width: 80px;
    height: 80px;
    margin-top: -40px;
  }
  
  .icon-wrapper i {
    font-size: 2rem !important;
  }
  
  h4 {
    font-size: 20px;
  }
  
  .text-muted {
    font-size: 14px;
  }
  
  .btn {
    font-size: 14px;
    padding: 0.5rem 2rem !important;
  }
}
</style>
