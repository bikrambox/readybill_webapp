<template>
  <div class="rb-main-layout">
    <Navbar />
    <main class="rb-main-content">
      <slot />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import Navbar from '../components/Navbar.vue'
import Footer from '../components/Footer.vue'
</script>

<style>
.rb-main-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.rb-main-content {
  flex: 1;
}
</style>
