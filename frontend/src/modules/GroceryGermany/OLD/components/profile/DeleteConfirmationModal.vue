<template>
  <div
    class="modal fade"
    :class="{ show: show }"
    :style="{ display: show ? 'block' : 'none' }"
    tabindex="-1"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">{{ $t('common.Are you sure you want to do the action ?') }}</h5>
          <button type="button" class="btn-close btn-close-white" @click="closeModal"></button>
        </div>
        
        <div class="modal-body">
          <div v-if="errorMessage" class="alert alert-danger">
            {{ errorMessage }}
          </div>
          
          <p class="text-center mb-4">
            {{ $t('profile_page.Once proceed, your inventory, transactions, dataset and account will be permanently deleted.') }}
          </p>
          
          <p class="text-center fw-bold mb-3">
            {{ $t('profile_page.Enter OTP to confirm deletion') }}
          </p>
          
          <div class="d-flex justify-content-center gap-2 mb-3">
            <input
              v-for="index in 6"
              :key="index"
              :ref="el => deleteOtpInputs[index - 1] = el"
              type="text"
              class="form-control text-center otp-input"
              maxlength="1"
              v-model="deleteOtp[index - 1]"
              @input="handleDeleteOTPInput(index - 1)"
              @keydown="handleKeyDown($event, index - 1)"
            />
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="closeModal">
            {{ $t('common.Cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- <div v-if="show" class="modal-backdrop fade show"></div> -->
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  show: Boolean,
  userId: [String, Number]
})

const emit = defineEmits(['update:show', 'confirmed'])

const deleteOtp = ref(['', '', '', '', '', ''])
const deleteOtpInputs = ref([])
const errorMessage = ref('')

watch(() => props.show, async (newVal) => {
  if (newVal) {
    await nextTick()
    deleteOtpInputs.value[0]?.focus()
    // Send OTP when modal opens
    sendDeleteOTP()
  } else {
    resetOTP()
  }
})

const sendDeleteOTP = async () => {
  try {
    await fetch(`${window.api_url}send-otp`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        purpose: 'delete_account',
        user_id: props.userId
      })
    })
  } catch (error) {
    console.error('Failed to send OTP:', error)
  }
}

const handleDeleteOTPInput = (index) => {
  const value = deleteOtp.value[index]
  
  if (!/^\d*$/.test(value)) {
    deleteOtp.value[index] = ''
    return
  }
  
  if (value && index < 5) {
    deleteOtpInputs.value[index + 1]?.focus()
  }
  
  if (deleteOtp.value.every(digit => digit !== '')) {
    confirmDelete()
  }
}

const handleKeyDown = (event, index) => {
  if (event.key === 'Backspace' && !deleteOtp.value[index] && index > 0) {
    deleteOtpInputs.value[index - 1]?.focus()
  }
}

const confirmDelete = async () => {
  const otpString = deleteOtp.value.join('')
  errorMessage.value = ''
  
  try {
    const response = await fetch(`${window.api_url}delete-account`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        user_id: props.userId,
        otp: otpString
      })
    })
    
    const data = await response.json()
    
    if (response.ok && data.status === 1) {
      emit('confirmed')
    } else {
      errorMessage.value = data.message || 'Invalid OTP. Please try again.'
      resetOTP()
    }
  } catch (error) {
    errorMessage.value = 'An error occurred. Please try again.'
  }
}

const resetOTP = () => {
  deleteOtp.value = ['', '', '', '', '', '']
  errorMessage.value = ''
}

const closeModal = () => {
  resetOTP()
  emit('update:show', false)
}
</script>

<style scoped>
.otp-input {
  width: 45px;
  height: 45px;
  font-size: 1.25rem;
  font-weight: bold;
}

@media (max-width: 576px) {
  .otp-input {
    width: 40px;
    height: 40px;
    font-size: 1rem;
  }
}
</style>
