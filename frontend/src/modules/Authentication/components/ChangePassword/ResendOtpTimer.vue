<template>
  <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
    <button 
      type="button" 
      class="btn btn-link text-decoration-none p-0 m-0"
      :class="canResend ? 'text-primary' : 'text-secondary'"
      :disabled="!canResend"
      @click="handleResend"
    >
      {{ $t('common.Resend OTP') }}
    </button>
    
    <span v-if="!canResend" class="text-secondary fw-bold">
      {{ $t('common.In') }}
    </span>
    
    <span v-if="!canResend" class="text-secondary fw-bold">
      {{ formattedTime }}
    </span>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  initialDuration: {
    type: Number,
    default: 60 // seconds
  }
})

const emit = defineEmits(['resend'])

// State
const remainingTime = ref(props.initialDuration)
const canResend = ref(false)
let timer = null

// Computed
const formattedTime = computed(() => {
  const minutes = Math.floor(remainingTime.value / 60)
  const seconds = remainingTime.value % 60
  return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})

// Start timer
const startTimer = () => {
  remainingTime.value = props.initialDuration
  canResend.value = false

  timer = setInterval(() => {
    remainingTime.value--

    if (remainingTime.value <= 0) {
      clearInterval(timer)
      canResend.value = true
    }
  }, 1000)
}

// Handle resend
const handleResend = () => {
  if (!canResend.value) return

  emit('resend')
  startTimer()
}

// Lifecycle
onMounted(() => {
  startTimer()
})

onUnmounted(() => {
  if (timer) {
    clearInterval(timer)
  }
})
</script>

<style scoped>
.btn-link:disabled {
  cursor: not-allowed;
  opacity: 0.65;
}

/* Mobile responsive */
@media (max-width: 576px) {
  .d-flex {
    font-size: 0.875rem;
  }
}
</style>
