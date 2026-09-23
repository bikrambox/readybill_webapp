<template>
  <div 
    v-if="show" 
    class="alert alert-danger alert-dismissible fade show" 
    role="alert"
  >
    <h5 v-if="heading" class="alert-heading mb-2">{{ heading }}</h5>
    <div v-html="message"></div>
    <button 
      type="button" 
      class="btn-close" 
      @click="emit('close')"
      aria-label="Close"
    ></button>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  heading: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])
</script>
