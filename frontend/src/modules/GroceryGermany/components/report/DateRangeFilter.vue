<template>
  <div class="row justify-content-center mb-4">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="row g-3">
        <div class="col-6">
          <label for="dateFrom" class="form-label fw-semibold">
            From Date
            <span v-if="localDateFrom === maxDate" class="badge bg-primary ms-2" style="font-size: 0.7rem;">
              <i class="bi bi-calendar-check me-1"></i>Today
            </span>
          </label>
          <input
            type="date"
            id="dateFrom"
            class="form-control date-input-highlight custom-date-picker"
            :class="{ 
              'is-invalid': errors.dateFrom,
              'today-selected': localDateFrom === maxDate
            }"
            v-model="localDateFrom"
            :max="maxDate"
            @input="handleDateInput('from')"
            @change="handleDateChange"
          />
          <div v-if="errors.dateFrom" class="invalid-feedback">
            {{ errors.dateFrom }}
          </div>
        </div>

        <div class="col-6">
          <label for="dateTo" class="form-label fw-semibold">
            To Date
            <span v-if="localDateTo === maxDate" class="badge bg-primary ms-2" style="font-size: 0.7rem;">
              <i class="bi bi-calendar-check me-1"></i>Today
            </span>
          </label>
          <input
            type="date"
            id="dateTo"
            class="form-control date-input-highlight custom-date-picker"
            :class="{ 
              'is-invalid': errors.dateTo,
              'today-selected': localDateTo === maxDate
            }"
            v-model="localDateTo"
            :max="maxDate"
            @input="handleDateInput('to')"
            @change="handleDateChange"
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
import { ref, watch, onMounted } from "vue";

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
const maxDate = ref("");
const errors = ref({
  dateFrom: "",
  dateTo: "",
});

onMounted(() => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  
  maxDate.value = `${year}-${month}-${day}`;
  console.log("✅ Max date set to (today):", maxDate.value);
});

const handleDateInput = (field) => {
  if (field === 'from') {
    errors.value.dateFrom = "";
  } else {
    errors.value.dateTo = "";
  }
};

const handleDateChange = () => {
  errors.value = { dateFrom: "", dateTo: "" };

  if (localDateFrom.value && localDateFrom.value > maxDate.value) {
    errors.value.dateFrom = "From date cannot be in the future";
    localDateFrom.value = "";
    emit("update:dateFrom", "");
    return;
  }

  if (localDateTo.value && localDateTo.value > maxDate.value) {
    errors.value.dateTo = "To date cannot be in the future";
    localDateTo.value = "";
    emit("update:dateTo", "");
    return;
  }

  if (localDateFrom.value && localDateTo.value && localDateFrom.value > localDateTo.value) {
    errors.value.dateFrom = "From date cannot be after To date";
    return;
  }

  emit("update:dateFrom", localDateFrom.value);
  emit("update:dateTo", localDateTo.value);

  if (localDateFrom.value && localDateTo.value) {
    console.log("✅ Both dates selected, emitting date-range-changed", {
      from: localDateFrom.value,
      to: localDateTo.value,
      maxDate: maxDate.value
    });
    emit("date-range-changed");
  }
};

watch(
  () => props.dateFrom,
  (newVal) => {
    if (newVal !== localDateFrom.value) {
      localDateFrom.value = newVal;
    }
  }
);

watch(
  () => props.dateTo,
  (newVal) => {
    if (newVal !== localDateTo.value) {
      localDateTo.value = newVal;
    }
  }
);
</script>

<style scoped>
/* Blue theme for today's date */
.today-selected {
  background: linear-gradient(135deg, #e3f2fd 0%, #ffffff 100%) !important;
  border: 2px solid #2196F3 !important;
  box-shadow: 0 0 0 0.2rem rgba(33, 150, 243, 0.2) !important;
  font-weight: 500;
}

.today-selected:focus {
  border-color: #1976D2 !important;
  box-shadow: 0 0 0 0.3rem rgba(25, 118, 210, 0.3) !important;
}

.date-input-highlight {
  transition: all 0.3s ease;
}

.date-input-highlight:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.badge {
  animation: fadeIn 0.4s ease;
  font-weight: 600;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.date-input-highlight::-webkit-calendar-picker-indicator {
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.date-input-highlight:hover::-webkit-calendar-picker-indicator {
  opacity: 1;
}

.today-selected::-webkit-calendar-picker-indicator {
  filter: invert(35%) sepia(98%) saturate(1654%) hue-rotate(196deg) brightness(95%) contrast(93%);
}
</style>
