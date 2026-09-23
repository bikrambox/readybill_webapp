<script setup>
import { ref, onMounted, computed, watch, nextTick, onBeforeUnmount } from "vue";
import { useUserPreferencesStore } from "@/modules/GroceryIndia/stores/userPreferences";
import { storeToRefs } from "pinia";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import ResultModal from "@/modules/Core/components/modals/Resultmodal.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

// ─── Result Modal ────────────────────────────────────────────────────────────
const showResultModal = ref(false);
const resultData = ref({ status: "success", message: "", title: "" });

const preferencesStore = useUserPreferencesStore();
const { preferences, loading, errors, saveSuccess } = storeToRefs(preferencesStore);

// ─── Local reactive refs ─────────────────────────────────────────────────────
const preference_mrp = ref(true);
const preference_mrp_invoice = ref(false);
const preference_quantity = ref(false);
const preference_hsn = ref(false);
const preference_hsn_invoice = ref(false);
const preference_invoice_gst_complaint = ref(false);
const preference_purchase_price = ref(false);
const preference_invoice_format = ref(0);
const preference_transaction_mark_as_paid = ref(false);
const preference_barcode = ref(false);
const preference_category = ref(false);
const preference_sku = ref(false);
const gstin = ref("");
const signature = ref(null);
const signaturePreviewUrl = ref(null);

// ─── Sync guard ──────────────────────────────────────────────────────────────
const isSyncing = ref(false);

const invoiceFormatOptions = [
  { label: "A4", value: 0 },
  { label: "80 mm", value: 1 },
  { label: "50 mm", value: 2 },
];

// ─── GST master toggle ───────────────────────────────────────────────────────
const prevHsn = ref(false);
const prevHsnInvoice = ref(false);

watch(preference_invoice_gst_complaint, (isEnabled) => {
  if (isEnabled) {
    prevHsn.value = preference_hsn.value;
    prevHsnInvoice.value = preference_hsn_invoice.value;
    preference_hsn.value = true;
    preference_hsn_invoice.value = true;
  } else {
    preference_hsn.value = prevHsn.value;
    preference_hsn_invoice.value = prevHsnInvoice.value;
  }
});

// ─── Computed ────────────────────────────────────────────────────────────────
const gstLocked = computed(() => preference_invoice_gst_complaint.value);
const gstinMissing = computed(() => gstLocked.value && !gstin.value.trim());
const signatureMissing = computed(
  () => gstLocked.value && !signature.value && !signaturePreviewUrl.value
);

const hasChanges = computed(() => {
  if (!preferences.value) return false;

  return (
    preference_mrp.value !== preferences.value.preference_mrp ||
    preference_quantity.value !== preferences.value.preference_quantity ||
    preference_hsn.value !== preferences.value.preference_hsn ||
    preference_mrp_invoice.value !== preferences.value.preference_mrp_invoice ||
    preference_hsn_invoice.value !== preferences.value.preference_hsn_invoice ||
    preference_invoice_format.value !== preferences.value.preference_invoice_format ||
    preference_transaction_mark_as_paid.value !==
      preferences.value.preference_transaction_mark_as_paid ||
    preference_barcode.value !== preferences.value.preference_barcode ||
    preference_invoice_gst_complaint.value !==
      preferences.value.preference_invoice_gst_complaint ||
    preference_purchase_price.value !== preferences.value.preference_purchase_price ||
    preference_category.value !== preferences.value.preference_category ||
    preference_sku.value !== preferences.value.preference_sku ||
    gstin.value !== (preferences.value.gstin ?? "") ||
    signature.value !== null
  );
});

// ─── Sync local state ────────────────────────────────────────────────────────
const syncLocalState = () => {
  if (!preferences.value) return;

  preference_mrp.value = preferences.value.preference_mrp;
  preference_quantity.value = preferences.value.preference_quantity;
  preference_hsn.value = preferences.value.preference_hsn;
  preference_mrp_invoice.value = preferences.value.preference_mrp_invoice;
  preference_hsn_invoice.value = preferences.value.preference_hsn_invoice;
  preference_invoice_format.value = preferences.value.preference_invoice_format;
  preference_transaction_mark_as_paid.value =
    preferences.value.preference_transaction_mark_as_paid;
  preference_barcode.value = preferences.value.preference_barcode;
  preference_invoice_gst_complaint.value =
    preferences.value.preference_invoice_gst_complaint ?? false;
  gstin.value = preferences.value.gstin ?? "";
  preference_purchase_price.value = preferences.value.preference_purchase_price;
  preference_category.value = preferences.value.preference_category;
  preference_sku.value = preferences.value.preference_sku;
  if (preferences.value.signature) {
    signaturePreviewUrl.value = `${import.meta.env.VITE_MEDIA_URL}/shop/signature/${
      preferences.value.signature
    }`;
  } else if (!signaturePreviewUrl.value) {
    signaturePreviewUrl.value = null;
  }
};

onMounted(async () => {
  await preferencesStore.fetchUserPreferences();
  syncLocalState();
});

watch(
  preferences,
  () => {
    if (isSyncing.value) return;
    syncLocalState();
  },
  { deep: true }
);

watch(saveSuccess, (newVal) => {
  if (newVal) {
    setTimeout(() => {
      preferencesStore.resetSaveSuccess();
    }, 3000);
  }
});

const revokeIfBlobUrl = (url) => {
  if (url && typeof url === "string" && url.startsWith("blob:")) {
    URL.revokeObjectURL(url);
  }
};

// ─── Signature handlers ───────────────────────────────────────────────────────
const handleSignatureUpload = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  revokeIfBlobUrl(signaturePreviewUrl.value);

  isSyncing.value = true;
  signature.value = file;
  signaturePreviewUrl.value = URL.createObjectURL(file);

  nextTick(() => {
    isSyncing.value = false;
  });
};

const triggerSignatureUpload = () => {
  document.getElementById("signatureInput")?.click();
};

const removeSignature = () => {
  isSyncing.value = true;

  revokeIfBlobUrl(signaturePreviewUrl.value);

  signature.value = null;
  signaturePreviewUrl.value = null;

  const input = document.getElementById("signatureInput");
  if (input) input.value = "";

  nextTick(() => {
    isSyncing.value = false;
  });
};

// ─── Drag & drop ─────────────────────────────────────────────────────────────
const isDragOver = ref(false);

const handleDragOver = (e) => {
  e.preventDefault();
  isDragOver.value = true;
};

const handleDragLeave = () => {
  isDragOver.value = false;
};

const handleDrop = (e) => {
  e.preventDefault();
  isDragOver.value = false;

  const file = e.dataTransfer?.files?.[0];
  if (!file || !file.type.startsWith("image/")) return;

  revokeIfBlobUrl(signaturePreviewUrl.value);

  isSyncing.value = true;
  signature.value = file;
  signaturePreviewUrl.value = URL.createObjectURL(file);

  nextTick(() => {
    isSyncing.value = false;
  });
};

// ─── Modal helpers ────────────────────────────────────────────────────────────
const showResult = (status, message, title = "") => {
  resultData.value = { status, message, title };
  showResultModal.value = true;
};

const closeResultModal = () => {
  showResultModal.value = false;
};

// ─── Save ────────────────────────────────────────────────────────────────────
const saveSettings = async () => {
  if (gstLocked.value && (gstinMissing.value || signatureMissing.value)) {
    showResult("error", t("common.fill_mandatory_gst_fields"), t("common.Incomplete"));
    return;
  }

  const result = await preferencesStore.updatePreferences({
    preference_mrp: preference_mrp.value,
    preference_quantity: preference_quantity.value,
    preference_hsn: preference_hsn.value,
    preference_mrp_invoice: preference_mrp_invoice.value,
    preference_hsn_invoice: preference_hsn_invoice.value,
    preference_invoice_format: preference_invoice_format.value,
    preference_transaction_mark_as_paid: preference_transaction_mark_as_paid.value,
    preference_barcode: preference_barcode.value,
    preference_invoice_gst_complaint: preference_invoice_gst_complaint.value,
    preference_purchase_price: preference_purchase_price.value,
    preference_category: preference_category.value,
    preference_sku: preference_sku.value,
    gstin: gstin.value,
    signature: signature.value,
  });

  if (result.success) {
    showResult(
      "success",
      result.message || t("common.Settings saved successfully!"),
      t("common.Success!")
    );
    signature.value = null;
  } else {
    console.error("Failed to save settings");
  }
};

const handleClearErrors = () => {
  preferencesStore.clearErrors();
};

onBeforeUnmount(() => {
  revokeIfBlobUrl(signaturePreviewUrl.value);
});
</script>

<template>
  <div class="settings-container">
    <div class="container-fluid py-4 px-2 px-sm-3 px-md-4">
      <!-- Breadcrumb -->
      <div class="mb-4">
        <h2 class="fw-bold mb-1 page-title">{{ $t("common.Settings") }}</h2>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 flex-wrap">
            <li class="breadcrumb-item">
              <a href="sell" class="text-decoration-none">{{ $t("common.Home") }}</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              {{ $t("common.Settings") }}
            </li>
          </ol>
        </nav>
      </div>

      <!-- Loading -->
      <div v-if="loading && !preferences" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">{{ $t("common.Loading") }}...</span>
        </div>
        <p class="mt-2 text-muted">{{ $t("common.Loading preferences") }}...</p>
      </div>

      <!-- Errors -->
      <div v-if="errors.length > 0" class="mb-3">
        <FormErrorBox :messages="errors" @clear="handleClearErrors" />
      </div>

      <!-- Result Modal -->
      <ResultModal
        :show="showResultModal"
        :status="resultData.status"
        :message="resultData.message"
        :title="resultData.title"
        @close="closeResultModal"
      />

      <!-- Main Card -->
      <div
        v-if="!loading || preferences"
        class="card border-0 shadow-sm mb-3 settings-card"
      >
        <div class="card-body p-3 p-sm-4">
          <!-- Inventory Management -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">
              {{ $t("common.Inventory Management") }}
            </h6>

            <div class="toggle-row mb-3">
              <div class="toggle-content">
                <div class="fw-semibold">
                  {{ $t("common.Maintain MRP") }}
                  ({{ $t("common.Maximum Retail Price") }})
                </div>
                <p class="text-muted small mb-0">
                  {{
                    $t("common.Track and manage maximum retail prices for your products")
                  }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
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

            <div class="toggle-row mb-3">
              <div class="toggle-content">
                <div class="fw-semibold">
                  {{ $t("common.Show MRP in invoices") }}
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Display MRP on customer invoices") }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
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

            <div class="toggle-row mb-3" :class="{ 'locked-row': gstLocked }">
              <div class="toggle-content">
                <div
                  class="fw-semibold d-flex flex-wrap align-items-center gap-1 gap-sm-2"
                >
                  <span>{{ $t("common.Use HSN/SAC codes") }}</span>
                  <span v-if="gstLocked" class="required-star">*</span>
                  <span v-if="gstLocked" class="locked-badge">
                    <i class="bi bi-lock-fill me-1"></i>{{ $t("common.Required") }}
                  </span>
                </div>
                <p class="text-muted small mb-0">
                  {{
                    $t(
                      "common.Include HSN (Harmonized System of Nomenclature) or SAC codes for tax compliance"
                    )
                  }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_hsn"
                    :disabled="loading || gstLocked"
                  />
                </div>
              </div>
            </div>

            <div class="toggle-row mb-4" :class="{ 'locked-row': gstLocked }">
              <div class="toggle-content">
                <div
                  class="fw-semibold d-flex flex-wrap align-items-center gap-1 gap-sm-2"
                >
                  <span>{{ $t("common.Show HSN/SAC codes in invoices") }}</span>
                  <span v-if="gstLocked" class="required-star">*</span>
                  <span v-if="gstLocked" class="locked-badge">
                    <i class="bi bi-lock-fill me-1"></i>{{ $t("common.Required") }}
                  </span>
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Display HSN/SAC codes on customer invoices") }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_hsn_invoice"
                    :disabled="loading || gstLocked"
                  />
                </div>
              </div>
            </div>

            <div class="toggle-row">
              <div class="toggle-content">
                <div class="fw-semibold">
                  {{ $t("common.Maintain Stock Levels") }}
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Track inventory quantities and stock movements") }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
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

            <div class="toggle-row">
              <div class="toggle-content">
                <div class="fw-semibold">
                  {{ $t("common.Maintain Purchase Price") }}
                </div>
                <p class="text-muted small mb-0">
                  {{
                    $t(
                      "common.Maintaining purchase price will help you in evaluating your Profit & Loss"
                    )
                  }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_purchase_price"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>

            <div class="toggle-row">
              <div class="toggle-content">
                <div class="fw-semibold">
                  {{ $t("common.Maintain Category") }}
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Category management helps organize items better") }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_category"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>

            <div class="toggle-row">
              <div class="toggle-content">
                <div class="fw-semibold">
                  {{ $t("common.Maintain SKU") }}
                </div>
                <p class="text-muted small mb-0">
                  {{
                    $t(
                      "common.Maintaining SKU helps in proper identification and tracking of items"
                    )
                  }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_sku"
                    :disabled="loading"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- GST Compliance -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">
              {{ $t("common.GST Compliance") }}
            </h6>

            <div class="gst-main-toggle mb-4">
              <div class="toggle-row">
                <div class="toggle-content">
                  <div class="fw-semibold">
                    {{ $t("common.Make all invoices GST Compliant") }}
                  </div>
                  <p class="text-muted small mb-0">
                    {{ $t("common.gst_complaince_sub_heading") }}
                  </p>
                </div>
                <div class="toggle-switch">
                  <div class="form-check form-switch m-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      role="switch"
                      v-model="preference_invoice_gst_complaint"
                      :disabled="loading"
                    />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="gstLocked" class="mandatory-banner mb-4">
              <i class="bi bi-info-circle-fill me-2"></i>
              <span>{{ $t("common.gst_complaince_mandatory_note") }}</span>
            </div>

            <!-- <div class="toggle-row mb-3" :class="{ 'locked-row': gstLocked }">
              <div class="toggle-content">
                <div
                  class="fw-semibold d-flex flex-wrap align-items-center gap-1 gap-sm-2"
                >
                  <span>{{ $t("common.Use HSN/SAC codes") }}</span>
                  <span v-if="gstLocked" class="required-star">*</span>
                  <span v-if="gstLocked" class="locked-badge">
                    <i class="bi bi-lock-fill me-1"></i>{{ $t("common.Required") }}
                  </span>
                </div>
                <p class="text-muted small mb-0">
                  {{
                    $t(
                      "common.Include HSN (Harmonized System of Nomenclature) or SAC codes for tax compliance"
                    )
                  }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_hsn"
                    :disabled="loading || gstLocked"
                  />
                </div>
              </div>
            </div>

            <div class="toggle-row mb-4" :class="{ 'locked-row': gstLocked }">
              <div class="toggle-content">
                <div
                  class="fw-semibold d-flex flex-wrap align-items-center gap-1 gap-sm-2"
                >
                  <span>{{ $t("common.Show HSN/SAC codes in invoices") }}</span>
                  <span v-if="gstLocked" class="required-star">*</span>
                  <span v-if="gstLocked" class="locked-badge">
                    <i class="bi bi-lock-fill me-1"></i>{{ $t("common.Required") }}
                  </span>
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Display HSN/SAC codes on customer invoices") }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_hsn_invoice"
                    :disabled="loading || gstLocked"
                  />
                </div>
              </div>
            </div> -->

            <hr class="gst-divider" />

            <div class="mb-4">
              <label class="fw-semibold mb-1 d-block" for="gstinField">
                <span>{{ $t("common.Enter your GSTIN") }}</span>
                <span v-if="gstLocked" class="required-star">*</span>
                <span v-if="gstLocked" class="locked-badge ms-2 mt-1 mt-sm-0">
                  <i class="bi bi-lock-fill me-1"></i>{{ $t("common.Required") }}
                </span>
              </label>
              <p class="text-muted small mb-2">
                {{ $t("common.GSTIN is mandatory for all GST Bills") }}
              </p>
              <input
                id="gstinField"
                v-model="gstin"
                type="text"
                class="form-control gstin-input"
                :class="{ 'is-invalid': gstinMissing }"
                maxlength="15"
                placeholder="e.g. 22AAAAA0000A1Z5"
                :disabled="loading"
              />
              <div v-if="gstinMissing" class="invalid-feedback d-block">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $t("common.GSTIN is required when GST Compliance is enabled") }}.
              </div>
            </div>

            <div>
              <div
                class="fw-semibold mb-1 d-flex flex-wrap align-items-center gap-1 gap-sm-2"
              >
                <span>{{ $t("common.Upload your signature") }}</span>
                <span v-if="gstLocked" class="required-star">*</span>
                <span v-if="gstLocked" class="locked-badge">
                  <i class="bi bi-lock-fill me-1"></i>{{ $t("common.Required") }}
                </span>
              </div>
              <p class="text-muted small mb-3">
                {{ $t("common.signature_upload_note") }}
              </p>

              <input
                type="file"
                id="signatureInput"
                accept="image/*"
                class="d-none"
                @change="handleSignatureUpload"
              />

              <div
                v-if="!signaturePreviewUrl"
                class="signature-dropzone"
                :class="{
                  'dropzone-dragover': isDragOver,
                  'dropzone-error': signatureMissing,
                }"
                @click="triggerSignatureUpload"
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
                @drop="handleDrop"
              >
                <div class="dropzone-icon">
                  <i class="bi bi-pen-fill"></i>
                </div>
                <div class="dropzone-text">
                  <span class="dropzone-primary">{{ $t("common.Click to upload") }}</span>
                  {{ $t("common.or drag and drop") }}
                </div>
                <div class="dropzone-hint">PNG, JPG up to 2MB</div>
              </div>

              <div v-else class="signature-card">
                <div class="signature-card-image">
                  <img
                    :src="signaturePreviewUrl"
                    alt="Signature preview"
                    loading="lazy"
                  />
                </div>

                <div class="signature-card-body">
                  <div class="signature-card-label">
                    <i class="bi bi-patch-check-fill text-success me-1"></i>
                    {{ $t("common.Signature uploaded") }}
                  </div>
                  <p class="signature-card-hint mb-0">
                    {{ $t("common.This signature will appear on all GST invoices") }}.
                  </p>

                  <div class="signature-card-actions mt-3">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-primary action-btn"
                      @click="triggerSignatureUpload"
                      :disabled="loading"
                    >
                      <i class="bi bi-arrow-repeat me-1"></i>
                      {{ $t("common.Change") }}
                    </button>

                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger action-btn"
                      @click="removeSignature"
                      :disabled="loading"
                    >
                      <i class="bi bi-trash3 me-1"></i>
                      {{ $t("common.Remove") }}
                    </button>
                  </div>
                </div>
              </div>

              <div v-if="signatureMissing" class="invalid-feedback d-block mt-2">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $t("common.Signature is required when GST Compliance is enabled") }}.
              </div>
            </div>
          </div>

          <!-- Invoice and Printing -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">
              {{ $t("common.Invoice and Printing") }}
            </h6>
            <div class="row align-items-center gy-3">
              <div class="col-12 col-md-6">
                <div class="fw-semibold">
                  {{ $t("common.Invoice paper size") }}
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Choose the paper size for printing invoices") }}
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
          </div>

          <!-- Payment Processing -->
          <div class="settings-section mb-4">
            <h6 class="section-heading mb-3">
              {{ $t("common.Payment Processing") }}
            </h6>
            <div class="toggle-row">
              <div class="toggle-content">
                <div class="fw-semibold mb-1">
                  {{
                    $t(
                      "common.Automatically mark transactions as paid when saved or printed"
                    )
                  }}
                </div>
                <p class="text-muted small mb-0">
                  {{
                    $t(
                      "common.If this option is disabled, then only the admin/ owner can mark transactions as paid"
                    )
                  }}.
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
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
          </div>
        </div>
      </div>

      <!-- Barcode Card -->
      <div
        v-if="!loading || preferences"
        class="card border-0 shadow-sm mb-3 settings-card"
      >
        <div class="card-body p-3 p-sm-4">
          <div class="settings-section">
            <h6 class="section-heading mb-3">{{ $t("common.Barcode") }}</h6>
            <div class="toggle-row">
              <div class="toggle-content">
                <div class="fw-semibold mb-1">
                  {{ $t("common.Enable Bar Code Scanning") }}
                </div>
                <p class="text-muted small mb-0">
                  {{ $t("common.Enable barcode scanning functionality for products") }}
                </p>
              </div>
              <div class="toggle-switch">
                <div class="form-check form-switch m-0">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    v-model="preference_barcode"
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
              {{ loading ? $t("common.Saving") + "..." : $t("common.Save Changes") }}
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
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

.settings-card {
  width: 100%;
  max-width: 100%;
  overflow: hidden;
}

.page-title {
  word-break: break-word;
}

.settings-section {
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 1.5rem;
  width: 100%;
  max-width: 100%;
  overflow: hidden;
}

.section-heading {
  color: #6c757d;
  font-weight: 600;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.toggle-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: start;
  column-gap: 1rem;
  row-gap: 0.5rem;
  width: 100%;
  max-width: 100%;
}

.toggle-content {
  min-width: 0;
  max-width: 100%;
}

.toggle-switch {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
  min-width: 3.25rem;
  padding-top: 0.125rem;
}

.gst-main-toggle {
  border: 1.5px solid #dee2e6;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  background-color: #fff;
  width: 100%;
  max-width: 100%;
  overflow: hidden;
}

.mandatory-banner {
  display: flex;
  align-items: flex-start;
  flex-wrap: wrap;
  background-color: #fff3cd;
  border: 1px solid #ffc107;
  border-left: 4px solid #ffc107;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-size: 0.85rem;
  color: #664d03;
  gap: 0.25rem;
  width: 100%;
  max-width: 100%;
}

.locked-row {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  border: 1px solid #e9ecef;
  transition: background-color 0.2s;
  width: 100%;
  max-width: 100%;
}

.required-star {
  color: #dc3545;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1;
}

.locked-badge {
  display: inline-flex;
  align-items: center;
  font-size: 0.7rem;
  font-weight: 600;
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffe08a;
  border-radius: 20px;
  padding: 1px 8px;
  vertical-align: middle;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
  max-width: 100%;
}

.gst-divider {
  border-color: #dee2e6;
  opacity: 1;
  margin-bottom: 1.25rem;
}

.gstin-input {
  border-radius: 8px;
  border: 1px solid #ced4da;
  font-size: 0.9rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  transition: border-color 0.18s, box-shadow 0.18s;
  width: 100%;
  max-width: 100%;
}

.gstin-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
  outline: none;
}

.gstin-input.is-invalid {
  border-color: #dc3545;
}

.gstin-input.is-invalid:focus {
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.2);
}

.signature-dropzone {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  border: 2px dashed #c8d0da;
  border-radius: 14px;
  padding: 2rem 1.25rem;
  background: #f8fafd;
  cursor: pointer;
  transition: border-color 0.2s, background-color 0.2s, box-shadow 0.2s;
  text-align: center;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
  overflow: hidden;
}

.signature-dropzone:hover {
  border-color: #0d6efd;
  background: #f0f5ff;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.07);
}

.dropzone-dragover {
  border-color: #0d6efd !important;
  background: #e8f0fe !important;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12) !important;
}

.dropzone-error {
  border-color: #dc3545 !important;
  background: #fff5f5 !important;
}

.dropzone-icon {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: linear-gradient(135deg, #e8f0fe 0%, #dbeafe 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.25rem;
  flex-shrink: 0;
}

.dropzone-icon i {
  font-size: 1.4rem;
  color: #0d6efd;
}

.dropzone-text {
  font-size: 0.88rem;
  color: #495057;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.dropzone-primary {
  color: #0d6efd;
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 2px;
}

.dropzone-hint {
  font-size: 0.78rem;
  color: #adb5bd;
}

.signature-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  border: 1.5px solid #d1e7dd;
  border-radius: 14px;
  padding: 1rem 1.25rem;
  background: linear-gradient(135deg, #f0fdf4 0%, #f8fffe 100%);
  transition: box-shadow 0.2s;
  width: 100%;
  max-width: 100%;
  min-width: 0;
  box-sizing: border-box;
  overflow: hidden;
}

.signature-card:hover {
  box-shadow: 0 2px 12px rgba(25, 135, 84, 0.1);
}

.signature-card-image {
  flex: 0 0 110px;
  width: 110px;
  height: 70px;
  min-width: 110px;
  border-radius: 10px;
  border: 1px solid #c3e6cb;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
}

.signature-card-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
}

.signature-card-body {
  flex: 1 1 auto;
  min-width: 0;
  max-width: 100%;
  overflow: hidden;
}

.signature-card-label {
  font-weight: 600;
  font-size: 0.92rem;
  color: #198754;
  margin-bottom: 0.2rem;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.signature-card-hint {
  font-size: 0.8rem;
  color: #6c757d;
  line-height: 1.4;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.signature-card-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  width: 100%;
  max-width: 100%;
  min-width: 0;
}

.action-btn {
  min-width: 0;
  max-width: 100%;
}

.form-check-input {
  width: 3rem;
  height: 1.5rem;
  margin-left: 0 !important;
  cursor: pointer;
  flex-shrink: 0;
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

.locked-row .form-check-input:disabled:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
  opacity: 0.75;
}

.invalid-feedback {
  font-size: 0.82rem;
  color: #dc3545;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.form-select,
.form-control {
  width: 100%;
  min-width: 0;
  max-width: 100%;
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
  min-height: 44px;
  max-width: 100%;
}

.save-btn:hover:not(:disabled) {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 991.98px) {
  .settings-section {
    padding: 1.25rem;
  }
}

@media (max-width: 767.98px) {
  .settings-section {
    padding: 1rem;
  }

  .gst-main-toggle {
    padding: 0.9rem 1rem;
  }

  .toggle-row {
    grid-template-columns: minmax(0, 1fr) 3.25rem;
    column-gap: 0.75rem;
  }

  .toggle-switch {
    justify-content: flex-end;
    align-self: start;
  }

  .locked-row {
    padding: 0.85rem;
  }

  .mandatory-banner {
    padding: 0.75rem;
  }

  .signature-card {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
    padding: 1rem;
  }

  .signature-card-image {
    flex: 0 0 auto;
    width: 100%;
    min-width: 0;
    height: 96px;
  }

  .signature-card-body {
    width: 100%;
    min-width: 0;
  }

  .signature-card-actions {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
    width: 100%;
  }

  .action-btn {
    width: 100%;
    margin: 0;
    min-width: 0;
    white-space: normal;
    text-align: center;
  }

  .save-btn {
    width: 100%;
  }
}

@media (max-width: 575.98px) {
  .page-title {
    font-size: 1.35rem;
  }

  .settings-section {
    padding: 0.9rem;
    border-radius: 10px;
  }

  .gst-main-toggle {
    border-radius: 10px;
    padding: 0.85rem 0.9rem;
  }

  .locked-row,
  .mandatory-banner {
    padding: 0.75rem;
  }

  .signature-dropzone {
    padding: 1.5rem 1rem;
  }

  .dropzone-icon {
    width: 46px;
    height: 46px;
  }

  .dropzone-icon i {
    font-size: 1.2rem;
  }

  .signature-card {
    padding: 0.9rem;
  }

  .signature-card-image {
    height: 88px;
  }

  .locked-badge {
    white-space: normal;
  }

  .action-btn {
    font-size: 0.82rem;
    padding: 0.5rem 0.75rem;
  }

  .save-btn {
    padding: 0.65rem 1rem;
  }
}

@media (max-width: 360px) {
  .section-heading {
    font-size: 0.72rem;
  }

  .dropzone-text,
  .signature-card-hint,
  .invalid-feedback {
    font-size: 0.78rem;
  }

  .toggle-row {
    grid-template-columns: minmax(0, 1fr) 3rem;
  }

  .form-check-input {
    width: 2.75rem;
    height: 1.4rem;
  }
}
</style>
