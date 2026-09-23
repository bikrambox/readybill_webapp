<template>
  <footer class="rb-footer bg-dark text-white py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 mb-4 mb-lg-0">
          <div class="d-flex align-items-center mb-3">
            <img
              src="@/assets/images/ready-bill-white-logo.png"
              alt="ReadyBill Logo"
              height="50"
              class="me-2"
            />
          </div>
          <p class="rb-footer-description">
            {{ $t("common.fastest_and_easiest") }}
          </p>
        </div>

        <div class="col-6 col-lg-2">
          <h6 class="fw-bold mb-3 rb-footer-heading">Product</h6>
          <ul class="list-unstyled rb-footer-list">
            <li class="mb-2">
              <a :href="getLocalizedPath('features')" class="rb-footer-link">{{
                $t("common.Features")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('pricing')" class="rb-footer-link">{{
                $t("common.Pricing")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('mobile-app')" class="rb-footer-link">{{
                $t("common.Mobile App")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('integrations')" class="rb-footer-link">{{
                $t("common.Integrations")
              }}</a>
            </li>
          </ul>
        </div>

        <div class="col-6 col-lg-2">
          <h6 class="fw-bold mb-3 rb-footer-heading">{{ $t("common.Company") }}</h6>
          <ul class="list-unstyled rb-footer-list">
            <li class="mb-2">
              <a :href="getLocalizedPath('about')" class="rb-footer-link">{{
                $t("common.About")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('contact')" class="rb-footer-link">{{
                $t("common.Contact")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('agents')" class="rb-footer-link">{{
                $t("common.Agents")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('career')" class="rb-footer-link">{{
                $t("common.Careers")
              }}</a>
            </li>
          </ul>
        </div>

        <div class="col-lg-2">
          <h6 class="fw-bold mb-3 rb-footer-heading">{{ $t("common.Legal") }}</h6>
          <ul class="list-unstyled rb-footer-list">
            <li class="mb-2">
              <a :href="getLocalizedPath('privacy-policy')" class="rb-footer-link">{{
                $t("common.Privacy Policy")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('terms-of-use')" class="rb-footer-link">{{
                $t("common.Terms of Use")
              }}</a>
            </li>
            <li class="mb-2">
              <a :href="getLocalizedPath('cookie-policy')" class="rb-footer-link">{{
                $t("common.Cookie Policy")
              }}</a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Increased Divider Size -->
      <hr class="border-secondary my-4 rb-footer-divider" />

      <!-- UPDATED BOTTOM SECTION WITH MOBILE SPACING -->
      <div class="row align-items-center rb-footer-bottom">
        <!-- LEFT: Two Logos with Divider -->
        <div class="col-6 col-md-4 mb-3 mb-md-0 order-1">
          <div class="d-flex align-items-center gap-2 rb-footer-logos">
            <div class="rb-footer-badge d-flex align-items-center justify-content-center">
              <img
                src="@/assets/images/alegra-logo.png"
                alt="Alegra labs Logo"
                height="24"
              />
            </div>
            <span class="text-white-50 rb-divider">|</span>
            <div class="rb-footer-badge d-flex align-items-center justify-content-center">
              <img
                src="@/assets/images/ready-bill-white-logo.png"
                alt="ReadyBill Logo"
                height="24"
              />
            </div>
          </div>
        </div>

        <!-- CENTER: Copyright Text (moves to bottom on mobile) -->
        <div class="col-12 col-md-4 text-center mb-3 mb-md-0 order-3 order-md-2">
          <p class="mb-0 small rb-copyright">
            {{ $t("common.copyright") }}
            <a
              href="https://www.alegralabs.com"
              target="_blank"
              rel="noopener noreferrer"
              class="rb-footer-link-primary"
            >
              Alegra Labs
            </a>
          </p>
        </div>

        <!-- RIGHT: Social Icons -->
        <div class="col-6 col-md-4 mb-3 mb-md-0 order-2 order-md-3">
          <div
            class="d-flex justify-content-end align-items-center gap-2 rb-social-icons"
          >
            <a
              href="https://x.com/LabsAlegra"
              class="rb-social-icon-link"
              target="_blank"
              rel="noopener noreferrer"
            >
              <div class="rb-social-icon-badge">
                <img
                  src="@/assets/images/scoial-icons/twittr.png"
                  alt="Twitter"
                  class="img-fluid"
                />
              </div>
            </a>
            <a
              href="https://www.facebook.com/Alegralabs/"
              class="rb-social-icon-link"
              target="_blank"
              rel="noopener noreferrer"
            >
              <div class="rb-social-icon-badge">
                <img
                  src="@/assets/images/scoial-icons/fb.png"
                  alt="Facebook"
                  class="img-fluid"
                />
              </div>
            </a>
            <a
              href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all"
              class="rb-social-icon-link"
              target="_blank"
              rel="noopener noreferrer"
            >
              <div class="rb-social-icon-badge">
                <img
                  src="@/assets/images/scoial-icons/in.png"
                  alt="LinkedIn"
                  class="img-fluid"
                />
              </div>
            </a>

            <a
              href="https://www.instagram.com/alegralabs7/"
              class="rb-social-icon-link"
              target="_blank"
              rel="noopener noreferrer"
            >
              <div class="rb-social-icon-badge">
                <img
                  src="@/assets/images/scoial-icons/insta.png"
                  alt="Instagram"
                  class="img-fluid"
                />
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { useLocalization } from "@/composables/useLocalization";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const { getLocalizedPath } = useLocalization();
</script>
