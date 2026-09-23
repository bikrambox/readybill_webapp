<template>
  <div class="row justify-content-center mb-4">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="row g-3">
        <div class="col-6">
          <label for="dateFrom" class="form-label fw-semibold">From Date</label>
          <input
            type="date"
            id="dateFrom"
            class="form-control"
            :class="{ 'is-invalid': errors.dateFrom }"
            v-model="localDateFrom"
            :max="maxFromDate"
            @change="validateAndEmit"
          />
          <div v-if="errors.dateFrom" class="invalid-feedback">
            {{ errors.dateFrom }}
          </div>
        </div>

        <div class="col-6">
          <label for="dateTo" class="form-label fw-semibold">To Date</label>
          <input
            type="date"
            id="dateTo"
            class="form-control"
            :class="{ 'is-invalid': errors.dateTo }"
            v-model="localDateTo"
            :max="maxToDate"
            @change="validateAndEmit"
          />
          <div v-if="errors.dateTo" class="invalid-feedback">
            {{ errors.dateTo }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  dateFrom: String,
  dateTo: String,
});

const emit = defineEmits([
  "update:dateFrom",
  "update:dateTo",
  "date-range-changed",
]);

const localDateFrom = ref(props.dateFrom);
const localDateTo = ref(props.dateTo);
const errors = ref({
  dateFrom: "",
  dateTo: "",
});

const today = computed(() => {
  const now = new Date();
  now.setHours(0, 0, 0, 0);
  return now.toISOString().split("T")[0];
});

const maxFromDate = computed(() => today.value);
const maxToDate = computed(() => today.value);

const validateAndEmit = () => {
  errors.value = { dateFrom: "", dateTo: "" };

  // Only check if dates are future dates
  if (localDateFrom.value && localDateFrom.value > today.value) {
    errors.value.dateFrom = "From date cannot be in the future";
    localDateFrom.value = "";
    return;
  }

  if (localDateTo.value && localDateTo.value > today.value) {
    errors.value.dateTo = "To date cannot be in the future";
    localDateTo.value = "";
    return;
  }

  // Emit valid dates
  if (localDateFrom.value) {
    emit("update:dateFrom", localDateFrom.value);
  }
  if (localDateTo.value) {
    emit("update:dateTo", localDateTo.value);
  }

  // Only emit change event if both dates are selected
  if (localDateFrom.value && localDateTo.value) {
    emit("date-range-changed");
  }
};

watch(
  () => props.dateFrom,
  (newVal) => {
    localDateFrom.value = newVal;
    validateAndEmit();
  }
);

watch(
  () => props.dateTo,
  (newVal) => {
    localDateTo.value = newVal;
    validateAndEmit();
  }
);

// Auto-validate on change
watch(localDateFrom, validateAndEmit);
watch(localDateTo, validateAndEmit);
</script>

