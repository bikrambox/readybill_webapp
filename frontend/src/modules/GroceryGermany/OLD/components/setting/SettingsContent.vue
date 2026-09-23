<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useUserPreferencesStore } from "@/modules/GroceryGermany/stores/userPreferences";
import { storeToRefs } from "pinia";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";

const preferencesStore = useUserPreferencesStore();
const { preferences, loading, errors, saveSuccess } =
  storeToRefs(preferencesStore);

// Local reactive references bound to store
const preference_mrp = ref(true);
const preference_mrp_invoice = ref(false);
const preference_quantity = ref(false);
const preference_hsn = ref(false);
const preference_hsn_invoice = ref(false);
const preference_invoice_format = ref(0);
const preference_transaction_mark_as_paid = ref(false);
const preference_transaction_mark_as_unpaid = ref(true);

// Invoice format options with numeric values
const invoiceFormatOptions = [
  { label: "A4", value: 0 },
  { label: "80 mm", value: 1 },
  { label: "50 mm", value: 2 }
];

// Computed to check if there are unsaved changes
const hasChanges = computed(() => {
  return (
    preference_mrp.value !== preferences.value.preference_mrp ||
    preference_quantity.value !== preferences.value.preference_quantity ||
    preference_hsn.value !== preferences.value.preference_hsn ||
    preference_mrp_invoice.value !== preferences.value.preference_mrp_invoice ||
    preference_hsn_invoice.value !== preferences.value.preference_hsn_invoice ||
    preference_invoice_format.value !== preferences.value.preference_invoice_format ||
    preference_transaction_mark_as_paid.value !==
      preferences.value.preference_transaction_mark_as_paid ||
    preference_transaction_mark_as_unpaid.value !==
      preferences.value.preference_transaction_mark_as_unpaid
  );
});

// Sync local state with store
const syncLocalState = () => {
  preference_mrp.value = preferences.value.preference_mrp;
  preference_quantity.value = preferences.value.preference_quantity;
  preference_hsn.value = preferences.value.preference_hsn;
  preference_mrp_invoice.value = preferences.value.preference_mrp_invoice;
  preference_hsn_invoice.value = preferences.value.preference_hsn_invoice;
  preference_invoice_format.value = preferences.value.preference_invoice_format;
  preference_transaction_mark_as_paid.value =
    preferences.value.preference_transaction_mark_as_paid;
  preference_transaction_mark_as_unpaid.value =
    preferences.value.preference_transaction_mark_as_unpaid;
};

// Load preferences on mount
onMounted(async () => {
  await preferencesStore.fetchUserPreferences();
  syncLocalState();
});

// Watch for store changes
watch(
  preferences,
  () => {
    syncLocalState();
  },
  { deep: true }
);

// Watch for save success
watch(saveSuccess, (newVal) => {
  if (newVal) {
    setTimeout(() => {
      preferencesStore.resetSaveSuccess();
    }, 3000);
  }
});

// Save settings
const saveSettings = async () => {
  const result = await preferencesStore.updatePreferences({
    preference_mrp: preference_mrp.value,
    preference_quantity: preference_quantity.value,
    preference_hsn: preference_hsn.value,
    preference_mrp_invoice: preference_mrp_invoice.value,
    preference_hsn_invoice: preference_hsn_invoice.value,
    preference_invoice_format: preference_invoice_format.value,
    preference_transaction_mark_as_paid:
      preference_transaction_mark_as_paid.value,
    preference_transaction_mark_as_unpaid:
      preference_transaction_mark_as_unpaid.value,
  });

  if (!result.success) {
    console.error("Failed to save settings");
  }
};

// Clear errors handler
const handleClearErrors = () => {
  preferencesStore.clearErrors();
};
</script>

<template>
  <div class="settings-container">
    <div class="container-fluid py-4">
      <!-- Breadcrumb and Title -->
      <div class="mb-4">
        <h2 class="fw-bold mb-1">Settings</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="#" class="text-decoration-none">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Settings</li>
          </ol>
        </nav>
      </div>

      <!-- Loading State -->
      <div v-if="loading && !preferences" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Loading preferences...</p>
      </div>

      <!-- Error Alert using FormErrorBox -->
      <div v-if="errors.length > 0" class="mb-3">
        <FormErrorBox :messages="errors" @clear="handleClearErrors" />
      </div>

      <!-- Success Alert -->
      <div
        v-if="saveSuccess"
        class="alert alert-success alert-dismissible fade show"
        role="alert"
      >
        <i class="bi bi-check-circle-fill me-2"></i>
        Settings saved successfully!
        <button
          type="button"
          class="btn-close"
          @click="preferencesStore.resetSaveSuccess()"
        ></button>
      </div>

      <!-- Single Card containing all sections -->
      <div v-if="!loading || preferences" class="card border-0 shadow-sm mb-3">
        <div class="card-body p-4">
          <!-- Inventory Management Section -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">Inventory Management</h6>

            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">
                    Maintain MRP (Maximum Retail Price)
                  </div>
                  <p class="text-muted small mb-0">
                    Track and manage maximum retail prices for your products
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_mrp"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>

            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">Show MRP in invoices</div>
                  <p class="text-muted small mb-0">
                    Display MRP on customer invoices
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_mrp_invoice"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">Maintain Stock Levels</div>
                  <p class="text-muted small mb-0">
                    Track inventory quantities and stock movements
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_quantity"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Tax & Compliance Section -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">Tax & Compliance</h6>

            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">Use HSN/SAC codes</div>
                  <p class="text-muted small mb-0">
                    Include HSN (Harmonized System of Nomenclature) or SAC codes
                    for tax compliance
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_hsn"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">
                    Show HSN/SAC codes in invoices
                  </div>
                  <p class="text-muted small mb-0">
                    Display HSN/SAC codes on customer invoices
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_hsn_invoice"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Invoice and Printing Section -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">Invoice and Printing</h6>

            <div class="row align-items-center">
              <div class="col-12 col-md-6 mb-2 mb-md-0">
                <div class="fw-semibold">Invoice paper size</div>
                <p class="text-muted small mb-0 d-md-none">
                  Choose the paper size for printing invoices
                </p>
              </div>
              <div class="col-12 col-md-6">
                <select
                  class="form-select"
                  v-model.number="preference_invoice_format"
                  :disabled="loading"
                >
                  <option
                    v-for="option in invoiceFormatOptions"
                    :key="option.value"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </select>
              </div>
            </div>
            <p class="text-muted small mb-0 mt-2 d-none d-md-block">
              Choose the paper size for printing invoices
            </p>
          </div>

          <!-- Payment Processing Section -->
          <div class="settings-section">
            <h6 class="section-heading mb-3">Payment Processing</h6>

            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">
                    Auto-mark transactions as paid
                  </div>
                  <p class="text-muted small mb-0">
                    Automatically mark transactions as paid when saved
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_transaction_mark_as_paid"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>

            <div>
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold mb-1">Admin-only payment marking</div>
                  <p class="text-muted small mb-0">
                    Only admin/owner can mark transactions as paid
                  </p>
                </div>
                <div class="form-check form-switch ms-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_transaction_mark_as_unpaid"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Save Button -->
      <div class="row" v-if="!loading || preferences">
        <div class="col-12">
          <div class="d-flex justify-content-center justify-content-md-end">
            <button
              class="btn btn-primary save-btn"
              @click="saveSettings"
              :disabled="loading || !hasChanges"
            >
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              {{ loading ? "Saving..." : "Save Changes" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.settings-container {
  margin: 0 auto;
}

.settings-section {
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 1.5rem;
}

.section-heading {
  color: #6c757d;
  font-weight: 600;
}

.form-check-input {
  width: 3rem;
  height: 1.5rem;
  cursor: pointer;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-check-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-check-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.form-select:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.save-btn {
  background-color: #0d6efd;
  border-color: #0d6efd;
  padding: 0.5rem 2rem;
  font-weight: 500;
}

.save-btn:hover:not(:disabled) {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (min-width: 768px) {
  .save-btn {
    width: auto;
  }
}

@media (max-width: 767.98px) {
  .settings-container {
    max-width: 100%;
  }

  .save-btn {
    width: 100%;
  }
}

@media (max-width: 991.98px) {
  .settings-container {
    max-width: 100%;
  }
}
</style>
