<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmVariant: {
    type: String,
    default: 'success'
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<template>
  <div 
    class="modal fade"
    :class="{ show: show, 'd-block': show }"
    tabindex="-1"
    :style="{ display: show ? 'block' : 'none' }"
    @click.self="handleCancel"
  >
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0 pb-2">
          <h6 class="modal-title fw-bold mb-0">
            <i class="bi bi-exclamation-circle text-warning me-2"></i>
            {{ title }}
          </h6>
          <button 
            type="button" 
            class="btn-close btn-close-sm" 
            @click="handleCancel"
          ></button>
        </div>

        <!-- Modal Body -->
        <div class="modal-body px-4 py-3">
          <p class="mb-0">{{ message }}</p>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer border-0 pt-2 pb-3 px-3">
          <button 
            type="button" 
            class="btn btn-sm btn-secondary px-3"
            @click="handleCancel"
          >
            {{ cancelText }}
          </button>
          <button 
            type="button" 
            class="btn btn-sm px-3"
            :class="`btn-${confirmVariant}`"
            @click="handleConfirm"
          >
            <i class="bi bi-check-circle me-1"></i>
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Backdrop -->
  <!-- <div 
    v-if="show"
    class="modal-backdrop fade"
    :class="{ show: show }"
    @click="handleCancel"
  ></div> -->
</template>

<style scoped>
.modal.show {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
  border-radius: 12px;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-sm {
  max-width: 400px;
}

@media (max-width: 576px) {
  .modal-sm {
    max-width: 95%;
    margin: 0.5rem auto;
  }
}
</style>
