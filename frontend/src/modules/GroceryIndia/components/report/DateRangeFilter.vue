<template>
  <div class="date-range-filter mb-4">
    <!-- ─── Preset Buttons ─── -->
    <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
      <button
        v-for="preset in presets"
        :key="preset.label"
        type="button"
        class="btn btn-sm preset-btn"
        :class="activePreset === preset.label ? 'btn-primary' : 'btn-outline-secondary'"
        @click="applyPreset(preset)"
      >
        <i :class="preset.icon + ' me-1'"></i>{{ preset.label }}
      </button>
    </div>

    <!-- ─── Input Trigger Row ─── -->
    <div class="row g-2 justify-content-center">
      <div class="col-12 col-sm-5">
        <div
          class="range-input-box"
          :class="{ 'range-input-box--active': showCalendar === 'from' }"
          @click="openCalendar('from')"
        >
          <div class="range-input-box__label">
            <i class="bi bi-calendar-event text-primary me-1"></i
            >{{ $t("common.From Date") }}
          </div>
          <div class="range-input-box__value">
            {{ localDateFrom ? formatDisplay(localDateFrom) : $t("common.Select date") }}
          </div>
        </div>
        <div v-if="errors.dateFrom" class="text-danger small mt-1">
          <i class="bi bi-exclamation-circle me-1"></i>{{ errors.dateFrom }}
        </div>
      </div>

      <div class="col-12 col-sm-2 d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-right fs-5 text-muted d-none d-sm-block"></i>
        <i class="bi bi-arrow-down fs-5 text-muted d-block d-sm-none"></i>
      </div>

      <div class="col-12 col-sm-5">
        <div
          class="range-input-box"
          :class="{ 'range-input-box--active': showCalendar === 'to' }"
          @click="openCalendar('to')"
        >
          <div class="range-input-box__label">
            <i class="bi bi-calendar-check text-success me-1"></i
            >{{ $t("common.To Date") }}
            <span
              v-if="localDateTo === maxDate"
              class="badge bg-success ms-2"
              style="font-size: 0.65rem"
              >Today</span
            >
          </div>
          <div class="range-input-box__value">
            {{ localDateTo ? formatDisplay(localDateTo) : $t("common.Select date") }}
          </div>
        </div>
        <div v-if="errors.dateTo" class="text-danger small mt-1">
          <i class="bi bi-exclamation-circle me-1"></i>{{ errors.dateTo }}
        </div>
      </div>
    </div>

    <!-- ─── Calendar Popup ─── -->
    <Transition name="cal-fade">
      <div v-if="showCalendar" class="calendar-popup-overlay" @click.self="closeCalendar">
        <div class="calendar-popup">
          <!-- Header -->
          <div class="cal-header">
            <div class="cal-header__title">
              <i
                class="bi me-2"
                :class="
                  showCalendar === 'from' ? 'bi-calendar-event' : 'bi-calendar-check'
                "
              ></i>
              {{
                showCalendar === "from"
                  ? $t("common.Select From Date")
                  : $t("common.Select To Date")
              }}
            </div>
            <button class="cal-close-btn" @click="closeCalendar">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <!-- Month/Year Navigation -->
          <div class="cal-nav">
            <button class="cal-nav__btn" @click="prevMonth">
              <i class="bi bi-chevron-left"></i>
            </button>
            <div class="cal-nav__center">
              <select v-model.number="calMonth" class="cal-select" @change="clampCalDay">
                <option v-for="(m, i) in monthNames" :key="i" :value="i">{{ m }}</option>
              </select>
              <select
                v-model.number="calYear"
                class="cal-select cal-select--year"
                @change="clampCalDay"
              >
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
            <button
              class="cal-nav__btn"
              @click="nextMonth"
              :disabled="isNextMonthDisabled"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </div>

          <!-- Day Names -->
          <div class="cal-grid cal-grid--header">
            <div v-for="d in dayNames" :key="d" class="cal-cell cal-cell--dayname">
              {{ d }}
            </div>
          </div>

          <!-- Days Grid -->
          <div class="cal-grid">
            <div
              v-for="(cell, idx) in calendarCells"
              :key="idx"
              class="cal-cell"
              :class="getCellClass(cell)"
              @click="cell.day && selectDay(cell.day)"
            >
              <span v-if="cell.day" class="cal-cell__inner">
                {{ cell.day }}
                <span v-if="isTodayCell(cell)" class="cal-cell__today-dot"></span>
              </span>
            </div>
          </div>

          <!-- Footer -->
          <div class="cal-footer">
            <button class="btn btn-sm btn-light border" @click="selectToday">
              <i class="bi bi-calendar-day me-1"></i>{{ $t("common.Today") }}
            </button>
            <button class="btn btn-sm btn-light border" @click="clearField">
              <i class="bi bi-x-circle me-1"></i>{{ $t("Clear") }}
            </button>
            <button class="btn btn-sm btn-primary ms-auto" @click="closeCalendar">
              <i class="bi bi-check2 me-1"></i>{{ $t("common.Done") }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ─── Range Summary ─── -->
    <Transition name="fade">
      <div v-if="localDateFrom && localDateTo && !hasErrors" class="text-center mt-3">
        <span class="range-summary-badge">
          <i class="bi bi-calendar-range me-2 text-primary"></i>
          <strong>{{ formatDisplay(localDateFrom) }}</strong>
          <span class="mx-2 text-muted">→</span>
          <strong>{{ formatDisplay(localDateTo) }}</strong>
          <span class="ms-2 text-muted small"
            >({{ dayDiff }} day{{ dayDiff !== 1 ? "s" : "" }})</span
          >
        </span>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
  dateFrom: String,
  dateTo: String,
});
const emit = defineEmits(["update:dateFrom", "update:dateTo", "date-range-changed"]);

// ─── Helpers ───────────────────────────────────────────────────
const pad = (n) => String(n).padStart(2, "0");
const toStr = (y, m, d) => `${y}-${pad(m)}-${pad(d)}`;
const todayObj = () => {
  const n = new Date();
  return { y: n.getFullYear(), m: n.getMonth(), d: n.getDate() };
};
const t = todayObj();
const maxDate = toStr(t.y, t.m + 1, t.d);

const monthNames = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];
const dayNames = ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"];

const formatDisplay = (str) => {
  if (!str) return "";
  const [y, m, d] = str.split("-").map(Number);
  return `${monthNames[m - 1].slice(0, 3)} ${d}, ${y}`;
};

const daysInMonth = (y, m) => new Date(y, m + 1, 0).getDate(); // m is 0-based

// ─── State ─────────────────────────────────────────────────────
const localDateFrom = ref(props.dateFrom || toStr(t.y, t.m + 1, 1));
const localDateTo = ref(props.dateTo || maxDate);
const errors = ref({ dateFrom: "", dateTo: "" });
const activePreset = ref("This Month");
const showCalendar = ref(""); // 'from' | 'to' | ''

// Calendar navigation state
const calMonth = ref(t.m);
const calYear = ref(t.y);

const years = computed(() => {
  const arr = [];
  for (let y = t.y; y >= t.y - 10; y--) arr.push(y);
  return arr;
});

// ─── Calendar Cells ────────────────────────────────────────────
const calendarCells = computed(() => {
  const firstDay = new Date(calYear.value, calMonth.value, 1).getDay();
  const total = daysInMonth(calYear.value, calMonth.value);
  const cells = [];
  for (let i = 0; i < firstDay; i++) cells.push({ day: null });
  for (let d = 1; d <= total; d++) cells.push({ day: d });
  while (cells.length % 7 !== 0) cells.push({ day: null });
  return cells;
});

const isNextMonthDisabled = computed(() => {
  return calYear.value === t.y && calMonth.value >= t.m;
});

// ─── Cell Classes ──────────────────────────────────────────────
const getCellClass = (cell) => {
  if (!cell.day) return "cal-cell--empty";
  const str = toStr(calYear.value, calMonth.value + 1, cell.day);
  const classes = [];

  if (str > maxDate) classes.push("cal-cell--disabled");
  else classes.push("cal-cell--active");

  if (str === localDateFrom.value) classes.push("cal-cell--from");
  if (str === localDateTo.value) classes.push("cal-cell--to");
  if (
    localDateFrom.value &&
    localDateTo.value &&
    str > localDateFrom.value &&
    str < localDateTo.value
  )
    classes.push("cal-cell--in-range");
  if (str === maxDate) classes.push("cal-cell--today");

  return classes;
};

const isTodayCell = (cell) => {
  if (!cell.day) return false;
  return toStr(calYear.value, calMonth.value + 1, cell.day) === maxDate;
};

// ─── Navigation ────────────────────────────────────────────────
const prevMonth = () => {
  if (calMonth.value === 0) {
    calMonth.value = 11;
    calYear.value--;
  } else calMonth.value--;
};
const nextMonth = () => {
  if (isNextMonthDisabled.value) return;
  if (calMonth.value === 11) {
    calMonth.value = 0;
    calYear.value++;
  } else calMonth.value++;
};
const clampCalDay = () => {}; // selects trigger recompute automatically

// ─── Open / Close ──────────────────────────────────────────────
const openCalendar = (field) => {
  showCalendar.value = field;
  // Navigate calendar to the currently selected date
  const str = field === "from" ? localDateFrom.value : localDateTo.value;
  if (str) {
    const [y, m] = str.split("-").map(Number);
    calYear.value = y;
    calMonth.value = m - 1;
  } else {
    calYear.value = t.y;
    calMonth.value = t.m;
  }
};
const closeCalendar = () => {
  showCalendar.value = "";
};

// ─── Day Selection ─────────────────────────────────────────────
const selectDay = (day) => {
  const str = toStr(calYear.value, calMonth.value + 1, day);
  if (str > maxDate) return;

  errors.value = { dateFrom: "", dateTo: "" };

  if (showCalendar.value === "from") {
    localDateFrom.value = str;
    if (localDateTo.value && str > localDateTo.value) {
      errors.value.dateFrom = "From date cannot be after To date";
      return;
    }
  } else {
    localDateTo.value = str;
    if (localDateFrom.value && str < localDateFrom.value) {
      errors.value.dateTo = "To date cannot be before From date";
      return;
    }
  }

  // Match preset
  const matched = presets.value.find(
    (p) => p.from === localDateFrom.value && p.to === localDateTo.value
  );
  activePreset.value = matched ? matched.label : "";

  emitAll();
  closeCalendar();
};

const selectToday = () => selectDay(t.d);

const clearField = () => {
  if (showCalendar.value === "from") {
    localDateFrom.value = "";
    emit("update:dateFrom", "");
  } else {
    localDateTo.value = "";
    emit("update:dateTo", "");
  }
  closeCalendar();
};

// ─── Presets ───────────────────────────────────────────────────
const presets = computed(() => [
  { label: "Today", icon: "bi bi-calendar-day", from: maxDate, to: maxDate },
  {
    label: "Yesterday",
    icon: "bi bi-calendar-minus",
    from: (() => {
      const d = new Date();
      d.setDate(d.getDate() - 1);
      return toStr(d.getFullYear(), d.getMonth() + 1, d.getDate());
    })(),
    to: (() => {
      const d = new Date();
      d.setDate(d.getDate() - 1);
      return toStr(d.getFullYear(), d.getMonth() + 1, d.getDate());
    })(),
  },
  {
    label: "This Week",
    icon: "bi bi-calendar-week",
    from: (() => {
      const d = new Date();
      d.setDate(d.getDate() - d.getDay());
      return toStr(d.getFullYear(), d.getMonth() + 1, d.getDate());
    })(),
    to: maxDate,
  },
  {
    label: "This Month",
    icon: "bi bi-calendar-month",
    from: toStr(t.y, t.m + 1, 1),
    to: maxDate,
  },
  {
    label: "Last Month",
    icon: "bi bi-calendar-x",
    from: (() => {
      const d = new Date(t.y, t.m - 1, 1);
      return toStr(d.getFullYear(), d.getMonth() + 1, 1);
    })(),
    to: (() => {
      const d = new Date(t.y, t.m, 0);
      return toStr(d.getFullYear(), d.getMonth() + 1, d.getDate());
    })(),
  },
  {
    label: "Last 7 Days",
    icon: "bi bi-calendar2-week",
    from: (() => {
      const d = new Date();
      d.setDate(d.getDate() - 6);
      return toStr(d.getFullYear(), d.getMonth() + 1, d.getDate());
    })(),
    to: maxDate,
  },
  {
    label: "Last 30 Days",
    icon: "bi bi-calendar2-range",
    from: (() => {
      const d = new Date();
      d.setDate(d.getDate() - 29);
      return toStr(d.getFullYear(), d.getMonth() + 1, d.getDate());
    })(),
    to: maxDate,
  },
  { label: "This Year", icon: "bi bi-calendar2", from: toStr(t.y, 1, 1), to: maxDate },
]);

const applyPreset = (preset) => {
  activePreset.value = preset.label;
  localDateFrom.value = preset.from;
  localDateTo.value = preset.to;
  errors.value = { dateFrom: "", dateTo: "" };
  emitAll();
};

// ─── Computed ──────────────────────────────────────────────────
const hasErrors = computed(() => errors.value.dateFrom || errors.value.dateTo);
const dayDiff = computed(() => {
  if (!localDateFrom.value || !localDateTo.value) return 0;
  return (
    Math.round((new Date(localDateTo.value) - new Date(localDateFrom.value)) / 86400000) +
    1
  );
});

// ─── Emit ──────────────────────────────────────────────────────
const emitAll = () => {
  emit("update:dateFrom", localDateFrom.value);
  emit("update:dateTo", localDateTo.value);
  emit("date-range-changed");
};

// ─── Close on Escape ───────────────────────────────────────────
const onKeydown = (e) => {
  if (e.key === "Escape") closeCalendar();
};
onMounted(() => {
  window.addEventListener("keydown", onKeydown);
  emitAll();
});
onBeforeUnmount(() => window.removeEventListener("keydown", onKeydown));

watch(
  () => props.dateFrom,
  (val) => {
    if (val && val !== localDateFrom.value) localDateFrom.value = val;
  }
);
watch(
  () => props.dateTo,
  (val) => {
    if (val && val !== localDateTo.value) localDateTo.value = val;
  }
);
</script>

<style scoped>
/* ── Preset Buttons ── */
.preset-btn {
  border-radius: 20px;
  font-size: 0.78rem;
  padding: 0.3rem 0.75rem;
  transition: all 0.2s ease;
}
.preset-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
}

/* ── Input Trigger Box ── */
.range-input-box {
  background: #f8f9fa;
  border: 1.5px solid #dee2e6;
  border-radius: 10px;
  padding: 0.65rem 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}
.range-input-box:hover {
  border-color: #adb5bd;
  background: #fff;
}
.range-input-box--active {
  border-color: #0d6efd;
  background: #fff;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}
.range-input-box__label {
  font-size: 0.72rem;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.range-input-box__value {
  font-size: 0.95rem;
  font-weight: 500;
  color: #212529;
  margin-top: 0.15rem;
}

/* ── Overlay ── */
.calendar-popup-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1055;
  padding: 1rem;
}

/* ── Popup Card ── */
.calendar-popup {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
  width: 320px;
  overflow: hidden;
}

/* ── Cal Header ── */
.cal-header {
  background: linear-gradient(135deg, #0d6efd, #6610f2);
  color: #fff;
  padding: 0.9rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cal-header__title {
  font-weight: 600;
  font-size: 0.95rem;
}
.cal-close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: #fff;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
}
.cal-close-btn:hover {
  background: rgba(255, 255, 255, 0.35);
}

/* ── Cal Nav ── */
.cal-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.6rem 0.75rem;
  border-bottom: 1px solid #f0f0f0;
  background: #fafafa;
}
.cal-nav__btn {
  background: #f0f0f0;
  border: none;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  color: #495057;
}
.cal-nav__btn:hover:not(:disabled) {
  background: #0d6efd;
  color: #fff;
}
.cal-nav__btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}
.cal-nav__center {
  display: flex;
  gap: 0.4rem;
}
.cal-select {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 0.25rem 0.4rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #212529;
  background: #fff;
  cursor: pointer;
  outline: none;
}
.cal-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
}
.cal-select--year {
  max-width: 72px;
}

/* ── Grid ── */
.cal-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  padding: 0.25rem 0.5rem;
  gap: 2px;
}
.cal-grid--header {
  padding-bottom: 0;
}

.cal-cell {
  aspect-ratio: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 0.82rem;
  position: relative;
  transition: background 0.15s, color 0.15s;
}
.cal-cell--dayname {
  font-weight: 700;
  font-size: 0.7rem;
  color: #adb5bd;
  text-transform: uppercase;
  aspect-ratio: unset;
  padding: 0.3rem 0;
}
.cal-cell--empty {
  pointer-events: none;
}
.cal-cell--disabled {
  color: #ced4da !important;
  cursor: not-allowed;
  pointer-events: none;
}
.cal-cell--active {
  cursor: pointer;
}
.cal-cell--active:hover {
  background: #e8f0fe;
  color: #0d6efd;
}

/* From / To selected */
.cal-cell--from .cal-cell__inner,
.cal-cell--to .cal-cell__inner {
  background: linear-gradient(135deg, #0d6efd, #6610f2);
  color: #fff !important;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  box-shadow: 0 3px 10px rgba(13, 110, 253, 0.4);
}

/* In-range highlight */
.cal-cell--in-range {
  background: rgba(13, 110, 253, 0.1);
  border-radius: 0;
}
.cal-cell--in-range:hover {
  background: rgba(13, 110, 253, 0.18);
}

/* Today dot */
.cal-cell__inner {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}
.cal-cell__today-dot {
  position: absolute;
  bottom: 1px;
  left: 50%;
  transform: translateX(-50%);
  width: 4px;
  height: 4px;
  background: #0d6efd;
  border-radius: 50%;
}
.cal-cell--from .cal-cell__today-dot,
.cal-cell--to .cal-cell__today-dot {
  background: #fff;
}

/* ── Footer ── */
.cal-footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 0.75rem;
  border-top: 1px solid #f0f0f0;
  background: #fafafa;
}

/* ── Summary Badge ── */
.range-summary-badge {
  display: inline-flex;
  align-items: center;
  background: linear-gradient(135deg, #e8f4fd, #f0faf4);
  border: 1px solid #bee5fb;
  border-radius: 50px;
  padding: 0.4rem 1.1rem;
  font-size: 0.85rem;
}

/* ── Transitions ── */
.cal-fade-enter-active,
.cal-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.cal-fade-enter-from,
.cal-fade-leave-to {
  opacity: 0;
  transform: scale(0.96);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
