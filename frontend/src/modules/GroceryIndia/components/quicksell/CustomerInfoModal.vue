<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div v-if="show" class="modal-backdrop">
        <div
          ref="dialogRef"
          class="modal-dialog"
          role="dialog"
          aria-modal="true"
          aria-labelledby="customer-modal-title"
        >
          <div class="modal-header">
            <h5 id="customer-modal-title" class="modal-title">
              {{
                mode === "sms"
                  ? $t("common.Send Invoice via SMS")
                  : $t("common.Customer Details")
              }}
            </h5>
            <button
              type="button"
              class="modal-close"
              @click.stop="handleClose"
              :aria-label="$t('common.Close')"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="modal-body">
            <!-- Mobile Number (always shown) -->
            <div class="form-group">
              <label class="form-label required" for="customer-mobile">
                {{
                  mode === "sms"
                    ? $t("common.Contact Number")
                    : $t("common.Mobile Number")
                }}
              </label>
              <div class="mobile-input-row">
                <CountryCodeSelect
                  v-model="form.countryCode"
                  @change="selectedCountry = $event"
                  :disabled="true"
                />
                <div class="input-with-icon flex-1">
                  <input
                    id="customer-mobile"
                    v-model="form.customer_mobile"
                    type="tel"
                    class="form-control"
                    :class="{ 'is-invalid': errors.customer_mobile }"
                    :placeholder="$t('common.Enter mobile number')"
                    maxlength="10"
                    @input="onMobileInput"
                  />
                  <span class="input-icon">
                    <i
                      v-if="searchLoading"
                      class="bi bi-arrow-repeat spin text-muted"
                    ></i>
                    <i
                      v-else-if="customerFound"
                      class="bi bi-check-circle-fill text-success"
                    ></i>
                  </span>
                </div>
              </div>
              <div v-if="errors.customer_mobile" class="invalid-feedback d-block">
                {{ errors.customer_mobile }}
              </div>
              <div v-if="customerFound && !errors.customer_mobile" class="found-hint">
                <i class="bi bi-person-check"></i>
                {{ $t("common.Customer details auto-filled") }}
              </div>
            </div>

            <!-- GST Customer Fields -->
            <template v-if="mode === 'customer'">
              <!-- Name -->
              <div class="form-group">
                <label class="form-label" for="customer-name">
                  {{ $t("common.Customer Name") }}
                  <span class="optional-tag">{{ $t("common.Optional") }}</span>
                </label>
                <input
                  id="customer-name"
                  v-model="form.customer_name"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': errors.customer_name }"
                  :placeholder="$t('common.Enter customer name')"
                />
                <div v-if="errors.customer_name" class="invalid-feedback d-block">
                  {{ errors.customer_name }}
                </div>
              </div>

              <!-- Address -->
              <div class="form-group">
                <label class="form-label" for="customer-address">
                  {{ $t("common.Address") }}
                  <span class="optional-tag">{{ $t("common.Optional") }}</span>
                </label>
                <textarea
                  id="customer-address"
                  v-model="form.customer_address"
                  class="form-control"
                  :class="{ 'is-invalid': errors.customer_address }"
                  rows="2"
                  :placeholder="$t('common.Enter address')"
                />
                <div v-if="errors.customer_address" class="invalid-feedback d-block">
                  {{ errors.customer_address }}
                </div>
              </div>

              <!-- GSTIN -->
              <div class="form-group">
                <label class="form-label" for="customer-gstin">
                  {{ $t("common.GSTIN") }}
                  <span class="optional-tag">{{ $t("common.Optional") }}</span>
                </label>
                <input
                  id="customer-gstin"
                  v-model="form.customer_gstin"
                  type="text"
                  class="form-control"
                  :class="{ 'is-invalid': errors.customer_gstin }"
                  :placeholder="$t('common.Enter GSTIN')"
                  maxlength="15"
                  @input="form.customer_gstin = form.customer_gstin.toUpperCase()"
                />
                <div v-if="errors.customer_gstin" class="invalid-feedback d-block">
                  {{ errors.customer_gstin }}
                </div>
              </div>
            </template>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-outline-secondary"
              @click.stop="handleClose"
              :disabled="loading"
            >
              {{ $t("common.Cancel") }}
            </button>
            <button
              type="button"
              class="btn btn-primary-action"
              @click.stop="handleSubmit"
              :disabled="loading"
            >
              <span v-if="loading">
                <i class="bi bi-arrow-repeat spin"></i>
                {{ $t("common.Processing") }}...
              </span>
              <span v-else>{{ resolvedConfirmLabel }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, ref, watch, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";
import { useQuickSellStore } from "@/modules/GroceryIndia/stores/quickSellStore";
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";

const { t } = useI18n();
const quickSellStore = useQuickSellStore();

const props = defineProps({
  show: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  confirmLabel: { type: String, default: "" },
  mode: { type: String, default: "customer" }, // 'customer' | 'sms'
  serverErrors: { type: Object, default: () => ({}) }, // ← NEW
});

const emit = defineEmits(["submit", "close"]);

const dialogRef = ref(null);

const resolvedConfirmLabel = computed(() => {
  if (props.confirmLabel) return props.confirmLabel;
  return props.mode === "sms" ? t("common.Send SMS") : t("common.Confirm");
});

const selectedCountry = ref(null);

// ── Form state ────────────────────────────────────────────────────────────────
const DEFAULT_FORM = () => ({
  countryCode: "+91",
  country_code: "IN",
  customer_mobile: "",
  customer_name: "",
  customer_address: "",
  customer_gstin: "",
});

const form = ref(DEFAULT_FORM());
const errors = ref({});
const searchLoading = ref(false);
const customerFound = ref(false);

// ── Watch serverErrors — merge into local errors ──────────────────────────────
watch(
  () => props.serverErrors,
  (val) => {
    if (!val || Object.keys(val).length === 0) return;
    // Normalize: take only the first message per field
    const normalized = Object.fromEntries(
      Object.entries(val).map(([field, messages]) => [
        field,
        Array.isArray(messages) ? messages[0] : messages,
      ])
    );
    errors.value = { ...errors.value, ...normalized };
  },
  { deep: true }
);

// ── Debounce timer ────────────────────────────────────────────────────────────
let debounceTimer = null;

// ── Mobile input handler ──────────────────────────────────────────────────────
const onMobileInput = () => {
  clearError("customer_mobile");
  customerFound.value = false;
  clearTimeout(debounceTimer);

  if (props.mode !== "customer") return;

  const mobile = form.value.customer_mobile.trim();
  if (!/^\d{7,15}$/.test(mobile)) return;

  debounceTimer = setTimeout(() => searchCustomer(mobile), 600);
};

// ── Customer search ───────────────────────────────────────────────────────────
const searchCustomer = async (mobile) => {
  try {
    searchLoading.value = true;
    const result = await quickSellStore.searchCustomer(mobile);

    if (result.success && result.customer) {
      const clean = (val) =>
        !val || val.trim().toLowerCase() === "nill" ? "" : val.trim();
      const c = result.customer;
      form.value.customer_name = clean(c.name) || form.value.customer_name;
      form.value.customer_address = clean(c.address) || form.value.customer_address;
      form.value.customer_gstin = clean(c.gstin)
        ? clean(c.gstin).toUpperCase()
        : form.value.customer_gstin;
      customerFound.value = true;
    } else {
      customerFound.value = false;
    }
  } catch {
    customerFound.value = false;
  } finally {
    searchLoading.value = false;
  }
};

// ── Outside-click handler ─────────────────────────────────────────────────────
const onDocumentClick = (e) => {
  if (!props.show || !dialogRef.value) return;
  if (!dialogRef.value.contains(e.target)) handleClose();
};

// ── Reset on open / cleanup on close ─────────────────────────────────────────
watch(
  () => props.show,
  (val) => {
    if (val) {
      form.value = DEFAULT_FORM();
      errors.value = {};
      customerFound.value = false;
      searchLoading.value = false;
      selectedCountry.value = null;
      clearTimeout(debounceTimer);
      setTimeout(() => document.addEventListener("click", onDocumentClick), 100);
    } else {
      clearTimeout(debounceTimer);
      document.removeEventListener("click", onDocumentClick);
    }
  }
);

onUnmounted(() => {
  clearTimeout(debounceTimer);
  document.removeEventListener("click", onDocumentClick);
});

// ── Helpers ───────────────────────────────────────────────────────────────────
const clearError = (field) => {
  if (errors.value[field]) delete errors.value[field];
};

const validate = () => {
  errors.value = {};
  const mobile = form.value.customer_mobile?.trim();
  if (!mobile) {
    errors.value.customer_mobile = t("common.Mobile number is required");
  } else if (!/^\d{7,15}$/.test(mobile)) {
    errors.value.customer_mobile = t("common.Enter a valid mobile number");
  }
  return Object.keys(errors.value).length === 0;
};

// ── Submit ────────────────────────────────────────────────────────────────────
const handleSubmit = () => {
  if (!validate()) return;
  emit("submit", {
    ...form.value,
    country_code: selectedCountry.value?.code ?? "IN",
    dial_code: selectedCountry.value?.dial_code ?? "+91",
  });
};

const handleClose = () => {
  if (props.loading) return;
  clearTimeout(debounceTimer);
  document.removeEventListener("click", onDocumentClick);
  emit("close");
};
</script>

<style scoped>
/* ── Modal fade transition ────────────────────────────────────────────────── */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}
.modal-fade-enter-active .modal-dialog,
.modal-fade-leave-active .modal-dialog {
  transition: transform 0.25s ease, opacity 0.25s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-from .modal-dialog,
.modal-fade-leave-to .modal-dialog {
  transform: translateY(-16px) scale(0.98);
  opacity: 0;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1055;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: rgba(100, 100, 100, 0.25);
}
.modal-dialog {
  background: #fff;
  border-radius: 12px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  overflow: hidden;
  pointer-events: all;
}
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 16px;
  border-bottom: 1px solid #e9ecef;
  flex-shrink: 0;
}
.modal-title {
  font-size: 17px;
  font-weight: 600;
  color: #1a1a2e;
  margin: 0;
}
.modal-close {
  background: none;
  border: none;
  font-size: 18px;
  color: #6c757d;
  cursor: pointer;
  padding: 4px 6px;
  line-height: 1;
  border-radius: 4px;
  transition: color 0.15s, background 0.15s;
}
.modal-close:hover {
  color: #333;
  background: #f1f3f5;
}
.modal-body {
  padding: 20px 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 1;
}
.mobile-input-row {
  display: flex;
  align-items: stretch;
  gap: 8px;
}
.flex-1 {
  flex: 1;
  min-width: 0;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-label {
  font-size: 13px;
  font-weight: 500;
  color: #374151;
}
.form-label.required::after {
  content: " *";
  color: #dc3545;
}
.optional-tag {
  font-size: 11px;
  font-weight: 400;
  color: #9ca3af;
  margin-left: 4px;
}
.input-with-icon {
  position: relative;
  display: flex;
  align-items: center;
}
.input-with-icon .form-control {
  padding-right: 36px;
}
.input-icon {
  position: absolute;
  right: 10px;
  font-size: 16px;
  pointer-events: none;
}
.found-hint {
  font-size: 12px;
  color: #198754;
  display: flex;
  align-items: center;
  gap: 5px;
}
.form-control {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  color: #1f2937;
  background: #fff;
  transition: border-color 0.15s, box-shadow 0.15s;
  outline: none;
  resize: none;
  font-family: inherit;
  pointer-events: all;
}
.form-control:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.12);
}
.form-control.is-invalid {
  border-color: #dc3545;
}
.form-control.is-invalid:focus {
  box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12);
}
.invalid-feedback {
  font-size: 12px;
  color: #dc3545;
}
.d-block {
  display: block;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px 20px;
  border-top: 1px solid #e9ecef;
  flex-shrink: 0;
}
.btn {
  padding: 9px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  border: none;
  display: flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  pointer-events: all;
}
.btn-outline-secondary {
  background: #fff;
  border: 1px solid #dee2e6;
  color: #6c757d;
}
.btn-outline-secondary:hover:not(:disabled) {
  background: #f8f9fa;
  border-color: #adb5bd;
  color: #333;
}
.btn-primary-action {
  background: #0066cc;
  color: #fff;
  border: 1px solid #0066cc;
}
.btn-primary-action:hover:not(:disabled) {
  background: #0052a3;
  border-color: #0052a3;
}
.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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
.text-success {
  color: #198754;
}
.text-muted {
  color: #6c757d;
}

@media (max-width: 480px) {
  .modal-header,
  .modal-footer {
    padding-inline: 16px;
  }
  .modal-body {
    padding: 16px;
  }
  .modal-footer {
    flex-direction: column-reverse;
  }
  .modal-footer .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
