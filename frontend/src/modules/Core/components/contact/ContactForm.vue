<template>
  <div class="contact-form-wrapper">
    <!-- Show all errors from store using FormErrorBox -->
    <FormErrorBox
      :messages="contactStore.errors"
      @clear="contactStore.clearErrors"
      class="mb-3"
    />

    <!-- Success message -->
    <div
      v-if="contactStore.successMessage"
      class="alert alert-success alert-dismissible fade show mb-3"
      role="alert"
    >
      {{ contactStore.successMessage }}
      <button type="button" class="btn-close" @click="contactStore.clearSuccess"></button>
    </div>

    <form
      @submit.prevent="handleSubmit"
      class="needs-validation"
      novalidate
      ref="formRef"
    >
      <div class="form-group mb-3">
        <label for="full_name" class="form-label">
          {{ $t("common.Full Name") }} <span class="text-danger">*</span>
        </label>
        <input
          type="text"
          id="full_name"
          v-model="formData.fullName"
          @input="validateField('fullName')"
          @blur="validateField('fullName')"
          class="form-control"
          :class="{ 'is-invalid': errors.fullName }"
          required
        />
        <div class="invalid-feedback" v-if="errors.fullName">
          {{ errors.fullName }}
        </div>
      </div>

      <div class="form-group mb-3">
        <label for="contact_no" class="form-label">
          {{ $t("common.Contact Number") }} <span class="text-danger">*</span>
        </label>
        <input
          type="tel"
          id="contact_no"
          v-model="formData.contactNo"
          @input="validateField('contactNo')"
          @blur="validateField('contactNo')"
          class="form-control"
          :class="{ 'is-invalid': errors.contactNo }"
          required
          pattern="[0-9]{10}"
        />
        <div class="invalid-feedback" v-if="errors.contactNo">
          {{ errors.contactNo }}
        </div>
      </div>

      <div class="form-group mb-3">
        <label for="email" class="form-label">
          {{ $t("common.Email") }} <span class="text-danger">*</span>
        </label>
        <input
          type="email"
          id="email"
          v-model="formData.email"
          @input="validateField('email')"
          @blur="validateField('email')"
          class="form-control"
          :class="{ 'is-invalid': errors.email }"
          required
        />
        <div class="invalid-feedback" v-if="errors.email">
          {{ errors.email }}
        </div>
      </div>

      <div class="form-group mb-3">
        <label for="message" class="form-label">
          {{ $t("common.Message") }} <span class="text-danger">*</span>
        </label>
        <textarea
          id="message"
          v-model="formData.message"
          @input="validateField('message')"
          @blur="validateField('message')"
          class="form-control"
          :class="{ 'is-invalid': errors.message }"
          rows="5"
          required
        ></textarea>
        <div class="invalid-feedback" v-if="errors.message">
          {{ errors.message }}
        </div>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="contactStore.isSubmitting">
        <span
          v-if="contactStore.isSubmitting"
          class="spinner-border spinner-border-sm me-2"
        ></span>
        {{
          contactStore.isSubmitting ? t("common.Submitting") + "..." : t("common.Submit")
        }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useContactStore } from "@/modules/Core/stores/contactStore";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const contactStore = useContactStore();
const formRef = ref(null);

const formData = reactive({
  fullName: "",
  contactNo: "",
  email: "",
  message: "",
});

const errors = reactive({
  fullName: "",
  contactNo: "",
  email: "",
  message: "",
});

const validateField = (field) => {
  switch (field) {
    case "fullName":
      if (!formData.fullName.trim()) {
        errors.fullName = "Full name is required";
      } else if (formData.fullName.trim().length < 3) {
        errors.fullName = "Full name must be at least 3 characters";
      } else {
        errors.fullName = "";
      }
      break;

    case "contactNo":
      if (!formData.contactNo.trim()) {
        errors.contactNo = "Contact number is required";
      } else if (!/^[0-9]{10}$/.test(formData.contactNo)) {
        errors.contactNo = "Contact number must be 10 digits";
      } else {
        errors.contactNo = "";
      }
      break;

    case "email":
      if (!formData.email.trim()) {
        errors.email = "Email is required";
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
        errors.email = "Please enter a valid email address";
      } else {
        errors.email = "";
      }
      break;

    case "message":
      if (!formData.message.trim()) {
        errors.message = "Message is required";
      } else if (formData.message.trim().length < 10) {
        errors.message = "Message must be at least 10 characters";
      } else {
        errors.message = "";
      }
      break;
  }

  return !errors[field];
};

const validateForm = () => {
  const fields = ["fullName", "contactNo", "email", "message"];
  let isValid = true;

  fields.forEach((field) => {
    const fieldValid = validateField(field);
    if (!fieldValid) {
      isValid = false;
    }
  });

  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  contactStore.clearErrors();
  contactStore.clearSuccess();

  const payload = {
    fullName: formData.fullName.trim(),
    contactNo: formData.contactNo.trim(),
    email: formData.email.trim(),
    message: formData.message.trim(),
  };

  const result = await contactStore.submitContactForm(payload);

  if (result.success) {
    Object.keys(formData).forEach((key) => (formData[key] = ""));
    Object.keys(errors).forEach((key) => (errors[key] = ""));
  }
};
</script>

<style scoped>
.contact-form-wrapper {
  background: #ffffff;
  padding: 0;
}

.form-label {
  font-weight: 500;
  color: #333;
  margin-bottom: 0.5rem;
}

.form-control {
  border: 1px solid #ced4da;
  border-radius: 6px;
  padding: 0.625rem 0.875rem;
  font-size: 0.95rem;
  transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-control.is-invalid {
  border-color: #dc3545;
  padding-right: calc(1.5em + 0.75rem);
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3ccpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right calc(0.375em + 0.1875rem) center;
  background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #dc3545;
}

textarea.form-control {
  resize: vertical;
  min-height: 120px;
}

.btn-primary {
  padding: 0.625rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-primary:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}

@media (max-width: 768px) {
  .form-control {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
  }

  .btn-primary {
    width: 100%;
    padding: 0.75rem;
  }
}
</style>
