<template>
  <div class="rb-feature-card h-100">
    <div class="card border-0 shadow-sm h-100 text-center p-4">
      <div class="rb-icon-wrapper mx-auto mb-3">
        <div class="rb-feature-icon" :class="`rb-bg-${color}-light`">
          <i :class="`bi bi-${icon} rb-text-${color}`"></i>
        </div>
      </div>
      
      <div class="card-body p-0">
        <h5 class="card-title fw-bold mb-3">{{ title }}</h5>
        <p class="card-text text-muted">{{ description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'primary'
  }
})
</script>
