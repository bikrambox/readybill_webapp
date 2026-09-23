<template>
  <teleport to="body">
    <transition name="modal-fade">
      <div 
        v-if="show" 
        class="modal d-block" 
        tabindex="-1"
        role="dialog"
        @click.self="handleClose"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg">
            <!-- Close Button -->
            <button 
              type="button" 
              class="btn-close position-absolute top-0 end-0 m-3"
              @click="handleClose"
              aria-label="Close"
            ></button>

            <!-- Modal Body -->
            <div class="modal-body text-center p-4 p-md-5">
              <h4 class="modal-title fw-bold mb-3">
                {{ $t('modal.Forgot Password') }}
              </h4>
              
              <p class="text-muted mb-2">
                {{ $t('common.to_change_your_password') }}
              </p>

              <!-- COMMON ERROR BOX -->
              <FormErrorBox
                v-if="allErrors.length"
                :messages="allErrors"
                @clear="clearErrors"
                class="mb-3"
              />

              <!-- Mobile Number -->
              <div class="row mb-3">
                <label class="col-12 text-start pb-2">
                  Mobile Number
                </label>
                <div class="col-12">
                  <div class="input-group">
                    <CountryCodeSelect 
                      v-model="countryCode"
                      @change="selectedCountry = $event"
                    />
                    <input
                      type="text"
                      class="form-control"
                      v-model="mobile"
                      :placeholder="t('common.Enter your mobile number')"
                    />
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex gap-3 justify-content-center flex-wrap">
                <button 
                  type="button" 
                  class="btn btn-success px-4"
                  :disabled="isLoading"
                  @click="handleSendOtp"
                >
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ $t('common.Send OTP') }}
                </button>
                
                <button 
                  type="button" 
                  class="btn btn-danger px-4"
                  :disabled="isLoading"
                  @click="handleClose"
                >
                  {{ $t('common.Close') }}
                </button>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Backdrop -->
    <transition name="backdrop-fade">
      <div 
        v-if="show" 
        class="modal-backdrop"
        @click="handleClose"
      ></div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'
import { useForgotPasswordStore } from '../stores/forgotPasswordStore.js'  // ✅ Import the store

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  mobileNumber: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'otp-sent'])

const forgotPasswordStore = useForgotPasswordStore()  // ✅ Initialize the store

const mobile = ref(props.mobileNumber || '')
const countryCode = ref('')
const selectedCountry = ref(null)
const isLoading = ref(false)

// Flatten errors into array for FormErrorBox
const allErrors = computed(() => {
  const list = []
  const errObj = forgotPasswordStore.errors || {}
  Object.values(errObj).forEach((arr) => {
    if (Array.isArray(arr)) list.push(...arr)
  })
  return list
})

const clearErrors = () => {
  forgotPasswordStore.clearErrors()
}

const handleClose = () => {
  if (!isLoading.value) {
    clearErrors()
    mobile.value = ''
    countryCode.value = ''
    selectedCountry.value = null
    emit('close')
  }
}

const handleSendOtp = async () => {
  clearErrors()

  // Frontend validation
  const frontendErrors = []
  
  if (!selectedCountry.value) {
    frontendErrors.push(t('common.Country code is required to continue')+'.')
  }
  
  if (!mobile.value) {
    frontendErrors.push(t('common.Mobile number is required to continue')+'.')
  }

  if (frontendErrors.length) {
    forgotPasswordStore.errors = { frontend: frontendErrors }
    return
  }

  isLoading.value = true

  const result = await forgotPasswordStore.sendOTP(
    mobile.value,
    selectedCountry.value.code
  )

  isLoading.value = false

  if (result.success) {
    // Emit event with mobile and country code for OTP modal
    emit('otp-sent', {
      mobile: mobile.value,
      countryCode: selectedCountry.value.code
    })
    // Close this modal
    emit('close')
  }
}
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 1060;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1055;
}

.modal-content {
  border-radius: 12px;
  overflow: hidden;
}

.modal-title {
  color: #212529;
  font-size: 1.5rem;
}

.btn {
  min-width: 120px;
  font-weight: 500;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-success {
  background-color: #198754;
  border-color: #198754;
}

.btn-success:hover {
  background-color: #157347;
  border-color: #146c43;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
}

.btn-danger {
  background-color: #dc3545;
  border-color: #dc3545;
}

.btn-danger:hover {
  background-color: #bb2d3b;
  border-color: #b02a37;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.btn-close {
  z-index: 10;
  opacity: 0.5;
  transition: opacity 0.2s;
}

.btn-close:hover {
  opacity: 1;
}

/* Modal Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal-dialog,
.modal-fade-leave-to .modal-dialog {
  transform: scale(0.9) translateY(-20px);
}

.modal-fade-enter-to .modal-dialog,
.modal-fade-leave-from .modal-dialog {
  transform: scale(1) translateY(0);
}

.modal-dialog {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Backdrop Animations */
.backdrop-fade-enter-active,
.backdrop-fade-leave-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.backdrop-fade-enter-from,
.backdrop-fade-leave-to {
  opacity: 0;
}

/* Mobile Responsive */
@media (max-width: 576px) {
  .modal-body {
    padding: 2rem 1.5rem !important;
  }

  .modal-title {
    font-size: 1.25rem;
  }

  .btn {
    min-width: 100px;
    font-size: 0.875rem;
  }

  .d-flex.gap-3 {
    gap: 0.75rem !important;
  }
}

@media (max-width: 400px) {
  .d-flex.gap-3 {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>
