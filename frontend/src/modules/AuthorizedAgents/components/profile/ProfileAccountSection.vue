<template>
  <div class="section-card">
    <div class="section-heading">
      <div class="section-icon">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <div>
        <h6 class="section-title">Account Info</h6>
        <p class="section-sub">Manage your login email</p>
      </div>
    </div>

    <hr class="section-divider" />

    <!-- Email -->
    <div class="mb-3">
      <label class="form-label field-label">
        <i class="bi bi-envelope me-1 text-primary"></i> Email Address
        <span class="required-star">*</span>
      </label>
      <input
        type="email"
        class="form-control custom-input"
        :class="{ 'is-invalid': errors.email }"
        :value="form.email"
        @input="emit('update:form', { email: $event.target.value })"
        placeholder="you@example.com"
      />
      <div class="invalid-feedback">{{ errors.email }}</div>
    </div>

    <!-- Mobile -->
    <div class="mb-3">
      <label class="form-label field-label">
        <i class="bi bi-telephone me-1 text-primary"></i> Mobile Number
        {{ dialCode }}
      </label>
      <div class="input-group mobile-group">
        <CountryCodeSelect
          v-model="localCountryCode"
          @change="selectedCountry = $event"
          :disabled="true"
        />
        <input
          type="text"
          class="form-control custom-input bg-light"
          :class="{ 'is-invalid': errors.mobile }"
          :value="form.mobile"
          placeholder="Enter your mobile number"
          readonly
        />
      </div>
      <span v-if="errors.mobile" class="text-danger small mt-1 d-block">
        {{ errors.mobile }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import CountryCodeSelect from "@/modules/Core/components/CountryCodeSelect.vue";

const props = defineProps({
  form: { type: Object, required: true },
  errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["update:form"]);

// Mirrors the same pattern used in SendSmsModal:
// localCountryCode  → v-model string value (e.g. "IN")
// selectedCountry   → full country object from @change (has .code, .dial_code, etc.)

console.log("props", props.form);

const localCountryCode = ref(props.form.dialCode ?? "+91");
const selectedCountry = ref(null);

// Keep localCountryCode in sync when parent resets / updates form
watch(
  () => props.form.countryCode,
  (val) => {
    if (val && val !== localCountryCode.value) {
      localCountryCode.value = val;
    }
  }
);

// const onCountryChange = (country) => {
//   selectedCountry.value = country;
//   emit("update:form", {
//     countryCode: country?.code ?? localCountryCode.value,
//     dialCode: country?.dial_code ?? "+91", // ← send dial_code e.g. "+91"
//     selectedCountry: country,
//   });
// };
</script>

<style scoped>
.section-card {
  background: #fff;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.07);
  border: 1px solid #f0f0f0;
  height: 100%;
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
.mobile-group {
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.mobile-group:focus-within {
  border-color: #0d6efd;
  box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
}
.mobile-group .form-control,
.mobile-group :deep(.country-code-select) {
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}
</style>
