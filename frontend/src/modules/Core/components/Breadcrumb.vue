<template>
  <section class="hero-section text-center py-5">
    <div class="container">
      <p class="text-muted mb-2">{{ title }}</p>
      <h1 class="hero-title">{{ subtitle }}</h1>
    </div>
  </section>
</template>

<script setup>
defineProps({

  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    required: true,
  },

})
</script>

<style scoped>
.hero-section {
  background-color: #ffffff;
  padding-top: 3rem;
  padding-bottom: 2rem;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 0;
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 1.75rem;
    padding: 0 1rem;
  }
}
</style>
