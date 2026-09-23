<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isVisible"
        class="modal fade show d-block"
        tabindex="-1"
        aria-hidden="false"
        @click.self="handleBackdropClick"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content rounded-4 border-0 shadow">
            
            <!-- Close Button -->
            <button 
              type="button" 
              class="btn-close position-absolute top-0 end-0 m-3" 
              @click="handleCancel"
              aria-label="Close"
              style="z-index: 1051;"
            ></button>

            <!-- Body -->
            <div class="modal-body text-center px-4 py-5">
              <!-- Warning Icon -->
              <div class="warning-icon mb-3">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
              </div>

              <h4 class="fw-bold mb-3">{{ title }}</h4>
              
              <p class="text-secondary mb-4">
                {{ message }}
                <br v-if="subMessage">
                <span v-if="subMessage">{{ subMessage }}</span>
              </p>

              <!-- Buttons -->
              <div class="d-flex gap-3 justify-content-center">
                <button 
                  type="button" 
                  class="btn btn-outline-secondary px-4 py-2"
                  @click="handleCancel"
                  style="min-width: 100px;"
                >
                  {{ cancelText }}
                </button>
                <button 
                  type="button" 
                  class="btn btn-danger px-4 py-2 fw-semibold"
                  @click="handleConfirm"
                  :disabled="isLoading"
                  style="min-width: 100px;"
                >
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ confirmText }}
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </Transition>

    <!-- Backdrop -->
    <Transition name="backdrop">
      <div 
        v-if="isVisible" 
        class="modal-backdrop fade show"
        @click="handleBackdropClick"
      ></div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Confirm Deletion'
  },
  message: {
    type: String,
    required: true
  },
  subMessage: {
    type: String,
    default: 'This action cannot be undone.'
  },
  confirmText: {
    type: String,
    default: 'Delete'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  closeOnBackdrop: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['confirm', 'cancel', 'close'])

const isVisible = ref(false)
const isLoading = ref(false)

function show() {
  isVisible.value = true
  document.body.style.overflow = 'hidden'
}

function hide() {
  isVisible.value = false
  isLoading.value = false
  document.body.style.overflow = ''
}

function handleBackdropClick() {
  if (props.closeOnBackdrop) {
    handleCancel()
  }
}

function handleCancel() {
  emit('cancel')
  emit('close')
  hide()
}

async function handleConfirm() {
  isLoading.value = true
  emit('confirm')
  // Don't auto-close; let parent handle it
  // Parent can call hide() via ref if needed
}

defineExpose({ show, hide, isLoading })
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
  transition: all 0.2s ease;
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-danger:hover:not(:disabled) {
  background-color: #bb2d3b;
  border-color: #b02a37;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.backdrop-enter-active,
.backdrop-leave-active {
  transition: opacity 0.3s ease;
}

.backdrop-enter-from,
.backdrop-leave-to {
  opacity: 0;
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
