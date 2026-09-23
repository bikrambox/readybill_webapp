<script setup>
import { ref, reactive, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import CountryCodeSelect from '@/modules/Core/components/CountryCodeSelect.vue'
import ImagePreview from './ImagePreview.vue'
import FormErrorBox from '@/modules/Core/components/FormErrorBox.vue'

const { t } = useI18n()

const props = defineProps({
  userData: {
    type: Object,
    required: true
  },
  isLoading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'change-mobile'])

const formData = reactive({
  user_id: '',
  entity_id: '',
  name: '',
  business_name: '',
  email: '',
  mobile: '',
  address: '',
  shop_type: '',
  gstin: '',
  logo: '',
  logo_url: '',
  api_key: '',
  isAdmin: 0,
  countryCode: '+91',
  staffPhoto: '',
  isLogoDelete: 0
})

// ADD THIS: Computed property to check if field should be disabled
const isFieldDisabled = computed(() => {
  return formData.isAdmin === 0
})

const logoFile = ref(null)
const errors = reactive({})
const errorMessages = ref([])
const selectedCountry = ref(null)

// Shop types with label and value
const shopTypes = [
  { label: 'Grocery', value: 'grocery' },
  { label: 'Pharmacy', value: 'pharmacy' }
]

// Computed property for GSTIN with getter/setter to handle "NA" value
const gstinDisplay = computed({
  get() {
    return formData.gstin === 'NA' ? '' : formData.gstin
  },
  set(value) {
    formData.gstin = value.trim() === '' ? 'NA' : value.trim().toUpperCase()
  }
})

// Updated validation rules
const validationRules = {
  name: [
    { required: true, message: t('common.Name is required') },
    { min: 2, message: t('common.Name must be at least 2 characters') },
    { max: 100, message: t('common.Name cannot exceed 100 characters') }
  ],
  address: [
    { required: true, message: t('common.Address is required') },
    { min: 5, message: t('common.Address must be at least 5 characters') },
    { max: 500, message: t('common.Address cannot exceed 500 characters') }
  ],
  shop_type: [
    { required: true, message: t('common.Shop type is required') }
  ],
  email: [
    { 
      pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, 
      message: 'Invalid email format',
      optional: true
    }
  ],
  gstin: [
    { 
      pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/,
      message: 'Invalid GSTIN format (e.g., 22AAAAA0000A1Z5)',
      optional: true
    }
  ]
}

// Sync formData with props.userData
watch(
  () => props.userData,
  (newData) => {
    Object.assign(formData, {
      ...newData,
      // Normalize shop_type to lowercase
      shop_type: newData.shop_type ? newData.shop_type.toLowerCase() : ''
    })
  },
  { deep: true, immediate: true }
)

// Validate single field
const validateField = (fieldName) => {
  const rules = validationRules[fieldName]
  if (!rules) return true

  delete errors[fieldName]

  let value = formData[fieldName]
  
  if (fieldName === 'gstin') {
    value = gstinDisplay.value
  }

  for (const rule of rules) {
    if (rule.optional && (!value || value.trim() === '')) {
      continue
    }

    if (rule.required && (!value || value.trim() === '')) {
      errors[fieldName] = rule.message
      return false
    }

    if (rule.min && value && value.length < rule.min) {
      errors[fieldName] = rule.message
      return false
    }

    if (rule.max && value && value.length > rule.max) {
      errors[fieldName] = rule.message
      return false
    }

    if (rule.pattern && value && value.trim() !== '' && !rule.pattern.test(value)) {
      errors[fieldName] = rule.message
      return false
    }
  }

  return true
}

// Validate all fields
const validateForm = () => {
  clearErrors()
  let isValid = true

  const requiredFields = ['name', 'address', 'shop_type']
  
  requiredFields.forEach(field => {
    if (!validateField(field)) {
      isValid = false
    }
  })

  if (formData.email && formData.email.trim() !== '') {
    if (!validateField('email')) {
      isValid = false
    }
  }

  if (gstinDisplay.value && gstinDisplay.value.trim() !== '') {
    if (!validateField('gstin')) {
      isValid = false
    }
  }

  if (logoFile.value) {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']
    const maxSize = 5 * 1024 * 1024 // 5MB

    if (!validTypes.includes(logoFile.value.type)) {
      errors.logo = t('common.Logo must be a valid image (JPEG, PNG, GIF)')
      isValid = false
    }

    if (logoFile.value.size > maxSize) {
      errors.logo = t('common.Logo size must be less than 5MB')
      isValid = false
    }
  }

  if (!isValid) {
    errorMessages.value = Object.values(errors)
  }

  return isValid
}

const clearErrors = () => {
  Object.keys(errors).forEach(key => delete errors[key])
  errorMessages.value = []
}

const handleLogoSelected = (file) => {
  logoFile.value = file
  formData.isLogoDelete = 0
  delete errors.logo
  
  const index = errorMessages.value.findIndex(msg => msg.includes('Logo'))
  if (index > -1) {
    errorMessages.value.splice(index, 1)
  }
}

const handleLogoDelete = () => {
  formData.isLogoDelete = 1
  formData.logo_url = ''
  logoFile.value = null
  delete errors.logo
  
  const index = errorMessages.value.findIndex(msg => msg.includes('Logo'))
  if (index > -1) {
    errorMessages.value.splice(index, 1)
  }
}

const submitForm = async () => {
  clearErrors()
  
  if (!validateForm()) {
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }

  const data = new FormData()

  // Append all non-file fields
  const fieldsToAppend = [
    'user_id', 'entity_id', 'name', 'business_name', 
    'email', 'mobile', 'address', 'shop_type', 
    'api_key', 'isAdmin', 'isLogoDelete'
  ]

  fieldsToAppend.forEach(key => {
    const value = formData[key]
    data.append(key, value !== null && value !== undefined ? value : '')
  })

  // Append country code from selected country
  data.append('country_code', selectedCountry.value?.code || formData.countryCode)
  
  // Append GSTIN
  data.append('gstin', formData.gstin)

  // Only append logo file if it exists and is a File object
  if (logoFile.value && logoFile.value instanceof File) {
    data.append('logo', logoFile.value, logoFile.value.name)
    console.log('Logo file appended:', logoFile.value.name, logoFile.value.type, logoFile.value.size)
  } else {
    console.log('No logo file to upload')
  }

  // Debug: Log FormData contents
  console.log('FormData contents:')
  for (let pair of data.entries()) {
    if (pair[1] instanceof File) {
      console.log(pair[0], 'File:', pair[1].name, pair[1].type, pair[1].size)
    } else {
      console.log(pair[0], pair[1])
    }
  }

  emit('submit', data)
}

// Expose method to handle backend errors
const handleBackendErrors = (backendErrors) => {
  clearErrors()
  
  if (typeof backendErrors === 'object' && backendErrors !== null) {
    Object.keys(backendErrors).forEach(field => {
      const fieldErrors = backendErrors[field]
      
      if (Array.isArray(fieldErrors)) {
        errors[field] = fieldErrors[0]
        errorMessages.value.push(...fieldErrors)
      } else {
        errors[field] = fieldErrors
        errorMessages.value.push(fieldErrors)
      }
    })
  }
  
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

defineExpose({
  handleBackendErrors
})
</script>

<<template>
  <div>
    <!-- Error Box -->
    <FormErrorBox 
      :messages="errorMessages" 
      @clear="clearErrors" 
      class="mb-4"
    />

    <form @submit.prevent="submitForm">
      <!-- Hidden Fields -->
      <input type="hidden" v-model="formData.user_id" />
      <input type="hidden" v-model="formData.isAdmin" />

      <!-- Entity ID (Readonly) -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold text-muted">
          {{ $t('profile_page.Entity ID') }}
        </label>
        <div class="col-md-9">
          <input
            type="text"
            class="form-control bg-light"
            v-model="formData.entity_id"
            readonly
          />
        </div>
      </div>

      <!-- Name - REQUIRED -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('common.Name') }} <span class="text-danger">*</span>
        </label>
        <div class="col-md-9">
          <input
            type="text"
            class="form-control"
            :class="{ 'border-danger': errors.name , 'bg-light': isFieldDisabled }"
            v-model="formData.name"
            :disabled="isFieldDisabled"
            @blur="validateField('name')"
            autofocus
          />
          <span v-if="errors.name" class="text-danger small mt-1 d-block">{{ errors.name }}</span>
        </div>
      </div>

      <!-- Business Name -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('profile_page.Business Name') }}
        </label>
        <div class="col-md-9">
          <input
            type="text"
            class="form-control"
            :class="{ 'border-danger': errors.business_name, 'bg-light': isFieldDisabled }"
            v-model="formData.business_name"
              :disabled="isFieldDisabled"
          />
          <span v-if="errors.business_name" class="text-danger small mt-1 d-block">{{ errors.business_name }}</span>
        </div>
      </div>

      <!-- Email - Optional -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('common.Email') }}
        </label>
        <div class="col-md-9">
          <input
            type="email"
            class="form-control"
            :class="{ 'border-danger': errors.email, 'bg-light': isFieldDisabled }"
            v-model="formData.email"
              :disabled="isFieldDisabled"
            @blur="validateField('email')"
            placeholder="example@email.com"
          />
          <span v-if="errors.email" class="text-danger small mt-1 d-block">{{ errors.email }}</span>
        </div>
      </div>

      <!-- Mobile Number -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('common.Mobile Number') }}
        </label>
        <div class="col-md-9">
          <div class="input-group">
            <CountryCodeSelect
              v-model="formData.countryCode"
              @change="selectedCountry = $event"
              :disabled="true"
            />
            <input
              type="text"
              class="form-control bg-light"
              :class="{ 'border-danger': errors.mobile }"
              v-model="formData.mobile"
              :placeholder="$t('profile_page.Enter your mobile number')"
              readonly
            />
          </div>
          <span v-if="errors.mobile" class="text-danger small mt-1 d-block">{{ errors.mobile }}</span>
          <button v-if="formData.isAdmin == 1"
            type="button"
            class="btn btn-link text-primary p-0 mt-2 text-decoration-none"
            @click="$emit('change-mobile')"
          >
            <i class="bi bi-pencil-square me-1"></i>
            {{ $t('profile_page.Change Mobile Number') }}
          </button>
        </div>
      </div>

      <!-- Address - REQUIRED -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('common.Address') }} <span class="text-danger">*</span>
        </label>
        <div class="col-md-9">
          <textarea
            class="form-control"
            :class="{ 'border-danger': errors.address, 'bg-light': isFieldDisabled }"
            v-model="formData.address"
              :disabled="isFieldDisabled"
            @blur="validateField('address')"
            rows="3"
          ></textarea>
          <span v-if="errors.address" class="text-danger small mt-1 d-block">{{ errors.address }}</span>
        </div>
      </div>

      <!-- Shop Type - REQUIRED -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('common.Shop Type') }} <span class="text-danger">*</span>
        </label>
        <div class="col-md-9">
          <select
            class="form-select bg-light"
            :class="{ 'border-danger': errors.shop_type }"
            v-model="formData.shop_type"
            @change="validateField('shop_type')"
            disabled
          >
            <option value="" disabled>{{ $t('common.Choose Type') }}</option>
            <option 
              v-for="type in shopTypes" 
              :key="type.value" 
              :value="type.value"
            >
              {{ type.label }}
            </option>
          </select>
          <span v-if="errors.shop_type" class="text-danger small mt-1 d-block">{{ errors.shop_type }}</span>
        </div>
      </div>

      <!-- GSTIN - Optional with NA handling -->
      <div class="row mb-3">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('profile_page.GSTIN Number') }}
        </label>
        <div class="col-md-9">
          <input
            type="text"
            class="form-control"
            :class="{ 'border-danger': errors.gstin, 'bg-light': isFieldDisabled }"
            v-model="gstinDisplay"
            :disabled="isFieldDisabled"
            @input="gstinDisplay = $event.target.value.toUpperCase()"
            @blur="validateField('gstin')"
            maxlength="15"
            placeholder="22AAAAA0000A1Z5"
          />
          <span v-if="errors.gstin" class="text-danger small mt-1 d-block">{{ errors.gstin }}</span>
          <!-- <small class="text-muted d-block mt-1">Optional - Format: 22AAAAA0000A1Z5</small> -->
        </div>
      </div>

      <!-- Logo Upload -->
      <div v-if="formData.isAdmin" class="row mb-4">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('profile_page.Logo') }}
        </label>
        <div class="col-md-9">
          <ImagePreview
            :preview="formData.logo_url"
            :error="errors.logo"
            @file-selected="handleLogoSelected"
            @delete="handleLogoDelete"
          />
        </div>
      </div>

      <!-- Staff Photo (Non-admin) -->
      <div v-else class="row mb-4">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('common.Photo') }}
          
        </label>
        <div class="col-md-9">
          <img
            :src="formData.staffPhoto || '/assets/img/user.jpg'"
            class="img-thumbnail"
            alt="Staff Photo"
            style="max-width: 200px; max-height: 100px; object-fit: cover;"
          />
        </div>
      </div>

      <!-- API Key (Admin only) -->
      <div v-if="formData.isAdmin" class="row mb-4">
        <label class="col-md-3 col-form-label fw-semibold">
          {{ $t('profile_page.API Key') }}
        </label>
        <div class="col-md-9">
          <textarea
            class="form-control bg-light font-monospace"
            v-model="formData.api_key"
            rows="3"
            readonly
            style="font-size: 0.875rem;"
          ></textarea>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="row" v-if="formData.isAdmin == 1">
        <div class="col-md-9 offset-md-3">
          <button type="submit" class="btn btn-primary px-4" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-check-lg me-2"></i>
            {{ $t('common.Update Changes') }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>


<style scoped>
.form-control,
.form-select {
  border-radius: 6px;
  border: 1px solid #dee2e6;
  padding: 0.625rem 0.75rem;
  font-size: 0.9375rem;
}

.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.bg-light {
  background-color: #f8f9fa !important;
}

.col-form-label {
  padding-top: calc(0.625rem + 1px);
  padding-bottom: calc(0.625rem + 1px);
  font-size: 0.9375rem;
}

.input-group {
  display: flex;
}

.text-danger.small {
  font-size: 0.875rem;
}

.btn-link {
  font-size: 0.875rem;
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
  
  .offset-md-3 {
    margin-left: 0;
  }
}
</style>
