<template>
  <div class="otp-form">
    <div class="form-wrapper">
      <div class="form-header">
        <h1 class="form-title">{{ $t('common.OTP Verification') }}</h1>
        <p class="form-description">
          {{ $t('common.sent_6_digit_otp') }}<br>
          <strong class="mobile-number">{{ formatMobile }}</strong>
        </p>
      </div>
      
      <form @submit.prevent="handleSubmit" class="verification-form">
        <div class="otp-section">
          <div class="otp-boxes">
            <input 
              v-for="(digit, index) in otp" 
              :key="index"
              type="tel"
              inputmode="numeric"
              maxlength="1"
              class="otp-box"
              v-model="otp[index]"
              @input="handleInput(index, $event)"
              @keydown="handleKeyDown(index, $event)"
              @paste="handlePaste"
              :ref="el => otpRefs[index] = el"
            />
          </div>
        </div>
        
        <FormErrorBox 
          v-if="registerStore.errorMessages.length"
          :messages="registerStore.errorMessages"
          @clear="registerStore.clearErrors"
        />
        
        <button 
          type="submit" 
          class="submit-button"
          :disabled="registerStore.isLoading || !isOtpComplete"
        >
          {{ registerStore.isLoading ? t('common.Verifying')+'...' : t('common.Verify OTP') }}
        </button>
      </form>
    </div>
    
    <div class="form-links">
      <p class="link-text">
        Didn't receive code? 
        <a 
          href="#" 
          @click.prevent="handleResendOtp"
          class="link-primary"
          :class="{ 'link-disabled': resendCooldown > 0 }"
        >
          {{ resendCooldown > 0 ? `Resend in ${resendCooldown}s` : 'Resend OTP' }}
        </a>
      </p>
      <button 
        type="button" 
        class="link-back" 
        @click="registerStore.previousStep()"
      >
        ← {{ $t('common.Change Mobile Number') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRegisterStore } from '../stores/registerStore'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const registerStore = useRegisterStore()
const otp = reactive(['', '', '', '', '', ''])
const otpRefs = ref([])
const resendCooldown = ref(0)
let cooldownTimer = null

const formatMobile = computed(() => {
  const mobile = registerStore.registrationData.mobile
  if (mobile.length === 10) {
    return `+91 ${mobile.slice(0, 5)} ${mobile.slice(5)}`
  }
  return mobile
})

const isOtpComplete = computed(() => {
  return otp.every(digit => digit !== '')
})

watch(otp, (newOtp) => {
  if (isOtpComplete.value) {
    registerStore.updateRegistrationField('otp', newOtp.join(''))
  }
}, { deep: true })

onMounted(() => {
  if (otpRefs.value[0]) {
    setTimeout(() => otpRefs.value[0].focus(), 100)
  }
  startResendCooldown()
})

onUnmounted(() => {
  if (cooldownTimer) {
    clearInterval(cooldownTimer)
  }
})

const handleInput = (index, event) => {
  const value = event.target.value.replace(/\D/g, '')
  otp[index] = value
  
  if (value && index < 5) {
    otpRefs.value[index + 1].focus()
  }
}

const handleKeyDown = (index, event) => {
  if (event.key === 'Backspace') {
    if (!otp[index] && index > 0) {
      otp[index - 1] = ''
      otpRefs.value[index - 1].focus()
    }
  }
}

const handlePaste = (event) => {
  event.preventDefault()
  const pastedData = event.clipboardData.getData('text').trim().replace(/\D/g, '')
  
  if (pastedData.length === 6) {
    pastedData.split('').forEach((digit, index) => {
      if (index < 6) {
        otp[index] = digit
      }
    })
    otpRefs.value[5].focus()
  }
}

const handleSubmit = async () => {
  if (!isOtpComplete.value) return
  
  try {
    await registerStore.verifyOtp()
  } catch (error) {
    console.error('Failed to verify OTP:', error)
    otp.forEach((_, index) => otp[index] = '')
    otpRefs.value[0].focus()
  }
}

const startResendCooldown = () => {
  resendCooldown.value = 30
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownTimer)
    }
  }, 1000)
}

const handleResendOtp = async () => {
  if (resendCooldown.value > 0) return
  
  try {
    await registerStore.resendOtp()
    otp.forEach((_, index) => otp[index] = '')
    otpRefs.value[0].focus()
    startResendCooldown()
  } catch (error) {
    console.error('Failed to resend OTP:', error)
  }
}
</script>

<style scoped>
.otp-form {
  width: 100%;
  display: flex;
  flex-direction: column;
}

.form-wrapper {
  flex: 1;
}

.form-header {
  margin-bottom: 36px;
}

.form-title {
  font-size: 32px;
  font-weight: 700;
  color: #1a1a1a;
  margin: 0 0 8px 0;
  line-height: 1.2;
  text-align: left;
}

.form-description {
  font-size: 16px;
  color: #6c757d;
  margin: 0;
  line-height: 1.6;
  text-align: left;
}

.mobile-number {
  color: #1a1a1a;
  font-weight: 600;
}

.verification-form {
  margin-bottom: 0;
}

.otp-section {
  margin-bottom: 24px;
}

.otp-boxes {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.otp-box {
  width: 54px;
  height: 54px;
  text-align: center;
  font-size: 24px;
  font-weight: 600;
  color: #1a1a1a;
  background-color: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  transition: all 0.2s ease;
  font-family: inherit;
}

.otp-box:focus {
  outline: none;
  border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.submit-button {
  width: 100%;
  height: 54px;
  padding: 0 24px;
  font-size: 17px;
  font-weight: 600;
  color: #fff;
  background-color: #0d6efd;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  margin-top: 12px;
}

.submit-button:hover:not(:disabled) {
  background-color: #0b5ed7;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.submit-button:active:not(:disabled) {
  transform: translateY(0);
}

.submit-button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
  opacity: 0.65;
}

.form-links {
  margin-top: 28px;
  text-align: center;
}

.link-text {
  font-size: 15px;
  color: #6c757d;
  margin: 0 0 16px 0;
}

.link-primary {
  color: #0d6efd;
  text-decoration: none;
  font-weight: 600;
}

.link-primary:hover {
  text-decoration: underline;
}

.link-disabled {
  color: #adb5bd;
  cursor: not-allowed;
  pointer-events: none;
}

.link-back {
  background: none;
  border: none;
  color: #0d6efd;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  font-family: inherit;
}

.link-back:hover {
  text-decoration: underline;
}

/* Mobile Responsive - Center Text Alignment */
@media (max-width: 767px) {
  .form-header {
    margin-bottom: 28px;
    text-align: center;
  }
  
  .form-title {
    font-size: 24px;
    text-align: center;
  }
  
  .form-description {
    font-size: 14px;
    text-align: center;
  }
  
  .otp-boxes {
    gap: 10px;
  }
  
  .otp-box {
    width: 48px;
    height: 48px;
    font-size: 20px;
    border-radius: 8px;
  }
  
  .submit-button {
    height: 50px;
    font-size: 16px;
    border-radius: 8px;
  }
  
  .link-text {
    font-size: 14px;
  }
  
  .link-back {
    font-size: 14px;
  }
  
  .form-links {
    margin-top: 20px;
  }
}

@media (max-width: 374px) {
  .form-title {
    font-size: 22px;
  }
  
  .form-description {
    font-size: 13px;
  }
  
  .otp-boxes {
    gap: 8px;
  }
  
  .otp-box {
    width: 44px;
    height: 44px;
    font-size: 18px;
  }
  
  .submit-button {
    height: 48px;
    font-size: 15px;
  }
}
</style>
