<template>
  <div class="dropdown">
    <button
      class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1 gap-sm-2 px-2 px-sm-3 rounded-pill"
      type="button"
      id="languageDropdown"
      data-bs-toggle="dropdown"
      aria-expanded="false"
      style="min-width: 45px"
    >
      <i class="bi bi-globe fs-6"></i>

      <!-- Show language name on all screen sizes -->
      <span class="small">
        {{ currentLanguage?.language || "EN" }}
      </span>
    </button>

    <ul
      class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3"
      aria-labelledby="languageDropdown"
      style="min-width: 150px; max-width: 90vw"
    >
      <li v-for="lang in languages" :key="lang.short_code">
        <a
          class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3"
          :class="{
            'active bg-primary bg-opacity-10 text-primary':
              localeStore.locale === lang.short_code,
          }"
          href="#"
          @click.prevent="changeLanguage(lang.short_code)"
        >
          <span class="d-flex align-items-center gap-2">
            <!-- Flag -->
            <!-- <span v-if="lang.flag">{{ lang.flag }}</span> -->
            <!-- Language text -->
            <span class="fw-medium">{{ lang.language }}</span>
          </span>
          <!-- Checkmark for active language -->
          <i
            v-if="localeStore.locale === lang.short_code"
            class="bi bi-check-lg text-primary fw-bold"
          ></i>
        </a>
      </li>

      <li v-if="loading">
        <span class="dropdown-item text-muted py-2">
          <span class="spinner-border spinner-border-sm me-2"></span>
          {{ $t("common.Loading") }}...
        </span>
      </li>

      <li v-if="error && !loading">
        <span class="dropdown-item text-danger py-2">
          <i class="bi bi-exclamation-circle me-2"></i>
          {{ $t("common.Failed to load") }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/config/api";
import { useLocaleStore } from "@/stores/localeStore";

const router = useRouter();
const route = useRoute();
const localeStore = useLocaleStore();

const languages = ref([]);
const loading = ref(false);
const error = ref(null);

// Fixed: Uses both store locale AND route for proper reactivity
const currentLanguage = computed(() => {
  // Wait for languages to load
  if (languages.value.length === 0) {
    return null;
  }

  // Priority 1: Use store locale (updated after language change)
  if (localeStore.locale) {
    const langFromStore = languages.value.find(
      (lang) => lang.short_code === localeStore.locale
    );
    if (langFromStore) {
      console.log("📱 Current language from store:", langFromStore);
      return langFromStore;
    }
  }

  // Priority 2: Extract from current route URL
  const pathParts = route.path.split("/").filter(Boolean);
  const urlLang = pathParts[1]?.toLowerCase();

  if (urlLang) {
    const langFromUrl = languages.value.find(
      (lang) => lang.short_code === urlLang
    );
    if (langFromUrl) {
      console.log("📱 Current language from URL:", langFromUrl);
      return langFromUrl;
    }
  }

  // Fallback: Return first language
  return languages.value[0];
});

// Watch route changes to ensure button updates
watch(
  () => route.path,
  (newPath) => {
    console.log("📱 Route changed:", newPath);
    const pathParts = newPath.split("/").filter(Boolean);
    const urlLang = pathParts[1]?.toLowerCase();
    console.log("📱 Language from URL:", urlLang);
  },
  { immediate: true }
);

const fetchLanguages = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await api.get("/all-lanaguages");

    if (response.data.status === 1 && response.data.data) {
      languages.value = response.data.data;
    } else {
      throw new Error("Invalid response format");
    }

    console.log("all-languages", response.data.data);
  } catch (err) {
    console.error("Error fetching languages:", err);
    error.value = err.message;

    // Fallback to default languages if API fails
    languages.value = [
      { short_code: "en", language: "English", flag: "🇬🇧" },
      { short_code: "de", language: "Deutsch", flag: "🇩🇪" },
    ];
  } finally {
    loading.value = false;
  }
};

// const changeLanguage = (langCode) => {
//   if (!langCode || typeof langCode !== "string") {
//     console.error("❌ Invalid language code:", langCode);
//     return;
//   }

//   console.log(`🌐 Changing language to: ${langCode}`);

//   // Get current route path
//   const currentPath = route.path;
//   const pathParts = currentPath.split("/").filter(Boolean);

//   // Extract country, language, and rest of path
//   const urlCountry = pathParts[0]?.toLowerCase() || "in";
//   const restOfPath = pathParts.slice(2).join("/");
//   const pathSuffix = restOfPath ? `/${restOfPath}` : "";

//   // Set locale in store with BOTH country and language
//   localeStore.setLocale(urlCountry, langCode);

//   // Build new URL with same country but new language
//   const newUrl = `/${urlCountry}/${langCode}${pathSuffix}`;

//   console.log(`🔄 Redirecting to: ${newUrl}`);

//   // Navigate to the new URL
//   router.push(newUrl).catch((err) => {
//     console.error("❌ Navigation error:", err);
//   });
// };

const changeLanguage = async (langCode) => {
  if (!langCode || typeof langCode !== "string") {
    console.error("❌ Invalid language code:", langCode);
    return;
  }

  console.log(`🌐 Changing language to: ${langCode}`);

  try {
    // Call switch-language API before navigating
    await api.post("/switch-language", { lang: langCode });

    // Get current route path
    const currentPath = route.path;
    const pathParts = currentPath.split("/").filter(Boolean);

    // Extract country, language, and rest of path
    const urlCountry = pathParts[0]?.toLowerCase() || "in";
    const restOfPath = pathParts.slice(2).join("/");
    const pathSuffix = restOfPath ? `/${restOfPath}` : "";

    // Set locale in store with BOTH country and language
    localeStore.setLocale(urlCountry, langCode);

    // Build new URL with same country but new language
    const newUrl = `/${urlCountry}/${langCode}${pathSuffix}`;

    console.log(`🔄 Redirecting to: ${newUrl}`);

    // Navigate to the new URL
    router.push(newUrl).catch((err) => {
      console.error("❌ Navigation error:", err);
    });
  } catch (err) {
    console.error("❌ Failed to switch language:", err);
  }
};


onMounted(() => {
  fetchLanguages();
});
</script>

<style scoped>
/* Minimal custom CSS - Bootstrap 5 handles most */
.dropdown-item {
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

/* Remove min-width on very small screens */
@media (max-width: 576px) {
  .btn {
    min-width: 40px !important;
  }
}
</style>
