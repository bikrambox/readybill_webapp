<template>
  <section class="mobile-app-section py-5">
    <div class="container text-center">
      <h2 class="app-title mb-3">{{ $t('common.Take your business mobile') }}</h2>
      <p class="app-subtitle mb-4">
        {{ $t('common.download_our_mobile_app_and_manage') }}<br />
        {{ $t('common.Available on iOS and Android') }}.
      </p>
      <div class="app-badges d-flex justify-content-center gap-3 flex-wrap">
        <a href="#" class="badge-link">
          <img src="@/assets/images/apple-store.png" alt="Download on App Store" class="store-badge" />
        </a>
        <a href="#" class="badge-link">
          <img src="@/assets/images/google-play.png" alt="Get it on Google Play" class="store-badge" />
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.mobile-app-section {
  background-color: #ffffff;
  padding: 3rem 0 4rem;
}

.app-title {
  font-size: 2rem;
  font-weight: 600;
  color: #1a1a1a;
}

.app-subtitle {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
}

.app-badges {
  margin-top: 1.5rem;
}

.badge-link {
  display: inline-block;
  transition: transform 0.3s ease;
}

.badge-link:hover {
  transform: translateY(-3px);
}

.store-badge {
  height: 50px;
  width: auto;
}

@media (max-width: 768px) {
  .app-title {
    font-size: 1.5rem;
  }
  
  .app-subtitle {
    font-size: 0.9rem;
    padding: 0 1rem;
  }
  
  .store-badge {
    height: 45px;
  }
  
  .app-badges {
    flex-direction: column;
    align-items: center;
  }
}
</style>
