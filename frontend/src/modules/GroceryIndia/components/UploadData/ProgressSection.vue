<template>
  <div v-if="progress" class="mb-4">
    <div class="progress" style="height: 25px;">
      <div
        class="progress-bar progress-bar-striped progress-bar-animated"
        role="progressbar"
        :style="{ width: `${progress.percentage}%` }"
        :aria-valuenow="progress.percentage"
        aria-valuemin="0"
        aria-valuemax="100"
      >
        {{ progress.percentage }}%
      </div>
    </div>
    <p class="text-center text-muted mt-2 mb-0">
      <i class="bi bi-hourglass-split me-2"></i>
      {{ progress.message }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  progress: {
    type: Object,
    required: true
  }
})
</script>
