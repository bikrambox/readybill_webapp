<template>
  <div
    class="modal fade"
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
          data-bs-dismiss="modal"
          aria-label="Close"
          style="z-index: 1;"
        ></button>

        <!-- Body -->
        <div class="modal-body text-center px-4 py-5">
          <!-- Error Box -->
          <FormErrorBox 
            v-if="errorMessages.length > 0"
            :messages="errorMessages" 
            @clear="clearErrors" 
            class="mb-4 text-start"
          />

          <h4 class="fw-bold mb-4">{{ $t('modal.Change Password') }}</h4>
          
          <p class="text-secondary mb-2">
            {{ $t('common.To change your password, we will send you an OTP') }}.
          </p>
          
          <p class="text-secondary mb-4">
            {{ $t('common.Click Send to confirm') }}
          </p>

          <!-- Buttons -->
          <div class="d-flex gap-3 justify-content-center">
            <button 
              type="button" 
              class="btn btn-success px-4 py-2 fw-semibold"
              @click="handleSendOtp"
              :disabled="changePasswordStore.isLoading"
              style="min-width: 120px;"
            >
              <span v-if="changePasswordStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ $t('common.Send OTP') }}
            </button>
            <button 
              type="button" 
              class="btn btn-danger px-4 py-2 fw-semibold"
              data-bs-dismiss="modal"
              :disabled="changePasswordStore.isLoading"
              style="min-width: 120px;"
            >
              {{ $t('common.Close') }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Modal } from 'bootstrap'
import { useI18n } from 'vue-i18n'
import { useChangePasswordStore } from '@/modules/Authentication/stores/changePassword'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

const { t } = useI18n()
const router = useRouter()
const changePasswordStore = useChangePasswordStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  mobileNumber: {
    type: String,
    required: true
  },
  countryCode: {
    type: String,
    default: 'IN'
  }
})

const emit = defineEmits(['update:show', 'close', 'success', 'error'])

const modalElement = ref(null)
const modalInstance = ref(null)
const errorMessages = ref([])

onMounted(() => {
  modalInstance.value = new Modal(modalElement.value)
  
  modalElement.value.addEventListener('hidden.bs.modal', () => {
    clearErrors()
    emit('update:show', false)
    emit('close')
  })
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    clearErrors()
    show()
  } else {
    hide()
  }
})

const show = () => {
  if (modalInstance.value) {
    modalInstance.value.show()
  }
}

const hide = () => {
  if (modalInstance.value) {
    modalInstance.value.hide()
  }
}

const clearErrors = () => {
  errorMessages.value = []
}

const handleSendOtp = async () => {
  clearErrors()
  
  const result = await changePasswordStore.sendPasswordChangeOTP(
    props.mobileNumber,
    props.countryCode
  )
  
  if (result.success) {
    // Store mobile and country code in store for change password page
    changePasswordStore.setPasswordChangeData(props.mobileNumber, props.countryCode)
    
    emit('success', result)
    hide()
    
    // Navigate to change password page without query params
    router.push({
      name: 'ChangePassword'
    })
  } else {
    handleErrors(result)
    emit('error', result.message)
  }
}

const handleErrors = (result) => {
  errorMessages.value = []
  
  if (result.errors) {
    Object.keys(result.errors).forEach(field => {
      if (Array.isArray(result.errors[field])) {
        errorMessages.value.push(...result.errors[field])
      } else {
        errorMessages.value.push(result.errors[field])
      }
    })
  } else if (result.message) {
    errorMessages.value = [result.message]
  } else {
    errorMessages.value = ['Failed to send OTP. Please try again.']
  }
}
</script>

<style scoped>
.modal-content {
  border-radius: 1rem !important;
}

.btn {
  border-radius: 8px;
  font-weight: 500;
}

@media (max-width: 768px) {
  .d-flex.gap-3 {
    flex-direction: column;
  }
  
  .d-flex.gap-3 button {
    width: 100%;
  }
}
</style>