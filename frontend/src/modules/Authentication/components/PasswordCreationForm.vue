<template>
  <div class="password-form">
    <div class="form-wrapper">
      <div class="form-header">
        <h1 class="form-title">{{ $t('register_page.Create Password') }}</h1>
        <p class="form-description">{{ $t('common.Create a strong password to secure your account') }}</p>
      </div>
      
      <form @submit.prevent="handleSubmit" class="creation-form">
        <div class="input-group">
          <div class="input-wrapper">
            <input 
              :type="showPassword ? 'text' : 'password'"
              class="form-input" 
              placeholder="Enter your password"
              v-model="registerStore.registrationData.password"
              @input="validatePassword"
              required
              autocomplete="new-password"
            />
            <button 
              type="button" 
              class="toggle-icon" 
              @click="showPassword = !showPassword"
              tabindex="-1"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
        </div>
        
        <!-- <div class="password-hints">
          <div class="hint-item" :class="{ 'hint-valid': hasMinLength }">
            <span class="hint-check">{{ hasMinLength ? '✓' : '○' }}</span>
            <span>At least 8 characters</span>
          </div>
          <div class="hint-item" :class="{ 'hint-valid': hasUpperCase }">
            <span class="hint-check">{{ hasUpperCase ? '✓' : '○' }}</span>
            <span>One uppercase letter</span>
          </div>
          <div class="hint-item" :class="{ 'hint-valid': hasLowerCase }">
            <span class="hint-check">{{ hasLowerCase ? '✓' : '○' }}</span>
            <span>One lowercase letter</span>
          </div>
          <div class="hint-item" :class="{ 'hint-valid': hasNumber }">
            <span class="hint-check">{{ hasNumber ? '✓' : '○' }}</span>
            <span>One number</span>
          </div>
        </div> -->
        
        <div class="input-group">
          <div class="input-wrapper">
            <input 
              :type="showConfirmPassword ? 'text' : 'password'"
              class="form-input" 
              placeholder="Confirm your password"
              v-model="registerStore.registrationData.confirmPassword"
              @input="validatePassword"
              required
              autocomplete="new-password"
            />
            <button 
              type="button" 
              class="toggle-icon" 
              @click="showConfirmPassword = !showConfirmPassword"
              tabindex="-1"
            >
              {{ showConfirmPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
        </div>
        
        <div v-if="passwordError" class="error-alert">
          {{ passwordError }}
        </div>
        
        <FormErrorBox 
          v-if="registerStore.errorMessages.length"
          :messages="registerStore.errorMessages"
          @clear="registerStore.clearErrors"
        />
        
        <button 
          type="submit" 
          class="submit-button"
          :disabled="registerStore.isLoading || !isPasswordValid"
        >
          {{ registerStore.isLoading ? $t('common.Processing')+'...' : $t('common.Continue') }}
        </button>
      </form>
    </div>
    
    <div class="form-links">
      <!-- <button 
        type="button" 
        class="link-back" 
        @click="registerStore.previousStep()"
      >
        ← Back
      </button> -->
    </div>
    
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRegisterStore } from '../stores/registerStore'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

const registerStore = useRegisterStore()
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const passwordError = ref('')

const hasMinLength = computed(() => registerStore.registrationData.password.length >= 8)
const hasUpperCase = computed(() => /[A-Z]/.test(registerStore.registrationData.password))
const hasLowerCase = computed(() => /[a-z]/.test(registerStore.registrationData.password))
const hasNumber = computed(() => /[0-9]/.test(registerStore.registrationData.password))

const isPasswordStrong = computed(() => {
  // return hasMinLength.value && hasUpperCase.value && hasLowerCase.value && hasNumber.value
  return hasMinLength.value;
})

const isPasswordValid = computed(() => {
  return registerStore.registrationData.password.length >= 8 && 
         registerStore.registrationData.password === registerStore.registrationData.confirmPassword
})

const validatePassword = () => {
  passwordError.value = ''
  
  if (registerStore.registrationData.confirmPassword && 
      registerStore.registrationData.password !== registerStore.registrationData.confirmPassword) {
    passwordError.value = t('common.Passwords do not match')
  }
}

const handleSubmit = () => {
  passwordError.value = ''
  
  if (!isPasswordStrong.value) {
    passwordError.value = t('common.Please meet all password requirements')
    return
  }
  
  if (registerStore.registrationData.password !== registerStore.registrationData.confirmPassword) {
    passwordError.value = t('common.Passwords do not match')
    return
  }
  
  registerStore.validatePassword()
}
</script>

<style scoped>
/* Same styles as before */
.password-form {
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
  line-height: 1.5;
  text-align: left;
}

.creation-form {
  margin-bottom: 0;
}

.input-group {
  margin-bottom: 18px;
}

.input-wrapper {
  position: relative;
  width: 100%;
}

.form-input {
  width: 100%;
  height: 54px;
  padding: 0 52px 0 18px;
  font-size: 16px;
  color: #1a1a1a;
  background-color: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  transition: all 0.2s ease;
  font-family: inherit;
}

.form-input::placeholder {
  color: #adb5bd;
}

.form-input:focus {
  outline: none;
  border-color: #0d6efd;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.toggle-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 20px;
  padding: 8px;
  line-height: 1;
  color: #6c757d;
}

.password-hints {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 18px;
  padding: 16px;
  background-color: #f8f9fa;
  border-radius: 10px;
}

.hint-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6c757d;
}

.hint-item.hint-valid {
  color: #198754;
  font-weight: 600;
}

.hint-check {
  font-size: 13px;
  font-weight: bold;
}

.error-alert {
  padding: 14px 18px;
  margin-bottom: 20px;
  background-color: #f8d7da;
  color: #842029;
  border: 1px solid #f5c2c7;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
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
  
  .form-input {
    height: 50px;
    padding: 0 48px 0 16px;
    font-size: 16px;
    border-radius: 8px;
  }
  
  .password-hints {
    grid-template-columns: 1fr;
    gap: 8px;
    padding: 14px;
    border-radius: 8px;
  }
  
  .hint-item {
    font-size: 13px;
  }
  
  .submit-button {
    height: 50px;
    font-size: 16px;
    border-radius: 8px;
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
    font-size: 14px;
  }
  
  .form-input {
    height: 48px;
    font-size: 15px;
  }
  
  .submit-button {
    height: 48px;
    font-size: 15px;
  }
}
</style>
