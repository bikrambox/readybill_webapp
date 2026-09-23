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
          
          <!-- Success Message -->
          <div v-if="successMessage" class="alert alert-success alert-dismissible fade show text-start">
            {{ successMessage }}
            <button type="button" class="btn-close" @click="successMessage = ''"></button>
          </div>
          
          <h4 class="fw-bold mb-3">{{ $t('profile_page.Enter OTP') }}</h4>
          
          <p class="text-secondary mb-4">
            {{ $t('profile_page.Enter OTP sent to') }} <strong>{{ mobile }}</strong>
          </p>
          
          <!-- OTP Input -->
          <div class="d-flex justify-content-center gap-2 mb-4">
            <input
              v-for="index in 6"
              :key="index"
              :ref="el => otpInputs[index - 1] = el"
              type="text"
              class="form-control text-center otp-input"
              maxlength="1"
              v-model="otp[index - 1]"
              @input="handleOTPInput(index - 1)"
              @keydown="handleKeyDown($event, index - 1)"
              :disabled="isVerifying"
            />
          </div>
          
          <!-- OTP Info -->
          <div class="text-center mb-3">
            <small class="text-muted">
              OTP expires in {{ otpExpiry }} minutes
            </small>
          </div>
          
          <!-- Attempt Counter -->
          <div v-if="attemptsLeft !== null" class="text-center mb-3">
            <small class="text-warning fw-semibold">
              {{ attemptsLeft }} {{ attemptsLeft === 1 ? 'attempt' : 'attempts' }} left
            </small>
          </div>
          
          <!-- Resend OTP -->
          <div class="text-center">
            <button
              type="button"
              class="btn btn-link text-decoration-none fw-semibold"
              @click="resendOTP"
              :disabled="resendTimer > 0 || isResending"
            >
              <span v-if="isResending" class="spinner-border spinner-border-sm me-2"></span>
              {{ resendTimer > 0 ? `Resend OTP in ${resendTimer}s` : 'Resend OTP' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, nextTick, computed, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { useI18n } from 'vue-i18n'
import { useProfileStore } from '../../stores/profile.js'
import { useForgotPasswordStore } from '@/modules/Authentication/stores/forgotPasswordStore.js'
import FormErrorBox from '@/modules/core/components/FormErrorBox.vue'

const { t } = useI18n()
const profileStore = useProfileStore()
const forgotPasswordStore = useForgotPasswordStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  mobile: {
    type: String,
    required: true
  },
  countryCode: {
    type: String,
    default: 'IN'
  },
  userId: {
    type: [String, Number],
    required: false
  },
  purpose: {
    type: String,
    default: 'change_mobile',
    validator: (value) => ['change_mobile', 'delete_account', 'forgot_password'].includes(value)
  }
})

const emit = defineEmits(['update:show', 'verified'])

const modalElement = ref(null)
const modalInstance = ref(null)
const otp = ref(['', '', '', '', '', ''])
const otpInputs = ref([])
const errorMessages = ref([])
const successMessage = ref('')
const resendTimer = ref(0)
const attemptsLeft = ref(null)
const isVerifying = ref(false)
const isResending = ref(false)

const otpExpiry = computed(() => {
  return props.purpose === 'forgot_password' 
    ? forgotPasswordStore.otpConfig.expiry 
    : profileStore.otpConfig.expiry
})

const otpMaxAttempts = computed(() => {
  return props.purpose === 'forgot_password'
    ? forgotPasswordStore.otpConfig.maxAttempts
    : profileStore.otpConfig.maxAttempts
})

const countdown = computed(() => {
  return props.purpose === 'forgot_password'
    ? forgotPasswordStore.otpConfig.countdown
    : profileStore.otpConfig.countdown
})

onMounted(() => {
  modalInstance.value = new Modal(modalElement.value)
  
  modalElement.value.addEventListener('shown.bs.modal', () => {
    nextTick(() => {
      otpInputs.value[0]?.focus()
    })
  })
  
  modalElement.value.addEventListener('hidden.bs.modal', () => {
    resetOTP()
    clearErrors()
    successMessage.value = ''
    attemptsLeft.value = null
    resendTimer.value = 0
    emit('update:show', false)
  })
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetOTP()
    clearErrors()
    startResendTimer()
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

const handleOTPInput = (index) => {
  const value = otp.value[index]
  
  if (!/^\d*$/.test(value)) {
    otp.value[index] = ''
    return
  }
  
  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }
  
  if (otp.value.every(digit => digit !== '')) {
    verifyOTP()
  }
}

const handleKeyDown = (event, index) => {
  if (event.key === 'Backspace' && !otp.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus()
  }
}

const verifyOTP = async () => {
  const otpString = otp.value.join('')
  clearErrors()
  successMessage.value = ''
  isVerifying.value = true
  
  let result
  
  // Call different API based on purpose
  if (props.purpose === 'delete_account') {
    result = await profileStore.deleteAccount(props.userId, otpString)
  } else if (props.purpose === 'forgot_password') {
    result = await forgotPasswordStore.verifyOTP(props.mobile, otpString)
  } else {
    result = await profileStore.verifyAndUpdateMobile(
      props.userId,
      props.mobile,
      props.countryCode,
      otpString
    )
  }
  
  isVerifying.value = false
  
  if (result.success) {
    successMessage.value = result.message
    
    setTimeout(() => {
      emit('verified')
      hide()
    }, 1500)
  } else {
    handleVerificationError(result)
  }
}

const handleVerificationError = (result) => {
  errorMessages.value = []
  
  if (result.errors) {
    // Extract all error messages
    Object.keys(result.errors).forEach(field => {
      if (Array.isArray(result.errors[field])) {
        errorMessages.value.push(...result.errors[field])
        
        // Extract attempts left from OTP error
        if (field === 'otp') {
          const attemptMatch = result.errors[field][0].match(/Attempt Left - (\d+)/)
          if (attemptMatch) {
            attemptsLeft.value = parseInt(attemptMatch[1])
          }
        }
      } else {
        errorMessages.value.push(result.errors[field])
      }
    })
  } else if (result.message) {
    errorMessages.value = [result.message]
  } else {
    errorMessages.value = ['An error occurred. Please try again.']
  }
  
  // Reset OTP inputs
  resetOTP()
}

const resendOTP = async () => {
  if (resendTimer.value > 0 || isResending.value) return
  
  clearErrors()
  successMessage.value = ''
  isResending.value = true
  
  let result
  
  // Call different resend API based on purpose
  if (props.purpose === 'delete_account') {
    result = await profileStore.sendDeleteAccountOTP(props.mobile, props.countryCode)
  } else if (props.purpose === 'forgot_password') {
    result = await forgotPasswordStore.sendOTP(props.mobile, props.countryCode)
  } else {
    result = await profileStore.resendOTP(props.mobile)
  }
  
  isResending.value = false
  
  if (result.success) {
    successMessage.value = result.message || 'OTP resent successfully'
    startResendTimer()
    resetOTP()
    attemptsLeft.value = otpMaxAttempts.value
  } else {
    // Show resend errors in FormErrorBox
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
      errorMessages.value = ['Failed to resend OTP. Please try again.']
    }
  }
}

const startResendTimer = () => {
  resendTimer.value = countdown.value
  const interval = setInterval(() => {
    resendTimer.value--
    if (resendTimer.value <= 0) {
      clearInterval(interval)
    }
  }, 1000)
}

const resetOTP = () => {
  otp.value = ['', '', '', '', '', '']
  
  nextTick(() => {
    otpInputs.value[0]?.focus()
  })
}
</script>


<style scoped>
.modal-content {
  border-radius: 1rem !important;
}

.otp-input {
  width: 48px;
  height: 48px;
  font-size: 1.25rem;
  font-weight: bold;
  border-radius: 8px;
  border: 2px solid #dee2e6;
  transition: all 0.2s ease;
}

.otp-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.otp-input:disabled {
  background-color: #e9ecef;
  cursor: not-allowed;
}

@media (max-width: 576px) {
  .otp-input {
    width: 42px;
    height: 42px;
    font-size: 1.1rem;
  }
  
  .gap-2 {
    gap: 0.35rem !important;
  }
}
</style>
