<template>
  <div class="d-flex flex-column justify-content-between align-items-center min-vh-100 position-relative overflow-hidden bg-image">
    <!-- Background Image -->
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-cover" 
         :style="{ backgroundImage: `url(${backgroundImage})` }">
    </div>
    
    <!-- Error Title -->
    <div class="position-relative z-1 mt-0 pt-3 pt-md-0">
      <h1 class="display-1 fw-bold text-dark user-select-none" 
          :class="{ 'display-3': isMobile }">
        {{ $t('common.404 NOT FOUND') }}
      </h1>
    </div>
    
    <!-- Spacer for centering -->
    <div class="flex-grow-1"></div>
    
    <!-- Error Message -->
    <div class="position-relative z-1 mb-3 mb-md-2 pb-md-3">
      <h2 class="fs-1 fw-bold text-dark text-center px-3" 
          :class="{ 'fs-3': isMobile }">
        {{ $t('common.Sorry! This page isnt available') }}
      </h2>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import defaultImage from '@/assets/images/404.jpg'

// Props
const props = defineProps({
  backgroundImage: {
    type: String,
    default: defaultImage
  }
})

// Reactive state for mobile detection
const isMobile = ref(false)

// Check if viewport is mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
}

// Lifecycle hooks
onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.bg-cover {
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: 0;
}

.z-1 {
  z-index: 1;
}
</style>
