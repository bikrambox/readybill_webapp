<template>
  <div class="row policy-section">
    <div class="col-md-4 col-12 mb-3 mb-md-0">
      <h6 class="fw-bold section-heading">
        {{ sectionNumber }}. <span v-html="heading"></span>
      </h6>
    </div>
    <div class="col-md-8 col-12 fw-normal section-content">
      <slot></slot>
    </div>
  </div>
</template>

<script setup>
defineProps({
  sectionNumber: {
    type: Number,
    required: true
  },
  heading: {
    type: String,
    required: true
  }
})
</script>

<style scoped>
.policy-section {
  margin-bottom: 1rem;
}

.section-heading {
  font-size: 1.1rem;
  color: #2c3e50;
  position: sticky;
  top: 20px;
  line-height: 1.5;
}

.section-content {
  color: #495057;
}

.section-content p:last-child {
  margin-bottom: 0;
}

.section-content :deep(a) {
  color: #0d6efd;
  text-decoration: none;
}

.section-content :deep(a:hover) {
  text-decoration: underline;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .section-heading {
    position: static;
    font-size: 1rem;
    margin-bottom: 0.75rem;
  }

  .section-content {
    padding-left: 0;
  }
}
</style>
