<template>
  <div 
    :class="['alert', alertClass, 'd-flex align-items-center']" 
    role="alert"
  >
    <i :class="['bi', iconClass, 'me-2']" style="font-size: 1.25rem;"></i>
    <div>{{ message }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'danger', 'warning', 'info'].includes(value)
  }
});

const alertClass = computed(() => `alert-${props.type}`);

const iconClass = computed(() => {
  const icons = {
    success: 'bi-check-circle-fill',
    danger: 'bi-exclamation-triangle-fill',
    warning: 'bi-exclamation-circle-fill',
    info: 'bi-info-circle-fill'
  };
  return icons[props.type];
});
</script>
