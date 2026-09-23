<template>
  <div id="app">

    <!-- Global Loader -->
    <GlobalLoader />

    <component :is="layout">
      <router-view />
    </component>
  </div>
</template>

<script setup>

// Import CSS files
import '@/modules/Core/assets/css/home/navbar.css'
import '@/modules/Core/assets/css/home/mobile-app.css'
import '@/modules/Core/assets/css/home/footer.css'

import { computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/modules/Core/layouts/MainLayout.vue'
import GlobalLoader from '@/modules/Core/components/GlobalLoader.vue'
import DashboardLayout from '@/modules/GroceryIndia/components/DashboardLayout.vue'
import { useI18n } from 'vue-i18n'
import { useLocaleStore } from '@/stores/localeStore'
import { useLoadingStore } from '@/stores/loadingStore'


const route = useRoute()
const { locale, setLocaleMessage } = useI18n()
const localeStore = useLocaleStore()
const loadingStore = useLoadingStore()

// Watch for route language changes with immediate: true
watch(
  () => route.params.lang,
  async (newLang) => {
    console.log('App watcher triggered: newLang =', newLang, ', current locale =', locale.value)
    
    if (newLang && newLang !== locale.value) {
      console.log('App: Language needs update from', locale.value, 'to', newLang)
      
      const country = route.params.country || 'in'
      
      // Update store
      await localeStore.setLocale(country, newLang)
      
      // Load translations
      const translations = await localeStore.loadTranslations(newLang)
      
      if (translations) {
        console.log('App: Setting translations for', newLang, Object.keys(translations))
        setLocaleMessage(newLang, translations)
        locale.value = newLang
        console.log('App: Locale successfully updated to', locale.value)
      } else {
        console.error('App: Failed to load translations for', newLang)
      }
    } else {
      console.log('App: No update needed, languages match')
    }
  },
  { immediate: true } // Run immediately on mount
)

// Dynamic layout based on route meta
const layout = computed(() => {
  const layoutType = route.meta.layout || 'main'
  return layoutType === 'dashboard' ? DashboardLayout : MainLayout
})
</script>

<style>
#app {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
