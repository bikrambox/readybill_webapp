<template>
  <section class="rb-mobile-app-section section-padding text-black">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold mb-3 rb-mobile-title">
          {{ $t('common.Take your business mobile') }}
        </h2>
        <p class="lead rb-mobile-subtitle">
          {{ $t('common.Download our mobile app and manage your business from anywhere') }}.<br
            class="d-none d-md-block"
          />
          {{ $t('common.Available on iOS and Android') }}.
        </p>
      </div>

      <div class="row justify-content-center">
        <div class="col-auto">
          <div
            class="d-flex flex-wrap justify-content-center rb-app-buttons"
          >
            <a href="#" class="rb-app-store-btn">
              <div
                class="rb-store-badge d-flex align-items-center py-2 px-2"
              >
                <!-- <i class="bi bi-apple fs-2 me-2"></i>
                <div class="text-start">
                  <div class="rb-store-text">Download on the</div>
                  <div class="rb-store-name">App Store</div>
                </div> -->
                <a href="#" target="_blank">
                  <img
                    src="@/assets/images/apple-store.png"
                    class="me-2"
                    height="50"
                  />
                </a>
              </div>
            </a>
            <a href="#" class="rb-app-store-btn">
              <div
                class="rb-store-badge d-flex align-items-center px-2"
              >
                <!-- <i class="bi bi-google-play fs-2 me-2"></i>
                <div class="text-start">
                  <div class="rb-store-text">Get it on</div>
                  <div class="rb-store-name">Google Play</div>
                </div> -->

                <a href="#" target="_blank">
                  <img
                    src="@/assets/images/google-play.png"
                    class="me-2"
                    height="50"
                  />
                </a>
              </div>
            </a>
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
