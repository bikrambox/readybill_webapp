<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: "",
  },
  description: {
    type: String,
    default: "",
  },
  price: {
    type: String,
    default: "",
  },
  priceSuffix: {
    type: String,
    default: "/year",
  },
  isPresent: {
    type: Boolean,
    default: false,
  },
  isBestValue: {
    type: Boolean,
    default: false,
  },
  pricing: {
    type: String,
    required: true,
  },
  bgColor: {
    type: String,
    default: "#f8f9fa",
  },
  borderColor: {
    type: String,
    default: "#dee2e6",
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["select"]);

const handleClick = () => {
  emit("select");
};
</script>

<template>
  <div class="plan-card-wrapper position-relative" @click="handleClick">
    <!-- Present Plan Badge - Above Border, Touching Left Side -->
    <div v-if="isPresent" class="present-badge">Present Plan</div>

    <div
      class="plan-card"
      :class="{ selected: isSelected, 'has-badge': isPresent }"
      :style="{
        backgroundColor: isSelected ? bgColor : '#f8f9fa',
        borderColor: isSelected ? borderColor : '#dee2e6',
      }"
    >
      <!-- Header row: title+badge -->
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="plan-header flex-grow-1">
          <!-- Pricing headline: "Upgrade to premium for $100/year" style -->
          <h5 class="plan-title mb-1">
            {{ name }}
            <span v-if="price" class="plan-price">{{ price }}</span>
            <span v-if="price" class="plan-price-suffix">{{ priceSuffix }}</span>
          </h5>
          <p v-if="subtitle" class="plan-subtitle mb-0">{{ subtitle }}</p>
        </div>

        <!-- Best Value Badge - Aligned Right -->
        <div v-if="isBestValue" class="best-value-badge ms-3">Best Value</div>
      </div>

      <!-- Description paragraph -->
      <p v-if="description" class="plan-description mb-0">{{ description }}</p>

      <!-- Fallback: legacy pricing prop -->
      <p v-if="!price && !description" class="plan-pricing text-muted mb-0">
        {{ pricing }}
      </p>
    </div>
  </div>
</template>

<style scoped>
.plan-card-wrapper {
  cursor: pointer;
  margin-bottom: 0;
  position: relative;
  padding-top: 0;
}

.plan-card {
  border: 2px solid;
  border-radius: 16px;
  padding: 2rem 2rem 2rem 2rem;
  position: relative;
  transition: all 0.3s ease;
}

.plan-card.has-badge {
  margin-top: 12px;
}

/* Highlighted/selected state matches the screenshot green tint */
.plan-card.selected {
  border-width: 2px;
}

/* Default unselected card gets a green tint to match the screenshot */
.plan-card:not(.selected) {
  background-color: #eef7f3 !important;
  border-color: #5ebe9b !important;
}

.plan-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(94, 190, 155, 0.18);
}

/* ---- Typography ---- */

/* "Upgrade to premiun for" part */
.plan-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1a1a1a;
  line-height: 1.3;
  margin-bottom: 0.25rem;
}

/* "$100" — larger, same bold weight but visually heavier */
.plan-price {
  font-size: 1.6rem;
  font-weight: 800;
  color: #1a1a1a;
}

/* "/year" — inline, smaller, muted */
.plan-price-suffix {
  font-size: 0.95rem;
  font-weight: 400;
  color: #6c757d;
  margin-left: 1px;
}

/* "Unlock all features with the premium plan" */
.plan-subtitle {
  font-size: 1rem;
  font-weight: 400;
  color: #333;
  margin-top: 0.1rem;
}

/* Description paragraph */
.plan-description {
  font-size: 1rem;
  color: #888;
  line-height: 1.7;
  margin-top: 1.25rem;
  max-width: 90ch;
}

/* Legacy fallback */
.plan-pricing {
  font-size: 0.95rem;
  color: #6c757d;
}

/* ---- Badges ---- */

.present-badge {
  position: absolute;
  top: -12px;
  left: 0;
  background-color: #5ebe9b;
  color: white;
  padding: 0.375rem 1.25rem;
  border-radius: 9px;
  border-bottom-left-radius: 0px !important;
  font-size: 0.875rem;
  font-weight: 500;
  pointer-events: none;
  z-index: 1;
}

.best-value-badge {
  background-color: #5ebe9b;
  color: white;
  padding: 0.375rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  pointer-events: none;
  flex-shrink: 0;
}

/* ---- Responsive ---- */

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

  .present-badge {
    font-size: 0.8rem;
    padding: 0.3rem 1rem;
    top: -10px;
  }

  .best-value-badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.85rem;
  }
}
</style>
