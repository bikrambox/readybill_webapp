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
          aria-label="Close"
          style="z-index: 1051;"
        ></button>

        <!-- Body -->
        <div class="modal-body text-center px-4 py-5">
          <!-- Warning Icon -->
          <div class="warning-icon mb-3">
            <i class="bi bi-exclamation-triangle-fill text-warning"></i>
          </div>

          <h4 class="fw-bold mb-3">{{ $t('common.Confirm Deletion') }}</h4>
          
          <p class="text-secondary mb-4">
            {{ $t('common.Are you sure you want to delete') }} <strong>{{ itemName }}</strong>?
            <br>
            {{ $t('common.This action cannot be undone') }}.
          </p>

          <!-- Buttons -->
          <div class="d-flex gap-3 justify-content-center">
            <button 
              type="button" 
              class="btn btn-outline-secondary px-4 py-2"
              @click="close"
              style="min-width: 100px;"
            >
              {{ $t('common.Cancel') }}
            </button>
            <button 
              type="button" 
              class="btn btn-danger px-4 py-2 fw-semibold"
              @click="confirm"
              style="min-width: 100px;"
            >
              {{ $t('common.Delete') }}
            </button>
          </div>
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
import { ref, watch, onMounted } from 'vue'
import { Modal } from 'bootstrap'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
  itemName: {
    type: String,
    default: 'this item'
  }
})

const emit = defineEmits(['confirm', 'close'])

const modalElement = ref(null)
const modalInstance = ref(null)
const showModal = ref(false)

onMounted(() => {
  if (modalElement.value) {
    modalInstance.value = new Modal(modalElement.value, {
      backdrop: 'static',
      keyboard: false
    })
    
    modalElement.value.addEventListener('hidden.bs.modal', () => {
      showModal.value = false
      emit('close')
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

function show() {
  showModal.value = true
}

function close() {
  showModal.value = false
}

function confirm() {
  emit('confirm')
  close()
}

defineExpose({ show, close })
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

.warning-icon {
  font-size: 4rem;
  line-height: 1;
}

.warning-icon i {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.btn {
  border-radius: 8px;
  font-weight: 500;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-danger:hover {
  background-color: #bb2d3b;
  border-color: #b02a37;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

@media (max-width: 576px) {
  .modal-body {
    padding: 2rem 1.5rem !important;
  }

  h4 {
    font-size: 1.25rem;
  }

  .warning-icon {
    font-size: 3rem;
  }

  .d-flex.gap-3 {
    flex-direction: column;
  }

  .d-flex.gap-3 button {
    width: 100%;
  }
}
</style>
