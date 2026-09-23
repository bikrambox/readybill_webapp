<script setup>
import { computed, defineProps, defineEmits } from "vue";

const props = defineProps({
  heading: {
    type: String,
    default: "",
  },
  subheading: {
    type: String,
    default: "",
  },
  description: {
    type: String,
    default: null,
  },
  price: {
    type: [String, Number],
    default: "",
  },
  months: {
    type: [String, Number],
    default: "",
  },
  isPresent: {
    type: Boolean,
    default: false,
  },
  isBestValue: {
    type: Boolean,
    default: false,
  },
  bgColor: {
    type: String,
    default: "#eef7f3",
  },
  borderColor: {
    type: String,
    default: "#5EBE9B",
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["select"]);
const handleClick = () => emit("select");

// "12 months" → "/ year" | "3 months" → "/ 3 months" | "1 month" → "/ month"
const priceSuffix = computed(() => {
  if (!props.months) return "";
  const m = parseInt(props.months);
  if (m === 12) return "/ year";
  return `/ ${m} ${m === 1 ? "month" : "months"}`;
});

// Show "Best Value" badge only when isBestValue is true from DB
// and the plan is NOT already the present plan (avoid badge overlap)
const showBestValueBadge = computed(() => props.isBestValue && !props.isPresent);
</script>

<template>
  <div
    class="plan-card-wrapper position-relative"
    :class="{ 'has-top-badge': isPresent || isBestValue }"
    @click="handleClick"
  >
    <!-- Present Plan Badge (top-left) -->
    <div v-if="isPresent" class="present-badge">Present Plan</div>

    <!-- Best Value Badge (top-left, only if not present plan) -->
    <div v-else-if="isBestValue" class="best-value-top-badge">⭐ Best Value</div>

    <div
      class="plan-card"
      :class="{ selected: isSelected }"
      :style="{
        backgroundColor: bgColor,
        borderColor: borderColor,
      }"
    >
      <!-- Header Row -->
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="plan-header flex-grow-1">
          <!-- Heading + Price -->
          <h5 class="plan-title mb-1">
            {{ heading }}
            <span v-if="price" class="plan-price"> ₹{{ price }}</span>
            <span v-if="months" class="plan-price-suffix">
              {{ priceSuffix }}
              <span class="plan-gst-label">+GST</span>
            </span>
          </h5>

          <!-- Subheading -->
          <p v-if="subheading" class="plan-subtitle mb-0">{{ subheading }}</p>
        </div>
      </div>

      <!-- Description -->
      <p v-if="description" class="plan-description mb-0">{{ description }}</p>
    </div>
  </div>
</template>

<style scoped>
.plan-card-wrapper {
  cursor: pointer;
  position: relative;
  padding-top: 0;
}

/* Push card down when either top badge is showing */
.plan-card-wrapper.has-top-badge {
  margin-top: 14px;
}

.plan-card {
  border: 2px solid;
  border-radius: 16px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.plan-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(94, 190, 155, 0.18);
}

/* ── Typography ── */
.plan-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1a1a1a;
  line-height: 1.3;
}

.plan-price {
  font-size: 1.6rem;
  font-weight: 800;
  color: #1a1a1a;
}

.plan-price-suffix {
  font-size: 0.95rem;
  font-weight: 400;
  color: #6c757d;
  margin-left: 1px;
}

.plan-subtitle {
  font-size: 1rem;
  font-weight: 400;
  color: #333;
  margin-top: 0.1rem;
}

.plan-description {
  font-size: 1rem;
  color: #888;
  line-height: 1.7;
  margin-top: 1.25rem;
  max-width: 90ch;
}

.plan-gst-label {
  font-size: 0.78rem;
  font-weight: 400;
  color: #6c757d;
  margin-left: 2px;
}

/* ── Present Plan Badge (top-left, green) ── */
.present-badge {
  position: absolute;
  top: -14px;
  left: 0;
  background-color: #5ebe9b;
  color: white;
  padding: 0.375rem 1.25rem;
  border-radius: 9px;
  border-bottom-left-radius: 0 !important;
  font-size: 0.875rem;
  font-weight: 500;
  pointer-events: none;
  z-index: 1;
}

/* ── Best Value Badge (top-left, amber) ── */
.best-value-top-badge {
  position: absolute;
  top: -14px;
  left: 0;
  background-color: #f59e0b;
  color: white;
  padding: 0.375rem 1.25rem;
  border-radius: 9px;
  border-bottom-left-radius: 0 !important;
  font-size: 0.875rem;
  font-weight: 600;
  pointer-events: none;
  z-index: 1;
}

/* ── Responsive ── */
@media (max-width: 576px) {
  .plan-card {
    padding: 1.5rem 1.25rem;
  }
  .plan-title,
  .plan-price {
    font-size: 1.25rem;
  }
  .plan-price-suffix {
    font-size: 0.875rem;
  }
  .plan-subtitle {
    font-size: 0.9rem;
  }
  .plan-description {
    font-size: 0.875rem;
  }
  .present-badge,
  .best-value-top-badge {
    font-size: 0.8rem;
    padding: 0.3rem 1rem;
    top: -12px;
  }
}
</style>
