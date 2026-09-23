<script setup>
import { ref, onMounted, watch, computed } from "vue";
import api from "@/config/api";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "change"]);

const countries = ref([]);
const loading = ref(false);
const error = ref(null);
const internalValue = ref(props.modelValue);

// Sync internalValue with modelValue from parent
watch(
  () => props.modelValue,
  (val) => {
    internalValue.value = val;
  }
);

// When internalValue changes, emit update:modelValue and the selected country object
watch(internalValue, (val) => {
  emit("update:modelValue", val);

  // Find the selected country object by dial_code
  const selected = countries.value.find((c) => c.dial_code === val) || null;
  emit("change", selected);
});

// Sort countries by name
const sortedCountries = computed(() =>
  [...countries.value].sort((a, b) => a.name.localeCompare(b.name))
);

onMounted(async () => {
  loading.value = true;
  try {
    // Optionally detect user's country
    const detectedRes = await api.get("/country-code");
    const detectedDialCode = detectedRes.data?.dialCode;

    // Load the list of countries
    const listRes = await api.get("/countries-json");
    countries.value = listRes.data || [];

    // If no value is set and we have a detected dial code, use it
    if (!props.modelValue && detectedDialCode) {
      internalValue.value = detectedDialCode;
    }
  } catch (e) {
    error.value = "Unable to load countries.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="w-28">
    <select
      v-model="internalValue"
      class="w-full border rounded px-2 py-2"
      :class="{ 'disabled-select': disabled }"
      :disabled="loading || disabled"
      style="height: 52px"
    >
      <option value="" disabled>
        {{ loading ? "Loading…" : "Code" }}
      </option>

      <option
        v-for="country in sortedCountries"
        :key="country.code"
        :value="country.dial_code"
        :data-country-code="country.code"
      >
        {{ country.flag }} {{ country.dial_code }}
      </option>
    </select>

    <p v-if="error" class="text-red-500 text-xs mt-1">
      {{ error }}
    </p>
  </div>
</template>

<style scoped>
.disabled-select {
  background-color: #e9ecef;
  cursor: not-allowed;
  /*opacity: 0.6;*/
}
</style>
