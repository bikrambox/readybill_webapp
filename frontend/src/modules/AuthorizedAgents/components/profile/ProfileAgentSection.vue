<template>
  <div class="section-card">
    <div class="section-heading">
      <div class="section-icon">
        <i class="bi bi-person-badge-fill"></i>
      </div>
      <div>
        <h6 class="section-title">Agent Details</h6>
        <p class="section-sub">Personal information and KYC documents</p>
      </div>
    </div>

    <hr class="section-divider" />

    <div class="row g-3">
      <!-- Full Name -->
      <div class="col-12">
        <label class="form-label field-label">
          <i class="bi bi-person me-1 text-primary"></i> Full Name
          <span class="required-star">*</span>
        </label>
        <input
          type="text"
          class="form-control custom-input"
          :class="{ 'is-invalid': errors.fullName }"
          :value="form.fullName"
          @input="emit('update:form', { fullName: $event.target.value })"
          placeholder="Enter full name"
        />
        <div class="invalid-feedback">{{ errors.fullName }}</div>
      </div>

      <!-- Address -->
      <div class="col-12">
        <label class="form-label field-label">
          <i class="bi bi-geo-alt me-1 text-primary"></i> Address
        </label>
        <textarea
          class="form-control custom-input"
          :value="form.address"
          @input="emit('update:form', { address: $event.target.value })"
          rows="3"
          placeholder="Enter full address"
          style="resize: none"
        ></textarea>
      </div>

      <!-- PAN Number -->
      <div class="col-12">
        <label class="form-label field-label">
          <i class="bi bi-card-text me-1 text-primary"></i> PAN Number
        </label>
        <input
          type="text"
          class="form-control custom-input"
          :class="{ 'is-invalid': errors.pan }"
          :value="form.pan"
          @input="emit('update:form', { pan: $event.target.value.toUpperCase() })"
          placeholder="ABCDE1234F"
          maxlength="10"
          style="text-transform: uppercase; letter-spacing: 2px; font-family: monospace"
        />
        <div class="invalid-feedback">{{ errors.pan }}</div>
      </div>

      <!-- Document Uploads -->
      <div class="col-12 mt-2">
        <div class="uploads-label mb-3">
          <i class="bi bi-paperclip me-1 text-primary"></i>
          <span class="field-label">KYC Documents</span>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <ProfilePhotoUpload
              label="Profile Photo"
              icon="bi-person-circle"
              accept="image/*"
              :preview="photoPreview"
              @fileSelected="onPhotoSelected"
            />
          </div>
          <div class="col-md-4">
            <ProfilePhotoUpload
              label="Aadhaar Card"
              icon="bi-credit-card-2-front"
              accept="image/*,application/pdf"
              :preview="aadharPreview"
              hint="Image or PDF"
              @fileSelected="onAadharSelected"
            />
          </div>
          <div class="col-md-4">
            <ProfilePhotoUpload
              label="UPI QR Code"
              icon="bi-qr-code"
              accept="image/*"
              :preview="upiPreview"
              @fileSelected="onUpiSelected"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import ProfilePhotoUpload from "./ProfilePhotoUpload.vue";

const props = defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
  photoPreviewUrl: { type: String, default: null },
  aadharPreviewUrl: { type: String, default: null },
  upiPreviewUrl: { type: String, default: null },
});

const emit = defineEmits(["update:form", "avatarChanged"]);

const photoPreview = ref(props.photoPreviewUrl);
const aadharPreview = ref(props.aadharPreviewUrl);
const upiPreview = ref(props.upiPreviewUrl);

watch(
  () => props.photoPreviewUrl,
  (url) => (photoPreview.value = url)
);
watch(
  () => props.aadharPreviewUrl,
  (url) => (aadharPreview.value = url)
);
watch(
  () => props.upiPreviewUrl,
  (url) => (upiPreview.value = url)
);

const toPreview = (file, callback) => {
  if (!file || !file.type.startsWith("image/")) return;
  const reader = new FileReader();
  reader.onload = (e) => callback(e.target.result);
  reader.readAsDataURL(file);
};

const onPhotoSelected = (file) => {
  emit("update:form", { photo: file });
  if (file) {
    toPreview(file, (url) => {
      photoPreview.value = url;
      emit("avatarChanged", url);
    });
  } else {
    photoPreview.value = props.photoPreviewUrl;
    emit("avatarChanged", props.photoPreviewUrl);
  }
};

const onAadharSelected = (file) => {
  emit("update:form", { aadhar: file });
  if (file) {
    toPreview(file, (url) => (aadharPreview.value = url));
  } else {
    aadharPreview.value = props.aadharPreviewUrl;
  }
};

const onUpiSelected = (file) => {
  emit("update:form", { upiQr: file });
  if (file) {
    toPreview(file, (url) => (upiPreview.value = url));
  } else {
    upiPreview.value = props.upiPreviewUrl;
  }
};
</script>

<style scoped>
.section-card {
  background: #fff;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
  border: 1px solid #f0f0f0;
}
.section-heading {
  display: flex;
  align-items: center;
  gap: 14px;
}
.section-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #eef2ff;
  color: #0d6efd;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.section-title {
  font-weight: 700;
  font-size: 0.95rem;
  color: #1a1a2e;
  margin: 0;
}
.section-sub {
  font-size: 0.78rem;
  color: #adb5bd;
  margin: 0;
}
.section-divider {
  border-color: #f0f0f0;
  margin: 18px 0;
}
.field-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #495057;
  margin-bottom: 6px;
  display: block;
}
.required-star {
  color: #dc3545;
  margin-left: 2px;
}
.custom-input {
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.9rem;
  border: 1.5px solid #dee2e6;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.custom-input:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}
.uploads-label {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
