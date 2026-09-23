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
          <h5 class="modal-title" id="sendSmsModalLabel">Send Invoice</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <div class="input-group">
              <CountryCodeSelect
                v-model="formData.countryCode"
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
            Close
          </button>
          <button
            type="button"
            class="btn btn-primary btn-action"
            @click="handleSend"
            :disabled="loading"
          >
            <span v-if="loading">
              <i class="bi bi-arrow-repeat spin"></i>
              Sending...
            </span>
            <span v-else> Send </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { Modal } from "bootstrap";
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";

const emit = defineEmits(["send", "close"]);

const modalElement = ref(null);
const modalInstance = ref(null);
const loading = ref(false);
const selectedCountry = ref(null);

const formData = reactive({
  mobile: "",
  // countryCode: "+91",
  country_code: selectedCountry.value?.code,
});

const errors = reactive({
  mobile: "",
});

onMounted(() => {
  modalInstance.value = new Modal(modalElement.value);
});

const show = () => {
  if (modalInstance.value) {
    modalInstance.value.show();
  }
};

const hide = () => {
  if (modalInstance.value) {
    modalInstance.value.hide();
  }
};

const validateForm = () => {
  errors.mobile = "";

  if (!formData.mobile || formData.mobile.trim() === "") {
    errors.mobile = "Mobile number is required";
    return false;
  }

  // Basic mobile number validation (10 digits for Indian numbers)
  const mobileRegex = /^[0-9]{10}$/;
  if (!mobileRegex.test(formData.mobile.trim())) {
    errors.mobile = "Please enter a valid 10-digit mobile number";
    return false;
  }

  return true;
};

const handleSend = () => {
  if (!validateForm()) {
    return;
  }

  emit("send", {
    mobile: formData.mobile,
    // countryCode: formData.countryCode,
      country_code: selectedCountry.value?.code,
  });
};

const handleClose = () => {
  formData.mobile = "";
  formData.countryCode = "IN";
  errors.mobile = "";
  emit("close");
};

const setLoading = (value) => {
  loading.value = value;
};

defineExpose({
  show,
  hide,
  setLoading,
});
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
  /* gap: 12px; */
}
</style>