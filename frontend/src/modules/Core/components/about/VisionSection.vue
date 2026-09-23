<template>
  <section class="vision-section py-4">
    <div class="container">
      <div class="vision-card">
        <div class="vision-content">
          <div class="icon-wrapper">
            <!-- <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#4169E1" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg> -->
            <img
              src="@/assets/images/icons/about/vision.png"
              alt=""
              class="img-fluid"
            />
          </div>
          <div class="text-content">
            <h2 class="vision-title mb-3">Our Vision</h2>
            <p class="vision-text">
              {{ $t('common.vision') }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.vision-section {
  padding: 2rem 0;
}

.vision-card {
  background: linear-gradient(135deg, #e8efff 0%, #f5f8ff 100%);
  border-radius: 12px;
  padding: 2.5rem;
  margin: 0 auto;
}

.vision-content {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 1.5rem;
}

.icon-wrapper {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  background-color: rgba(255, 255, 255, 0.6);
  border-radius: 8px;
  padding: 8px;
}

.text-content {
  flex: 1;
}

.vision-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #1a1a1a;
  text-align: left;
}

.vision-text {
  font-size: 0.95rem;
  line-height: 1.7;
  color: #555;
  margin-bottom: 0;
  text-align: left;
}

/* Mobile View */
@media (max-width: 768px) {
  .vision-card {
    padding: 1.5rem;
    margin: 0 1rem;
  }

  .vision-content {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }

  .icon-wrapper {
    width: 56px;
    height: 56px;
  }

  .text-content {
    text-align: center;
  }

  .vision-title {
    font-size: 1.5rem;
    text-align: center;
  }

  .vision-text {
    font-size: 0.9rem;
    text-align: center;
  }
}

@media (max-width: 576px) {
  .vision-card {
    padding: 1.25rem;
  }

  .icon-wrapper {
    width: 48px;
    height: 48px;
  }

  .vision-title {
    font-size: 1.25rem;
  }

  .vision-text {
    font-size: 0.85rem;
  }
}
</style>
