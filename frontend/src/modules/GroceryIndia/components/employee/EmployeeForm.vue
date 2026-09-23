<template>
  <form @submit.prevent="handleSubmit" class="inventory-form">
    <!-- Error Box -->
    <FormErrorBox :messages="errorMessages" @clear="clearErrors" class="mb-3" />

    <!-- Success Message -->
    <div v-if="successMessage" class="alert alert-success alert-dismissible fade show">
      {{ successMessage }}
      <button type="button" class="btn-close" @click="successMessage = ''"></button>
    </div>

    <div class="row g-3">
      <!-- Employee Name -->
      <div class="col-12 col-md-6">
        <label class="form-label">{{ $t("common.Employee Name") }}</label>
        <input
          type="text"
          class="form-control"
          :class="{ 'border-danger': errors.name }"
          placeholder="Employee Name"
          v-model="formData.name"
        />
      </div>

      <!-- Mobile Number -->
      <div class="col-12 col-md-6">
        <label class="form-label">{{ $t("common.Contact Number") }}</label>
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
            :placeholder="$t('profile_page.Enter your mobile number')"
          />
        </div>
      </div>

      <!-- Password -->
      <div class="col-12 col-md-6">
        <label class="form-label">{{ $t("common.Password") }}</label>
        <input
          type="password"
          class="form-control"
          :class="{ 'border-danger': errors.password }"
          placeholder="Password"
          v-model="formData.password"
        />
      </div>

      <!-- Confirm Password -->
      <div class="col-12 col-md-6">
        <label class="form-label">{{ $t("common.Confirm Password") }}</label>
        <input
          type="password"
          class="form-control"
          :class="{ 'border-danger': errors.passwordConfirmation }"
          placeholder="Confirm Password"
          v-model="formData.passwordConfirmation"
          @input="validatePasswordMatch"
        />
      </div>

      <!-- Address -->
      <div class="col-12 col-md-6">
        <label class="form-label">Address</label>
        <textarea
          class="form-control"
          :class="{ 'border-danger': errors.address }"
          v-model="formData.address"
          rows="3"
        ></textarea>
      </div>

      <!-- Photo Upload -->
      <div class="col-12 col-md-6">
        <label class="form-label">
          {{ $t("common.Photo") }} ({{ $t("common.Optional") }})
        </label>
        <ImagePreview
          :key="imageKey"
          :file="photoFile"
          :preview="formData.photo"
          :error="errors.photo ? errors.photo[0] : ''"
          @update:file="photoFile = $event"
          @update:preview="formData.photo = $event"
          @delete="handleLogoDelete"
          @file-selected="handlePhotoSelected"
        />
      </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions">
      <button type="submit" class="btn btn-primary" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ loading ? "Submitting..." : "Submit" }}
      </button>

      <button
        type="button"
        class="btn btn-outline-secondary"
        @click="handleCancel"
        :disabled="loading"
      >
        {{ $t("common.Cancel") }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useEmployeeStore } from "@/modules/GroceryIndia/stores/employee";
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";
import ImagePreview from "../profile/ImagePreview.vue";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const emit = defineEmits(["submit", "cancel"]);
const employeeStore = useEmployeeStore();

const getInitialFormData = (countryCode = "") => ({
  name: "",
  mobile: "",
  countryCode,
  password: "",
  passwordConfirmation: "",
  address: "",
  photo: "",
  isPhotoDelete: 0,
});

const formData = ref(getInitialFormData());

const selectedCountry = ref(null);
const photoFile = ref(null);
const errors = reactive({});
const errorMessages = ref([]);
const successMessage = ref("");
const imageKey = ref(0);

const loading = computed(() => employeeStore.loading);

// Validation functions
const validateName = () => {
  if (!formData.value.name || formData.value.name.trim() === "") {
    return t("common.Employee name is required");
  }
  if (formData.value.name.trim().length < 2) {
    return t("common.Employee name must be at least 2 characters");
  }
  return null;
};

const validateMobile = () => {
  if (!formData.value.mobile || formData.value.mobile.trim() === "") {
    return t("common.Mobile number is required");
  }

  const mobileRegex = /^[0-9]{10}$/;
  if (!mobileRegex.test(formData.value.mobile.trim())) {
    return t("common.Mobile number must be 10 digits");
  }

  return null;
};

const validatePassword = () => {
  if (!formData.value.password || formData.value.password.trim() === "") {
    return t("common.Password is required");
  }
  if (formData.value.password.length < 8) {
    return t("common.Password must be at least 8 characters");
  }
  return null;
};

const validatePasswordConfirmation = () => {
  if (
    !formData.value.passwordConfirmation ||
    formData.value.passwordConfirmation.trim() === ""
  ) {
    return t("common.Please confirm your password");
  }

  if (formData.value.password !== formData.value.passwordConfirmation) {
    return t("common.Passwords do not match");
  }

  return null;
};

const validateAddress = () => {
  if (!formData.value.address || formData.value.address.trim() === "") {
    return t("common.Address is required");
  }
  if (formData.value.address.trim().length < 5) {
    return t("common.Address must be at least 5 characters");
  }
  return null;
};

const validatePasswordMatch = () => {
  const error = validatePasswordConfirmation();

  if (error) {
    errors.passwordConfirmation = [error];
  } else {
    delete errors.passwordConfirmation;
  }
};

const clearErrors = () => {
  Object.keys(errors).forEach((key) => delete errors[key]);
  errorMessages.value = [];
  successMessage.value = "";
};

const validateForm = () => {
  Object.keys(errors).forEach((key) => delete errors[key]);

  const validationErrors = [];

  const nameError = validateName();
  if (nameError) {
    errors.name = [nameError];
    validationErrors.push(nameError);
  }

  const mobileError = validateMobile();
  if (mobileError) {
    errors.mobile = [mobileError];
    validationErrors.push(mobileError);
  }

  const passwordError = validatePassword();
  if (passwordError) {
    errors.password = [passwordError];
    validationErrors.push(passwordError);
  }

  const passwordConfirmationError = validatePasswordConfirmation();
  if (passwordConfirmationError) {
    errors.passwordConfirmation = [passwordConfirmationError];
    validationErrors.push(passwordConfirmationError);
  }

  const addressError = validateAddress();
  if (addressError) {
    errors.address = [addressError];
    validationErrors.push(addressError);
  }

  if (!selectedCountry.value?.code && !formData.value.countryCode) {
    const countryCodeError = t("common.Country code is required");
    errors.countryCode = [countryCodeError];
    validationErrors.push(countryCodeError);
  }

  return validationErrors;
};

const resetForm = () => {
  const currentCountryCode = formData.value.countryCode;

  clearErrors();
  formData.value = getInitialFormData(currentCountryCode);
  photoFile.value = null;
  imageKey.value++;
};

const handleSubmit = async () => {
  clearErrors();

  const validationErrors = validateForm();
  if (validationErrors.length > 0) {
    errorMessages.value = validationErrors;
    return;
  }

  const payload = {
    name: formData.value.name.trim(),
    mobile: formData.value.mobile.trim(),
    countryCode: selectedCountry.value?.code || formData.value.countryCode,
    password: formData.value.password,
    passwordConfirmation: formData.value.passwordConfirmation,
    address: formData.value.address.trim(),
    photo: photoFile.value,
  };

  const result = await employeeStore.addEmployee(payload);

  if (result.success) {
    emit("submit", result.data);
    successMessage.value = result.message || t("common.New Employee Added successfully");

    resetForm();
  } else {
    if (result.errors) {
      Object.assign(errors, result.errors);

      const messages = [];
      Object.keys(result.errors).forEach((key) => {
        if (Array.isArray(result.errors[key])) {
          messages.push(...result.errors[key]);
        } else {
          messages.push(result.errors[key]);
        }
      });

      errorMessages.value = messages;
    } else if (result.message) {
      errorMessages.value = [result.message];
    }
  }
};

const handlePhotoSelected = (file) => {
  photoFile.value = file;
  formData.value.isPhotoDelete = 0;

  delete errors.photo;

  const index = errorMessages.value.findIndex(
    (msg) => msg.toLowerCase().includes("photo") || msg.toLowerCase().includes("image")
  );

  if (index > -1) {
    errorMessages.value.splice(index, 1);
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    formData.value.photo = e.target.result;
  };
  reader.readAsDataURL(file);
};

const handleLogoDelete = () => {
  formData.value.photo = "";
  photoFile.value = null;
  formData.value.isPhotoDelete = 1;
};

const handleCancel = () => {
  resetForm();
  emit("cancel");
};
</script>

<style scoped>
.inventory-form {
  width: 100%;
}

.form-label {
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}

.form-label-sm {
  font-size: 13px;
  font-weight: 500;
  color: #6c757d;
  margin-bottom: 5px;
}

.form-control,
.form-select {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-control:focus,
.form-select:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 0.2rem rgba(0, 102, 204, 0.15);
}

.border-danger {
  border-color: #dc3545 !important;
}

.text-danger {
  color: #dc3545;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}

.input-group-text {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px 0 0 8px;
  color: #6c757d;
  font-weight: 500;
}

.tax-details {
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 25px;
}

.form-actions .btn {
  padding: 12px 30px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
}

.btn-primary {
  background-color: #0066cc;
  border-color: #0066cc;
}

.btn-primary:hover {
  background-color: #0052a3;
  border-color: #0052a3;
}

.btn-primary:disabled {
  background-color: #6c757d;
  border-color: #6c757d;
  cursor: not-allowed;
}

.btn-outline-secondary {
  color: #6c757d;
  border-color: #dee2e6;
}

.btn-outline-secondary:hover {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  color: #333;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
  border-width: 0.15em;
}

@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
