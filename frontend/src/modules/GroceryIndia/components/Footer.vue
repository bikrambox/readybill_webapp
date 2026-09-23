<template>
  <footer class="app-footer">
    <div class="footer-content" :class="{ 'desktop-layout': isLargeDesktop }">
      <!-- Large Desktop Layout (>1200px) -->
      <template v-if="isLargeDesktop">
        <div class="footer-left">
          <p class="copyright">
            {{ $t("common.copyright_line") }}
            <a href="https://www.alegralabs.com" target="_blank" class="developer-link"
              >Alegra Labs</a
            >
          </p>
        </div>

        <div class="footer-right">
          <div class="social-links">
            <a href="https://x.com/LabsAlegra" aria-label="Twitter"
              ><i class="bi bi-twitter-x"></i
            ></a>
            <a href="https://www.facebook.com/Alegralabs/" aria-label="Facebook"
              ><i class="bi bi-facebook"></i
            ></a>
            <a
              href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all"
              aria-label="LinkedIn"
              ><i class="bi bi-linkedin"></i
            ></a>
            <a href="https://www.instagram.com/alegralabs7/" aria-label="Instagram"
              ><i class="bi bi-instagram"></i
            ></a>
          </div>
          <div class="footer-links">
            <router-link :to="{ name: 'About' }">{{ $t("common.About") }}</router-link>
            <router-link :to="{ name: 'Contact' }">{{
              $t("common.Contact")
            }}</router-link>
            <router-link :to="{ name: 'Agents' }">{{ $t("common.Agents") }}</router-link>
            <router-link :to="{ name: 'PrivacyPolicy' }">{{
              $t("common.Privacy Policy")
            }}</router-link>
            <router-link :to="{ name: 'TermsOfUse' }">{{
              $t("common.Terms of Use")
            }}</router-link>
          </div>
        </div>
      </template>

      <!-- Centered Layout (All other screens) -->
      <template v-else>
        <div class="centered-footer-content">
          <div class="social-links">
            <a href="https://x.com/LabsAlegra" aria-label="Twitter"
              ><i class="bi bi-twitter-x"></i
            ></a>
            <a href="https://www.facebook.com/Alegralabs/" aria-label="Facebook"
              ><i class="bi bi-facebook"></i
            ></a>
            <a
              href="https://www.linkedin.com/company/helix-enterprise/posts/?feedView=all"
              aria-label="LinkedIn"
              ><i class="bi bi-linkedin"></i
            ></a>
            <a href="https://www.instagram.com/alegralabs7/" aria-label="Instagram"
              ><i class="bi bi-instagram"></i
            ></a>
          </div>
          <div class="footer-links">
            <router-link :to="{ name: 'About' }">{{ $t("common.About") }}</router-link>
            <router-link :to="{ name: 'Contact' }">{{
              $t("common.Contact")
            }}</router-link>
            <router-link :to="{ name: 'Agents' }">{{ $t("common.Agents") }}</router-link>
            <router-link :to="{ name: 'PrivacyPolicy' }">{{
              $t("common.Privacy Policy")
            }}</router-link>
            <router-link :to="{ name: 'TermsOfUse' }">{{
              $t("common.Terms of Use")
            }}</router-link>
          </div>
          <p class="copyright">
            {{ $t("common.copyright_line") }}
            <a href="https://www.alegralabs.com" target="_blank" class="developer-link"
              >Alegra Labs</a
            >
          </p>
        </div>
      </template>
    </div>
  </footer>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";

import { useI18n } from "vue-i18n";
const { t } = useI18n();

const isLargeDesktop = ref(false);

const checkScreenSize = () => {
  isLargeDesktop.value = window.innerWidth > 1200;
};

onMounted(() => {
  checkScreenSize();
  window.addEventListener("resize", checkScreenSize);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkScreenSize);
});
</script>

<style scoped>
.app-footer {
  background-color: #ffffff;
  border-top: 1px solid #e9ecef;
  padding: 25px 20px;
  margin-top: auto;
  width: 100%;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
}

/* Large Desktop Layout (>1200px) - Left/Right Split */
.footer-content.desktop-layout {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 30px;
}

.footer-left {
  flex: 0 1 auto;
}

.footer-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 15px;
  flex: 0 1 auto;
}

/* Centered Layout (All other screens) */
.centered-footer-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  text-align: center;
}

.social-links {
  display: flex;
  gap: 15px;
  justify-content: center;
}

/* Desktop: Social icons aligned to right */
.footer-content.desktop-layout .social-links {
  justify-content: flex-end;
}

.social-links a {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: #f8f9fa;
  color: #6c757d;
  text-decoration: none;
  transition: all 0.2s;
  font-size: 16px;
}

.social-links a:hover {
  background-color: #0066cc;
  color: white;
  transform: translateY(-2px);
}

.footer-links {
  display: flex;
  flex-wrap: wrap;
  gap: 15px 20px;
  justify-content: center;
  align-items: center;
}

/* Desktop: Links aligned to right */
.footer-content.desktop-layout .footer-links {
  justify-content: flex-end;
}

.footer-links a {
  color: #6c757d;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.2s;
  white-space: nowrap;
}

.footer-links a:hover {
  color: #0066cc;
}

.copyright {
  color: #6c757d;
  font-size: 14px;
  margin: 0;
  line-height: 1.6;
}

.developer-link {
  color: #0066cc;
  text-decoration: none;
  font-weight: 500;
}

.developer-link:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .app-footer {
    padding: 25px 15px;
  }
}

@media (max-width: 480px) {
  .app-footer {
    padding: 20px 15px;
  }

  .social-links {
    gap: 12px;
  }

  .social-links a {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }

  .footer-links {
    gap: 10px 15px;
  }

  .footer-links a {
    font-size: 13px;
  }

  .copyright {
    font-size: 13px;
  }
}
</style>
