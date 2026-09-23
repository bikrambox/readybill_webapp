<template>
  <div>
    <!-- <button class="back-btn" type="button" @click="emit('back')">
      <svg
        width="18"
        height="18"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
      >
        <polyline points="15 18 9 12 15 6"></polyline>
      </svg>
      {{ $t("common.Back") }}
    </button> -->

    <h2 class="auth-title">{{ $t("common.Agent Details") }}</h2>
    <p class="auth-subtitle">{{ $t("common.Tell us a bit more about yourself") }}</p>

    <form @submit.prevent="handleNext">
      <FormErrorBox
        v-if="allErrors.length"
        :messages="allErrors"
        @clear="registerStore.clearErrors"
      />

      <!-- Full Name -->
      <div class="form-group">
        <label for="fullName" class="form-label">{{ $t("common.Full Name") }}</label>
        <input
          id="fullName"
          type="text"
          class="form-control"
          :placeholder="t('common.Enter your full name')"
          v-model="form.fullName"
          autocomplete="off"
        />
      </div>

      <!-- Address -->
      <div class="form-group">
        <label for="address" class="form-label">{{ $t("common.Address") }}</label>
        <textarea
          id="address"
          class="form-control textarea-control"
          :placeholder="t('common.Enter your full address')"
          v-model="form.address"
          rows="3"
          autocomplete="off"
        ></textarea>
      </div>

      <!-- Mobile No. -->
      <div class="form-group">
        <label for="mobile" class="form-label">{{ $t("common.Mobile No.") }}</label>
        <input
          id="mobile"
          type="tel"
          class="form-control"
          :placeholder="t('common.Enter your mobile number')"
          v-model="form.mobile"
          autocomplete="off"
          maxlength="15"
        />
      </div>

      <!-- PAN Number -->
      <div class="form-group">
        <label for="panNumber" class="form-label">{{ $t("common.PAN Number") }}</label>
        <input
          id="panNumber"
          type="text"
          class="form-control"
          :placeholder="t('common.Enter your PAN number')"
          v-model="form.panNumber"
          autocomplete="off"
          maxlength="10"
          @input="form.panNumber = form.panNumber.toUpperCase()"
        />
      </div>

      <button type="submit" class="btn btn-primary btn-block" :disabled="isLoading">
        <span v-if="isLoading">{{ $t("common.Please wait") }}...</span>
        <span v-else>{{ $t("common.Continue") }}</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import FormErrorBox from "@/modules/Core/components/FormErrorBox.vue";
import { useRegisterStore } from "@/modules/AuthorizedAgents/stores/registerStore";

const registerStore = useRegisterStore();

const props = defineProps({
  form: { type: Object, required: true },
  isLoading: { type: Boolean, default: false },
  serverErrors: { type: Array, default: () => [] },
});

const emit = defineEmits(["back", "next"]);

const { t } = useI18n();
const localErrors = ref([]);

const form = reactive({
  fullName: props.form.fullName || "",
  address: props.form.address || "",
  mobile: props.form.mobile || "",
  panNumber: props.form.panNumber || "",
});

// Merge local validation errors + server errors
const allErrors = computed(() => [...localErrors.value, ...props.serverErrors]);

// Clear only local errors
const clearErrors = () => {
  localErrors.value = [];
};

// Auto-scroll to top when new server errors arrive
watch(
  () => props.serverErrors,
  (errs) => {
    if (errs.length) window.scrollTo({ top: 0, behavior: "smooth" });
  }
);

const handleNext = () => {
  localErrors.value = [];
  const errs = [];

  if (!form.fullName.trim()) errs.push(t("common.Full name is required") + ".");

  if (!form.address.trim()) errs.push(t("common.Address is required") + ".");

  if (!form.mobile.trim()) {
    errs.push(t("common.Mobile number is required") + ".");
  } else if (!/^\+?[0-9]{7,15}$/.test(form.mobile.replace(/\s/g, ""))) {
    errs.push(t("common.Please enter a valid mobile number") + ".");
  }

  if (!form.panNumber.trim()) {
    errs.push(t("common.PAN number is required") + ".");
  } else if (!/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(form.panNumber)) {
    errs.push(t("common.valid_pan_validation") + ".");
  }

  if (errs.length) {
    localErrors.value = errs;
    return;
  }

  emit("next", { ...form });
};
</script>

<style scoped>
.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  color: #007bff;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  padding: 0;
  margin-bottom: 20px;
  transition: color 0.2s;
}
.back-btn:hover {
  color: #0056b3;
}

.auth-title {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 12px;
  color: #1a1a1a;
  line-height: 1.3;
}
.auth-subtitle {
  font-size: 15px;
  color: #666;
  margin-bottom: 35px;
  line-height: 1.5;
}
.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 14px;
  font-weight: 500;
  color: #333;
  margin-bottom: 8px;
}
.form-control {
  height: 52px;
  border-radius: 8px;
  border: 1px solid #ddd;
  padding: 14px 16px;
  font-size: 15px;
  width: 100%;
  transition: all 0.3s ease;
}
.textarea-control {
  height: auto;
  resize: vertical;
  min-height: 90px;
}
.form-control::placeholder {
  color: #999;
}
.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
  outline: none;
}
.btn-primary {
  height: 52px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  background-color: #007bff;
  border: none;
  width: 100%;
  margin-top: 8px;
  transition: all 0.3s ease;
}
.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}
.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 767px) {
  .auth-title {
    font-size: 24px;
  }
  .auth-subtitle {
    font-size: 14px;
    margin-bottom: 30px;
  }
  .form-control {
    height: 50px;
    font-size: 16px;
  }
  .btn-primary {
    height: 50px;
    font-size: 15px;
  }
}
@media (max-width: 374px) {
  .auth-title {
    font-size: 22px;
  }
  .form-control {
    height: 48px;
  }
  .btn-primary {
    height: 48px;
  }
}
</style>
