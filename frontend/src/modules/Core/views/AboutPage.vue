<template>
  <div class="rb-home-page">
    <AboutHero />

    <section class="">
      <div class="container">
        
        <div class="row mb-4">
          <div class="col-12">
            <VisionSection />
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-4 col-lg-4">
            <FeatureCard
              :path="mobileIcon"
              :title="t('common.Mobile-First Design')"
              :description="t('index_page.manage_your_shop_inventory')"
              color="primary"
            />
          </div>

          <div class="col-md-4 col-lg-4">
            <FeatureCard
              :path="analyticsIcon"
              :title="t('common.Voice Commands')"
              :description="t('common.speed_up_transactions')"
              color="success"
            />
          </div>

          <div class="col-md-4 col-lg-4">
            <FeatureCard
              :path="teamIcon"
              :title="t('common.Staff Management')"
              :description="t('common.enable_your_staff')"
              color="info"
            />
          </div>
        </div>

        <div class="row my-4">
          <div class="col-12">
            <WhyChoose />
          </div>
        </div>

        <div class="row my-4">
          <div class="col-12">
            <FounderSection />
          </div>
        </div>

      </div>
    </section>

    <MobileAppSection />
  </div>
</template>

<script setup>

import mobileIcon from '@/assets/images/icons/about/mobile.png';
import analyticsIcon from '@/assets/images/icons/about/analytics.png';
import teamIcon from '@/assets/images/icons/about/team.png';

import FeatureCard from "../components/about/FeatureCard.vue";
import MobileAppSection from "../components/home/MobileAppSection.vue";

import AboutHero from "../components/about/AboutHero.vue";
import VisionSection from "../components/about/VisionSection.vue";
import FeatureCards from "../components/about/FeatureCard.vue";
import WhyChoose from "../components/about/WhyChoose.vue";
import FounderSection from "../components/about/FounderSection.vue";
import MobileApp from "../components/about/MobileApp.vue";

// Import CSS files
// import "../assets/css/home/navbar.css";
import "../assets/css/home/hero.css";
import "../assets/css/home/feature-card.css";
// import "../assets/css/home/mobile-app.css";
// import "../assets/css/home/footer.css";

import { useI18n } from 'vue-i18n'
const { t } = useI18n()

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
