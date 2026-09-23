<template>
  <div class="mb-3">
    <div class="progress mb-2" style="height: 25px;">
      <div 
        class="progress-bar progress-bar-striped progress-bar-animated" 
        role="progressbar" 
        :style="{ width: percentage + '%' }"
        :aria-valuenow="percentage" 
        aria-valuemin="0" 
        aria-valuemax="100"
      >
        {{ percentage }}%
      </div>
    </div>
    <p class="text-center text-muted mb-0">{{ message }}</p>
  </div>
</template>

<script setup>
defineProps({
  percentage: {
    type: Number,
    default: 0
  },
  message: {
    type: String,
    default: 'Processing...'
  }
})
</script>
