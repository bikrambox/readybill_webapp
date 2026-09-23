<template>
  <div id="app">
    <!-- Global Loader -->
    <GlobalLoader v-if="!hasError" />

    <!-- Error Page (if error occurs) -->
    <ErrorPage
      v-if="hasError"
      :error-code="errorInfo?.type || 'ERROR'"
      :error-title="errorInfo?.title || 'Application Error'"
      :error-message="errorInfo?.message || 'Something went wrong'"
      :error-details="errorInfo?.details"
      :show-retry="true"
      :home-url="'/'"
      @retry="handleRetry"
    />

    <!-- Main App Content -->
    <component v-else :is="layout">
      <router-view />
    </component>
  </div>
</template>

<script setup>
// Import CSS files
import "@/modules/Core/assets/css/home/navbar.css";
import "@/modules/Core/assets/css/home/mobile-app.css";
import "@/modules/Core/assets/css/home/footer.css";

import {
  computed,
  watch,
  ref,
  onErrorCaptured,
  onMounted,
  getCurrentInstance,
  defineAsyncComponent,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import MainLayout from "@/modules/Core/layouts/MainLayout.vue";
import GlobalLoader from "@/modules/Core/components/GlobalLoader.vue";
import ErrorPage from "@/components/ErrorPage.vue";
import DashboardLayout from "@/modules/GroceryGermany/components/DashboardLayout.vue";
import { useI18n } from "vue-i18n";
import { useLocaleStore } from "@/stores/localeStore";
import { useLoadingStore } from "@/stores/loadingStore";

import AgentDashboardLayout from "@/modules/AuthorizedAgents/components/DashboardLayout.vue";

const route = useRoute();
const router = useRouter();
const { locale, setLocaleMessage } = useI18n();
const localeStore = useLocaleStore();
const loadingStore = useLoadingStore();

// Error state
const hasError = ref(false);
const errorInfo = ref(null);

// Handle retry
const handleRetry = () => {
  hasError.value = false;
  errorInfo.value = null;
  localeStore.clearError();
  window.location.reload();
};

const { appContext } = getCurrentInstance();
const countryName = appContext.config.globalProperties.$countryName || "India";

// Watch for store errors
watch(
  () => localeStore.hasError,
  (newHasError) => {
    if (newHasError) {
      console.error("Store error detected:", localeStore.errorInfo);
      hasError.value = true;
      errorInfo.value = {
        type: localeStore.errorInfo?.type || "STORE_ERROR",
        title: "Localization Error",
        message:
          localeStore.errorInfo?.message || "Failed to load localization data",
        details: localeStore.errorInfo?.details,
      };
      loadingStore.hideLoading();
    }
  },
  { immediate: true }
);

// Watch for route language changes
watch(
  () => route.params.lang,
  async (newLang) => {
    if (hasError.value) return; // Skip if error state

    console.log(
      "App watcher triggered: newLang =",
      newLang,
      ", current locale =",
      locale.value
    );

    if (newLang && newLang !== locale.value) {
      console.log(
        "App: Language needs update from",
        locale.value,
        "to",
        newLang
      );

      try {
        const country = route.params.country || "in";

        // Show loading
        loadingStore.showLoading("Switching language...");

        // Update store
        const result = await localeStore.setLocale(country, newLang);

        if (!result.success) {
          throw new Error(result.message || "Failed to set locale");
        }

        // Load translations
        const translations = await localeStore.loadTranslations(newLang);

        if (translations) {
          console.log(
            "App: Setting translations for",
            newLang,
            Object.keys(translations)
          );
          setLocaleMessage(newLang, translations);
          locale.value = newLang;
          console.log("App: Locale successfully updated to", locale.value);
        } else {
          throw new Error(`Failed to load translations for ${newLang}`);
        }

        // Hide loading
        loadingStore.hideLoading();

        window.location.reload();
      } catch (error) {
        console.error("App: Language switch failed:", error);
        loadingStore.hideLoading();

        hasError.value = true;
        errorInfo.value = {
          type: "LANGUAGE_SWITCH_ERROR",
          title: "Failed to Switch Language",
          message:
            "We could not switch to the selected language. Please try again.",
          details: error.message,
        };
      }
    } else {
      console.log("App: No update needed, languages match");
    }
  },
  { immediate: true }
);

// // Dynamic layout based on route meta
// const layout = computed(() => {
//   const layoutType = route.meta.layout || 'main'

//   console.log('layoutType',layoutType);

//   // return layoutType === 'dashboard' ? DashboardLayout : MainLayout

//     if(layoutType === 'dashboard'){
//       return DashboardLayout;
//     }
//     else if(layoutType === 'MainLayout'){
//       return MainLayout;
//     }
//     else{
//       return 'main';
//     }

// })

// const layout = computed(() => {
//   const layoutType = route.meta.layout || "main";

//   // AuthorizedAgents dashboard layout
//   if (layoutType === "agentDashboard") {
//     return AgentDashboardLayout;
//   }

//   if (layoutType === "dashboard") {
//     const moduleName = `Grocery${countryName.replace(/\s+/g, "")}`;

//     return defineAsyncComponent(() =>
//       import(`@/modules/${moduleName}/components/DashboardLayout.vue`).catch(
//         () => {
//           console.warn(
//             `No dashboard layout found for ${moduleName}, fallback used`
//           );
//           return import("@/modules/Core/layouts/MainLayout.vue");
//         }
//       )
//     );
//   }

//   if (layoutType === "MainLayout") {
//     return MainLayout;
//   }

//   return "main";
// });

const layout = computed(() => {
  const layoutType = route.meta.layout || "main";

  // AuthorizedAgents dashboard layout
  if (layoutType === "agentDashboard") {
    return AgentDashboardLayout;
  }

  // Grocery module dynamic dashboard layout
  if (layoutType === "dashboard") {
    const moduleName = `Grocery${countryName.replace(/\s+/g, "")}`;
    return defineAsyncComponent(() =>
      import(`@/modules/${moduleName}/components/DashboardLayout.vue`).catch(
        () => {
          console.warn(
            `No dashboard layout found for ${moduleName}, fallback used`
          );
          return import("@/modules/Core/layouts/MainLayout.vue");
        }
      )
    );
  }

  if (layoutType === "MainLayout") {
    return MainLayout;
  }

  return "main";
});

// Capture errors in child components
onErrorCaptured((err, instance, info) => {
  console.error("Error captured in App.vue:", err, info);

  loadingStore.hideLoading();

  hasError.value = true;
  errorInfo.value = {
    type: "COMPONENT_ERROR",
    title: "Component Error",
    message: "A component encountered an error. Please refresh the page.",
    details: `${err.message}\n\nComponent: ${
      instance?.$options?.name || "Unknown"
    }\n\nInfo: ${info}`,
  };

  // Prevent error from propagating
  return false;
});

// Check initialization on mount
onMounted(() => {
  // Set a timeout to check if app is stuck
  setTimeout(() => {
    if (loadingStore.isLoading && !localeStore.isInitialized) {
      console.error("App initialization timeout");
      loadingStore.hideLoading();

      hasError.value = true;
      errorInfo.value = {
        type: "TIMEOUT",
        title: "Initialization Timeout",
        message:
          "The application is taking too long to load. Please check your internet connection and try again.",
        details: "Application failed to initialize within 30 seconds",
      };
    }
  }, 30000); // 30 second timeout
});
</script>

<style>
#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Breadcrum style */
.breadcrumb {
  margin-bottom: 10px;
  background: transparent;
  padding: 0;
  font-size: 14px;
}

.breadcrumb-item a {
  color: #6c757d;
  text-decoration: none;
}

.breadcrumb-item.active {
  color: #333 !important;
}
/* Breadcrum style */
</style>
