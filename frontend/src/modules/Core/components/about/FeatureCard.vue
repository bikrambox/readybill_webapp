<template>
  <div class="rb-feature-card h-100">
    <div class="card border-0 shadow-sm h-100 p-4">
      <div class="mb-3">
        <div class="rb-feature-icon" :class="`rb-bg-${color}-light`">
          <!-- <i :class="`bi bi-${icon} rb-text-${color}`"></i> -->

       <img :src="path" alt="" class="feature-icon-img" />

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

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

defineProps({
  path: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    required: true,
  },
  color: {
    type: String,
    default: "primary",
  },
});
</script>

<style scoped>
/* Desktop - Left Aligned */
.rb-feature-card .card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.rb-feature-card .card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
}

.rb-icon-wrapper {
  /* No specific alignment on desktop - defaults to left */
}

.rb-feature-icon {
  display: flex;
  width: 64px;
  height: 64px;
  border-radius: 12px;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
}


.feature-icon-img {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 8px;
  padding: 8px;
}

/* Icon background colors */
.rb-bg-primary-light {
  background-color: #DCFCE7;
}

.rb-bg-success-light {
  background-color: #F3E8FF;
}

.rb-bg-warning-light {
  background-color: rgba(255, 193, 7, 0.1);
}

.rb-bg-danger-light {
  background-color: rgba(220, 53, 69, 0.1);
}

.rb-bg-info-light {
  background-color: #FFEDD4;
}

/* Icon text colors */
.rb-text-primary {
  color: #0d6efd;
}

.rb-text-success {
  color: #198754;
}

.rb-text-warning {
  color: #ffc107;
}

.rb-text-danger {
  color: #dc3545;
}

.rb-text-info {
  color: #0dcaf0;
}

.card-title {
  font-size: 1.25rem;
  color: #1a1a1a;
  text-align: left;
}

.card-text {
  font-size: 0.95rem;
  line-height: 1.6;
  color: #6c757d;
  text-align: left;
}

/* Mobile - Center Aligned */
@media (max-width: 768px) {
  .rb-feature-icon {
    display: block;
    width: 56px;
    height: 56px;
    font-size: 1.5rem;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 1rem;
  }

  .card-title {
    font-size: 1.1rem;
    text-align: center;
  }

  .card-text {
    font-size: 0.9rem;
    text-align: center;
  }
}

@media (max-width: 576px) {
  .rb-feature-card .card {
    padding: 1.25rem !important;
  }

  .rb-feature-icon {
    width: 52px;
    height: 52px;
    font-size: 1.35rem;
  }

  .card-title {
    font-size: 1rem;
    margin-bottom: 0.75rem !important;
  }

  .card-text {
    font-size: 0.85rem;
  }
}
</style>
