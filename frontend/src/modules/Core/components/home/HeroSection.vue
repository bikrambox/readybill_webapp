<template>
  <section class="rb-hero-section">
    <div class="container">
      <div class="row align-items-center justify-content-between g-4">
        <!-- Left Content -->
        <div class="col-lg-5 col-md-6">
          <!-- <h1 class="fw-bold mb-3 rb-hero-title">
            Quick sell with your mobile and a 
            <span class="text-gradient">voice command</span>
          </h1> -->

          <h1
            class="fw-bold mb-3 rb-hero-title"
            v-html="
              $t('common.quick_sell_voice', {
                star: '<span class=\'text-gradient\'>voice command</span>',
              })
            "
          ></h1>

          <p class="text-muted mb-3 rb-hero-text">
            {{ $t('index_page.no_customer_waiting') }}
          </p>

          <div class="mb-4">
            <span class="badge rb-feature-badge">
              <i class="bi bi-lightning-charge-fill me-1"></i>
              {{ $t('index_page.Super fast') }}. {{ $t('index_page.Super easy') }}
            </span>
          </div>

          <div class="d-flex flex-wrap gap-3">
            <!-- <button class="btn btn-primary">{{ $t('common.Start Free Trial') }}</button>
            <button class="btn btn-outline-secondary">
              <i class="bi bi-play-circle me-2"></i>
              {{ $t('common.Watch Demo') }}
            </button> -->

            <a :href="getLocalizedPath('register')" class="btn btn-primary">{{ $t('common.Start Free Trial') }}</a>
            <a :href="getLocalizedPath('watch-demo')" class="btn btn-outline-secondary"><i class="bi bi-play-circle me-2"></i>
              {{ $t('common.Watch Demo') }}</a>

          </div>
        </div>

        <!-- Right Image -->
        <div class="col-lg-5 col-md-6">
          <div class="rb-image-wrapper position-relative">
            <img
              src="@/assets/images/homepage-photo.jpg"
              alt="Store owner using ReadyBill app"
              class="rounded-4"
            />
            <div class="rb-badge-overlay position-absolute">
              <div
                class="bg-white shadow-sm rounded-3 p-2 d-flex align-items-center"
              >
                <i class="bi bi-graph-up-arrow text-success me-2"></i>
                <div class="lh-sm">
                  <div class="fw-semibold text-dark small">
                    {{ $t('common.Real-time Analytics') }}
                  </div>
                  <div class="text-muted rb-badge-subtitle">
                    {{ $t('common.Track sales instantly') }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

import { useLocalization } from "@/composables/useLocalization";

const { getLocalizedPath } = useLocalization();

</script>

<style scoped>
.rb-hero-section {
  padding: 60px 0 80px;
  background-color: #fff;
  min-height: 650px;
  display: flex;
  align-items: center;
}

@media (max-width: 991px) {
  .rb-hero-section {
    min-height: auto;
    padding: 40px 0 60px;
  }
}

.rb-hero-title {
  font-size: 2.5rem;
  line-height: 1.2;
  color: #111827;
  letter-spacing: -0.01em;
}

@media (max-width: 991px) {
  .rb-hero-title {
    font-size: 2rem;
  }
}

@media (max-width: 767px) {
  .rb-hero-title {
    font-size: 1.75rem;
  }
}

.text-gradient {
  background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.rb-hero-text {
  font-size: 1.1rem;
  line-height: 1.7;
  color: #6b7280;
}

.rb-feature-badge {
  background-color: #ecfdf5 !important;
  color: #059669 !important;
  border: none;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.btn {
  padding: 0.625rem 1.5rem;
  font-weight: 500;
  border-radius: 0.5rem;
  font-size: 1rem;
}

.btn-primary {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

.btn-primary:hover {
  background-color: #2563eb;
  border-color: #2563eb;
}

.btn-outline-secondary {
  color: #374151;
  border-color: #d1d5db;
  background-color: transparent;
}

.btn-outline-secondary:hover {
  background-color: #f9fafb;
  border-color: #9ca3af;
  color: #111827;
}

/* Image wrapper with increased height */
.rb-image-wrapper {
  width: 100%;
  height: 520px;
  overflow: hidden;
  border-radius: 1rem;
}

.rb-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  display: block;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

@media (max-width: 991px) {
  .rb-image-wrapper {
    height: 450px;
  }
}

@media (max-width: 767px) {
  .rb-image-wrapper {
    height: 380px;
  }
}

.rb-badge-overlay {
  bottom: 20px;
  left: 20px;
  max-width: 220px;
  z-index: 10;
}

.rb-badge-overlay .bg-white {
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.rb-badge-overlay i {
  font-size: 1.25rem;
}

.rb-badge-subtitle {
  font-size: 0.75rem;
  line-height: 1.2;
}

.rounded-4 {
  border-radius: 1rem !important;
}

/* Equal spacing control */
.row {
  margin-left: 0;
  margin-right: 0;
}

.col-lg-5 {
  padding-left: 15px;
  padding-right: 15px;
}

@media (min-width: 992px) {
  .container {
    padding-left: 15px;
    padding-right: 15px;
  }
}
</style>
