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

          <h4 class="fw-bold mb-4">{{ $t('profile_page.Change Mobile Number') }}</h4>
          
          <p class="text-secondary mb-4">
            {{ $t('profile_page.To change your mobile number, Please enter your new mobile number') }}
          </p>
          
          <div class="mb-4">
            <label class="form-label fw-semibold text-start d-block mb-2">
              {{ $t('common.Mobile Number') }}
            </label>
            <div class="input-group">
              <CountryCodeSelect
                v-model="countryCode"
                @change="selectedCountry = $event"
              />
              <input 
                v-model="newMobile"
                type="text" 
                class="form-control"
                :class="{ 'border-danger': errors.mobile }"
                :placeholder="$t('profile_page.Enter your mobile number')"
                maxlength="10"
                @input="validateMobile"
              >
            </div>
            <span v-if="errors.mobile" class="text-danger small d-block text-start mt-1">{{ errors.mobile }}</span>
          </div>

          <!-- Buttons -->
          <div class="d-flex gap-3 justify-content-center">
            <button 
              type="button" 
              class="btn btn-success px-4 py-2 fw-semibold"
              @click="sendOTP"
              :disabled="isLoading || !isValidMobile"
              style="min-width: 120px;"
            >
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
              Send OTP
            </button>
            <button 
              type="button" 
              class="btn btn-danger px-4 py-2 fw-semibold"
              data-bs-dismiss="modal"
              style="min-width: 120px;"
            >
              Close
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { useI18n } from 'vue-i18n'
import { useProfileStore } from '../../stores/profile.js'
import CountryCodeSelect from '@/modules/Core/components/CountryCodeSelect.vue'
import FormErrorBox from '@/modules/core/components/FormErrorBox.vue'

const { t } = useI18n()
const profileStore = useProfileStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  userId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['update:show', 'success'])

const modalElement = ref(null)
const modalInstance = ref(null)
const newMobile = ref('')
const countryCode = ref('+91')
const selectedCountry = ref(null)
const errors = ref({})
const errorMessages = ref([])
const isLoading = ref(false)

const isValidMobile = computed(() => {
  return /^[0-9]{10}$/.test(newMobile.value)
})

onMounted(() => {
  modalInstance.value = new Modal(modalElement.value)
  
  modalElement.value.addEventListener('hidden.bs.modal', () => {
    resetForm()
    emit('update:show', false)
  })
})

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetForm()
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

const validateMobile = () => {
  errors.value.mobile = ''
  
  if (!newMobile.value) {
    errors.value.mobile = 'Mobile number is required'
    return false
  }
  
  if (!/^[0-9]{10}$/.test(newMobile.value)) {
    errors.value.mobile = 'Mobile number must be 10 digits'
    return false
  }
  
  return true
}

const clearErrors = () => {
  errors.value = {}
  errorMessages.value = []
}

const resetForm = () => {
  newMobile.value = ''
  countryCode.value = '+91'
  selectedCountry.value = null
  clearErrors()
}

const sendOTP = async () => {
  clearErrors()
  
  if (!validateMobile()) {
    return
  }
  
  isLoading.value = true
  
  const result = await profileStore.sendOTP(
    props.userId,
    newMobile.value,
    selectedCountry.value?.code || 'IN'
  )
  
  isLoading.value = false
  
  if (result.success) {
    emit('success', {
      mobile: newMobile.value,
      country_code: selectedCountry.value?.code || 'IN'
    })
    hide()
  } else {
    handleErrors(result)
  }
}

const handleErrors = (result) => {
  if (result.errors) {
    errorMessages.value = []
    
    Object.keys(result.errors).forEach(field => {
      if (Array.isArray(result.errors[field])) {
        errors.value[field] = result.errors[field][0]
        errorMessages.value.push(...result.errors[field])
      } else {
        errors.value[field] = result.errors[field]
        errorMessages.value.push(result.errors[field])
      }
    })
  } else if (result.message) {
    errorMessages.value = [result.message]
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

.input-group {
  display: flex;
}

.text-danger.small {
  font-size: 0.875rem;
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
