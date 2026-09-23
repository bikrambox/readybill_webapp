<template>
  <div class="auth-layout">
    <div class="auth-card">
      <!-- Desktop Left Side with Image -->
      <div class="auth-left">
        <div class="image-container">
          <img :src="desktopImage" alt="Authentication" class="auth-image" />
          <div class="auth-overlay">
            <h3>{{ $t("common.Smart Voice Billing Made Effortless") }}</h3>
            <p>{{ $t("common.simplify_your_billing") }}.</p>
          </div>
        </div>
      </div>

      <!-- Right Side with Form -->
      <div class="auth-right">
        <!-- Mobile Header with Background Image -->
        <div class="mobile-header">
          <div class="mobile-image-wrapper">
            <img :src="mobileImage" alt="Mobile Banner" class="mobile-banner" />
            <div class="mobile-overlay">
              <div class="mobile-logo-group">
                <a class="d-flex align-items-center text-decoration-none" href="/">
                  <img :src="logoImage" alt="ReadyBill" class="mobile-logo" />
                </a>
                <span class="mobile-pipe">|</span>
                <span class="mobile-agents-badge">Agents</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Content -->
        <div class="auth-content">
          <!-- Desktop Logo -->
          <div class="desktop-logo">
            <a class="d-flex align-items-center text-decoration-none" href="/"
              ><img :src="logoImage" alt="ReadyBill" class="logo" />
            </a>
            <span class="logo-pipe">|</span>
            <span class="agents-badge">Agents</span>
          </div>
          <slot />
        </div>

        <!-- Desktop Footer -->
        <div class="desktop-footer">
          <p class="footer-text">{{ $t("common.copyright_reserved") }}</p>
          <div class="footer-links">
            <a :href="getLocalizedPath('privacy-policy')" class="footer-link">{{
              $t("common.Privacy Policy")
            }}</a>
            <span class="footer-separator">•</span>
            <a :href="getLocalizedPath('terms-of-use')" class="footer-link">{{
              $t("common.Terms of Service")
            }}</a>
          </div>
        </div>

        <!-- Mobile Footer -->
        <div class="mobile-footer">
          <p class="footer-text">{{ $t("common.copyright_reserved") }}</p>
          <div class="footer-links">
            <a :href="getLocalizedPath('privacy-policy')" class="footer-link">{{
              $t("common.Privacy Policy")
            }}</a>
            <span class="footer-separator">•</span>
            <a :href="getLocalizedPath('terms-of-use')" class="footer-link">{{
              $t("common.Terms of Service")
            }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useLocalization } from "@/composables/useLocalization";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const { getLocalizedPath } = useLocalization();

const desktopImage = computed(() => {
  try {
    return new URL("@/assets/images/register.png", import.meta.url).href;
  } catch {
    return "/src/assets/images/register.png";
  }
});

const mobileImage = computed(() => {
  try {
    return new URL("@/assets/images/register-mobile.png", import.meta.url).href;
  } catch {
    return "/src/assets/images/register-mobile.png";
  }
});

const logoImage = computed(() => {
  try {
    return new URL("@/assets/images/readybill.png", import.meta.url).href;
  } catch {
    return "/src/assets/images/readybill.png";
  }
});
</script>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8f9fa;
  padding: 20px;
}

.auth-card {
  display: flex;
  width: 100%;
  max-width: 1100px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  min-height: 600px;
}

.auth-left {
  flex: 1;
  position: relative;
  display: none;
}

.image-container {
  width: 100%;
  height: 100%;
  position: relative;
}

.auth-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.auth-overlay {
  position: absolute;
  bottom: 40px;
  left: 40px;
  right: 40px;
  color: white;
}

.auth-overlay h3 {
  font-size: 28px;
  font-weight: 700;
  line-height: 1.3;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  margin: 0;
}

.auth-right {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.mobile-header {
  display: none;
}

.auth-content {
  padding: 60px 50px;
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

/* ── Desktop Logo Group ── */
.desktop-logo {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 40px;
}

.logo {
  height: 42px;
  width: auto;
}

.logo-pipe {
  color: #d0d5dd;
  font-size: 24px;
  font-weight: 200;
  line-height: 1;
  user-select: none;
  margin-top: -2px;
}

.agents-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 11px 3px 10px;
  background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
  border: 1px solid #c7d2fe;
  border-radius: 20px;
  font-size: 11.5px;
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: #4338ca;
  line-height: 1.5;
  box-shadow: 0 1px 4px rgba(99, 102, 241, 0.15);
  transition: box-shadow 0.2s ease;
}

.agents-badge:hover {
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
}

/* ── Mobile Logo Group ── */
.mobile-logo {
  height: 36px;
  width: auto;
}

.mobile-logo-group {
  display: flex;
  align-items: center;
  gap: 12px;
  filter: drop-shadow(0 2px 10px rgba(0, 0, 0, 0.3));
}

.mobile-pipe {
  color: rgba(255, 255, 255, 0.5);
  font-size: 22px;
  font-weight: 200;
  line-height: 1;
  user-select: none;
  margin-top: -2px;
}

.mobile-agents-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 11px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 20px;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #ffffff;
  line-height: 1.5;
}

/* Desktop Footer */
.desktop-footer {
  padding: 24px 50px;
  border-top: 1px solid #e9ecef;
  text-align: center;
  background: #fafbfc;
}

.mobile-footer {
  display: none;
}

.footer-text {
  font-size: 13px;
  color: #6c757d;
  margin: 0 0 8px 0;
}

.footer-links {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
}

.footer-link {
  font-size: 13px;
  color: #0d6efd;
  text-decoration: none;
  transition: all 0.2s ease;
}

.footer-link:hover {
  text-decoration: underline;
  color: #0b5ed7;
}

.footer-separator {
  color: #dee2e6;
  font-size: 12px;
}

/* Desktop View */
@media (min-width: 768px) {
  .auth-left {
    display: block;
  }

  .mobile-header {
    display: none !important;
  }

  .desktop-footer {
    display: block;
  }

  .mobile-footer {
    display: none !important;
  }
}

/* Mobile View */
@media (max-width: 767px) {
  .auth-layout {
    padding: 0;
    background-color: #f8f9fa;
    align-items: flex-start;
    min-height: auto;
  }

  .auth-card {
    border-radius: 0;
    box-shadow: none;
    min-height: 100vh;
    flex-direction: column;
  }

  .mobile-header {
    display: block;
    width: 100%;
  }

  .mobile-image-wrapper {
    position: relative;
    width: 100%;
    height: 520px;
    overflow: hidden;
  }

  .mobile-banner {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
  }

  .mobile-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.2) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mobile-logo {
    filter: brightness(0) invert(1);
  }

  .desktop-logo {
    display: none;
  }

  .auth-right {
    background: white;
    border-radius: 24px 24px 0 0;
    margin-top: -24px;
    position: relative;
    z-index: 1;
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  .auth-content {
    padding: 32px 24px;
    flex: 1;
    overflow-y: auto;
  }

  .desktop-footer {
    display: none;
  }

  .mobile-footer {
    display: block;
    padding: 20px 24px;
    border-top: 1px solid #e9ecef;
    background: #fff;
    text-align: center;
    flex-shrink: 0;
  }

  .mobile-footer .footer-text {
    font-size: 12px;
    margin-bottom: 8px;
  }

  .mobile-footer .footer-links {
    gap: 10px;
  }

  .mobile-footer .footer-link {
    font-size: 12px;
  }
}

@media (max-width: 374px) {
  .mobile-image-wrapper {
    height: 200px;
  }

  .mobile-logo {
    height: 30px;
  }

  .mobile-agents-badge {
    font-size: 10px;
    padding: 2px 8px;
  }

  .auth-right {
    border-radius: 20px 20px 0 0;
    margin-top: -20px;
  }

  .auth-content {
    padding: 28px 20px;
  }

  .mobile-footer {
    padding: 18px 20px;
  }
}
</style>
