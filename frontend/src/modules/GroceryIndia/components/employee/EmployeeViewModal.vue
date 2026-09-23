<template>
  <div>
    <!-- Main Employee Modal -->
    <div
      ref="modalElement"
      class="modal fade"
      tabindex="-1"
      aria-labelledby="employeeModalLabel"
      aria-hidden="true"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
    >
      <div
        class="modal-dialog modal-dialog-centered modal-dialog-scrollable employee-modal-dialog"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="employeeModalLabel">
              {{ $t("common.Employee Details") }}
            </h5>
            <button
              type="button"
              class="btn-close"
              @click="hide"
              :disabled="loading"
              aria-label="Close"
            ></button>
          </div>

          <div class="modal-body">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
              </div>
              <p class="text-muted mt-2 small">
                {{ $t("common.Loading") }} {{ $t("common.Employee Details") }}...
              </p>
            </div>

            <!-- Error Box -->
            <FormErrorBox
              v-if="errorMessages.length > 0"
              :messages="errorMessages"
              @clear="clearError"
              class="mb-3"
            />

            <!-- Success Message -->
            <div
              v-if="successMessage"
              class="alert alert-success alert-dismissible fade show"
            >
              {{ successMessage }}
              <button
                type="button"
                class="btn-close"
                @click="successMessage = ''"
              ></button>
            </div>

            <form v-if="!loading">
              <!-- Name -->
              <div class="mb-3">
                <label for="employeeName" class="form-label">
                  {{ $t("common.Employee Name") }} <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': hasFieldError('name') }"
                  id="employeeName"
                  v-model="formData.name"
                  :disabled="loading"
                  required
                />
                <!-- <div v-if="hasFieldError('name')" class="invalid-feedback d-block">
                  <div
                    v-for="(msg, index) in getFieldErrors('name')"
                    :key="`name-${index}`"
                  >
                    {{ msg }}
                  </div>
                </div> -->
              </div>

              <!-- Contact Number -->
              <div class="mb-3">
                <label for="employeeContact" class="form-label">
                  {{ $t("common.Contact Number") }} <span class="text-danger">*</span>
                </label>
                <div class="contact-row">
                  <div class="country-code-wrapper">
                    <CountryCodeSelector
                      v-model="formData.countryCode"
                      :disabled="true"
                      @change="handleCountryChange"
                    />
                  </div>
                  <div class="contact-input-wrapper">
                    <input
                      type="tel"
                      class="form-control"
                      :class="{ 'is-invalid': hasFieldError('mobile') }"
                      id="employeeContact"
                      v-model="formData.contact"
                      pattern="[0-9]{10}"
                      placeholder="10-digit mobile number"
                      :disabled="loading"
                      required
                    />
                  </div>
                </div>
                <!-- <div v-if="hasFieldError('mobile')" class="invalid-feedback d-block">
                  <div
                    v-for="(msg, index) in getFieldErrors('mobile')"
                    :key="`mobile-${index}`"
                  >
                    {{ msg }}
                  </div>
                </div> -->
              </div>

              <!-- Address -->
              <div class="mb-3">
                <label for="employeeAddress" class="form-label">
                  {{ $t("common.Address") }} <span class="text-danger">*</span>
                </label>
                <textarea
                  class="form-control"
                  :class="{ 'is-invalid': hasFieldError('address') }"
                  id="employeeAddress"
                  v-model="formData.address"
                  rows="3"
                  :disabled="loading"
                  required
                ></textarea>
                <!-- <div v-if="hasFieldError('address')" class="invalid-feedback d-block">
                  <div
                    v-for="(msg, index) in getFieldErrors('address')"
                    :key="`address-${index}`"
                  >
                    {{ msg }}
                  </div>
                </div> -->
              </div>

              <!-- Change Password Link -->
              <div class="mb-3">
                <button
                  type="button"
                  class="btn btn-link text-danger p-0 text-decoration-none"
                  @click="togglePasswordFields"
                  :disabled="loading"
                >
                  <i class="bi bi-key me-1"></i>
                  {{
                    showPasswordFields
                      ? $t("common.Cancel Password Change")
                      : $t("common.Change Password")
                  }}
                </button>
              </div>

              <!-- Password Fields -->
              <div v-if="showPasswordFields" class="border rounded p-3 mb-3 bg-light">
                <h6 class="fw-semibold mb-3 text-danger">
                  <i class="bi bi-shield-lock me-2"></i>{{ $t("common.Change Password") }}
                </h6>

                <div class="mb-3">
                  <label for="newPassword" class="form-label">
                    {{ $t("common.New Password") }} <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input
                      :type="showNewPassword ? 'text' : 'password'"
                      class="form-control"
                      :class="{ 'is-invalid': hasFieldError('password') }"
                      id="newPassword"
                      v-model="passwordData.newPassword"
                      :disabled="loading"
                      placeholder="Enter new password"
                      minlength="8"
                    />
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      :disabled="loading"
                    >
                      <i :class="showNewPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                    </button>
                  </div>
                  <small class="text-muted d-block mt-1">
                    {{ $t("common.Password must be at least 8 characters") }}
                  </small>
                  <!-- <div v-if="hasFieldError('password')" class="invalid-feedback d-block">
                    <div
                      v-for="(msg, index) in getFieldErrors('password')"
                      :key="`password-${index}`"
                    >
                      {{ msg }}
                    </div>
                  </div> -->
                </div>

                <div class="mb-0">
                  <label for="confirmPassword" class="form-label">
                    {{ $t("common.Confirm Password") }} <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input
                      :type="showConfirmPassword ? 'text' : 'password'"
                      class="form-control"
                      :class="{ 'is-invalid': hasFieldError('password_confirmation') }"
                      id="confirmPassword"
                      v-model="passwordData.confirmPassword"
                      :disabled="loading"
                      placeholder="Confirm new password"
                    />
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      :disabled="loading"
                    >
                      <i
                        :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"
                      ></i>
                    </button>
                  </div>
                  <!-- <div
                    v-if="hasFieldError('password_confirmation')"
                    class="invalid-feedback d-block"
                  >
                    <div
                      v-for="(msg, index) in getFieldErrors('password_confirmation')"
                      :key="`password-confirmation-${index}`"
                    >
                      {{ msg }}
                    </div>
                  </div> -->
                </div>
              </div>

              <!-- Photo Preview -->
              <div class="mb-4">
                <label class="form-label fw-semibold">
                  {{ $t("common.Employee Photo") }}
                </label>

                <div
                  class="image-preview-clickable"
                  :class="{ 'is-clickable': hasPreviewImage }"
                  @click="handleImageAreaClick"
                >
                  <ImagePreview
                    :preview="formData.photo"
                    :error="getFieldErrors('photo')[0] || ''"
                    @file-selected="handlePhotoSelected"
                    @delete="handlePhotoDelete"
                  />
                </div>

                <!-- <div v-if="hasFieldError('photo')" class="invalid-feedback d-block">
                  <div
                    v-for="(msg, index) in getFieldErrors('photo')"
                    :key="`photo-${index}`"
                  >
                    {{ msg }}
                  </div>
                </div> -->
              </div>
            </form>
          </div>

          <div class="modal-footer modal-footer-wrap">
            <button
              type="button"
              class="btn btn-secondary footer-btn"
              @click="hide"
              :disabled="loading"
            >
              {{ $t("common.Cancel") }}
            </button>
            <button
              type="button"
              class="btn btn-primary footer-btn"
              @click="handleSave"
              :disabled="loading"
            >
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
              ></span>
              {{ loading ? $t("common.Saving") + "..." : $t("common.Save Changes") }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Full Image Preview Modal -->
    <div
      ref="imageModalElement"
      class="modal fade"
      tabindex="-1"
      aria-labelledby="employeeImagePreviewLabel"
      aria-hidden="true"
    >
      <div
        class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down image-modal-dialog"
      >
        <div class="modal-content image-modal-content">
          <div class="modal-header image-modal-header">
            <h5 class="modal-title" id="employeeImagePreviewLabel">
              {{ $t("common.Employee Photo") }}
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              @click="closeImagePreview"
              aria-label="Close"
            ></button>
          </div>

          <div class="modal-body image-modal-body" @click="closeImagePreview">
            <img
              v-if="hasPreviewImage"
              :src="fullPreviewImage"
              alt="Employee photo preview"
              class="full-preview-image"
              @click.stop
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick, computed } from "vue";
import { Modal } from "bootstrap";
import { useI18n } from "vue-i18n";
import { useEmployeeStore } from "@/modules/GroceryIndia/stores/employeeStore";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import CountryCodeSelector from "@/modules/Core/components/CountryCodeSelect.vue";
import ImagePreview from "@/modules/GroceryIndia/components/profile/ImagePreview.vue";

const { t } = useI18n();

const props = defineProps({
  employee: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["save", "close"]);

const employeeStore = useEmployeeStore();

const modalElement = ref(null);
const imageModalElement = ref(null);

let modalInstance = null;
let imageModalInstance = null;

const loading = ref(false);
const errors = ref({});
const photoFile = ref(null);
const errorMessages = ref([]);
const successMessage = ref("");
const selectedCountry = ref(null);
const showPasswordFields = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const formData = ref({
  id: null,
  staff_id: null,
  name: "",
  email: "",
  contact: "",
  countryCode: "",
  address: "",
  photo: "",
  isPhotoDelete: 0,
  active: 1,
  created_at: "",
  last_logged_in: "",
  country: "",
});

const passwordData = ref({
  newPassword: "",
  confirmPassword: "",
});

const hasPreviewImage = computed(() => !!formData.value.photo);
const fullPreviewImage = computed(() => formData.value.photo || "");

const getFieldErrors = (field) => {
  const fieldErrors = errors.value?.[field];
  return Array.isArray(fieldErrors) ? fieldErrors : [];
};

const hasFieldError = (field) => {
  return getFieldErrors(field).length > 0;
};

const resetForm = () => {
  formData.value = {
    id: null,
    staff_id: null,
    name: "",
    email: "",
    contact: "",
    countryCode: "",
    address: "",
    photo: "",
    isPhotoDelete: 0,
    active: 1,
    created_at: "",
    last_logged_in: "",
    country: "",
  };

  passwordData.value = {
    newPassword: "",
    confirmPassword: "",
  };

  showPasswordFields.value = false;
  showNewPassword.value = false;
  showConfirmPassword.value = false;
  clearError();
  photoFile.value = null;
  selectedCountry.value = null;
};

const clearError = () => {
  errorMessages.value = [];
  successMessage.value = "";
  errors.value = {};
};

const handleCountryChange = (country) => {
  selectedCountry.value = country;
};

const removeFieldError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field];
    errors.value = { ...errors.value };
  }
};

const removeMessagesContaining = (keywords = []) => {
  errorMessages.value = errorMessages.value.filter((msg) => {
    const lowerMsg = String(msg).toLowerCase();
    return !keywords.some((keyword) => lowerMsg.includes(keyword.toLowerCase()));
  });
};

const handlePhotoSelected = (file) => {
  photoFile.value = file;
  formData.value.isPhotoDelete = 0;
  removeFieldError("photo");
  removeMessagesContaining(["photo", "image"]);

  const reader = new FileReader();
  reader.onload = (e) => {
    formData.value.photo = e.target.result;
  };
  reader.readAsDataURL(file);
};

const handlePhotoDelete = () => {
  formData.value.isPhotoDelete = 1;
  formData.value.photo = "";
  photoFile.value = null;
  removeFieldError("photo");
  removeMessagesContaining(["photo", "image"]);
  closeImagePreview();
};

const handleImageAreaClick = (event) => {
  if (!hasPreviewImage.value) return;

  const clickedDeleteButton = event.target.closest("button");
  const clickedFileInput = event.target.closest("input");
  const clickedLabel = event.target.closest("label");

  if (clickedDeleteButton || clickedFileInput || clickedLabel) return;

  openImagePreview();
};

const togglePasswordFields = () => {
  showPasswordFields.value = !showPasswordFields.value;

  if (!showPasswordFields.value) {
    passwordData.value.newPassword = "";
    passwordData.value.confirmPassword = "";
    removeFieldError("password");
    removeFieldError("password_confirmation");
    removeMessagesContaining(["password"]);
  }
};

const fetchEmployeeDetails = async (id) => {
  if (!id) return;

  loading.value = true;
  clearError();

  try {
    const employee = await employeeStore.fetchEmployeeById(id);

    if (employee) {
      formData.value = {
        id: employee.id,
        staff_id: employee.staff_id,
        name: employee.name,
        email: employee.email === "-" ? "" : employee.email,
        contact: employee.contact,
        countryCode: employee.countryCode || "",
        address: employee.address || "",
        photo: employee.photo,
        isPhotoDelete: 0,
        active: employee.active || 1,
        created_at: employee.created_at || "",
        last_logged_in: employee.last_logged_in || "",
        country: employee.country || "",
      };
    }
  } catch (error) {
    console.error("Error fetching employee details:", error);
    errorMessages.value = [error.message || t("common.Failed to load employee details")];
  } finally {
    loading.value = false;
  }
};

const validatePasswordFields = () => {
  let isValid = true;
  removeFieldError("password");
  removeFieldError("password_confirmation");
  removeMessagesContaining(["password"]);

  const passwordErrors = [];
  const passwordConfirmationErrors = [];

  if (showPasswordFields.value) {
    if (!passwordData.value.newPassword) {
      passwordErrors.push(t("common.New password is required"));
      isValid = false;
    } else if (passwordData.value.newPassword.length < 8) {
      passwordErrors.push(t("common.Password must be at least 8 characters"));
      isValid = false;
    }

    if (!passwordData.value.confirmPassword) {
      passwordConfirmationErrors.push(t("common.Confirm password is required"));
      isValid = false;
    } else if (passwordData.value.newPassword !== passwordData.value.confirmPassword) {
      passwordConfirmationErrors.push(t("common.Passwords do not match"));
      isValid = false;
    }
  }

  if (passwordErrors.length) {
    errors.value.password = passwordErrors;
  }

  if (passwordConfirmationErrors.length) {
    errors.value.password_confirmation = passwordConfirmationErrors;
  }

  errorMessages.value = [
    ...errorMessages.value,
    ...passwordErrors,
    ...passwordConfirmationErrors,
  ];

  return isValid;
};

const handleSave = async () => {
  if (loading.value) return;

  clearError();

  if (showPasswordFields.value && !validatePasswordFields()) {
    return;
  }

  loading.value = true;

  try {
    const formDataToSend = new FormData();
    formDataToSend.append("staff_id", formData.value.staff_id || "");
    formDataToSend.append("name", formData.value.name || "");
    formDataToSend.append("mobile", formData.value.contact || "");
    formDataToSend.append("address", formData.value.address || "");

    if (formData.value.email && formData.value.email !== "") {
      formDataToSend.append("email", formData.value.email);
    }

    if (selectedCountry.value?.code) {
      formDataToSend.append("country_code", selectedCountry.value.code);
    }

    if (formData.value.isPhotoDelete === 1) {
      formDataToSend.append("is_photo_delete", "1");
    } else if (photoFile.value) {
      formDataToSend.append("photo", photoFile.value);
    }

    if (showPasswordFields.value && passwordData.value.newPassword) {
      formDataToSend.append("password", passwordData.value.newPassword);
      formDataToSend.append("password_confirmation", passwordData.value.confirmPassword);
    }

    const result = await employeeStore.updateEmployee(formDataToSend);

    if (result.success) {
      successMessage.value = result.message || t("common.Employee updated successfully");

      showPasswordFields.value = false;
      passwordData.value.newPassword = "";
      passwordData.value.confirmPassword = "";

      emit("save", {
        ...formData.value,
        id: formData.value.id,
        email: formData.value.email || "-",
      });

      setTimeout(() => {
        hide();
      }, 1500);
    } else {
      errors.value = result.errors || {};

      const messages = Object.values(errors.value).flatMap((fieldErrors) =>
        Array.isArray(fieldErrors) ? fieldErrors : []
      );

      errorMessages.value = messages.length
        ? [...new Set(messages)]
        : [result.message || t("common.Update failed")];
    }
  } catch (error) {
    console.error("Error updating employee:", error);

    if (error.response?.data?.status === "failed") {
      const response = error.response.data;
      const responseErrors =
        response.data && typeof response.data === "object" ? response.data : {};

      errors.value = responseErrors;

      const messages = [];
      if (response.message) {
        messages.push(response.message);
      }

      Object.values(responseErrors).forEach((fieldErrors) => {
        if (Array.isArray(fieldErrors)) {
          messages.push(...fieldErrors);
        }
      });

      errorMessages.value = [...new Set(messages)];
    } else {
      errorMessages.value = [
        error.response?.data?.message ||
          error.message ||
          t("common.Failed to update employee. Please try again."),
      ];
    }
  } finally {
    loading.value = false;
  }
};

watch(
  () => props.employee,
  async (newEmployee) => {
    if (newEmployee && newEmployee.id) {
      await nextTick();
      await fetchEmployeeDetails(newEmployee.staff_id);
    }
  },
  { immediate: true, deep: true }
);

const show = () => {
  if (modalInstance) {
    modalInstance.show();
  }
};

const hide = () => {
  closeImagePreview();
  if (modalInstance) {
    modalInstance.hide();
  }
};

const openImagePreview = () => {
  if (!hasPreviewImage.value || !imageModalInstance) return;
  imageModalInstance.show();
};

const closeImagePreview = () => {
  if (imageModalInstance) {
    imageModalInstance.hide();
  }
};

onMounted(() => {
  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value, {
      backdrop: "static",
      keyboard: false,
    });

    modalElement.value.addEventListener("hidden.bs.modal", () => {
      resetForm();
      emit("close");
    });
  }

  if (imageModalElement.value) {
    imageModalInstance = new Modal(imageModalElement.value, {
      backdrop: true,
      keyboard: true,
      focus: true,
    });
  }
});

onUnmounted(() => {
  if (modalInstance) {
    modalInstance.dispose();
    modalInstance = null;
  }

  if (imageModalInstance) {
    imageModalInstance.dispose();
    imageModalInstance = null;
  }
});

defineExpose({
  show,
  hide,
});
</script>

<style scoped>
.employee-modal-dialog {
  max-width: 500px;
}

.form-label {
  font-weight: 600;
  color: #495057;
  margin-bottom: 0.5rem;
  font-size: 14px;
}

.form-control {
  font-size: 14px;
  padding: 0.5rem 0.75rem;
}

.form-text {
  font-size: 12px;
  color: #6c757d;
  margin-top: 0.25rem;
}

.invalid-feedback {
  display: block;
  font-size: 12px;
  margin-top: 0.25rem;
}

.btn-link {
  font-size: 14px;
  font-weight: 500;
}

.btn-link:hover {
  text-decoration: underline !important;
}

.border.rounded {
  border-color: #ffc9c9 !important;
}

.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.modal-title {
  font-weight: 600;
  font-size: 1.125rem;
  color: #212529;
}

.modal-footer {
  background-color: #f8f9fa;
  border-top: 1px solid #dee2e6;
}

.btn {
  font-size: 14px;
  padding: 0.5rem 1rem;
}

.btn:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}

.input-group .btn-outline-secondary {
  border-color: #ced4da;
}

.input-group .btn-outline-secondary:hover {
  background-color: #e9ecef;
  border-color: #ced4da;
  color: #495057;
}

.contact-row {
  display: flex;
  gap: 0.5rem;
  align-items: stretch;
  width: 100%;
}

.country-code-wrapper {
  flex: 0 0 auto;
  min-width: 92px;
}

.contact-input-wrapper {
  flex: 1 1 auto;
  min-width: 0;
}

.image-preview-clickable {
  width: 100%;
}

.image-preview-clickable.is-clickable {
  cursor: pointer;
}

.image-preview-clickable.is-clickable:hover {
  opacity: 0.96;
}

.modal-footer-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.footer-btn {
  min-width: 120px;
}

.image-modal-dialog {
  max-width: 900px;
}

.image-modal-content {
  background: #111;
  border: 0;
}

.image-modal-header {
  background: #111;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.image-modal-header .modal-title {
  color: #fff;
}

.image-modal-body {
  padding: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  background: #111;
  cursor: zoom-out;
}

.full-preview-image {
  display: block;
  max-width: 100%;
  max-height: 75vh;
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: 0.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

@media (max-width: 576px) {
  .employee-modal-dialog {
    margin: 0.5rem;
  }

  .contact-row {
    flex-direction: column;
  }

  .country-code-wrapper,
  .contact-input-wrapper {
    width: 100%;
  }

  .modal-footer-wrap {
    flex-direction: column;
  }

  .footer-btn {
    width: 100%;
    min-width: 0;
  }

  .image-modal-body {
    padding: 0.75rem;
  }

  .full-preview-image {
    max-height: 68vh;
  }
}
</style>
