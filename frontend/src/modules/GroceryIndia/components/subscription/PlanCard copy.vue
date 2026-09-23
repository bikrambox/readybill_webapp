<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  isPresent: {
    type: Boolean,
    default: false
  },
  isBestValue: {
    type: Boolean,
    default: false
  },
  pricing: {
    type: String,
    required: true
  },
  bgColor: {
    type: String,
    default: '#f8f9fa'
  },
  borderColor: {
    type: String,
    default: '#dee2e6'
  },
  isSelected: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['select']);

const handleClick = () => {
  emit('select');
};
</script>

<template>
  <div 
    class="plan-card-wrapper position-relative"
    @click="handleClick"
  >
    <!-- Present Plan Badge - Above Border, Touching Left Side -->
    <div v-if="isPresent" class="present-badge">
      Present Plan
    </div>

    <div 
      class="plan-card" 
      :class="{ 'selected': isSelected, 'has-badge': isPresent }"
      :style="{
        backgroundColor: isSelected ? bgColor : '#f8f9fa',
        borderColor: isSelected ? borderColor : '#dee2e6'
      }"
    >
      <div class="d-flex justify-content-between align-items-start">
        <div class="plan-info flex-grow-1">
          <h5 class="plan-name mb-2">{{ name }}</h5>
          <p class="plan-pricing text-muted mb-0">{{ pricing }}</p>
        </div>
        
        <!-- Best Value Badge - Aligned Right -->
        <div v-if="isBestValue" class="best-value-badge ms-3">
          Best Value
        </div>
      </div>
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
  border-radius: 12px;
  padding: 1.5rem;
  position: relative;
  transition: all 0.3s ease;
}

.plan-card.has-badge {
  margin-top: 12px;
}

.plan-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.plan-card.selected {
  border-width: 2px;
}

.present-badge {
  position: absolute;
  top: -12px;
  left: 0;
  background-color: #5EBE9B;
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
  background-color: #5EBE9B;
  color: white;
  padding: 0.375rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  pointer-events: none;
  flex-shrink: 0;
}

.plan-info {
  padding-right: 1rem;
}

.plan-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.5rem;
}

.plan-pricing {
  font-size: 0.95rem;
  color: #6c757d;
}

@media (max-width: 576px) {
  .plan-card {
    padding: 1.25rem;
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

  .plan-name {
    font-size: 1.1rem;
  }

  .plan-pricing {
    font-size: 0.875rem;
  }

  .plan-info {
    padding-right: 0.5rem;
  }
}
</style>
