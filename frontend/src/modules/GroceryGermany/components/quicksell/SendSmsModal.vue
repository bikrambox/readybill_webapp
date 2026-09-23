<template>
  <!-- Modal -->
  <div
    class="modal fade"
    id="sendSmsModal"
    tabindex="-1"
    aria-labelledby="sendSmsModalLabel"
    aria-hidden="true"
    ref="modalElement"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="sendSmsModalLabel">
            {{ $t('common.Send Invoice') }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">{{ $t('common.Contact Number') }}</label>
            <div class="input-group">
              <CountryCodeSelect
                v-model="formData.dialCode"
                @change="selectedCountry = $event"
                :disabled="true"
              />
              <input
                type="text"
                class="form-control"
                :class="{ 'border-danger': errors.mobile }"
                v-model="formData.mobile"
                placeholder="Enter mobile number"
              />
            </div>
            <div
              v-if="errors.mobile"
              class="text-danger mt-1"
              style="font-size: 12px"
            >
              {{ errors.mobile }}
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary btn-action"
            data-bs-dismiss="modal"
            @click="handleClose"
          >
            {{ $t('common.Close') }}
          </button>
          <button
            type="button"
            class="btn btn-primary btn-action"
            @click="handleSend"
            :disabled="loading"
          >
            <span v-if="loading">
              <i class="bi bi-arrow-repeat spin"></i>
              {{ $t('common.Sending') }}...
            </span>
            <span v-else>{{ $t('common.Send') }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, getCurrentInstance } from 'vue'
import { Modal } from 'bootstrap'
import CountryCodeSelect from '@/modules/Core/components/CountryCodeSelect.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const emit = defineEmits(['send', 'close'])

const modalElement = ref(null)
const modalInstance = ref(null)
const loading = ref(false)
const selectedCountry = ref(null)

const { appContext } = getCurrentInstance()
const dialCode =
  appContext.config.globalProperties.$dialCode.toUpperCase()

const formData = reactive({
  mobile: '',
  // v-model for CountryCodeSelect (e.g. "+91")
  dialCode: dialCode,
})

const errors = reactive({
  mobile: '',
})

onMounted(() => {
  modalInstance.value = new Modal(modalElement.value)
})

const show = () => {
  if (modalInstance.value) {
    modalInstance.value.show()
  }

  // Set dropdown from global default each time modal opens
  formData.dialCode = dialCode

  // If CountryCodeSelect emits/accepts an object, adapt this:
  // e.g. { dialCode: '+91', code: 'IN', name: 'India' }
  selectedCountry.value = { dialCode }
}

const hide = () => {
  if (modalInstance.value) {
    modalInstance.value.hide()
  }
}

const validateForm = () => {
  errors.mobile = ''

  if (!formData.mobile || formData.mobile.trim() === '') {
    errors.mobile = t('common.Mobile number is required')
    return false
  }

  const mobileRegex = /^[0-9]{10}$/
  if (!mobileRegex.test(formData.mobile.trim())) {
    errors.mobile = t('common.Please enter a valid 10-digit mobile number')
    return false
  }

  return true
}

const handleSend = () => {
  if (!validateForm()) {
    return
  }

  emit('send', {
    mobile: formData.mobile,
    // send dial code to backend
    dial_code: formData.dialCode,
  })
}

const handleClose = () => {
  formData.mobile = ''
  formData.dialCode = dialCode // reset to default from global
  errors.mobile = ''
  emit('close')
}

const setLoading = value => {
  loading.value = value
}

defineExpose({
  show,
  hide,
  setLoading,
})
</script>

<style scoped>
.modal-title {
  width: 100%;
  text-align: center;
  font-weight: 600;
}

.modal-header {
  border-bottom: 2px solid #e9ecef;
}

.modal-footer {
  border-top: 2px solid #e9ecef;
}

.input-group {
  display: flex;
  gap: 10px;
}

.form-label {
  font-weight: 500;
  margin-bottom: 8px;
  color: #333;
}

.spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.btn-action {
  min-width: 120px;
  padding: 10px 24px;
  font-weight: 500;
  font-size: 14px;
  border-radius: 6px;
  transition: all 0.2s;
}

.modal-footer {
  border-top: 2px solid #e9ecef;
  padding: 16px 24px;
  display: flex;
  justify-content: center;
}
</style>
