<template>
  <form @submit.prevent="handleVerify">
    <div class="row mb-3">
      <label class="col-md-4 col-lg-3 col-form-label fw-bold">
        {{ $t('common.OTP') }}
      </label>
      <div class="col-md-8 col-lg-9">
        <div class="d-flex gap-2 otp-input-container">
          <input 
            v-for="index in 6" 
            :key="index"
            :ref="el => setOtpInputRef(el, index)"
            v-model="otpDigits[index - 1]"
            type="text" 
            maxlength="1"
            class="form-control otp-box text-center"
            :class="{ 'is-invalid': hasError }"
            inputmode="numeric"
            pattern="[0-9]*"
            @input="handleInput(index, $event)"
            @keydown="handleKeyDown(index, $event)"
            @paste="handlePaste"
          />
        </div>
        <div v-if="errorMessage" class="invalid-feedback d-block mt-2">
          {{ errorMessage }}
        </div>
      </div>
    </div>
  </form>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
// import { verifyOtp } from '../services/authService'

const props = defineProps({
  mobileNumber: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['verified', 'resend'])

// State
const otpDigits = ref(['', '', '', '', '', ''])
const otpInputRefs = ref([])
const errorMessage = ref('')
const hasError = ref(false)

// Computed
const otpValue = computed(() => otpDigits.value.join(''))

// Set input refs
const setOtpInputRef = (el, index) => {
  if (el) {
    otpInputRefs.value[index - 1] = el
  }
}

// Handle input
const handleInput = (index, event) => {
  const value = event.target.value

  // Only allow numbers
  if (!/^\d*$/.test(value)) {
    otpDigits.value[index - 1] = ''
    return
  }

  otpDigits.value[index - 1] = value

  // Auto-focus next input
  if (value && index < 6) {
    otpInputRefs.value[index]?.focus()
  }

  // Auto-submit when all digits are filled
  if (index === 6 && value && otpValue.value.length === 6) {
    handleVerify()
  }

  // Clear error on input
  if (hasError.value) {
    hasError.value = false
    errorMessage.value = ''
  }
}

// Handle keydown
const handleKeyDown = (index, event) => {
  // Handle backspace
  if (event.key === 'Backspace' && !otpDigits.value[index - 1] && index > 1) {
    otpInputRefs.value[index - 2]?.focus()
  }

  // Handle arrow keys
  if (event.key === 'ArrowLeft' && index > 1) {
    event.preventDefault()
    otpInputRefs.value[index - 2]?.focus()
  }
  if (event.key === 'ArrowRight' && index < 6) {
    event.preventDefault()
    otpInputRefs.value[index]?.focus()
  }
}

// Handle paste
const handlePaste = (event) => {
  event.preventDefault()
  const pastedData = event.clipboardData.getData('text').trim()
  
  if (/^\d{6}$/.test(pastedData)) {
    const digits = pastedData.split('')
    digits.forEach((digit, index) => {
      otpDigits.value[index] = digit
    })
    otpInputRefs.value[5]?.focus()
    
    // Auto-submit after paste
    setTimeout(() => {
      handleVerify()
    }, 100)
  }
}

// Handle verify
const handleVerify = async () => {
  if (otpValue.value.length !== 6) {
    hasError.value = true
    errorMessage.value = t('common.Please enter all 6 digits')
    return
  }

  try {
    const response = await verifyOtp({
      mobile: props.mobileNumber,
      otp: otpValue.value
    })

    if (response.status === 1) {
      emit('verified')
    } else {
      hasError.value = true
      errorMessage.value = response.message || t('common.Invalid OTP')
    }
  } catch (error) {
    hasError.value = true
    errorMessage.value = error.message || t('common.Verification failed')
  }
}

// Focus first input on mount
onMounted(() => {
  otpInputRefs.value[0]?.focus()
})
</script>

<style scoped>
.otp-box {
  width: 50px;
  height: 50px;
  font-size: 1.5rem;
  font-weight: 600;
  border: 2px solid #dee2e6;
  border-radius: 0.375rem;
  transition: all 0.2s;
}

.otp-box:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
  outline: 0;
}

.otp-box.is-invalid {
  border-color: #dc3545;
}

/* Mobile responsive */
@media (max-width: 576px) {
  .otp-input-container {
    gap: 0.375rem !important;
  }

  .otp-box {
    width: 40px;
    height: 40px;
    font-size: 1.25rem;
  }
}

@media (max-width: 400px) {
  .otp-box {
    width: 35px;
    height: 35px;
    font-size: 1.1rem;
  }
}
</style>
