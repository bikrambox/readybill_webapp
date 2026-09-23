<template>
  <div>
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

    <!-- Maximum Attempts Warning -->
    <div v-if="retryAfter" class="alert alert-warning alert-dismissible fade show">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <strong>{{ $t('common.Maximum attempts reached') }}.</strong> {{ $t('common.Please try again after') }} {{ retryAfter }}.
      <button type="button" class="btn-close" @click="retryAfter = ''"></button>
    </div>

    <h4 class="fw-bold text-center mb-3">{{ $t('common.Verify OTP') }}</h4>
    
    <p class="text-center text-secondary mb-4">
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
        :disabled="changePasswordStore.isLoading || isMaxAttemptsReached"
      />
    </div>
    
    <!-- OTP Info -->
    <div class="text-center mb-3">
      <small class="text-muted">
        {{ $t('common.OTP expires in 5 minutes') }}
      </small>
    </div>
    
    <!-- Attempt Counter -->
    <div v-if="attemptsLeft !== null && !isMaxAttemptsReached" class="text-center mb-3">
      <small class="text-warning fw-semibold">
        {{ attemptsLeft }} {{ attemptsLeft === 1 ? 'attempt' : 'attempts' }} {{ $t('common.left') }}
      </small>
    </div>
    
    <!-- Max Attempts Info -->
    <div v-if="isMaxAttemptsReached" class="text-center mb-3">
      <small class="text-danger fw-semibold">
        <i class="bi bi-exclamation-circle me-1"></i>
        {{ $t('common.Maximum attempts reached') }}. {{ $t('common.Please try again later') }}.
      </small>
    </div>
    
    <!-- Resend OTP -->
    <div class="text-center">
      <button
        type="button"
        class="btn btn-link text-decoration-none fw-semibold"
        @click="resendOTP"
        :disabled="resendTimer > 0 || changePasswordStore.isLoading || isMaxAttemptsReached"
      >
        <span v-if="changePasswordStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
        {{ resendTimer > 0 ? `${$t('common.Resend OTP in')} ${resendTimer}s` : t('common.Resend OTP') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useChangePasswordStore } from '@/modules/Authentication/stores/changePassword'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

const { t } = useI18n()
const changePasswordStore = useChangePasswordStore()

const props = defineProps({
  mobile: {
    type: String,
    required: true
  },
  countryCode: {
    type: String,
    default: 'IN'
  }
})

const emit = defineEmits(['verified'])

const otp = ref(['', '', '', '', '', ''])
const otpInputs = ref([])
const errorMessages = ref([])
const successMessage = ref('')
const resendTimer = ref(0)
const attemptsLeft = ref(null)
const retryAfter = ref('')
const isMaxAttemptsReached = ref(false)

onMounted(() => {
  startResendTimer()
  nextTick(() => {
    otpInputs.value[0]?.focus()
  })
})

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
  retryAfter.value = ''
  
  const result = await changePasswordStore.verifyOTP(props.mobile, otpString, '')
  
  if (result.success) {
    successMessage.value = result.message || t('common.OTP verified successfully')
    isMaxAttemptsReached.value = false
    
    setTimeout(() => {
      emit('verified')
    }, 1000)
  } else {
    handleErrors(result)
  }
}

const resendOTP = async () => {
  if (resendTimer.value > 0 || isMaxAttemptsReached.value) return
  
  clearErrors()
  successMessage.value = ''
  retryAfter.value = ''
  
  const result = await changePasswordStore.sendPasswordChangeOTP(props.mobile, props.countryCode)
  
  if (result.success) {
    successMessage.value = result.message || t('common.OTP resent successfully')
    startResendTimer()
    resetOTP()
    attemptsLeft.value = 3
    isMaxAttemptsReached.value = false
  } else {
    handleErrors(result)
  }
}

const handleErrors = (result) => {
  errorMessages.value = []
  
  // Check for maximum attempts error (HTTP 429)
  if (result.code === 429 || result.message?.toLowerCase().includes('maximum attempts')) {
    isMaxAttemptsReached.value = true
    
    if (result.data?.retry_after) {
      retryAfter.value = result.data.retry_after
      errorMessages.value = [`${t('common.Maximum attempts reached')}. ${t('common.Please try again after')} ${result.data.retry_after}.`]
    } else {
      errorMessages.value = [`${t('common.Maximum attempts reached')}. ${t('common.Please try again later')}.`]
    }
    
    // Disable OTP inputs
    resetOTP()
    return
  }
  
  if (result.errors) {
    Object.keys(result.errors).forEach(field => {
      if (Array.isArray(result.errors[field])) {
        errorMessages.value.push(...result.errors[field])
        
        // Extract attempts left
        if (field === 'otp') {
          const attemptMatch = result.errors[field][0].match(/Attempt Left - (\d+)/)
          if (attemptMatch) {
            attemptsLeft.value = parseInt(attemptMatch[1])
            
            // Check if attempts left is 0
            if (attemptsLeft.value === 0) {
              isMaxAttemptsReached.value = true
            }
          }
        }
      } else {
        errorMessages.value.push(result.errors[field])
      }
    })
  } else if (result.message) {
    errorMessages.value = [result.message]
  } else {
    errorMessages.value = [`${t('common.An error occurred')}. ${t('Please try again')}.`]
  }
  
  resetOTP()
}

const startResendTimer = () => {
  resendTimer.value = 60
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
    if (!isMaxAttemptsReached.value) {
      otpInputs.value[0]?.focus()
    }
  })
}
</script>

<style scoped>
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
  opacity: 0.6;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffecb5;
  color: #856404;
}

.alert-warning .btn-close {
  filter: none;
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
