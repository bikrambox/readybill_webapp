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

    <h4 class="fw-bold text-center mb-4">{{ $t('common.Set New Password') }}</h4>
    
    <form @submit.prevent="handleSubmit">
      <!-- New Password -->
      <div class="mb-3">
        <label class="form-label fw-semibold">
          {{ $t('common.New Password') }} <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <input
            :type="showPassword ? 'text' : 'password'"
            class="form-control"
            :class="{ 'border-danger': errors.password }"
            v-model="formData.password"
            @blur="validateField('password')"
            @input="errors.password = ''"
            :placeholder="$t('common.Enter new password')"
            autocomplete="new-password"
          />
          <button
            class="btn btn-outline-secondary"
            type="button"
            @click="showPassword = !showPassword"
            tabindex="-1"
          >
            <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
          </button>
        </div>
        <span v-if="errors.password" class="text-danger small d-block mt-1">{{ errors.password }}</span>
        <small class="text-muted d-block mt-1">
          {{ $t('common.Password must be at least 8 characters') }}
        </small>
      </div>

      <!-- Confirm Password -->
      <div class="mb-4">
        <label class="form-label fw-semibold">
          {{ $t('common.Confirm Password') }} <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <input
            :type="showConfirmPassword ? 'text' : 'password'"
            class="form-control"
            :class="{ 'border-danger': errors.password_confirmation }"
            v-model="formData.password_confirmation"
            @blur="validateField('password_confirmation')"
            @input="errors.password_confirmation = ''"
            :placeholder="$t('Confirm new password')"
            autocomplete="new-password"
          />
          <button
            class="btn btn-outline-secondary"
            type="button"
            @click="showConfirmPassword = !showConfirmPassword"
            tabindex="-1"
          >
            <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
          </button>
        </div>
        <span v-if="errors.password_confirmation" class="text-danger small d-block mt-1">{{ errors.password_confirmation }}</span>
      </div>

      <!-- Submit Button -->
      <div class="d-grid">
        <button
          type="submit"
          class="btn btn-primary py-2 fw-semibold"
          :disabled="changePasswordStore.isLoading"
        >
          <span v-if="changePasswordStore.isLoading" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="bi bi-check-lg me-2"></i>
          {{ $t('common.Update Password') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useChangePasswordStore } from '@/modules/Authentication/stores/changePassword'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

const { t } = useI18n()
const changePasswordStore = useChangePasswordStore()

const props = defineProps({
  mobile: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['success'])

const formData = reactive({
  password: '',
  password_confirmation: ''
})

const errors = reactive({
  password: '',
  password_confirmation: ''
})

const errorMessages = ref([])
const successMessage = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const clearErrors = () => {
  errorMessages.value = []
  errors.password = ''
  errors.password_confirmation = ''
  successMessage.value = ''
}

const validateField = (field) => {
  errors[field] = ''
  
  if (field === 'password') {
    if (!formData.password) {
      errors.password = t('common.Password is required')
      return false
    }
    
    if (formData.password.length < 8) {
      errors.password = t('common.Password must be at least 8 characters')
      return false
    }
  }
  
  if (field === 'password_confirmation') {
    if (!formData.password_confirmation) {
      errors.password_confirmation = t('common.Confirm password is required')
      return false
    }
    
    if (formData.password !== formData.password_confirmation) {
      errors.password_confirmation = t('common.Passwords do not match')
      return false
    }
  }
  
  return true
}

const validateForm = () => {
  let isValid = true
  
  if (!validateField('password')) isValid = false
  if (!validateField('password_confirmation')) isValid = false
  
  return isValid
}

const handleSubmit = async () => {
  clearErrors()
  
  if (!validateForm()) {
    return
  }
  
  try {
    const result = await changePasswordStore.updatePassword(
      props.mobile,
      formData.password,
      formData.password_confirmation
    )
    
    if (result.success) {
      successMessage.value = result.message || t('common.Password updated successfully')
      
      // Reset form
      formData.password = ''
      formData.password_confirmation = ''
      
      // Emit success after a short delay
      setTimeout(() => {
        emit('success', result)
      }, 1000)
    } else {
      handleErrors(result)
    }
  } catch (error) {
    console.error('Password update error:', error)
    errorMessages.value = [`${t('common.An unexpected error occurred. Please try again.')}`]
  }
}

const handleErrors = (result) => {
  errorMessages.value = []
  
  if (result.errors) {
    Object.keys(result.errors).forEach(field => {
      if (Array.isArray(result.errors[field])) {
        errors[field] = result.errors[field][0]
        errorMessages.value.push(...result.errors[field])
      } else {
        errors[field] = result.errors[field]
        errorMessages.value.push(result.errors[field])
      }
    })
  } else if (result.message) {
    errorMessages.value = [result.message]
  } else {
    errorMessages.value = [`${t('common.An error occurred')}. ${t('common.Please try again')}.`]
  }
}
</script>

<style scoped>
.form-control,
.btn {
  border-radius: 6px;
}

.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.form-control.border-danger {
  border-color: #dc3545 !important;
}

.form-control.border-danger:focus {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
}

.input-group .btn {
  border-radius: 0 6px 6px 0;
}

.input-group .btn-outline-secondary {
  border-color: #dee2e6;
}

.input-group .btn-outline-secondary:hover {
  background-color: #e9ecef;
  border-color: #dee2e6;
  color: #495057;
}

.text-danger.small {
  font-size: 0.875rem;
}

.btn-primary {
  font-size: 1rem;
}

.btn-primary:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

@media (max-width: 576px) {
  h4 {
    font-size: 1.25rem;
  }
  
  .form-label {
    font-size: 0.9rem;
  }
  
  .btn-primary {
    font-size: 0.95rem;
  }
}
</style>
