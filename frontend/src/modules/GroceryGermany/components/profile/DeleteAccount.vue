<template>
  <div>
    <div class="row align-items-center">
      <label class="col-md-3 col-form-label fw-semibold">
        {{ $t('common.Delete Account') }}
      </label>
      <div class="col-md-9">
        <button
          type="button"
          class="btn btn-link text-danger p-0 text-decoration-none fw-semibold"
          @click="showConfirmation"
        >
          <i class="bi bi-trash3 me-1"></i>
          {{ $t('common.Delete My Account') }}
        </button>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div
    class="modal fade"
    tabindex="-1"
    aria-hidden="true"
    ref="confirmModalElement"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow">
        
        <!-- Close Button -->
        <button 
          type="button" 
          class="btn-close position-absolute top-0 end-0 m-3" 
          data-bs-dismiss="modal"
          aria-label="Close"
          @click="handleModalClose"
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

          <h4 class="fw-bold mb-4">{{ $t('common.Delete Account') }}</h4>
          
          <p class="text-secondary mb-4">
            {{ $t('common.To delete your account, we will send you an OTP.') }}
          </p>
          
          <p class="text-secondary mb-4">
            {{ $t('common.Click Send to confirm') }}
          </p>

          <!-- Buttons -->
          <div class="d-flex gap-3 justify-content-center">
            <button 
              type="button" 
              class="btn btn-success px-4 py-2 fw-semibold"
              @click="confirmDelete"
              :disabled="isLoading"
              style="min-width: 120px;"
            >
              <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
              {{ $t('common.Send OTP') }}
            </button>
            <button 
              type="button" 
              class="btn btn-danger px-4 py-2 fw-semibold"
              data-bs-dismiss="modal"
              @click="handleModalClose"
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
import { ref, onMounted } from 'vue'
import { Modal } from 'bootstrap'
import { useI18n } from 'vue-i18n'
import { useProfileStore } from '../../stores/profile.js'
import FormErrorBox from '@/modules/core/components/FormErrorBox.vue'

const { t } = useI18n()
const profileStore = useProfileStore()

const props = defineProps({
  userId: {
    type: [String, Number],
    required: true
  },
  mobile: {
    type: String,
    required: true
  },
  countryCode: {
    type: String,
    default: 'IN'
  }
})

const emit = defineEmits(['show-otp'])

const confirmModalElement = ref(null)
const confirmModalInstance = ref(null)
const isLoading = ref(false)
const errorMessages = ref([])

onMounted(() => {
  confirmModalInstance.value = new Modal(confirmModalElement.value)
  
  confirmModalElement.value.addEventListener('hidden.bs.modal', () => {
    clearErrors()
  })
})

const showConfirmation = () => {
  clearErrors()
  if (confirmModalInstance.value) {
    confirmModalInstance.value.show()
  }
}

const clearErrors = () => {
  errorMessages.value = []
}

const handleModalClose = () => {
  clearErrors()
}

const confirmDelete = async () => {
  clearErrors()
  isLoading.value = true
  
  const result = await profileStore.sendDeleteAccountOTP(
    props.mobile,
    props.countryCode
  )
  
  isLoading.value = false
  
  if (result.success) {
    if (confirmModalInstance.value) {
      confirmModalInstance.value.hide()
    }
    
    clearErrors()
    emit('show-otp')
  } else {
    handleErrors(result)
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
    errorMessages.value = ['An error occurred. Please try again.']
  }
}
</script>

<style scoped>
.btn-link {
  font-size: 0.9375rem;
}

.btn-link:hover {
  text-decoration: underline !important;
}

.modal-content {
  border-radius: 1rem !important;
}

.btn {
  border-radius: 8px;
  font-weight: 500;
}

@media (max-width: 768px) {
  .col-md-3,
  .col-md-9 {
    flex: 0 0 100%;
    max-width: 100%;
  }
  
  .col-form-label {
    margin-bottom: 0.5rem;
  }
  
  .d-flex.gap-3 {
    flex-direction: column;
  }
  
  .d-flex.gap-3 button {
    width: 100%;
  }
}
</style>
