<template>
  <Transition name="fade">
    <div v-if="loadingStore.isLoading" class="global-loader">
      <div class="loader-content">
        <div class="spinner"></div>
        <p v-if="loadingStore.loadingMessage" class="loader-text">
          {{ loadingStore.loadingMessage }}
        </p>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useLoadingStore } from '@/stores/loadingStore'

const loadingStore = useLoadingStore()
</script>

<style scoped>
.global-loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loader-content {
  text-align: center;
}

.spinner {
  width: 60px;
  height: 60px;
  border: 6px solid #f0f0f0;
  border-top: 6px solid #5EBE9B;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loader-text {
  margin: 0;
  font-size: 1rem;
  color: #666;
  font-weight: 500;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
