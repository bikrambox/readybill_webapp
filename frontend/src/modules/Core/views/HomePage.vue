<template>
  <div class="rb-home-page">
    <HeroSection />
    
    <section class="rb-features-section section-padding">
      <div class="container">
        <div class="text-center mb-5">
          <h2 class="display-5 fw-bold mb-3 rb-section-title">{{ $t('common.Everything you need to run your business') }}</h2>
          <p class="lead text-muted rb-section-subtitle">
            {{ $t('common.download_our_mobile_app_and_manage') }}
          </p>
        </div>
        
        <div class="row g-4">
          <div class="col-md-6 col-lg-3">
            <FeatureCard
              icon="phone"
              :title="t('common.Mobile-First Design')"
              :description="t('index_page.manage_your_shop_inventory')"
              color="primary"
            />
          </div>
          
          <div class="col-md-6 col-lg-3">
            <FeatureCard
              icon="lightning-charge"
              :title="t('common.Voice Commands')"
              :description="t('common.speed_up_transactions')"
              color="success"
            />
          </div>
          
          <div class="col-md-6 col-lg-3">
            <FeatureCard
              icon="people"
              :title="t('common.Staff Management')"
              :description="t('common.enable_your_staff')"
              color="info"
            />
          </div>
          
          <div class="col-md-6 col-lg-3">
            <FeatureCard
              icon="graph-up"
              :title="t('common.Real-time Insights')"
              :description="t('common.get_instant_access_to_sales_data')"
              color="warning"
            />
          </div>
        </div>
      </div>
    </section>
    
    <MobileAppSection />
  </div>
</template>

<script setup>
import HeroSection from '../components/home/HeroSection.vue'
import FeatureCard from '../components/home/FeatureCard.vue'
import MobileAppSection from '../components/home/MobileAppSection.vue'

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

// Import CSS files
// import '../assets/css/home/navbar.css'
import '../assets/css/home/hero.css'
import '../assets/css/home/feature-card.css'
// import '../assets/css/home/mobile-app.css'
// import '../assets/css/home/footer.css'
</script>

<style>
.rb-home-page {
  overflow-x: hidden;
}

.rb-features-section {
  background-color: #ffffff;
}

.rb-section-title {
  color: #0f172a;
}

.rb-section-subtitle {
  color: #64748b;
  font-size: 1.125rem;
}

@media (max-width: 768px) {
  .rb-section-title {
    font-size: 1.75rem;
  }
  
  .rb-section-subtitle {
    font-size: 1rem;
  }
}
</style>
