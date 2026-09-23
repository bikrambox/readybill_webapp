<template>
  <div 
    class="modal fade"
    :class="{ show: props.show, 'd-block': props.show }"
    tabindex="-1"
    :style="{ display: props.show ? 'block' : 'none' }"
    @click.self="handleCancel"
  >
    <div class="modal-dialog modal-dialog-centered custom-modal-width">
      <div class="modal-content rounded-4 shadow-lg">
        <!-- Modal Header -->
        <div class="modal-header border-0 pb-2 pt-4 px-4">
          <h5 class="modal-title fw-bold mb-0 d-flex align-items-center">
            <i class="bi bi-exclamation-circle text-danger me-2 fs-4"></i>
            <span>Confirmation</span>
          </h5>
          <button 
            type="button" 
            class="btn-close" 
            @click="handleCancel"
            :disabled="isLoading"
          ></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body px-4 py-4">
          <!-- First Step: Radio Selection -->
          <div v-if="!showSecondConfirmation && !isLoading">
            <p class="mb-4 text-muted fs-6">
              <i class="bi bi-question-circle me-2"></i>
              {{ $t('common.Do you want to') }} <strong>"{{ $t('commonAppend') }}"</strong> {{ $t('common.or') }} <strong>"{{ $t('common.Replace') }}"</strong>?
            </p>
            
            <div class="mb-4">
              <!-- Append Option -->
              <label 
                class="radio-option mb-3 p-3 rounded-3 border d-flex align-items-center w-100" 
                :class="{ 'selected': selectedAction === '1' }"
                for="append"
              >
                <input 
                  class="form-check-input me-3" 
                  type="radio" 
                  name="datasetAction" 
                  id="append" 
                  value="1" 
                  v-model="selectedAction"
                  :disabled="isLoading"
                >
                <div class="d-flex align-items-center">
                  <i class="bi bi-plus-circle text-success me-2 fs-5"></i>
                  <span class="fw-semibold fs-6">{{ $t('common.Append') }}</span>
                </div>
              </label>

              <!-- Replace Option -->
              <label 
                class="radio-option p-3 rounded-3 border d-flex align-items-center w-100" 
                :class="{ 'selected': selectedAction === '2' }"
                for="replace"
              >
                <input 
                  class="form-check-input me-3" 
                  type="radio" 
                  name="datasetAction" 
                  id="replace" 
                  value="2" 
                  v-model="selectedAction"
                  :disabled="isLoading"
                >
                <div class="d-flex align-items-center">
                  <i class="bi bi-arrow-repeat text-warning me-2 fs-5"></i>
                  <span class="fw-semibold fs-6">{{ $t('common.Replace') }}</span>
                </div>
              </label>
            </div>

            <div class="d-flex gap-3 justify-content-end">
              <button 
                class="btn btn-secondary px-4 py-2" 
                @click="handleCancel"
                :disabled="isLoading"
              >
                {{ $t('common.Cancel') }}
              </button>
              <button 
                class="btn btn-primary px-4 py-2" 
                @click="handleFirstProceed"
                :disabled="isLoading"
              >
                {{ $t('common.Proceed') }}
              </button>
            </div>
          </div>

          <!-- Second Step: Confirmation Warning -->
          <div v-else-if="!isLoading">
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger mb-4" role="alert">
              <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill text-danger me-3 fs-4"></i>
                <div>
                  <strong class="fs-6">{{ $t('common.Warning') }}:</strong> 
                  <span class="fs-6">{{ $t('common.warinig_note') }}</span>
                </div>
              </div>
            </div>

            <div class="d-flex gap-3 justify-content-end">
              <button 
                class="btn btn-secondary px-4 py-2" 
                @click="handleCancel"
                :disabled="isLoading"
              >
                {{ $t('common.Cancel') }}
              </button>
              <button 
                class="btn btn-danger px-4 py-2" 
                @click="handleSecondProceed"
                :disabled="isLoading"
              >
                {{ $t('common.Confirm Delete') }}
              </button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-else class="text-center py-5">
            <div class="spinner-border text-primary mb-4" role="status" style="width: 3.5rem; height: 3.5rem;">
              <span class="visually-hidden">{{ $t('common.Processing') }}...</span>
            </div>
            <h5 class="text-primary fw-semibold mb-2">{{ $t('common.Processing Export') }}...</h5>
            <p class="text-muted mb-0">{{ $t('common.Please wait while we process your request') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Backdrop -->
  <div 
    v-if="props.show" 
    class="modal-backdrop fade show"
    @click="handleCancel"
  ></div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['confirm', 'cancel'])
const selectedAction = ref('1')
const showSecondConfirmation = ref(false)
const isLoading = ref(false)

// Reset modal state when show prop changes
watch(() => props.show, (newValue) => {
  if (newValue) {
    // Reset to initial state when modal opens
    selectedAction.value = '1'
    showSecondConfirmation.value = false
    isLoading.value = false
  }
})

const handleFirstProceed = async () => {
  if (selectedAction.value === '2') {
    showSecondConfirmation.value = true
  } else {
    isLoading.value = true
    emit('confirm', parseInt(selectedAction.value))
  }
}

const handleSecondProceed = async () => {
  isLoading.value = true
  emit('confirm', 2)
}

const handleCancel = () => {
  if (!isLoading.value) {
    resetModal()
    emit('cancel')
  }
}

const resetModal = () => {
  selectedAction.value = '1'
  showSecondConfirmation.value = false
  isLoading.value = false
}
</script>

<style scoped>
/* Custom Modal Width */
.custom-modal-width {
  max-width: 500px;
}

/* Modal Backdrop */
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

/* Modal Animation */
.modal.show .modal-dialog {
  animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Modal Content */
.modal-content {
  border: none;
}

/* Radio Option Styling - Full Clickable Card */
.radio-option {
  transition: all 0.25s ease;
  cursor: pointer;
  background-color: #fff;
  border: 2px solid #dee2e6 !important;
  margin-bottom: 0;
}

.radio-option:hover {
  background-color: #f8f9fa;
  border-color: #adb5bd !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transform: translateX(4px);
}

.radio-option.selected {
  background-color: #f8f9fa;
  border-color: #0d6efd !important;
  box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
}

.radio-option .form-check-input {
  width: 1.25rem;
  height: 1.25rem;
  margin-top: 0;
  cursor: pointer;
  flex-shrink: 0;
}

/* Alert Styling */
.alert {
  border-radius: 10px;
  border-width: 1px;
  border-style: solid;
}

/* Button Styling */
.btn {
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  min-width: 120px;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active:not(:disabled) {
  transform: translateY(0);
}

.btn-primary {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-danger:hover:not(:disabled) {
  background-color: #bb2d3b;
  border-color: #b02a37;
}

.btn-secondary {
  background-color: #6c757d;
  border-color: #6c757d;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background-color: #5c636a;
  border-color: #565e64;
}

/* Close Button */
.btn-close {
  transition: all 0.2s ease;
}

.btn-close:hover:not(:disabled) {
  transform: scale(1.1);
  opacity: 0.8;
}

/* Disabled State */
.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none !important;
}

/* Responsive Design */
@media (max-width: 576px) {
  .custom-modal-width {
    margin: 0.75rem;
  }

  .modal-body {
    padding: 1.5rem !important;
  }

  .d-flex.gap-3 {
    flex-direction: column;
    gap: 0.75rem !important;
  }

  .d-flex.gap-3 button {
    width: 100%;
  }

  .btn {
    min-width: auto;
  }
}
</style>
