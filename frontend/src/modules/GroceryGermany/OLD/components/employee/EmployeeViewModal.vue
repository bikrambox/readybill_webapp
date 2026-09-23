<template>
  <div
    ref="modalElement"
    class="modal fade"
    tabindex="-1"
    aria-labelledby="employeeModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="employeeModalLabel">Employee Details</h5>
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
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2 small">Loading employee details...</p>
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

          <form v-if="!loading" @submit.prevent="handleSave">
            <!-- Name -->
            <div class="mb-3">
              <label for="employeeName" class="form-label">
                Employee Name <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                id="employeeName"
                v-model="formData.name"
                :disabled="loading"
                required
              />
              <div v-if="errors.name" class="invalid-feedback">
                {{ errors.name[0] }}
              </div>
            </div>

            <!-- Email -->
            <div class="mb-3">
              <label for="employeeEmail" class="form-label">Email</label>
              <input
                type="email"
                class="form-control"
                :class="{ 'is-invalid': errors.email }"
                id="employeeEmail"
                v-model="formData.email"
                :disabled="loading"
              />
              <div v-if="errors.email" class="invalid-feedback">
                {{ errors.email[0] }}
              </div>
            </div>

            <!-- Contact Number with Country Code -->
            <div class="mb-3">
              <label for="employeeContact" class="form-label">
                Contact Number <span class="text-danger">*</span>
              </label>
              <div class="d-flex gap-2">
                <CountryCodeSelector
                  v-model="formData.countryCode"
                  :disabled="true"
                  @change="handleCountryChange"
                />
                <input
                  type="tel"
                  class="form-control flex-grow-1"
                  :class="{ 'is-invalid': errors.mobile }"
                  id="employeeContact"
                  v-model="formData.contact"
                  pattern="[0-9]{10}"
                  placeholder="10-digit mobile number"
                  :disabled="loading"
                  required
                />
              </div>
              <div v-if="errors.mobile" class="invalid-feedback d-block">
                {{ errors.mobile[0] }}
              </div>
            </div>

            <!-- Address -->
            <div class="mb-3">
              <label for="employeeAddress" class="form-label">
                Address <span class="text-danger">*</span>
              </label>
              <textarea
                class="form-control"
                :class="{ 'is-invalid': errors.address }"
                id="employeeAddress"
                v-model="formData.address"
                rows="3"
                :disabled="loading"
                required
              ></textarea>
              <div v-if="errors.address" class="invalid-feedback">
                {{ errors.address[0] }}
              </div>
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
                    ? "Cancel Password Change"
                    : "Change Password"
                }}
              </button>
            </div>

            <!-- Password Fields (Conditional) -->
            <div
              v-if="showPasswordFields"
              class="border rounded p-3 mb-3 bg-light"
            >
              <h6 class="fw-semibold mb-3 text-danger">
                <i class="bi bi-shield-lock me-2"></i>Change Password
              </h6>

              <!-- New Password -->
              <div class="mb-3">
                <label for="newPassword" class="form-label">
                  New Password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input
                    :type="showNewPassword ? 'text' : 'password'"
                    class="form-control"
                    :class="{ 'is-invalid': errors.password }"
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
                    <i
                      :class="showNewPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"
                    ></i>
                  </button>
                </div>
                <small class="text-muted d-block mt-1"
                  >Password must be at least 8 characters</small
                >
                <div v-if="errors.password" class="invalid-feedback d-block">
                  {{ errors.password[0] }}
                </div>
              </div>

              <!-- Confirm Password -->
              <div class="mb-0">
                <label for="confirmPassword" class="form-label">
                  Confirm Password <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="form-control"
                    :class="{ 'is-invalid': errors.password_confirmation }"
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
                      :class="
                        showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'
                      "
                    ></i>
                  </button>
                </div>
                <div
                  v-if="errors.password_confirmation"
                  class="invalid-feedback d-block"
                >
                  {{ errors.password_confirmation[0] }}
                </div>
              </div>
            </div>

            <!-- Photo Preview using ImagePreview Component -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Employee Photo</label>
              <ImagePreview
                :preview="formData.photo"
                :error="errors.photo"
                @file-selected="handlePhotoSelected"
                @delete="handlePhotoDelete"
              />
            </div>

            <!-- Additional Info (Read-only) -->
            <!-- <div v-if="formData.created_at" class="mb-0">
              <div class="card bg-light border-0">
                <div class="card-body p-3">
                  <h6
                    class="card-title mb-2 text-muted"
                    style="font-size: 13px"
                  >
                    <i class="bi bi-info-circle me-1"></i>Additional Information
                  </h6>
                  <div class="row g-2" style="font-size: 13px">
                    <div class="col-6">
                      <strong>Created:</strong><br />
                      <span class="text-muted">{{
                        formatDate(formData.created_at)
                      }}</span>
                    </div>
                    <div class="col-6">
                      <strong>Last Login:</strong><br />
                      <span class="text-muted">{{
                        formatDate(formData.last_logged_in)
                      }}</span>
                    </div>
                    <div class="col-6">
                      <strong>Status:</strong><br />
                      <span
                        :class="
                          formData.active ? 'text-success' : 'text-danger'
                        "
                      >
                        {{ formData.active ? "Active" : "Inactive" }}
                      </span>
                    </div>
                    <div class="col-6">
                      <strong>Country:</strong><br />
                      <span class="text-muted">{{ formData.country }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div> -->

          </form>
        </div>

        <div class="modal-footer">
          <button
            type="button"
            class="btn btn-secondary"
            @click="hide"
            :disabled="loading"
          >
            Cancel
          </button>
          <button
            type="button"
            class="btn btn-primary"
            @click="handleSave"
            :disabled="loading"
          >
            <span
              v-if="loading"
              class="spinner-border spinner-border-sm me-2"
              role="status"
            ></span>
            {{ loading ? "Saving..." : "Save Changes" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from "vue";
import { Modal } from "bootstrap";
import { useEmployeeStore } from "@/modules/GroceryGermany/stores/employeeStore";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import CountryCodeSelector from "@/modules/Core/components/CountryCodeSelect.vue";
import ImagePreview from "@/modules/GroceryGermany/components/profile/ImagePreview.vue";

const props = defineProps({
  employee: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["save", "close"]);

const employeeStore = useEmployeeStore();

const modalElement = ref(null);
let modalInstance = null;

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

const formatDate = (dateString) => {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleString("en-IN", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
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
};

const clearError = () => {
  errorMessages.value = [];
  successMessage.value = "";
  errors.value = {};
};

const handleCountryChange = (country) => {
  selectedCountry.value = country;
};

const handlePhotoSelected = (file) => {
  photoFile.value = file;
  formData.value.isPhotoDelete = 0;
  delete errors.value.photo;

  const index = errorMessages.value.findIndex(
    (msg) =>
      msg.toLowerCase().includes("photo") || msg.toLowerCase().includes("image")
  );
  if (index > -1) {
    errorMessages.value.splice(index, 1);
  }

  // Create preview URL
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
  delete errors.value.photo;

  const index = errorMessages.value.findIndex(
    (msg) =>
      msg.toLowerCase().includes("photo") || msg.toLowerCase().includes("image")
  );
  if (index > -1) {
    errorMessages.value.splice(index, 1);
  }
};

const togglePasswordFields = () => {
  showPasswordFields.value = !showPasswordFields.value;

  if (!showPasswordFields.value) {
    passwordData.value.newPassword = "";
    passwordData.value.confirmPassword = "";
    delete errors.value.password;
    delete errors.value.password_confirmation;

    // Remove password-related errors
    errorMessages.value = errorMessages.value.filter(
      (msg) => !msg.toLowerCase().includes("password")
    );
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
    errorMessages.value = [error.message || "Failed to load employee details"];
  } finally {
    loading.value = false;
  }
};

const validatePasswordFields = () => {
  let isValid = true;

  if (showPasswordFields.value) {
    if (!passwordData.value.newPassword) {
      errors.value.password = ["New password is required"];
      errorMessages.value.push("New password is required");
      isValid = false;
    } else if (passwordData.value.newPassword.length < 8) {
      errors.value.password = ["Password must be at least 8 characters"];
      errorMessages.value.push("Password must be at least 8 characters");
      isValid = false;
    }

    if (!passwordData.value.confirmPassword) {
      errors.value.password_confirmation = ["Confirm password is required"];
      errorMessages.value.push("Confirm password is required");
      isValid = false;
    } else if (
      passwordData.value.newPassword !== passwordData.value.confirmPassword
    ) {
      errors.value.password_confirmation = ["Passwords do not match"];
      errorMessages.value.push("Passwords do not match");
      isValid = false;
    }
  }

  return isValid;
};

const handleSave = async () => {
  if (loading.value) return;

  clearError();

  // Validate password fields if shown
  if (showPasswordFields.value && !validatePasswordFields()) {
    return;
  }

  loading.value = true;

  try {
    const formDataToSend = new FormData();
    formDataToSend.append("staff_id", formData.value.staff_id || "");
    formDataToSend.append("name", formData.value.name);
    formDataToSend.append("mobile", formData.value.contact);
    formDataToSend.append("address", formData.value.address);

    if (formData.value.email && formData.value.email !== "") {
      formDataToSend.append("email", formData.value.email);
    }

    if (selectedCountry.value) {
      formDataToSend.append("country_code", selectedCountry.value.code);
    }

    // Handle photo
    if (formData.value.isPhotoDelete === 1) {
      formDataToSend.append("is_photo_delete", "1");
    } else if (photoFile.value) {
      formDataToSend.append("photo", photoFile.value);
    }

    // Handle password change
    if (showPasswordFields.value && passwordData.value.newPassword) {
      formDataToSend.append("password", passwordData.value.newPassword);
      formDataToSend.append(
        "password_confirmation",
        passwordData.value.confirmPassword
      );
    }

    const result = await employeeStore.updateEmployee(formDataToSend);

    if (result.success) {
      successMessage.value = result.message || "Employee updated successfully";

      // Reset password fields
      showPasswordFields.value = false;
      passwordData.value.newPassword = "";
      passwordData.value.confirmPassword = "";

      // Emit success
      emit("save", {
        ...formData.value,
        id: formData.value.id,
        email: formData.value.email || "-",
      });

      // Close modal after 1.5 seconds
      setTimeout(() => {
        hide();
      }, 1500);
    } else {
      throw new Error(result.message || "Update failed");
    }
  } catch (error) {
    console.error("Error updating employee:", error);

    if (error.response?.data?.status === "failed") {
      const response = error.response.data;

      const messages = [];

      if (response.message) {
        messages.push(response.message);
      }

      if (response.data && typeof response.data === "object") {
        errors.value = response.data;

        Object.keys(response.data).forEach((field) => {
          if (Array.isArray(response.data[field])) {
            response.data[field].forEach((msg) => {
              messages.push(msg);
            });
          }
        });
      }

      errorMessages.value = messages;
    } else {
      errorMessages.value = [
        error.response?.data?.message ||
          error.message ||
          "Failed to update employee. Please try again.",
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
  if (modalInstance) {
    modalInstance.hide();
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
});

onUnmounted(() => {
  if (modalInstance) {
    modalInstance.dispose();
    modalInstance = null;
  }
});

defineExpose({
  show,
  hide,
});
</script>

<style scoped>
.modal-dialog {
  max-width: 500px;
}

/* Form styling */
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

/* Password change link */
.btn-link {
  font-size: 14px;
  font-weight: 500;
}

.btn-link:hover {
  text-decoration: underline !important;
}

/* Password fields container */
.border.rounded {
  border-color: #ffc9c9 !important;
}

/* Modal Header */
.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.modal-title {
  font-weight: 600;
  font-size: 1.125rem;
  color: #212529;
}

/* Modal Footer */
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

/* Input group buttons */
.input-group .btn-outline-secondary {
  border-color: #ced4da;
}

.input-group .btn-outline-secondary:hover {
  background-color: #e9ecef;
  border-color: #ced4da;
  color: #495057;
}

/* Responsive */
@media (max-width: 576px) {
  .modal-dialog {
    margin: 0.5rem;
  }
}
</style>
